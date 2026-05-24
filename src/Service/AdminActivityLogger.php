<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AdminActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class AdminActivityLogger
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function log(User $admin, string $action, string $details): void
    {
        $entry = (new AdminActivityLog())
            ->setAdmin($admin)
            ->setAction($action)
            ->setDetails($details);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
