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

    public function save(UserLog $userLog, bool $flush = false): void
    {
        $this->getEntityManager()->persist($userLog);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRecentLogs(int $limit = 100): array
    {
        return $this->createQueryBuilder('ul')
            ->leftJoin('ul.user', 'u')
            ->addSelect('u')
            ->orderBy('ul.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('ul')
            ->andWhere('ul.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ul.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByAction(string $action, int $limit = 100): array
    {
        return $this->createQueryBuilder('ul')
            ->leftJoin('ul.user', 'u')
            ->addSelect('u')
            ->andWhere('ul.action = :action')
            ->setParameter('action', $action)
            ->orderBy('ul.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('ul')
            ->leftJoin('ul.user', 'u')
            ->addSelect('u')
            ->andWhere('ul.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('ul.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByAction(string $action): int
    {
        return $this->createQueryBuilder('ul')
            ->select('COUNT(ul.id)')
            ->andWhere('ul.action = :action')
            ->setParameter('action', $action)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getActivityStats(\DateTimeInterface $since): array
    {
        $qb = $this->createQueryBuilder('ul')
            ->select('ul.action, COUNT(ul.id) as count')
            ->andWhere('ul.createdAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('ul.action')
            ->orderBy('count', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function deleteOldLogs(int $daysOld = 90): int
    {
        $date = new \DateTime();
        $date->modify("-{$daysOld} days");

        return $this->createQueryBuilder('ul')
            ->delete()
            ->andWhere('ul.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
