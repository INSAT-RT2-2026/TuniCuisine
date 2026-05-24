<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeComment;
use App\Entity\RecipeRating;
use App\Entity\User;
use App\Enum\ModerationStatus;
use App\Form\RecipeCommentFormType;
use App\Repository\RecipeRatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecipeInteractionController extends AbstractController
{
    #[Route('/recipe/{id}/rate', name: 'app_recipe_rate', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rate(
        Recipe $recipe,
        Request $request,
        RecipeRatingRepository $ratingRepository,
        EntityManagerInterface $em,
    ): Response {
        if (!$recipe->isPublished()) {
            throw $this->createNotFoundException();
        }

        $score = (int) $request->request->get('score', 0);
        if ($score < 1 || $score > 5) {
            $this->addFlash('error', 'Rating must be between 1 and 5.');

            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $rating = $ratingRepository->findOneByUserAndRecipe($user, $recipe) ?? new RecipeRating();
        $rating->setUser($user)->setRecipe($recipe)->setScore($score);
        $em->persist($rating);
        $em->flush();

        $this->addFlash('success', 'Thanks for your rating!');

        return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
    }

    #[Route('/recipe/{id}/comment', name: 'app_recipe_comment', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function comment(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        if (!$recipe->isPublished()) {
            throw $this->createNotFoundException();
        }

        $comment = new RecipeComment();
        $form = $this->createForm(RecipeCommentFormType::class, $comment);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Could not post comment.');

            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $comment
            ->setAuthor($user)
            ->setRecipe($recipe)
            ->setStatus($this->isGranted('ROLE_ADMIN') ? ModerationStatus::Published : ModerationStatus::Pending);

        if ($this->isGranted('ROLE_ADMIN')) {
            $comment->setReviewedBy($user)->setReviewedAt(new \DateTimeImmutable());
        }

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', $this->isGranted('ROLE_ADMIN') ? 'Comment posted.' : 'comment.pending');

        return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
    }
}
