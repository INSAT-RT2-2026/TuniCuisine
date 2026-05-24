<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Recipe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class RecipeImageResolver
{
  private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

  public function __construct(
    #[Autowire('%kernel.project_dir%')]
    private readonly string $projectDir,
  ) {}

  public function slugify(string $name): string
  {
    $slug = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $name);
    if ($slug === false || $slug === null) {
      $slug = strtolower($name);
    }
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

    return trim($slug, '-') ?: 'recipe';
  }

  /**
   * Resolves recipe image URL: local file in public/images/recipes/{slug}.{ext} first,
   * then stored image URL, then a default placeholder.
   */
  public function resolve(Recipe $recipe): string
  {
    $slug = $this->slugify($recipe->getName());
    $dir = $this->projectDir.'/public/images/recipes';

    foreach (self::EXTENSIONS as $ext) {
      if (is_file($dir.'/'.$slug.'.'.$ext)) {
        return '/images/recipes/'.$slug.'.'.$ext;
      }
    }

    $stored = $recipe->getImage();
    if ($stored !== null && $stored !== '') {
      if (str_starts_with($stored, 'http') || str_starts_with($stored, '/')) {
        return $stored;
      }

      return '/images/recipes/'.$stored;
    }

    return '/images/tunisian_cuisine_banner_1774909075250.png';
  }
}
