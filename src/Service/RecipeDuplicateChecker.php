<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\RecipeRepository;

final class RecipeDuplicateChecker
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    /**
     * @return string[]
     */
    public function findSimilarNames(string $name, ?int $excludeRecipeId = null): array
    {
        return $this->recipeRepository->findSimilarPublishedNames($name, $excludeRecipeId);
    }

    public function hasSimilar(string $name, ?int $excludeRecipeId = null): bool
    {
        return $this->findSimilarNames($name, $excludeRecipeId) !== [];
    }
}
