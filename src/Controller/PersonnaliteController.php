<?php

namespace App\Controller;

use App\Repository\PersonnaliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_personnalites_')]
class PersonnaliteController extends AbstractController
{
    #[Route('/66-visages', name: 'index')]
    public function index(PersonnaliteRepository $personnaliteRepository): Response
    {
        return $this->render('personnalite/index.html.twig', [
            'personnalites' => $personnaliteRepository->findAllOrdered(),
        ]);
    }

    /**
     * Top-level (not /66-visages-prefixed) so personality pages have a short,
     * shareable URL — e.g. /bertin-nahum. Low priority so it's only matched
     * once every other, more specific route (/, /a-propos, /contacts, /admin/*)
     * has already had a chance to match.
     */
    #[Route('/{slug}', name: 'show', priority: -1)]
    public function show(PersonnaliteRepository $personnaliteRepository, string $slug): Response
    {
        $personnalite = $personnaliteRepository->findOneBySlug($slug);

        if (null === $personnalite) {
            throw $this->createNotFoundException('Cette personnalité n\'existe pas.');
        }

        return $this->render('personnalite/show.html.twig', [
            'personnalite' => $personnalite,
            'others' => $personnaliteRepository->findRandomOthers($personnalite),
        ]);
    }
}
