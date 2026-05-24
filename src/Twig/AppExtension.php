<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\UserFavoriteRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly NotificationRepository $notificationRepository,
        private readonly UserFavoriteRepository $favoriteRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_notifications_count', [$this, 'getUnreadNotificationsCount']),
            new TwigFunction('is_recipe_favorite', [$this, 'isRecipeFavorite']),
        ];
    }

    public function getUnreadNotificationsCount(): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return 0;
        }

        return $this->notificationRepository->countUnreadForUser($user);
    }

    public function isRecipeFavorite(Recipe $recipe): bool
    {
        $user = $this->security->getUser();
        if (!$user instanceof User || $recipe->getId() === null) {
            return false;
        }

        return $this->favoriteRepository->findOneByUserAndRecipe($user, $recipe) !== null;
    }
}
