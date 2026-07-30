<?php

namespace App\Controller\Admin;

use App\Entity\Personnalite;
use App\Form\PersonnaliteType;
use App\Repository\PersonnaliteRepository;
use App\Service\PersonnalitePhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/personnalites', name: 'admin_personnalites_')]
class PersonnaliteAdminController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PersonnaliteRepository $personnaliteRepository): Response
    {
        return $this->render('admin/personnalite/index.html.twig', [
            'personnalites' => $personnaliteRepository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PersonnaliteRepository $personnaliteRepository,
        PersonnalitePhotoUploader $photoUploader,
    ): Response {
        $personnalite = new Personnalite();

        $form = $this->createForm(PersonnaliteType::class, $personnalite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnalite->setSlug($this->generateUniqueSlug($personnalite, $personnaliteRepository));

            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $personnalite->setPhoto($photoUploader->upload($photoFile, $personnalite->getFullName()));
            }

            $entityManager->persist($personnalite);
            $entityManager->flush();

            $this->addFlash('success', sprintf('%s a été ajouté·e.', $personnalite->getFullName()));

            return $this->redirectToRoute('admin_personnalites_index');
        }

        return $this->render('admin/personnalite/form.html.twig', [
            'form' => $form,
            'personnalite' => $personnalite,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(
        Request $request,
        Personnalite $personnalite,
        EntityManagerInterface $entityManager,
        PersonnalitePhotoUploader $photoUploader,
    ): Response {
        $form = $this->createForm(PersonnaliteType::class, $personnalite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $photoUploader->remove($personnalite->getPhoto());
                $personnalite->setPhoto($photoUploader->upload($photoFile, $personnalite->getFullName()));
            }

            $entityManager->flush();

            $this->addFlash('success', sprintf('%s a été mis·e à jour.', $personnalite->getFullName()));

            return $this->redirectToRoute('admin_personnalites_index');
        }

        return $this->render('admin/personnalite/form.html.twig', [
            'form' => $form,
            'personnalite' => $personnalite,
        ]);
    }

    #[Route('/{id}/toggle-visibility', name: 'toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(
        Request $request,
        Personnalite $personnalite,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->isCsrfTokenValid('toggle-visibility-personnalite-'.$personnalite->getId(), $request->request->get('_token'))) {
            $personnalite->setVisible(!$personnalite->isVisible());
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '%s est désormais %s sur le site public.',
                $personnalite->getFullName(),
                $personnalite->isVisible() ? 'visible' : 'masqué·e'
            ));
        }

        return $this->redirectToRoute('admin_personnalites_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Personnalite $personnalite,
        EntityManagerInterface $entityManager,
        PersonnalitePhotoUploader $photoUploader,
    ): Response {
        if ($this->isCsrfTokenValid('delete-personnalite-'.$personnalite->getId(), $request->request->get('_token'))) {
            $photoUploader->remove($personnalite->getPhoto());
            $entityManager->remove($personnalite);
            $entityManager->flush();

            $this->addFlash('success', sprintf('%s a été supprimé·e.', $personnalite->getFullName()));
        }

        return $this->redirectToRoute('admin_personnalites_index');
    }

    private function generateUniqueSlug(Personnalite $personnalite, PersonnaliteRepository $personnaliteRepository): string
    {
        $slugger = new AsciiSlugger();
        $base = strtolower((string) $slugger->slug($personnalite->getFullName()));
        $slug = $base;
        $suffix = 2;

        while ($personnaliteRepository->findOneBySlug($slug) !== null) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
