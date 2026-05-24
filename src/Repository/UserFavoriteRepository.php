<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\User;
use App\Entity\UserFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFavorite>
 */
class UserFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFavorite::class);
    }

    public function findOneByUserAndRecipe(User $user, Recipe $recipe): ?UserFavorite
    {
        return $this->findOneBy(['user' => $user, 'recipe' => $recipe]);
    }

    /**
     * @return UserFavorite[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.recipe', 'r')
            ->addSelect('r')
            ->andWhere('f.user = :user')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int[]
     */
    public function findRecipeIdsForUser(User $user): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.recipe) as recipeId')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $row): int => (int) $row['recipeId'], $rows);
    }
}
