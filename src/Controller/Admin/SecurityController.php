<?php

namespace App\Controller\Admin;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_personnalites_index');
        }

        return $this->render('admin/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the logout key on your firewall.');
    }

    #[Route('/admin/login/google', name: 'admin_login_google')]
    public function connectGoogle(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('google')->redirect(['email']);
    }

    /**
     * Handled entirely by App\Security\GoogleAuthenticator — this action
     * only exists so the redirect_route configured in knpu_oauth2_client.yaml
     * resolves to a route.
     */
    #[Route('/admin/login/google/check', name: 'admin_login_google_check')]
    public function connectGoogleCheck(): Response
    {
        throw new \LogicException('This route should be intercepted by GoogleAuthenticator.');
    }
}
