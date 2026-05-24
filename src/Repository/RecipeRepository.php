<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use App\Enum\RecipeStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    private function publishedQb()
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :published')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('published', RecipeStatus::Published);
    }

    /**
     * @return Recipe[]
     */
    public function findPublishedAll(): array
    {
        return $this->publishedQb()
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function searchPublished(string $query): array
    {
        return $this->publishedQb()
            ->distinct()
            ->leftJoin('r.region', 'region')
            ->leftJoin('r.recipeIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'ingredient')
            ->andWhere('r.name LIKE :query OR r.description LIKE :query OR region.name LIKE :query OR ingredient.name LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function findPublishedByRegion(int $regionId): array
    {
        return $this->publishedQb()
            ->andWhere('r.region = :regionId')
            ->setParameter('regionId', $regionId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.submittedBy', 'u')
            ->addSelect('u')
            ->andWhere('r.status = :pending')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('pending', RecipeStatus::Pending)
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function findBySubmitter(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.submittedBy = :userId')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function findSimilarPublishedNames(string $name, ?int $excludeId = null): array
    {
        $qb = $this->publishedQb()
            ->select('r.name')
            ->andWhere('LOWER(r.name) LIKE :name')
            ->setParameter('name', '%'.mb_strtolower(trim($name)).'%');

        if ($excludeId !== null) {
            $qb->andWhere('r.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return array_column($qb->getQuery()->getArrayResult(), 'name');
    }
}
