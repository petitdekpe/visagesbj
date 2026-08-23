<?php

namespace App\Enum;

/**
 * Fixed taxonomy for classifying personnalités, assigned one at a time via
 * /admin (never guessed in bulk) — see project memory on tying decorative
 * counts/labels to real, validated data rather than inferred placeholders.
 */
enum PersonnaliteCategory: string
{
    case Musique = 'musique';
    case Sport = 'sport';
    case Entrepreneuriat = 'entrepreneuriat';
    case Litterature = 'litterature';
    case Art = 'art';
    case Science = 'science';
    case Politique = 'politique';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Musique => 'Musique',
            self::Sport => 'Sport',
            self::Entrepreneuriat => 'Entrepreneuriat & innovation',
            self::Litterature => 'Littérature & savoir',
            self::Art => 'Arts visuels',
            self::Science => 'Science & médecine',
            self::Politique => 'Politique & administration',
            self::Autre => 'Autre',
        };
    }
}
