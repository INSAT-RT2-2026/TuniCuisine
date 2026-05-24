<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
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

    /**
     * @return Recipe[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('r')
            ->distinct()
            ->leftJoin('r.region', 'region')
            ->leftJoin('r.recipeIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'ingredient')
            ->where('r.name LIKE :query')
            ->orWhere('r.description LIKE :query')
            ->orWhere('region.name LIKE :query')
            ->orWhere('ingredient.name LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Recipe[]
     */
    public function findByRegion(int $regionId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.region = :regionId')
            ->setParameter('regionId', $regionId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
