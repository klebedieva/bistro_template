<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return Review[] Returns an array of approved reviews (limited to 3)
     */
    public function findApprovedReviews(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isApproved = :approved')
            ->andWhere('r.menuItem IS NULL')
            ->setParameter('approved', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Review[] Returns an array of all reviews
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Review[] Returns an array of approved reviews ordered by date
     */
    public function findApprovedOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isApproved = :approved')
            ->andWhere('r.menuItem IS NULL')
            ->setParameter('approved', true)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compute count and average rating of approved reviews for a given dish.
     *
     * @return array{cnt:int, avg:float}
     */
    public function getApprovedStatsForMenuItem(int $menuItemId): array
    {
        $row = $this->createQueryBuilder('r')
            ->select('COUNT(r.id) AS cnt, COALESCE(AVG(r.rating), 0) AS avg')
            ->andWhere('r.menuItem = :id')
            ->andWhere('r.isApproved = 1')
            ->setParameter('id', $menuItemId)
            ->getQuery()
            ->getSingleResult();

        return [
            'cnt' => (int)($row['cnt'] ?? 0),
            'avg' => (float)($row['avg'] ?? 0.0),
        ];
    }
}