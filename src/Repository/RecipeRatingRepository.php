<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\RecipeRating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeRating>
 */
class RecipeRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeRating::class);
    }

    public function findOneByUserAndRecipe(User $user, Recipe $recipe): ?RecipeRating
    {
        return $this->findOneBy(['user' => $user, 'recipe' => $recipe]);
    }

    /**
     * @param list<Recipe> $recipes
     *
     * @return array<int, array{average: float, count: int}>
     */
    public function getAveragesForRecipes(array $recipes): array
    {
        $ids = array_values(array_filter(array_map(
            static fn (Recipe $recipe): ?int => $recipe->getId(),
            $recipes,
        )));

        if ($ids === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('rr')
            ->select('IDENTITY(rr.recipe) AS recipeId', 'AVG(rr.score) AS avgScore', 'COUNT(rr.id) AS cnt')
            ->andWhere('rr.recipe IN (:ids)')
            ->setParameter('ids', $ids)
            ->groupBy('rr.recipe')
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['recipeId']] = [
                'average' => round((float) $row['avgScore'], 1),
                'count' => (int) $row['cnt'],
            ];
        }

        return $map;
    }

    /**
     * @return array{average: float|null, count: int}
     */
    public function getAverageForRecipe(Recipe $recipe): array
    {
        $result = $this->createQueryBuilder('rr')
            ->select('AVG(rr.score) as avgScore', 'COUNT(rr.id) as cnt')
            ->andWhere('rr.recipe = :recipe')
            ->setParameter('recipe', $recipe)
            ->getQuery()
            ->getSingleResult();

        return [
            'average' => $result['avgScore'] !== null ? round((float) $result['avgScore'], 1) : null,
            'count' => (int) $result['cnt'],
        ];
    }
}
