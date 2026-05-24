<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function notifyAdmins(string $type, string $title, string $message, ?string $link = null, ?int $recipeId = null, ?int $tipId = null): void
    {
        foreach ($this->userRepository->findAdmins() as $admin) {
            $this->create($admin, $type, $title, $message, $link, $recipeId, $tipId);
        }
    }

    public function notifyUser(User $user, string $type, string $title, string $message, ?string $link = null, ?int $recipeId = null, ?int $tipId = null): void
    {
        $this->create($user, $type, $title, $message, $link, $recipeId, $tipId);
    }

    public function markRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->em->flush();
    }

    public function markAllRead(User $user): void
    {
        $this->em->createQueryBuilder()
            ->update(Notification::class, 'n')
            ->set('n.isRead', ':read')
            ->andWhere('n.recipient = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('read', true)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function countUnread(User $user): int
    {
        return $this->notificationRepository->countUnreadForUser($user);
    }

    private function create(User $recipient, string $type, string $title, string $message, ?string $link, ?int $recipeId, ?int $tipId): void
    {
        $notification = (new Notification())
            ->setRecipient($recipient)
            ->setType($type)
            ->setTitle($title)
            ->setMessage($message)
            ->setLink($link)
            ->setRelatedRecipeId($recipeId)
            ->setRelatedTipId($tipId);

        $this->em->persist($notification);
        $this->em->flush();
    }
}
