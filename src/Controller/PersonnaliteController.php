<?php

namespace App\Controller;

use App\Repository\PersonnaliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/66-visages', name: 'app_personnalites_')]
class PersonnaliteController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PersonnaliteRepository $personnaliteRepository): Response
    {
        return $this->render('personnalite/index.html.twig', [
            'personnalites' => $personnaliteRepository->findAllOrdered(),
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(PersonnaliteRepository $personnaliteRepository, string $slug): Response
    {
        $personnalite = $personnaliteRepository->findOneBySlug($slug);

        if (null === $personnalite) {
            throw $this->createNotFoundException('Cette personnalité n\'existe pas.');
        }

        return $this->render('personnalite/show.html.twig', [
            'personnalite' => $personnalite,
        ]);
    }
}
