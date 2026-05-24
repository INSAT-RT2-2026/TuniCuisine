<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, RecipeRepository $recipeRepository, RegionRepository $regionRepository): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $regionId = $request->query->getInt('region', 0);

        if ($query !== '') {
            $recipes = $recipeRepository->search($query);
        } elseif ($regionId > 0) {
            $recipes = $recipeRepository->findByRegion($regionId);
        } else {
            $recipes = $recipeRepository->findAll();
        }

        return $this->render('frontend/recipes.html.twig', [
            'recipes' => $recipes,
            'regions' => $regionRepository->findAll(),
            'query' => $query,
            'selectedRegion' => $regionId,
        ]);
    }

    #[Route('/recipes/{id}', name: 'app_recipe_show', requirements: ['id' => '\d+'])]
    public function show(Recipe $recipe): Response
    {
        return $this->render('frontend/recipe_detail.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
