<?php

namespace App\Service;

use App\Entity\AdminUser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Shared by the public "forgot password" flow and the admin user management
 * page's "send a reset link" action.
 */
class AdminPasswordResetMailer
{
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire(env: 'MAILER_FROM_ADDRESS')] private readonly string $fromAddress,
        #[Autowire(env: 'MAILER_FROM_NAME')] private readonly string $fromName,
    ) {
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function sendResetEmail(AdminUser $user): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe admin')
            ->htmlTemplate('admin/emails/reset_password.html.twig')
            ->context([
                'resetUrl' => $this->urlGenerator->generate(
                    'admin_reset_password',
                    ['token' => $resetToken->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]);

        $this->mailer->send($email);
    }
}
