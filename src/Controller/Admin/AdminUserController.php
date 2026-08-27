<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Form\AdminUserEmailType;
use App\Form\AdminUserType;
use App\Form\SetPasswordType;
use App\Repository\AdminUserRepository;
use App\Service\AdminPasswordResetMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/admin/utilisateurs', name: 'admin_users_')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(AdminUserRepository $adminUserRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'adminUsers' => $adminUserRepository->findBy([], ['email' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $adminUser = new AdminUser();

        $form = $this->createForm(AdminUserType::class, $adminUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminUser->setPassword($passwordHasher->hashPassword($adminUser, $form->get('plainPassword')->getData()));

            $entityManager->persist($adminUser);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le compte "%s" a été créé.', $adminUser->getEmail()));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        $emailForm = $this->createForm(AdminUserEmailType::class, $adminUser);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'e-mail a été mis à jour.');

            return $this->redirectToRoute('admin_users_edit', ['id' => $adminUser->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'adminUser' => $adminUser,
            'emailForm' => $emailForm,
            'setPasswordForm' => $this->createForm(SetPasswordType::class)->createView(),
        ]);
    }

    #[Route('/{id}/mot-de-passe', name: 'set_password', methods: ['POST'])]
    public function setPassword(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(SetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminUser->setPassword($passwordHasher->hashPassword($adminUser, $form->get('plainPassword')->getData()));
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le mot de passe de "%s" a été mis à jour.', $adminUser->getEmail()));
        } else {
            $this->addFlash('error', 'Le mot de passe n\'a pas pu être mis à jour.');
        }

        return $this->redirectToRoute('admin_users_edit', ['id' => $adminUser->getId()]);
    }

    #[Route('/{id}/envoyer-reinitialisation', name: 'send_reset', methods: ['POST'])]
    public function sendReset(Request $request, AdminUser $adminUser, AdminPasswordResetMailer $resetMailer): Response
    {
        if ($this->isCsrfTokenValid('send-reset-'.$adminUser->getId(), $request->request->get('_token'))) {
            try {
                $resetMailer->sendResetEmail($adminUser);
                $this->addFlash('success', sprintf('Un lien de réinitialisation a été envoyé à "%s".', $adminUser->getEmail()));
            } catch (ResetPasswordExceptionInterface $e) {
                $this->addFlash('error', 'Impossible d\'envoyer le lien pour le moment : '.$e->getReason());
            }
        }

        return $this->redirectToRoute('admin_users_edit', ['id' => $adminUser->getId()]);
    }

    #[Route('/{id}/desactiver-2fa', name: 'disable_2fa', methods: ['POST'])]
    public function disable2fa(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('disable-2fa-'.$adminUser->getId(), $request->request->get('_token'))) {
            $adminUser->setTotpSecret(null);
            $entityManager->flush();

            $this->addFlash('success', sprintf('La double authentification de "%s" a été désactivée.', $adminUser->getEmail()));
        }

        return $this->redirectToRoute('admin_users_edit', ['id' => $adminUser->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, AdminUser $adminUser, EntityManagerInterface $entityManager, AdminUserRepository $adminUserRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete-user-'.$adminUser->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_users_index');
        }

        if ($adminUser === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte depuis cette page.');

            return $this->redirectToRoute('admin_users_index');
        }

        if (count($adminUserRepository->findAll()) <= 1) {
            $this->addFlash('error', 'Impossible de supprimer le dernier compte administrateur.');

            return $this->redirectToRoute('admin_users_index');
        }

        $entityManager->remove($adminUser);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Le compte "%s" a été supprimé.', $adminUser->getEmail()));

        return $this->redirectToRoute('admin_users_index');
    }
}
