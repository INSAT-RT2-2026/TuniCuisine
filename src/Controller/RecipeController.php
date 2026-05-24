<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Enum\RecipeStatus;
use App\Form\RecipeCommentFormType;
use App\Repository\RecipeCommentRepository;
use App\Repository\RecipeRatingRepository;
use App\Repository\RecipeRepository;
use App\Repository\RegionRepository;
use App\Repository\UserFavoriteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        RecipeRepository $recipeRepository,
        RegionRepository $regionRepository,
        UserFavoriteRepository $favoriteRepository,
        RecipeRatingRepository $ratingRepository,
    ): Response {
        $query = trim((string) $request->query->get('q', ''));
        $regionId = $request->query->getInt('region', 0);

        if ($query !== '') {
            $recipes = $recipeRepository->searchPublished($query);
        } elseif ($regionId > 0) {
            $recipes = $recipeRepository->findPublishedByRegion($regionId);
        } else {
            $recipes = $recipeRepository->findPublishedAll();
        }

        $favoriteRecipeIds = [];
        if ($this->getUser() instanceof User) {
            $favoriteRecipeIds = $favoriteRepository->findRecipeIdsForUser($this->getUser());
        }

        return $this->render('frontend/recipes.html.twig', [
            'recipes' => $recipes,
            'recipeRatings' => $ratingRepository->getAveragesForRecipes($recipes),
            'regions' => $regionRepository->findAll(),
            'query' => $query,
            'selectedRegion' => $regionId,
            'favoriteRecipeIds' => $favoriteRecipeIds,
        ]);
    }

    #[Route('/recipes/{id}', name: 'app_recipe_show', requirements: ['id' => '\d+'])]
    public function show(
        Recipe $recipe,
        RecipeRatingRepository $ratingRepository,
        RecipeCommentRepository $commentRepository,
    ): Response {
        if (!$this->canViewRecipe($recipe)) {
            throw $this->createNotFoundException();
        }

        $ratingStats = $ratingRepository->getAverageForRecipe($recipe);
        $userRating = null;
        if ($this->getUser() instanceof User) {
            $userRating = $ratingRepository->findOneByUserAndRecipe($this->getUser(), $recipe);
        }

        return $this->render('frontend/recipe_detail.html.twig', [
            'recipe' => $recipe,
            'ratingStats' => $ratingStats,
            'userRating' => $userRating,
            'comments' => $commentRepository->findPublishedForRecipe($recipe),
            'commentForm' => $this->createForm(RecipeCommentFormType::class)->createView(),
        ]);
    }

    private function canViewRecipe(Recipe $recipe): bool
    {
        if ($recipe->isDeleted()) {
            return false;
        }

        if ($recipe->isPublished()) {
            return true;
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $submitter = $recipe->getSubmittedBy();

        return $submitter !== null
            && $submitter->getId() === $user->getId()
            && \in_array($recipe->getStatus(), [RecipeStatus::Pending, RecipeStatus::Rejected], true);
    }
}
