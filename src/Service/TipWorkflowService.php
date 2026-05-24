<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CommunityTip;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\ModerationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TipWorkflowService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationService $notificationService,
        private readonly AdminActivityLogger $activityLogger,
        private readonly RecipeModerationMailer $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function submitPending(CommunityTip $tip, User $user): void
    {
        $tip
            ->setSubmittedBy($user)
            ->setAuthorName($user->getDisplayName())
            ->setStatus(ModerationStatus::Pending);

        $this->em->persist($tip);
        $this->em->flush();

        $this->notificationService->notifyAdmins(
            Notification::TYPE_TIP_PENDING,
            'New cooking tip',
            sprintf('%s submitted a tip for review.', $user->getDisplayName()),
            $this->urlGenerator->generate('admin_tips_pending'),
            null,
            $tip->getId(),
        );
    }

    public function publishDirectly(CommunityTip $tip, User $admin): void
    {
        $tip
            ->setSubmittedBy($admin)
            ->setAuthorName($admin->getDisplayName())
            ->setStatus(ModerationStatus::Published)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason(null);

        $this->em->persist($tip);
        $this->em->flush();

        $this->activityLogger->log($admin, 'tip.publish', sprintf('Published tip #%d directly', $tip->getId()));
    }

    public function approve(CommunityTip $tip, User $admin): void
    {
        $tip
            ->setStatus(ModerationStatus::Published)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason(null);

        $this->em->flush();
        $this->activityLogger->log($admin, 'tip.approve', sprintf('Approved tip #%d', $tip->getId()));

        $submitter = $tip->getSubmittedBy();
        if ($submitter !== null) {
            $this->notificationService->notifyUser(
                $submitter,
                Notification::TYPE_TIP_DECISION,
                'Tip approved',
                'Your cooking tip is now published.',
                $this->urlGenerator->generate('app_tips'),
                null,
                $tip->getId(),
            );
            $this->mailer->sendTipDecision($submitter, mb_substr($tip->getContent(), 0, 80), true);
        }
    }

    public function reject(CommunityTip $tip, User $admin, string $reason): void
    {
        $tip
            ->setStatus(ModerationStatus::Rejected)
            ->setReviewedBy($admin)
            ->setReviewedAt(new \DateTimeImmutable())
            ->setRejectionReason($reason);

        $this->em->flush();
        $this->activityLogger->log($admin, 'tip.reject', sprintf('Rejected tip #%d: %s', $tip->getId(), $reason));

        $submitter = $tip->getSubmittedBy();
        if ($submitter !== null) {
            $this->notificationService->notifyUser(
                $submitter,
                Notification::TYPE_TIP_DECISION,
                'Tip declined',
                $reason,
                $this->urlGenerator->generate('app_tips'),
                null,
                $tip->getId(),
            );
            $this->mailer->sendTipDecision($submitter, mb_substr($tip->getContent(), 0, 80), false, $reason);
        }
    }
}
