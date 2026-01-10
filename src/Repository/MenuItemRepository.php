<?php

namespace App\Repository;

use App\Entity\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItem>
 */
class MenuItemRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, MenuItem::class);
	}

	/**
	 * Find all menu items with badges, tags and allergens loaded (eager loading)
	 * to avoid N+1 queries when accessing relationships.
	 *
	 * @return MenuItem[]
	 */
	public function findAllWithRelations(): array
	{
		return $this->createQueryBuilder('m')
			->leftJoin('m.badges', 'b')
			->addSelect('b')
			->leftJoin('m.tags', 't')
			->addSelect('t')
			->leftJoin('m.allergens', 'a')
			->addSelect('a')
			->orderBy('m.category', 'ASC')
			->addOrderBy('m.id', 'ASC')
			->getQuery()
			->getResult();
	}



	/**
	 * Return lightweight data for related dishes of the same category.
	 * Only selects required fields to avoid hydrating full entities.
	 *
	 * @param string $category Category code used for filtering
	 * @param int $excludeId Current dish id to exclude from the list
	 * @param int $limit Max number of items to return
	 * @return array<int, array{id:string,name:string,description:?string,price:float,image:?string}>
	 */
	public function findRelatedForCard(string $category, int $excludeId, int $limit = 3): array
	{
		$qb = $this->createQueryBuilder('m')
			->select('m.id, m.name, m.description, m.price, m.image')
			->andWhere('m.category = :category')
			->andWhere('m.id <> :exclude')
			->setParameter('category', $category)
			->setParameter('exclude', $excludeId)
			->setMaxResults($limit)
			->orderBy('m.id', 'DESC');

		$rows = $qb->getQuery()->getArrayResult();

		return array_map(static function (array $row): array {
			return [
				'id' => (string)($row['id'] ?? ''),
				'name' => (string)($row['name'] ?? ''),
				'description' => $row['description'] ?? null,
				'price' => (float)($row['price'] ?? 0),
				'image' => $row['image'] ?? null,
			];
		}, $rows);
	}
}
