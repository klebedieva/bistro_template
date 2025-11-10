<?php

namespace App\Tests\Integration\Service;

use App\DTO\ReservationCreateRequest;
use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Integration tests that spin up the real Symfony kernel and hit the database.
 * These tests walk through the complete `ReservationService` flow, helping beginners see
 * how Arrange/Act/Assert translates to Symfony + Doctrine code.
 */
final class ReservationServiceIntegrationTest extends KernelTestCase
{
    /**
     * Real Doctrine entity manager fetched from the container.
     * We use it to reset the schema and assert against persisted entities.
     */
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Safety first: if a previous test left the kernel running we shut it down.
        // Each test needs a fresh container instance to avoid leaking state.
        self::ensureKernelShutdown();

        // Replace the usual DATABASE_URL with an in-memory SQLite database.
        // Using SQLite keeps tests fast and disposable—once the test ends the database disappears.
        $sqliteUrl = 'sqlite:///:memory:';
        putenv('DATABASE_URL=' . $sqliteUrl);
        $_ENV['DATABASE_URL'] = $sqliteUrl;
        $_SERVER['DATABASE_URL'] = $sqliteUrl;

        // Boot Symfony and grab the container so we can fetch real services.
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Drop and recreate every table before each test.
        // This guarantees a pristine database state (no leftovers from previous tests).
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Cleanly close Doctrine's EntityManager and stop the kernel to free resources.
        $this->entityManager->close();
        self::ensureKernelShutdown();
    }

    public function testCreateReservationInitializesPendingReservation(): void
    {
        // --- Arrange ---------------------------------------------------------------------------------------------
        // Create a DTO that mimics what the controller would build from a reservation form.
        $dto = new ReservationCreateRequest();
        $dto->firstName = 'Paul';
        $dto->lastName = 'Martin';
        $dto->email = 'paul.martin@example.test';
        $dto->phone = '0611223344';
        $dto->date = (new \DateTime('+1 day'))->format('Y-m-d');
        $dto->time = '19:00';
        $dto->guests = 4;
        $dto->message = 'Corner table please.';

        // Pull the real service from the container (no mocks).
        // This lets the test exercise Doctrine mapping, validation, etc.
        $service = new ReservationService($this->entityManager);

        // --- Act -------------------------------------------------------------------------------------------------
        // Call the method we want to verify: it should persist and return a populated `Reservation`.
        $reservation = $service->createReservation($dto);

        // --- Assert ----------------------------------------------------------------------------------------------
        // Check the returned entity's fields to ensure data was copied correctly.
        self::assertNotNull($reservation->getId());
        self::assertSame('Paul', $reservation->getFirstName());
        self::assertSame('Martin', $reservation->getLastName());
        self::assertSame('paul.martin@example.test', $reservation->getEmail());
        self::assertSame('Corner table please.', $reservation->getMessage());
        self::assertSame(ReservationStatus::PENDING, $reservation->getStatus());
        self::assertFalse($reservation->isConfirmed());

        // Query the database again to double-check that the record truly exists.
        $persisted = $this->entityManager->getRepository(Reservation::class)->findAll();
        self::assertCount(1, $persisted);
    }
}

