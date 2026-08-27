<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Form\AdminUserEmailType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/profil', name: 'admin_profile_')]
class ProfileController extends AbstractController
{
    private const SESSION_PENDING_TOTP_SECRET = 'admin_profile_pending_totp_secret';

    #[Route('', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var AdminUser $adminUser */
        $adminUser = $this->getUser();

        $emailForm = $this->createForm(AdminUserEmailType::class, $adminUser);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre e-mail a été mis à jour.');

            return $this->redirectToRoute('admin_profile_edit');
        }

        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted()) {
            if (!$passwordHasher->isPasswordValid($adminUser, $passwordForm->get('currentPassword')->getData())) {
                $passwordForm->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } elseif ($passwordForm->isValid()) {
                $adminUser->setPassword($passwordHasher->hashPassword($adminUser, $passwordForm->get('plainPassword')->getData()));
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

                return $this->redirectToRoute('admin_profile_edit');
            }
        }

        return $this->render('admin/profile/edit.html.twig', [
            'adminUser' => $adminUser,
            'emailForm' => $emailForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/2fa/activer', name: '2fa_enable')]
    public function enable2fa(Request $request, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        /** @var AdminUser $adminUser */
        $adminUser = $this->getUser();
        $session = $requestStack->getSession();

        if ($request->isMethod('POST')) {
            $secret = $session->get(self::SESSION_PENDING_TOTP_SECRET);
            $code = (string) $request->request->get('code');

            if ($secret && TOTP::createFromSecret($secret)->verify($code)) {
                $adminUser->setTotpSecret($secret);
                $entityManager->flush();
                $session->remove(self::SESSION_PENDING_TOTP_SECRET);

                $this->addFlash('success', 'La double authentification est activée.');

                return $this->redirectToRoute('admin_profile_edit');
            }

            $this->addFlash('error', 'Code invalide, réessayez.');
        }

        $secret = $session->get(self::SESSION_PENDING_TOTP_SECRET);
        if (!$secret) {
            $totp = TOTP::generate();
            $totp->setLabel($adminUser->getEmail());
            $totp->setIssuer('Hisse ton Drapeau');
            $secret = $totp->getSecret();
            $session->set(self::SESSION_PENDING_TOTP_SECRET, $secret);
        } else {
            $totp = TOTP::createFromSecret($secret);
            $totp->setLabel($adminUser->getEmail());
            $totp->setIssuer('Hisse ton Drapeau');
        }

        return $this->render('admin/profile/2fa_enable.html.twig', [
            'secret' => $secret,
            'provisioningUri' => $totp->getProvisioningUri(),
        ]);
    }

    #[Route('/2fa/desactiver', name: '2fa_disable', methods: ['POST'])]
    public function disable2fa(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var AdminUser $adminUser */
        $adminUser = $this->getUser();

        if ($this->isCsrfTokenValid('disable-own-2fa', $request->request->get('_token'))) {
            $adminUser->setTotpSecret(null);
            $entityManager->flush();

            $this->addFlash('success', 'La double authentification a été désactivée.');
        }

        return $this->redirectToRoute('admin_profile_edit');
    }
}
