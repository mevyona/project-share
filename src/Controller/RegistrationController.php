<?php
namespace App\Controller;

use App\Entity\PasswordHistory;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_inscription')]
public function register(
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    Security $security,
    EntityManagerInterface $em,
    ActivityLogger $activityLogger
): Response
{
    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('plainPassword')->getData();
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $em->persist($user);
        $history = new PasswordHistory();
        $history->setUser($user);
        $history->setPassword($hashedPassword);
        $history->setChangedAt(new \DateTimeImmutable());
        $em->persist($history);
        $em->flush();

        return $security->login($user, AppCustomAuthenticator::class, 'main');
    }
    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form,
    ]);
}

}
