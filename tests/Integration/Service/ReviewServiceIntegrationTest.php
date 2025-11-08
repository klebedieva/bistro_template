<?php

namespace App\Tests\Integration\Service;

use App\DTO\ReviewCreateRequest;
use App\Entity\Review;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Integration test suite that boots Symfony and hits the in-memory database.
 * Ideal for newcomers: it shows how to wire up the container, call the real `ReviewService`,
 * and assert that Doctrine persisted the data we expect.
 */
final class ReviewServiceIntegrationTest extends KernelTestCase
{
    /**
     * Real Doctrine entity manager used for schema resets and repository assertions.
     */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Always start by shutting down any previous kernel instance.
        // Avoids sharing state between integration tests.
        self::ensureKernelShutdown();

        // Point Doctrine to an in-memory SQLite database. Fast, isolated, disposable.
        $sqliteUrl = 'sqlite:///:memory:';
        putenv('DATABASE_URL=' . $sqliteUrl);
        $_ENV['DATABASE_URL'] = $sqliteUrl;
        $_SERVER['DATABASE_URL'] = $sqliteUrl;

        // Boot Symfony's kernel and collect the services we need.
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Drop and recreate every table so each test starts from scratch.
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Cleanly close Doctrine and shut down the kernel between tests.
        $this->entityManager->close();
        self::ensureKernelShutdown();
    }

    public function testCreateReviewPersistsEntityWithModerationDisabled(): void
    {
        // --- Arrange ---------------------------------------------------------------------------------------------
        // Mimic a review form submission by filling the DTO manually.
        $dto = new ReviewCreateRequest();
        $dto->name = 'Alice';
        $dto->email = 'alice@example.test';
        $dto->rating = 5;
        $dto->comment = 'Fantastic experience!';

        // Use the real service from the container for a full integration flow.
        $service = new ReviewService($this->entityManager);

        // --- Act -------------------------------------------------------------------------------------------------
        // Persist the review; the service should validate data and set moderation flags.
        $review = $service->createReview($dto);

        // --- Assert ----------------------------------------------------------------------------------------------
        // Validate the hydrated entity to ensure all fields were saved correctly.
        self::assertNotNull($review->getId());
        self::assertSame('Alice', $review->getName());
        self::assertSame('alice@example.test', $review->getEmail());
        self::assertSame(5, $review->getRating());
        self::assertSame('Fantastic experience!', $review->getComment());
        self::assertFalse($review->isIsApproved());

        // Finally, query Doctrine to confirm the record truly landed in the database.
        $persisted = $this->entityManager->getRepository(Review::class)->findAll();
        self::assertCount(1, $persisted);
    }
}

