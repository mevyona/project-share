<?php
namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository
    ) {
        $this->entityManager          = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setLink($link);
        $notification->setData($data);

        $this->notificationRepository->save($notification, true);

        return $notification;
    }

    public function notifySuccess(User $user, string $title, string $message, ?string $link = null): Notification
    {
        return $this->createNotification($user, 'success', $title, $message, $link);
    }

    public function notifyInfo(User $user, string $title, string $message, ?string $link = null): Notification
    {
        return $this->createNotification($user, 'info', $title, $message, $link);
    }

    public function notifyWarning(User $user, string $title, string $message, ?string $link = null): Notification
    {
        return $this->createNotification($user, 'warning', $title, $message, $link);
    }

    public function notifyError(User $user, string $title, string $message, ?string $link = null): Notification
    {
        return $this->createNotification($user, 'error', $title, $message, $link);
    }

    public function markAsRead(Notification $notification): void
    {
        if (! $notification->isRead()) {
            $notification->setIsRead(true);
            $this->entityManager->flush();
        }
    }

    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }

    public function deleteNotification(Notification $notification): void
    {
        $this->notificationRepository->remove($notification, true);
    }

    public function getUnreadNotifications(User $user): array
    {
        return $this->notificationRepository->findUnreadByUser($user);
    }

    public function getUnreadCount(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    public function getRecentNotifications(User $user, int $limit = 10): array
    {
        return $this->notificationRepository->findRecentByUser($user, $limit);
    }

    public function cleanOldNotifications(int $daysOld = 30): int
    {
        return $this->notificationRepository->deleteOldReadNotifications($daysOld);
    }
}
