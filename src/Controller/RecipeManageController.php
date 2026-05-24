<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Enum\RecipeStatus;
use App\Form\RecipeFormType;
use App\Repository\RecipeRepository;
use App\Service\RecipeDuplicateChecker;
use App\Service\RecipeImageUploader;
use App\Service\RecipeWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class RecipeManageController extends AbstractController
{
    #[Route('/recipe/new', name: 'app_recipe_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        RecipeImageUploader $uploader,
        RecipeWorkflowService $workflow,
        RecipeDuplicateChecker $duplicateChecker,
    ): Response {
        $recipe = new Recipe();
        if (!$recipe->getRecipeIngredients()->count()) {
            $recipe->addRecipeIngredient(new \App\Entity\RecipeIngredient());
        }
        if (!$recipe->getSteps()->count()) {
            $step = new \App\Entity\Step();
            $step->setStepOrder(1)->setDescription('');
            $recipe->addStep($step);
        }

        return $this->handleForm($request, $recipe, $em, $uploader, $workflow, $duplicateChecker, true);
    }

    #[Route('/recipe/{id}/edit', name: 'app_recipe_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $em,
        RecipeImageUploader $uploader,
        RecipeWorkflowService $workflow,
        RecipeDuplicateChecker $duplicateChecker,
    ): Response {
        $this->denyAccessUnlessGranted('recipe_edit', $recipe);

        return $this->handleForm($request, $recipe, $em, $uploader, $workflow, $duplicateChecker, false);
    }

    #[Route('/my-recipes', name: 'app_my_recipes')]
    public function myRecipes(RecipeRepository $recipeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('recipe/my_recipes.html.twig', [
            'recipes' => $recipeRepository->findBySubmitter($user->getId() ?? 0),
        ]);
    }

    private function handleForm(
        Request $request,
        Recipe $recipe,
        EntityManagerInterface $em,
        RecipeImageUploader $uploader,
        RecipeWorkflowService $workflow,
        RecipeDuplicateChecker $duplicateChecker,
        bool $isNew,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $form = $this->createForm(RecipeFormType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile !== null) {
                try {
                    $path = $uploader->upload($imageFile, $recipe->getName());
                    if ($path !== null) {
                        $recipe->setImage($path);
                    }
                } catch (\Throwable $e) {
                    $this->addFlash('error', $e->getMessage());

                    return $this->render('recipe/form.html.twig', [
                        'form' => $form,
                        'recipe' => $recipe,
                        'isNew' => $isNew,
                        'similarNames' => [],
                    ]);
                }
            }

            $order = 1;
            foreach ($recipe->getSteps() as $step) {
                if ($step->getStepOrder() < 1) {
                    $step->setStepOrder($order);
                }
                ++$order;
            }

            $recipe->setSubmittedBy($recipe->getSubmittedBy() ?? $user);
            $em->persist($recipe);
            $em->flush();

            if ($isAdmin) {
                $workflow->publishDirectly($recipe, $user);
                $this->addFlash('success', 'recipe.published');

                return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
            }

            $workflow->submitForReview($recipe, $user);
            $this->addFlash('success', 'recipe.submitted');

            return $this->redirectToRoute('app_my_recipes');
        }

        $similarNames = [];
        if ($form->isSubmitted() && $recipe->getName() !== '') {
            $similarNames = $duplicateChecker->findSimilarNames($recipe->getName(), $recipe->getId());
        }

        return $this->render('recipe/form.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
            'isNew' => $isNew,
            'similarNames' => $similarNames,
        ]);
    }
}
