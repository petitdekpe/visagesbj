<?php

namespace App\Entity;

use App\Enum\PersonnaliteCategory;
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

    /**
     * Traduction fongbe du prénom/nom, affichée par le bouton de bascule
     * FR/fongbe sur la page détail (voir les champs *Fongbe plus bas).
     * Vide par défaut : la fiche retombe alors sur firstName/lastName.
     */
    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    private ?string $firstNameFongbe = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    private ?string $lastNameFongbe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $role = null;

    /**
     * Active l'affichage éditorial structuré (Parcours / Contribution au
     * Bénin / Rayonnement / Actualité / Sources) sur la fiche détail.
     * false par défaut : affichage simple historique (paragraphe unique,
     * Wikipédia + réalisations) tant qu'une personne n'a pas explicitement
     * activé la nouvelle mise en page, même si les champs ci-dessous sont
     * renseignés.
     */
    #[ORM\Column(options: ['default' => false])]
    private bool $editorialEnabled = false;

    /**
     * Parcours de la personnalité (ex-champ "bio") : un paragraphe par ligne
     * vide, mise en emphase via <strong>/<em> (rendu |raw dans le template).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $parcours = null;

    /**
     * Ce que la personnalité a concrètement apporté au Bénin. Même
     * convention de mise en forme que le parcours.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contributionBenin = null;

    /**
     * Rayonnement national/international de la personnalité. Même
     * convention de mise en forme que le parcours.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rayonnement = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $actualiteDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $actualiteText = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Merci de renseigner une URL valide.')]
    #[Assert\Length(max: 255)]
    private ?string $actualiteUrl = null;

    /**
     * Assigned one person at a time via /admin — never guessed or bulk-derived
     * (see App\Enum\PersonnaliteCategory). Null means "not yet classified",
     * which is expected for most of the roster until curated.
     */
    #[ORM\Column(enumType: PersonnaliteCategory::class, nullable: true)]
    private ?PersonnaliteCategory $category = null;

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

    /**
     * Sources / références citées, une par ligne : "Texte" ou
     * "Texte | URL" — même format que les réalisations. Affichées en bas
     * de la fiche détail.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $sources = null;

    /**
     * Traduction fongbe des champs éditoriaux ci-dessus. Renseignés au cas
     * par cas (actuellement seule la fiche de Fabroni Bill Yoclounon les
     * utilise, via un bouton de bascule FR/fongbe sur sa page détail) —
     * quand un champ est vide, la fiche retombe sur le texte français.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $roleFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $parcoursFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contributionBeninFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rayonnementFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $actualiteTextFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $achievementsFongbe = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $sourcesFongbe = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $slug = '';

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $visible = true;

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

    public function getFirstNameFongbe(): ?string
    {
        return $this->firstNameFongbe;
    }

    public function setFirstNameFongbe(?string $firstNameFongbe): static
    {
        $this->firstNameFongbe = $firstNameFongbe;

        return $this;
    }

    public function getLastNameFongbe(): ?string
    {
        return $this->lastNameFongbe;
    }

    public function setLastNameFongbe(?string $lastNameFongbe): static
    {
        $this->lastNameFongbe = $lastNameFongbe;

        return $this;
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

    public function isEditorialEnabled(): bool
    {
        return $this->editorialEnabled;
    }

    public function setEditorialEnabled(bool $editorialEnabled): static
    {
        $this->editorialEnabled = $editorialEnabled;

        return $this;
    }

    public function getParcours(): ?string
    {
        return $this->parcours;
    }

    public function setParcours(?string $parcours): static
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getContributionBenin(): ?string
    {
        return $this->contributionBenin;
    }

    public function setContributionBenin(?string $contributionBenin): static
    {
        $this->contributionBenin = $contributionBenin;

        return $this;
    }

    public function getRayonnement(): ?string
    {
        return $this->rayonnement;
    }

    public function setRayonnement(?string $rayonnement): static
    {
        $this->rayonnement = $rayonnement;

        return $this;
    }

    public function getActualiteDate(): ?\DateTimeImmutable
    {
        return $this->actualiteDate;
    }

    public function setActualiteDate(?\DateTimeImmutable $actualiteDate): static
    {
        $this->actualiteDate = $actualiteDate;

        return $this;
    }

    public function getActualiteText(): ?string
    {
        return $this->actualiteText;
    }

    public function setActualiteText(?string $actualiteText): static
    {
        $this->actualiteText = $actualiteText;

        return $this;
    }

    public function getActualiteUrl(): ?string
    {
        return $this->actualiteUrl;
    }

    public function setActualiteUrl(?string $actualiteUrl): static
    {
        $this->actualiteUrl = $actualiteUrl;

        return $this;
    }

    public function getCategory(): ?PersonnaliteCategory
    {
        return $this->category;
    }

    public function setCategory(?PersonnaliteCategory $category): static
    {
        $this->category = $category;

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

    public function getSources(): ?string
    {
        return $this->sources;
    }

    public function setSources(?string $sources): static
    {
        $this->sources = $sources;

        return $this;
    }

    public function getRoleFongbe(): ?string
    {
        return $this->roleFongbe;
    }

    public function setRoleFongbe(?string $roleFongbe): static
    {
        $this->roleFongbe = $roleFongbe;

        return $this;
    }

    public function getParcoursFongbe(): ?string
    {
        return $this->parcoursFongbe;
    }

    public function setParcoursFongbe(?string $parcoursFongbe): static
    {
        $this->parcoursFongbe = $parcoursFongbe;

        return $this;
    }

    public function getContributionBeninFongbe(): ?string
    {
        return $this->contributionBeninFongbe;
    }

    public function setContributionBeninFongbe(?string $contributionBeninFongbe): static
    {
        $this->contributionBeninFongbe = $contributionBeninFongbe;

        return $this;
    }

    public function getRayonnementFongbe(): ?string
    {
        return $this->rayonnementFongbe;
    }

    public function setRayonnementFongbe(?string $rayonnementFongbe): static
    {
        $this->rayonnementFongbe = $rayonnementFongbe;

        return $this;
    }

    public function getActualiteTextFongbe(): ?string
    {
        return $this->actualiteTextFongbe;
    }

    public function setActualiteTextFongbe(?string $actualiteTextFongbe): static
    {
        $this->actualiteTextFongbe = $actualiteTextFongbe;

        return $this;
    }

    public function getAchievementsFongbe(): ?string
    {
        return $this->achievementsFongbe;
    }

    public function setAchievementsFongbe(?string $achievementsFongbe): static
    {
        $this->achievementsFongbe = $achievementsFongbe;

        return $this;
    }

    public function getSourcesFongbe(): ?string
    {
        return $this->sourcesFongbe;
    }

    public function setSourcesFongbe(?string $sourcesFongbe): static
    {
        $this->sourcesFongbe = $sourcesFongbe;

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

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

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
