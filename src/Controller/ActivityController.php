<?php
namespace App\Controller;

use App\Service\ActivityLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/activity')]
#[IsGranted('ROLE_USER')]
class ActivityController extends AbstractController
{
    private ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    #[Route('/my-activity', name: 'app_my_activity', methods: ['GET'])]
    public function myActivity(): Response
    {
        $user       = $this->getUser();
        $activities = $this->activityLogger->getUserActivity($user, 100);

        return $this->render('activity/my_activity.html.twig', [
            'activities' => $activities,
        ]);
    }

    #[Route('/all', name: 'app_activity_all', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function allActivity(): Response
    {
        $activities = $this->activityLogger->getRecentActivity(200);
        $stats      = $this->activityLogger->getActivityStats(30);

        return $this->render('activity/all_activity.html.twig', [
            'activities' => $activities,
            'stats'      => $stats,
        ]);
    }
}
