<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    public function index(NotificationRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('account/notifications.html.twig', [
            'notifications' => $repository->findForUser($user),
        ]);
    }

    #[Route('/notifications/{id}/read', name: 'app_notification_read', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function markRead(Notification $notification, NotificationService $notificationService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($notification->getRecipient()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $notificationService->markRead($notification);

        if ($notification->getLink()) {
            return $this->redirect($notification->getLink());
        }

        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/notifications/read-all', name: 'app_notifications_read_all', methods: ['POST'])]
    public function markAllRead(NotificationService $notificationService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $notificationService->markAllRead($user);

        return $this->redirectToRoute('app_notifications');
    }
}
