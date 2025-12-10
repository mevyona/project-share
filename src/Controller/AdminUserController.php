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

    #[Route('/history', name: 'admin_user_history')]
    public function history(UserLogRepository $logRepo): Response
    {
        return $this->render('admin/user/history.html.twig', [
            'logs' => $logRepo->findRecentLogs(100),
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
