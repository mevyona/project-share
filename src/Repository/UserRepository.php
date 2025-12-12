<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findUsersWithOldPassword(int $days): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.passwordHistories', 'ph')
            ->groupBy('u.id')
            ->having('MAX(ph.changedAt) < :limit')
            ->setParameter('limit', new \DateTime("-$days days"))
            ->getQuery()
            ->getResult();
    }


    public function searchUsers(?string $search, ?string $role, ?string $suspended): array
{
    $qb = $this->createQueryBuilder('u');

    
    if (!empty($search)) {
        $qb->andWhere('u.email LIKE :search 
                       OR u.firstname LIKE :search
                       OR u.lastname LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }
    if (!empty($role)) {

       
        if ($role === 'ROLE_ADMIN') {
            $qb->andWhere('u.roles LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }
    
       
        if ($role === 'ROLE_USER') {
            $qb->andWhere('u.roles NOT LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }
    }
    
    

   
    if ($suspended !== null && $suspended !== '') {
        $qb->andWhere('u.isSuspended = :suspended')
           ->setParameter('suspended', (bool)$suspended);
    }

    return $qb->orderBy('u.email', 'ASC')
              ->getQuery()
              ->getResult();
}

public function countAllUsers(): int
{
    return $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countActiveUsers(): int
{
    return $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->andWhere('u.isSuspended = 0')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countSuspendedUsers(): int
{
    return $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->andWhere('u.isSuspended = 1')
        ->getQuery()
        ->getSingleScalarResult();
}

public function findPaginated(int $page, int $limit): array
{
    $offset = ($page - 1) * $limit;

    return $this->createQueryBuilder('u')
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->orderBy('u.id', 'ASC')
        ->getQuery()
        ->getResult();
}

public function countUsers(): int
{
    return (int) $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->getQuery()
        ->getSingleScalarResult();
}

public function searchUsersPaginated(?string $search, ?string $role, ?string $suspended, int $page, int $limit): array
{
    $qb = $this->createQueryBuilder('u');

    if (!empty($search)) {
        $qb->andWhere('u.email LIKE :search 
                       OR u.firstname LIKE :search
                       OR u.lastname LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    if (!empty($role)) {
        if ($role === 'ROLE_ADMIN') {
            $qb->andWhere('u.roles LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }

        if ($role === 'ROLE_USER') {
            $qb->andWhere('u.roles NOT LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }
    }

    if ($suspended !== null && $suspended !== '') {
        $qb->andWhere('u.isSuspended = :suspended')
           ->setParameter('suspended', (bool) $suspended);
    }

    // Pagination
    $offset = ($page - 1) * $limit;

    return $qb->orderBy('u.email', 'ASC')
              ->setFirstResult($offset)
              ->setMaxResults($limit)
              ->getQuery()
              ->getResult();
}

public function countFilteredUsers(?string $search, ?string $role, ?string $suspended): int
{
    $qb = $this->createQueryBuilder('u')
              ->select('COUNT(u.id)');

    if (!empty($search)) {
        $qb->andWhere('u.email LIKE :search 
                       OR u.firstname LIKE :search
                       OR u.lastname LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    if (!empty($role)) {
        if ($role === 'ROLE_ADMIN') {
            $qb->andWhere('u.roles LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }

        if ($role === 'ROLE_USER') {
            $qb->andWhere('u.roles NOT LIKE :admin')
               ->setParameter('admin', '%"ROLE_ADMIN"%');
        }
    }

    if ($suspended !== null && $suspended !== '') {
        $qb->andWhere('u.isSuspended = :suspended')
           ->setParameter('suspended', (bool)$suspended);
    }

    return (int) $qb->getQuery()->getSingleScalarResult();
}


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
