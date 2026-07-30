<?php

namespace App\Controller;

use App\Repository\PersonnaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_personnalites_')]
class PersonnaliteController extends AbstractController
{
    #[Route('/66-visages', name: 'index')]
    public function index(PersonnaliteRepository $personnaliteRepository): Response
    {
        return $this->render('personnalite/index.html.twig', [
            'personnalites' => $personnaliteRepository->findVisibleOrdered(),
        ]);
    }

    /**
     * Consent page sent privately to each personality via their own personalized
     * link ({slug}/consentement) so they can authorize the use of their name and
     * image for the campaign (APDP — loi n° 2009-09). Checking the box and
     * submitting records the IP address, timestamp and user agent of whoever
     * checked it, as evidence of consent.
     */
    #[Route('/{slug}/consentement', name: 'consent')]
    public function consent(
        Request $request,
        PersonnaliteRepository $personnaliteRepository,
        EntityManagerInterface $entityManager,
        string $slug,
    ): Response {
        $personnalite = $personnaliteRepository->findOneBySlug($slug);

        if (null === $personnalite) {
            throw $this->createNotFoundException('Cette personnalité n\'existe pas.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('consentement-'.$personnalite->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException();
            }

            if ('1' === $request->request->get('consent')) {
                $personnalite->setConsentAccepted(true);
                $personnalite->setConsentedAt(new \DateTimeImmutable());
                $personnalite->setConsentIp((string) $request->getClientIp());
                $personnalite->setConsentUserAgent(substr((string) $request->headers->get('User-Agent'), 0, 255));
                $entityManager->flush();

                $this->addFlash('success', 'Merci, votre consentement a bien été enregistré.');
            } else {
                $this->addFlash('error', 'Merci de cocher la case pour valider votre consentement.');
            }

            return $this->redirectToRoute('app_personnalites_consent', ['slug' => $slug]);
        }

        return $this->render('personnalite/consent.html.twig', [
            'personnalite' => $personnalite,
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

        if (null === $personnalite || !$personnalite->isVisible()) {
            throw $this->createNotFoundException('Cette personnalité n\'existe pas.');
        }

        return $this->render('personnalite/show.html.twig', [
            'personnalite' => $personnalite,
            'others' => $personnaliteRepository->findRandomOthers($personnalite),
        ]);
    }
}
