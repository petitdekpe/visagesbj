<?php

namespace App\Twig;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the site-wide settings (address/phone/email/publication director)
 * to every template as `site_settings()`, so the footer, contact page and
 * legal notice can read them without every controller having to pass them in.
 */
class SiteSettingsExtension extends AbstractExtension
{
    public function __construct(
        private readonly SiteSettingsRepository $siteSettingsRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_settings', $this->getSiteSettings(...)),
        ];
    }

    public function getSiteSettings(): SiteSettings
    {
        return $this->siteSettingsRepository->getSettings();
    }
}
