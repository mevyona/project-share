<?php

namespace App\Controller;

use App\Entity\Fichier;
use App\Form\FichierType;
use App\Repository\FichierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class FichierController extends AbstractController
{
    #[Route('/ajout-fichier', name: 'app_ajout_fichier')]
    #[IsGranted('ROLE_ADMIN')]
    public function ajoutFichier(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $fichier = new Fichier();
        $form = $this->createForm(FichierType::class, $fichier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fichier')->getData();

            if ($file) {
                $nomFichierServeur = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $nomFichierServeur = $slugger->slug($nomFichierServeur);
                $nomFichierServeur = $nomFichierServeur . '-' . uniqid() . '.' . $file->guessExtension();
                try {
                    $fichier->setNomServeur($nomFichierServeur);
                    $fichier->setNomOriginal($file->getClientOriginalName());
                    $fichier->setDateEnvoi(new \DateTime());
                    $fichier->setExtension($file->guessExtension());
                    $fichier->setTaille($file->getSize());
                    $em->persist($fichier);
                    $em->flush();
                    $file->move(
                        $this->getParameter('file_directory'),
                        $nomFichierServeur
                    );
                    $this->addFlash('success', 'Fichier envoyé avec succès !');
                    return $this->redirectToRoute('app_ajout_fichier');
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload du fichier.");
                }
            }
        }
        return $this->render('fichier/ajout-fichier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/liste-fichiers', name: 'app_liste_fichiers')]
    public function listeFichiers(FichierRepository $fichierRepository): Response
    {
        $fichiers = $fichierRepository->findAll();

        return $this->render('fichier/liste_fichiers.html.twig', [
            'fichiers' => $fichiers,
        ]);
    }

    #[Route('/private-telechargement-fichier/{id}', name: 'app_telechargement_fichier', requirements:
            ["id" => "\d+"])]
    public function telechargementFichier(Fichier $fichier)
    {
        if ($fichier == null) {
            $this->redirectToRoute('app_liste_fichiers_par_utilisateur');
        } else {
            return $this->file($this->getParameter('file_directory') . '/' . $fichier->getNomServeur(),
                $fichier->getNomOriginal());
        }
    }

    #[Route('/mes-fichiers', name: 'app_mes_fichiers')]
    public function mesFichiers(FichierRepository $fichierRepository): Response
    {
        $user = $this->getUser();
        $fichiers = $fichierRepository->findBy(
            ['user' => $user],
            ['dateEnvoi' => 'DESC']
        );
        $totalUsed = $fichierRepository->getTotalStorageUsed($user);
        return $this->render('fichier/mes-fichiers.html.twig', [
            'fichiers' => $fichiers,
            'totalUsed' => $totalUsed,
        ]);
    }

    #[Route('/top-users', name: 'app_top_users')]
    public function topUsers(FichierRepository $repo): Response
    {
        $topUsers = $repo->findTopUsersByFileCount(5); 

        return $this->render('fichier/top-users.html.twig', [
            'topUsers' => $topUsers,
        ]);
    }

}
