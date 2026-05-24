<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdminActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminActivityLog>
 */
class AdminActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminActivityLog::class);
    }

    /**
     * @return AdminActivityLog[]
     */
    public function findLatest(int $limit = 100): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.admin', 'a')
            ->addSelect('a')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
