<?php

namespace App\Controller\Admin;

use App\Form\ForgotPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\AdminUserRepository;
use App\Service\AdminPasswordResetMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/admin')]
class ForgotPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly AdminUserRepository $adminUserRepository,
        private readonly AdminPasswordResetMailer $resetMailer,
    ) {
    }

    #[Route('/mot-de-passe-oublie', name: 'admin_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminUser = $this->adminUserRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($adminUser) {
                try {
                    $this->resetMailer->sendResetEmail($adminUser);
                } catch (ResetPasswordExceptionInterface) {
                    // Deliberately silent: the flash message below is identical
                    // whether the account exists or not, or the throttle was hit —
                    // this endpoint must never reveal which admin emails exist.
                }
            }

            $this->addFlash('success', 'Si un compte existe avec cet e-mail, un lien de réinitialisation vient de lui être envoyé.');

            return $this->redirectToRoute('admin_login');
        }

        return $this->render('admin/forgot_password/request.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'admin_reset_password')]
    public function reset(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ?string $token = null,
    ): Response {
        if (null !== $token) {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('admin_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('Aucun jeton de réinitialisation trouvé dans la session.');
        }

        try {
            $adminUser = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            $this->addFlash('error', 'Ce lien de réinitialisation n\'est plus valide, demandez-en un nouveau.');

            return $this->redirectToRoute('admin_forgot_password_request');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            $adminUser->setPassword($passwordHasher->hashPassword($adminUser, $form->get('plainPassword')->getData()));
            $entityManager->flush();

            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé, vous pouvez vous connecter.');

            return $this->redirectToRoute('admin_login');
        }

        return $this->render('admin/forgot_password/reset.html.twig', [
            'form' => $form,
        ]);
    }
}
