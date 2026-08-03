<?php

namespace App\Controller;

use App\Entity\Personnalite;
use App\Repository\PersonnaliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends AbstractController
{
    /**
     * The whole (small) photo pool is sent once and recycled client-side by
     * the infinite 2D tile grid — see assets/controllers/gallery_controller.js.
     * There's no "next batch" to fetch: the point is that it never runs out,
     * looping through the same pool forever in every scroll direction.
     */
    #[Route('/galerie', name: 'app_gallery')]
    public function index(PersonnaliteRepository $personnaliteRepository, Packages $assetPackages): Response
    {
        $personnalites = $personnaliteRepository->findVisibleWithPhoto();

        return $this->render('gallery/index.html.twig', [
            'photosJson' => json_encode(
                array_map(fn (Personnalite $p) => $this->serializePersonnalite($p, $assetPackages), $personnalites),
                JSON_THROW_ON_ERROR
            ),
        ]);
    }

    /**
     * @return array{title: string, imageUrl: string, caption: string, url: string}
     */
    private function serializePersonnalite(Personnalite $personnalite, Packages $assetPackages): array
    {
        return [
            'title' => $personnalite->getFullName(),
            'imageUrl' => $assetPackages->getUrl('images/personnalites/'.$personnalite->getPhoto()),
            'caption' => $personnalite->getRole()
                ? sprintf('%s · %s', $personnalite->getFullName(), $personnalite->getRole())
                : $personnalite->getFullName(),
            'url' => $this->generateUrl('app_personnalites_show', ['slug' => $personnalite->getSlug()]),
        ];
    }
}
