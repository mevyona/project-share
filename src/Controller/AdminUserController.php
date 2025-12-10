<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserLogRepository;
use App\Repository\UserRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index')]
    public function index(UserRepository $repo): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $repo->findAll(),
        ]);
    }

    #[Route('/audit', name: 'admin_user_audit')]
    public function audit(Request $request, UserLogRepository $logRepo): Response
    {
        $action   = $request->query->get('action', '');
        $userId   = $request->query->get('user_id', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo   = $request->query->get('date_to', '');
        $limit    = $request->query->getInt('limit', 100);

        $qb = $logRepo->createQueryBuilder('ul')
            ->leftJoin('ul.user', 'u')
            ->addSelect('u')
            ->andWhere('ul.action IN (:adminActions)')
            ->setParameter('adminActions', ['user_update', 'user_suspend', 'user_unsuspend', 'user_delete'])
            ->orderBy('ul.createdAt', 'DESC');

        if ($action) {
            $qb->andWhere('ul.action = :action')
                ->setParameter('action', $action);
        }

        if ($userId) {
            $qb->andWhere('ul.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($dateFrom) {
            $qb->andWhere('ul.createdAt >= :dateFrom')
                ->setParameter('dateFrom', new \DateTime($dateFrom));
        }

        if ($dateTo) {
            $dateTo = new \DateTime($dateTo);
            $dateTo->setTime(23, 59, 59);
            $qb->andWhere('ul.createdAt <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $qb->setMaxResults($limit);
        $logs = $qb->getQuery()->getResult();

        $stats = [
            'total'     => count($logs),
            'byAction'  => $logRepo->createQueryBuilder('ul')
                ->select('ul.action, COUNT(ul.id) as count')
                ->andWhere('ul.action IN (:adminActions)')
                ->setParameter('adminActions', ['user_update', 'user_suspend', 'user_unsuspend', 'user_delete'])
                ->groupBy('ul.action')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult(),
            'last24h'   => $logRepo->createQueryBuilder('ul')
                ->select('COUNT(ul.id)')
                ->andWhere('ul.action IN (:adminActions)')
                ->andWhere('ul.createdAt >= :yesterday')
                ->setParameter('adminActions', ['user_update', 'user_suspend', 'user_unsuspend', 'user_delete'])
                ->setParameter('yesterday', new \DateTime('-24 hours'))
                ->getQuery()
                ->getSingleScalarResult(),
            'last7days' => $logRepo->createQueryBuilder('ul')
                ->select('COUNT(ul.id)')
                ->andWhere('ul.action IN (:adminActions)')
                ->andWhere('ul.createdAt >= :lastWeek')
                ->setParameter('adminActions', ['user_update', 'user_suspend', 'user_unsuspend', 'user_delete'])
                ->setParameter('lastWeek', new \DateTime('-7 days'))
                ->getQuery()
                ->getSingleScalarResult(),
        ];

        $allUsers = $logRepo->createQueryBuilder('ul')
            ->select('DISTINCT u.id, u.email, u.firstname, u.lastname')
            ->leftJoin('ul.user', 'u')
            ->andWhere('ul.action IN (:adminActions)')
            ->setParameter('adminActions', ['user_update', 'user_suspend', 'user_unsuspend', 'user_delete'])
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/user/audit.html.twig', [
            'logs'     => $logs,
            'stats'    => $stats,
            'allUsers' => $allUsers,
            'filters'  => [
                'action'    => $action,
                'user_id'   => $userId,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'limit'     => $limit,
            ],
        ]);
    }

    #[Route('/{id}/history', name: 'admin_user_single_history')]
    public function singleHistory(User $user, UserLogRepository $logRepo): Response
    {
        return $this->render('admin/user/single_history.html.twig', [
            'user' => $user,
            'logs' => $logRepo->findByUser($user),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $activityLogger->logUserUpdate($this->getUser(), $user);

            $this->addFlash('success', 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/toggle-suspend', name: 'admin_user_toggle_suspend')]
    public function toggleSuspend(User $user, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        $wasSuspended = $user->isSuspended();
        $user->setIsSuspended(! $wasSuspended);
        $em->flush();

        if ($wasSuspended) {
            $activityLogger->logUserUnsuspend($this->getUser(), $user);
            $this->addFlash('success', 'Utilisateur réactivé avec succès.');
        } else {
            $activityLogger->logUserSuspend($this->getUser(), $user);
            $this->addFlash('warning', 'Utilisateur suspendu avec succès.');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em, ActivityLogger $activityLogger): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            $userEmail = $user->getEmail();

            $em->remove($user);
            $em->flush();

            $activityLogger->logUserDelete($this->getUser(), $userEmail);

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
