<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Recipe;
use App\Service\RecipeImageResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RecipeExtension extends AbstractExtension
{
  public function __construct(
    private readonly RecipeImageResolver $imageResolver,
  ) {}

  public function getFunctions(): array
  {
    return [
      new TwigFunction('recipe_image', [$this, 'recipeImage']),
      new TwigFunction('recipe_slug', [$this, 'recipeSlug']),
    ];
  }

  public function recipeImage(Recipe $recipe): string
  {
    return $this->imageResolver->resolve($recipe);
  }

  public function recipeSlug(Recipe $recipe): string
  {
    return $this->imageResolver->slugify($recipe->getName());
  }
}
