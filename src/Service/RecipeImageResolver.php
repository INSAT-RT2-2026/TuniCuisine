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
   * Resolves recipe image URL: uploaded/stored path first, then slug-based file, then placeholder.
   */
  public function resolve(Recipe $recipe): string
  {
    $stored = $recipe->getImage();
    if ($stored !== null && $stored !== '') {
      $path = $this->normalizePublicPath($stored);
      if (!preg_match('#^https?://#i', $path)) {
        $file = $this->projectDir.'/public'.parse_url($path, PHP_URL_PATH);
        if (is_file($file)) {
          return $path;
        }
      } else {
        return $path;
      }
    }

    $slug = $this->slugify($recipe->getName());
    $dir = $this->projectDir.'/public/images/recipes';

    foreach (self::EXTENSIONS as $ext) {
      if (is_file($dir.'/'.$slug.'.'.$ext)) {
        return '/images/recipes/'.$slug.'.'.$ext;
      }
    }

    $legacyDir = $this->projectDir.'/public/images';
    foreach (self::EXTENSIONS as $ext) {
      if (is_file($legacyDir.'/'.$slug.'.'.$ext)) {
        return '/images/'.$slug.'.'.$ext;
      }
    }

    return '/images/tunisian_cuisine_banner_1774909075250.png';
  }

  private function normalizePublicPath(string $stored): string
  {
    if (preg_match('#^https?://#i', $stored)) {
      return $stored;
    }

    if (str_starts_with($stored, '/')) {
      return $stored;
    }

    return '/'.$stored;
  }
}
