<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\UserLog;
use App\Repository\UserLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLogger
{
    private UserLogRepository $userLogRepository;
    private RequestStack $requestStack;

    public function __construct(
        UserLogRepository $userLogRepository,
        RequestStack $requestStack
    ) {
        $this->userLogRepository = $userLogRepository;
        $this->requestStack      = $requestStack;
    }

    public function log(User $user, string $action, ?string $details = null): UserLog
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new UserLog();
        $log->setUser($user);
        $log->setAction($action);
        $log->setDetails($details);

        if ($request) {
            $log->setIpAddress($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
        }

        $this->userLogRepository->save($log, true);

        return $log;
    }

    public function logLogin(User $user): UserLog
    {
        return $this->log($user, 'login', 'Connexion réussie');
    }

    public function logLogout(User $user): UserLog
    {
        return $this->log($user, 'logout', 'Déconnexion');
    }

    public function logRegister(User $user): UserLog
    {
        return $this->log($user, 'register', 'Nouvel utilisateur inscrit');
    }

    public function logPasswordChange(User $user): UserLog
    {
        return $this->log($user, 'password_change', 'Mot de passe modifié');
    }

    public function logProfileUpdate(User $user): UserLog
    {
        return $this->log($user, 'profile_update', 'Profil mis à jour');
    }

    public function logUserCreate(User $admin, User $createdUser): UserLog
    {
        return $this->log(
            $admin,
            'user_create',
            sprintf('Utilisateur créé: %s', $createdUser->getEmail())
        );
    }

    public function logUserUpdate(User $admin, User $updatedUser): UserLog
    {
        return $this->log(
            $admin,
            'user_update',
            sprintf('Utilisateur modifié: %s', $updatedUser->getEmail())
        );
    }

    public function logUserDelete(User $admin, string $deletedUserEmail): UserLog
    {
        return $this->log(
            $admin,
            'user_delete',
            sprintf('Utilisateur supprimé: %s', $deletedUserEmail)
        );
    }

    public function logUserSuspend(User $admin, User $suspendedUser): UserLog
    {
        return $this->log(
            $admin,
            'user_suspend',
            sprintf('Utilisateur suspendu: %s', $suspendedUser->getEmail())
        );
    }

    public function logUserUnsuspend(User $admin, User $unsuspendedUser): UserLog
    {
        return $this->log(
            $admin,
            'user_unsuspend',
            sprintf('Utilisateur réactivé: %s', $unsuspendedUser->getEmail())
        );
    }

    public function getRecentActivity(int $limit = 100): array
    {
        return $this->userLogRepository->findRecentLogs($limit);
    }

    public function getUserActivity(User $user, int $limit = 50): array
    {
        return $this->userLogRepository->findByUser($user, $limit);
    }

    public function getActivityStats(int $days = 30): array
    {
        $since = new \DateTime();
        $since->modify("-{$days} days");

        return $this->userLogRepository->getActivityStats($since);
    }
}
