<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Region;
use App\Entity\Ingredient;
use App\Repository\RegionRepository;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_tips')]
    public function tips(): Response
    {
        return $this->render('frontend/tips.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('frontend/about.html.twig');
    }

    #[Route('/regions', name: 'app_regions')]
    public function regions(RegionRepository $regionRepository): Response
    {
        return $this->render('frontend/regions.html.twig', [
            'regions' => $regionRepository->findAll(),
        ]);
    }

    #[Route('/ingredients', name: 'app_ingredients')]
    public function ingredients(IngredientRepository $ingredientRepository): Response
    {
        $ingredients = $ingredientRepository->findAll();
        $categories = [];
        foreach ($ingredients as $ing) {
            $cat = $ing->getCategory();
            if ($cat && !in_array($cat, $categories, true)) {
                $categories[] = $cat;
            }
        }
        sort($categories);

        return $this->render('frontend/ingredients.html.twig', [
            'ingredients' => $ingredients,
            'categories' => $categories,
        ]);
    }

    #[Route('/ask-chef', name: 'app_ask_chef')]
    public function askChef(): Response
    {
        return $this->render('frontend/ask_chef.html.twig');
    }
}
