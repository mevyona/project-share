<?php
namespace App\Repository;

use App\Entity\User;
use App\Entity\UserLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLog>
 */
class UserLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLog::class);
    }

    public function findRecentLogs(int $limit = 100): array
    {
        return $this->createQueryBuilder('ul')
            ->orderBy('ul.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('ul')
            ->andWhere('ul.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ul.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
