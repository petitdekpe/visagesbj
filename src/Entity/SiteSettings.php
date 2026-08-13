<?php

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Singleton-style settings row (address/phone/email/publication director)
 * edited from /admin and displayed on the footer, contact page and legal
 * notice. See SiteSettingsRepository::getSettings() for how the single row
 * is fetched/created.
 */
#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
#[ORM\Table(name: 'site_settings')]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Merci de renseigner une adresse e-mail valide.')]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $publicationDirector = null;

    #[ORM\Column]
    private bool $gaEnabled = false;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^G-[A-Z0-9]+$/', message: "L'identifiant doit être au format G-XXXXXXXXXX (Google Analytics 4).")]
    private ?string $gaMeasurementId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPublicationDirector(): ?string
    {
        return $this->publicationDirector;
    }

    public function setPublicationDirector(?string $publicationDirector): static
    {
        $this->publicationDirector = $publicationDirector;

        return $this;
    }

    public function isGaEnabled(): bool
    {
        return $this->gaEnabled;
    }

    public function setGaEnabled(bool $gaEnabled): static
    {
        $this->gaEnabled = $gaEnabled;

        return $this;
    }

    public function getGaMeasurementId(): ?string
    {
        return $this->gaMeasurementId;
    }

    public function setGaMeasurementId(?string $gaMeasurementId): static
    {
        $this->gaMeasurementId = $gaMeasurementId;

        return $this;
    }

    /**
     * True only once GA is both switched on and has an ID to send data to —
     * the single condition every template/consent check should use instead
     * of re-deriving it from the two raw fields.
     */
    public function isGaConfigured(): bool
    {
        return $this->gaEnabled && null !== $this->gaMeasurementId && '' !== $this->gaMeasurementId;
    }

    #[Assert\Callback]
    public function validateGaMeasurementId(ExecutionContextInterface $context): void
    {
        if ($this->gaEnabled && !$this->gaMeasurementId) {
            $context->buildViolation("Renseignez l'identifiant de mesure pour activer Google Analytics.")
                ->atPath('gaMeasurementId')
                ->addViolation();
        }
    }
}
