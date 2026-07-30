<?php

namespace App\Entity;

use App\Repository\PersonnaliteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonnaliteRepository::class)]
#[ORM\Table(name: 'personnalite')]
class Personnalite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\Length(max: 120)]
    private string $firstName = '';

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 150)]
    private string $lastName = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $role = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Merci de renseigner une URL valide.')]
    #[Assert\Length(max: 255)]
    private ?string $wikipediaUrl = null;

    /**
     * One achievement/distinction per line, formatted as "Texte" or
     * "Texte | URL" (URL optional — e.g. a YouTube clip for an artist).
     * Parsed for display in the show template; at most 3 are shown.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $achievements = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $slug = '';

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $consentAccepted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $consentedAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $consentIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $consentUserAgent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getWikipediaUrl(): ?string
    {
        return $this->wikipediaUrl;
    }

    public function setWikipediaUrl(?string $wikipediaUrl): static
    {
        $this->wikipediaUrl = $wikipediaUrl;

        return $this;
    }

    public function getAchievements(): ?string
    {
        return $this->achievements;
    }

    public function setAchievements(?string $achievements): static
    {
        $this->achievements = $achievements;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isConsentAccepted(): bool
    {
        return $this->consentAccepted;
    }

    public function setConsentAccepted(bool $consentAccepted): static
    {
        $this->consentAccepted = $consentAccepted;

        return $this;
    }

    public function getConsentedAt(): ?\DateTimeImmutable
    {
        return $this->consentedAt;
    }

    public function setConsentedAt(?\DateTimeImmutable $consentedAt): static
    {
        $this->consentedAt = $consentedAt;

        return $this;
    }

    public function getConsentIp(): ?string
    {
        return $this->consentIp;
    }

    public function setConsentIp(?string $consentIp): static
    {
        $this->consentIp = $consentIp;

        return $this;
    }

    public function getConsentUserAgent(): ?string
    {
        return $this->consentUserAgent;
    }

    public function setConsentUserAgent(?string $consentUserAgent): static
    {
        $this->consentUserAgent = $consentUserAgent;

        return $this;
    }
}
