<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Recipe;
use App\Entity\User;
use App\Enum\RecipeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RecipeWorkflowService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationService $notificationService,
        private readonly AdminActivityLogger $activityLogger,
        private readonly RecipeModerationMailer $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function submitForReview(Recipe $recipe, User $submitter): void
    {
        $recipe
            ->setSubmittedBy($submitter)
            ->setStatus(RecipeStatus::Pending)
            ->setRejectionReason(null)
            ->setReviewedBy(null)
            ->setReviewedAt(null);

        $this->em->persist($recipe);
        $this->em->flush();

        $link = $this->urlGenerator->generate('admin_recipes_pending');
        $this->notificationService->notifyAdmins(
            Notification::TYPE_RECIPE_PENDING,
            'New recipe submission',
            sprintf('%s submitted "%s" for review.', $submitter->getDisplayName(), $recipe->getName()),
            $link,
            $recipe->getId(),
        );
    }

    public function publishDirectly(Recipe $recipe, User $admin): void
    {
        $recipe
            ->setStatus(RecipeStatus::Published)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason(null);

        $this->em->flush();
        $this->activityLogger->log($admin, 'recipe.publish', sprintf('Published recipe #%d "%s"', $recipe->getId(), $recipe->getName()));
    }

    public function approve(Recipe $recipe, User $admin): void
    {
        $recipe
            ->setStatus(RecipeStatus::Published)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason(null);

        $this->em->flush();
        $this->activityLogger->log($admin, 'recipe.approve', sprintf('Approved recipe #%d "%s"', $recipe->getId(), $recipe->getName()));

        $submitter = $recipe->getSubmittedBy();
        if ($submitter !== null) {
            $link = $this->urlGenerator->generate('app_recipe_show', ['id' => $recipe->getId()]);
            $this->notificationService->notifyUser(
                $submitter,
                Notification::TYPE_RECIPE_DECISION,
                'Recipe approved',
                sprintf('Your recipe "%s" is now live.', $recipe->getName()),
                $link,
                $recipe->getId(),
            );
            $this->mailer->sendRecipeDecision($recipe, $submitter, true);
        }
    }

    public function reject(Recipe $recipe, User $admin, string $reason): void
    {
        $recipe
            ->setStatus(RecipeStatus::Rejected)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason($reason);

        $this->em->flush();
        $this->activityLogger->log($admin, 'recipe.reject', sprintf('Rejected recipe #%d "%s": %s', $recipe->getId(), $recipe->getName(), $reason));

        $submitter = $recipe->getSubmittedBy();
        if ($submitter !== null) {
            $this->notificationService->notifyUser(
                $submitter,
                Notification::TYPE_RECIPE_DECISION,
                'Recipe declined',
                sprintf('Your recipe "%s" was declined: %s', $recipe->getName(), $reason),
                $this->urlGenerator->generate('app_my_recipes'),
                $recipe->getId(),
            );
            $this->mailer->sendRecipeDecision($recipe, $submitter, false, $reason);
        }
    }

    public function softDelete(Recipe $recipe, User $admin): void
    {
        $recipe->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();
        $this->activityLogger->log($admin, 'recipe.delete', sprintf('Soft-deleted recipe #%d "%s"', $recipe->getId(), $recipe->getName()));
    }
}
