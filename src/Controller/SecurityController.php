<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/change-password', name: 'app_change_password')]
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, ValidatorInterface $validator): Response {

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $newPassword = $request->request->get('password');
            $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte trouvé pour cet email.');
                return $this->redirectToRoute('app_change_password');
            }
            $violations = $validator->validate($newPassword, [
                new NotBlank([
                    "message" => "Le mot de passe ne peut pas être vide."
                ]),
                new Length([
                    "min" => 12,
                    "minMessage" => "Le mot de passe doit contenir au moins {{ limit }} caractères.",
                ]),
                new Regex([
                    "pattern" => "/[A-Z]/",
                    "message" => "Le mot de passe doit contenir au moins une lettre majuscule.",
                ]),
                new Regex([
                    "pattern" => "/[a-z]/",
                    "message" => "Le mot de passe doit contenir au moins une lettre minuscule.",
                ]),
                new Regex([
                    "pattern" => "/[0-9]/",
                    "message" => "Le mot de passe doit contenir au moins un chiffre.",
                ]),
                new Regex([
                    "pattern" => "/[\W]/",
                    "message" => "Le mot de passe doit contenir au moins un caractère spécial.",
                ]),
                new NotCompromisedPassword([
                    "message" => "Ce mot de passe a été compromis dans une fuite de données."
                ])
            ]);
    
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
                return $this->redirectToRoute('app_change_password');
            }
            foreach ($user->getPasswordHistories() as $old) {
                $tempUser = clone $user;
                $tempUser->setPassword($old->getPassword());

                if ($hasher->isPasswordValid($tempUser, $newPassword)) {
                    $this->addFlash('error', 'Impossible : vous avez déjà utilisé ce mot de passe.');
                    return $this->redirectToRoute('app_change_password');
                }
            }
            if ($hasher->isPasswordValid($user, $newPassword)) {
                $this->addFlash('error', 'Ce mot de passe est déjà votre mot de passe actuel.');
                return $this->redirectToRoute('app_change_password');
            }
            $history = new \App\Entity\PasswordHistory();
            $history->setUser($user);
            $history->setPassword($user->getPassword());
            $history->setChangedAt(new \DateTimeImmutable());
            $em->persist($history);
            $user->setPassword($hasher->hashPassword($user, $newPassword));
            $em->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/change_password.html.twig');
    }
}
