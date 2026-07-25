<?php

namespace App\Controller\Admin;

use App\Form\SiteSettingsType;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/parametres', name: 'admin_settings_edit')]
class SiteSettingsController extends AbstractController
{
    public function __invoke(
        Request $request,
        SiteSettingsRepository $siteSettingsRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $settings = $siteSettingsRepository->getSettings();

        $form = $this->createForm(SiteSettingsType::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Les paramètres du site ont été mis à jour.');

            return $this->redirectToRoute('admin_settings_edit');
        }

        return $this->render('admin/settings/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
