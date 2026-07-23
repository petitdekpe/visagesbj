<?php

namespace App\Controller;

use App\Repository\PersonnaliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PersonnaliteRepository $personnaliteRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'featured' => $personnaliteRepository->findFeatured(6),
            'heroFaces' => $personnaliteRepository->findFeatured(66),
        ]);
    }
}
