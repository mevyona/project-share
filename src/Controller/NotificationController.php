<?php
namespace App\Controller;

use App\Entity\Notification;
use App\Service\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notification')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    private NotificationManager $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    #[Route('/panel', name: 'app_notification_panel', methods: ['GET'])]
    public function panel(): Response
    {
        $user          = $this->getUser();
        $notifications = $this->notificationManager->getRecentNotifications($user, 20);
        $unreadCount   = $this->notificationManager->getUnreadCount($user);

        return $this->render('notification/panel.html.twig', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    #[Route('/list', name: 'app_notification_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user          = $this->getUser();
        $notifications = $this->notificationManager->getRecentNotifications($user, 20);
        $unreadCount   = $this->notificationManager->getUnreadCount($user);

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id'        => $notification->getId(),
                'type'      => $notification->getType(),
                'title'     => $notification->getTitle(),
                'message'   => $notification->getMessage(),
                'link'      => $notification->getLink(),
                'isRead'    => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                'icon'      => $notification->getIcon(),
                'typeClass' => $notification->getTypeClass(),
            ];
        }

        return new JsonResponse([
            'notifications' => $data,
            'unreadCount'   => $unreadCount,
        ]);
    }

    #[Route('/count', name: 'app_notification_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        $user        = $this->getUser();
        $unreadCount = $this->notificationManager->getUnreadCount($user);

        return new JsonResponse(['count' => $unreadCount]);
    }

    #[Route('/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $this->notificationManager->markAsRead($notification);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/read-all', name: 'app_notification_read_all', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        $user  = $this->getUser();
        $count = $this->notificationManager->markAllAsRead($user);

        return new JsonResponse([
            'success' => true,
            'count'   => $count,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_notification_delete', methods: ['DELETE'])]
    public function delete(Notification $notification): JsonResponse
    {
        if ($notification->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $this->notificationManager->deleteNotification($notification);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/test', name: 'app_notification_test', methods: ['POST'])]
    public function test(): JsonResponse
    {
        $user = $this->getUser();

        $types = ['success', 'info', 'warning', 'error'];
        $type  = $types[array_rand($types)];

        $messages = [
            'success' => ['title' => 'Opération réussie', 'message' => 'Votre action a été exécutée avec succès.'],
            'info'    => ['title' => 'Information', 'message' => 'Ceci est une notification d\'information pour vous tenir informé.'],
            'warning' => ['title' => 'Attention', 'message' => 'Veuillez vérifier vos paramètres avant de continuer.'],
            'error'   => ['title' => 'Erreur détectée', 'message' => 'Une erreur s\'est produite lors de l\'opération.'],
        ];

        $this->notificationManager->createNotification(
            $user,
            $type,
            $messages[$type]['title'],
            $messages[$type]['message']
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Notification de test envoyée',
        ]);
    }
}
