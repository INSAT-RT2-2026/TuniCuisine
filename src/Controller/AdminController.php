<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommunityTip;
use App\Entity\Recipe;
use App\Entity\RecipeComment;
use App\Entity\User;
use App\Enum\ModerationStatus;
use App\Enum\RecipeStatus;
use App\Form\RecipeFormType;
use App\Repository\AdminActivityLogRepository;
use App\Repository\CommunityTipRepository;
use App\Repository\RecipeCommentRepository;
use App\Repository\RecipeRepository;
use App\Service\AdminActivityLogger;
use App\Service\RecipeDuplicateChecker;
use App\Service\RecipeImageUploader;
use App\Service\RecipeWorkflowService;
use App\Service\TipWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(
        RecipeRepository $recipeRepository,
        CommunityTipRepository $tipRepository,
        RecipeCommentRepository $commentRepository,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'pendingRecipes' => $recipeRepository->findPending(),
            'pendingTips' => $tipRepository->findPending(),
            'pendingComments' => $commentRepository->findPending(),
        ]);
    }

    #[Route('/recipes/pending', name: 'admin_recipes_pending')]
    public function pendingRecipes(RecipeRepository $recipeRepository): Response
    {
        return $this->render('admin/recipes_pending.html.twig', [
            'recipes' => $recipeRepository->findPending(),
        ]);
    }

    #[Route('/recipes/{id}/approve', name: 'admin_recipe_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approveRecipe(Recipe $recipe, RecipeWorkflowService $workflow): Response
    {
        if ($recipe->getStatus() !== RecipeStatus::Pending) {
            $this->addFlash('error', 'Recipe is not pending.');

            return $this->redirectToRoute('admin_recipes_pending');
        }

        /** @var User $admin */
        $admin = $this->getUser();
        $workflow->approve($recipe, $admin);
        $this->addFlash('success', 'Recipe approved.');

        return $this->redirectToRoute('admin_recipes_pending');
    }

    #[Route('/recipes/{id}/reject', name: 'admin_recipe_reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rejectRecipe(Recipe $recipe, Request $request, RecipeWorkflowService $workflow): Response
    {
        $reason = trim((string) $request->request->get('reason', ''));
        if ($reason === '') {
            $this->addFlash('error', 'Please provide a rejection reason.');

            return $this->redirectToRoute('admin_recipes_pending');
        }

        /** @var User $admin */
        $admin = $this->getUser();
        $workflow->reject($recipe, $admin, $reason);
        $this->addFlash('success', 'Recipe declined.');

        return $this->redirectToRoute('admin_recipes_pending');
    }

    #[Route('/recipes/{id}/edit', name: 'admin_recipe_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editRecipe(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $em,
        RecipeImageUploader $uploader,
        RecipeWorkflowService $workflow,
        RecipeDuplicateChecker $duplicateChecker,
        AdminActivityLogger $activityLogger,
    ): Response {
        $form = $this->createForm(RecipeFormType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile !== null) {
                $path = $uploader->upload($imageFile, $recipe->getName());
                if ($path !== null) {
                    $recipe->setImage($path);
                }
            }
            $em->flush();
            /** @var User $admin */
            $admin = $this->getUser();
            $activityLogger->log($admin, 'recipe.edit', sprintf('Edited recipe #%d', $recipe->getId()));
            $this->addFlash('success', 'Recipe updated.');

            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

        return $this->render('recipe/form.html.twig', [
            'form' => $form,
            'recipe' => $recipe,
            'isNew' => false,
            'similarNames' => $duplicateChecker->findSimilarNames($recipe->getName(), $recipe->getId()),
            'adminEdit' => true,
        ]);
    }

    #[Route('/recipes/{id}/delete', name: 'admin_recipe_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteRecipe(Recipe $recipe, RecipeWorkflowService $workflow): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();
        $workflow->softDelete($recipe, $admin);
        $this->addFlash('success', 'Recipe removed.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/tips/pending', name: 'admin_tips_pending')]
    public function pendingTips(CommunityTipRepository $repository): Response
    {
        return $this->render('admin/tips_pending.html.twig', [
            'tips' => $repository->findPending(),
        ]);
    }

    #[Route('/tips/{id}/approve', name: 'admin_tip_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approveTip(CommunityTip $tip, TipWorkflowService $workflow): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();
        $workflow->approve($tip, $admin);
        $this->addFlash('success', 'Tip approved.');

        return $this->redirectToRoute('admin_tips_pending');
    }

    #[Route('/tips/{id}/reject', name: 'admin_tip_reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rejectTip(CommunityTip $tip, Request $request, TipWorkflowService $workflow): Response
    {
        $reason = trim((string) $request->request->get('reason', ''));
        if ($reason === '') {
            $this->addFlash('error', 'Please provide a rejection reason.');

            return $this->redirectToRoute('admin_tips_pending');
        }

        /** @var User $admin */
        $admin = $this->getUser();
        $workflow->reject($tip, $admin, $reason);
        $this->addFlash('success', 'Tip declined.');

        return $this->redirectToRoute('admin_tips_pending');
    }

    #[Route('/comments/pending', name: 'admin_comments_pending')]
    public function pendingComments(RecipeCommentRepository $repository): Response
    {
        return $this->render('admin/comments_pending.html.twig', [
            'comments' => $repository->findPending(),
        ]);
    }

    #[Route('/comments/{id}/approve', name: 'admin_comment_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approveComment(RecipeComment $comment, EntityManagerInterface $em): Response
    {
        $comment->setStatus(ModerationStatus::Published)->setReviewedAt(new \DateTimeImmutable());
        /** @var User $admin */
        $admin = $this->getUser();
        $comment->setReviewedBy($admin);
        $em->flush();

        return $this->redirectToRoute('admin_comments_pending');
    }

    #[Route('/comments/{id}/reject', name: 'admin_comment_reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rejectComment(RecipeComment $comment, EntityManagerInterface $em): Response
    {
        $comment->setStatus(ModerationStatus::Rejected)->setReviewedAt(new \DateTimeImmutable());
        /** @var User $admin */
        $admin = $this->getUser();
        $comment->setReviewedBy($admin);
        $em->flush();

        return $this->redirectToRoute('admin_comments_pending');
    }

    #[Route('/activity', name: 'admin_activity')]
    public function activity(AdminActivityLogRepository $repository): Response
    {
        return $this->render('admin/activity.html.twig', [
            'logs' => $repository->findLatest(),
        ]);
    }
}
