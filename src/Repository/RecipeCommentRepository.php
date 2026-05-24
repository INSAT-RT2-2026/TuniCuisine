<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\RecipeComment;
use App\Enum\ModerationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeComment>
 */
class RecipeCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeComment::class);
    }

    /**
     * @return RecipeComment[]
     */
    public function findPublishedForRecipe(Recipe $recipe): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.author', 'a')
            ->addSelect('a')
            ->andWhere('c.recipe = :recipe')
            ->andWhere('c.status = :status')
            ->setParameter('recipe', $recipe)
            ->setParameter('status', ModerationStatus::Published)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RecipeComment[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.author', 'a')
            ->innerJoin('c.recipe', 'r')
            ->addSelect('a', 'r')
            ->andWhere('c.status = :status')
            ->setParameter('status', ModerationStatus::Pending)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
