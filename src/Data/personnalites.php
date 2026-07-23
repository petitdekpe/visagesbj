<?php

/**
 * Liste de référence des personnalités affichées sur le site.
 *
 * Source : "Stratégie de campagne & rétroplanning" (SoLE SA / Ministère de la Culture,
 * des Arts et du Patrimoine), page 6 "LES 66 VISAGES DU BENIN" — cette page est
 * explicitement intitulée "Quelques noms" (liste illustrative, pas nécessairement
 * définitive), complétée par Angélique Kidjo et Mylène Flicka qui apparaissent dans
 * les visuels d'affichage (pages 8 et 10) mais pas dans le tableau de noms.
 *
 * IMPORTANT : cette liste contient 72 entrées, soit 6 de plus que les "66 visages"
 * du concept de campagne. La sélection finale des 66 officiels reste à arbitrer par
 * l'équipe campagne.
 *
 * Ce fichier n'est utilisé que pour l'ensemencement initial d'une base vide, via :
 *   php bin/console app:personnalites:load
 * Cette commande n'ajoute que les entrées manquantes et ne touche jamais à celles
 * déjà en base — la gestion au quotidien (ajout, modification, photo, suppression)
 * se fait ensuite via /admin, pas en éditant ce fichier.
 *
 * Le split prénom/nom est une heuristique (premier mot = prénom, reste = nom) posée
 * pour l'affichage en deux lignes des cartes ; à corriger au cas par cas si besoin.
 * "position" : 1-3 = mis en avant sur l'accueil (les 3 avec un visuel déjà produit),
 * 10 = reste de la liste, classé ensuite par nom alphabétique.
 */

return [
    // --- Mis en avant sur l'accueil (visuels déjà produits, cf. slides 8-10) ---
    [
        'firstName' => 'Angélique',
        'lastName' => 'Kidjo',
        'role' => 'Chanteuse, compositrice et militante béninoise, figure majeure de la musique africaine et lauréate de plusieurs Grammy Awards.',
        'position' => 1,
    ],
    [
        'firstName' => 'Bertin',
        'lastName' => 'Nahum',
        'role' => "Entrepreneur et ingénieur béninois, reconnu comme l'un des pionniers mondiaux de la robotique médicale.",
        'position' => 2,
    ],
    [
        'firstName' => 'Mylène',
        'lastName' => 'Flicka',
        'role' => "Entrepreneure, investisseuse et figure de l'innovation africaine, engagée dans le développement de l'écosystème technologique du continent.",
        'position' => 3,
    ],

    // --- Reste de la liste ("Quelques noms", page 6 du rétroplanning) ---
    ['firstName' => 'Pépé', 'lastName' => 'Oleka', 'role' => null, 'position' => 10],
    ['firstName' => 'Prof Eugène', 'lastName' => 'Zoumenou', 'role' => null, 'position' => 10],
    ['firstName' => 'Bella', 'lastName' => 'Agossou', 'role' => null, 'position' => 10],
    ['firstName' => 'Gilles', 'lastName' => 'Kounou', 'role' => null, 'position' => 10],
    ['firstName' => 'Wally', 'lastName' => 'Badarou', 'role' => null, 'position' => 10],
    ['firstName' => 'Stéphane', 'lastName' => 'Sessègnon', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'OPA', 'role' => null, 'position' => 10],
    ['firstName' => 'Sandra', 'lastName' => 'Idossou', 'role' => null, 'position' => 10],
    ['firstName' => 'Grégoire', 'lastName' => 'Ahongbonon', 'role' => null, 'position' => 10],
    ['firstName' => 'Godfrey', 'lastName' => 'Nzamujo', 'role' => null, 'position' => 10],
    ['firstName' => 'Florent', 'lastName' => 'Couao-Zotti', 'role' => null, 'position' => 10],
    ['firstName' => 'Marie-Cécile', 'lastName' => 'Zinsou', 'role' => null, 'position' => 10],
    ['firstName' => 'Vincent', 'lastName' => 'Ahéhéhinou', 'role' => null, 'position' => 10],
    ['firstName' => 'Romuald', 'lastName' => 'Hazoumè', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Tchif', 'role' => null, 'position' => 10],
    ['firstName' => 'Charly', 'lastName' => "d'Almeida", 'role' => null, 'position' => 10],
    ['firstName' => 'Marie-Élise', 'lastName' => 'Gbèdo', 'role' => null, 'position' => 10],

    ['firstName' => 'Gilles', 'lastName' => 'Louèkè', 'role' => null, 'position' => 10],
    ['firstName' => 'Pipi', 'lastName' => 'Wobaho', 'role' => null, 'position' => 10],
    ['firstName' => 'Steve', 'lastName' => 'Mounié', 'role' => null, 'position' => 10],
    ['firstName' => 'Lolo', 'lastName' => 'Andoche', 'role' => null, 'position' => 10],
    ['firstName' => 'Ignace', 'lastName' => 'Don Metok', 'role' => null, 'position' => 10],
    ['firstName' => 'Richard', 'lastName' => 'Flash', 'role' => null, 'position' => 10],
    ['firstName' => 'Richard', 'lastName' => 'Odjrado', 'role' => null, 'position' => 10],
    ['firstName' => 'Samuel', 'lastName' => 'Dossou Aworet', 'role' => null, 'position' => 10],
    ['firstName' => 'Djimon', 'lastName' => 'Houssou', 'role' => null, 'position' => 10],
    ['firstName' => 'Fabroni', 'lastName' => 'Bill Yoclounon', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Edgar Yves Jr.', 'role' => null, 'position' => 10],
    ['firstName' => 'Sidikou', 'lastName' => 'Karimou', 'role' => null, 'position' => 10],
    ['firstName' => 'Leonard', 'lastName' => 'Wantchekon', 'role' => null, 'position' => 10],
    ['firstName' => 'Ian', 'lastName' => 'Mahinmi', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'UPE', 'role' => null, 'position' => 10],
    ['firstName' => 'Claude', 'lastName' => 'Borna', 'role' => null, 'position' => 10],
    ['firstName' => 'Jean-Hugues', 'lastName' => 'Houinsou', 'role' => null, 'position' => 10],

    ['firstName' => 'Paulin J.', 'lastName' => 'Hountondji', 'role' => null, 'position' => 10],
    ['firstName' => 'Francois', 'lastName' => 'Okioh', 'role' => null, 'position' => 10],
    ['firstName' => 'Stanislas', 'lastName' => 'Adotevi', 'role' => null, 'position' => 10],
    ['firstName' => 'Noélie', 'lastName' => 'Yarigo', 'role' => null, 'position' => 10],
    ['firstName' => 'Odile', 'lastName' => 'Ahouanwanou', 'role' => null, 'position' => 10],
    ['firstName' => 'Coffi', 'lastName' => 'Codjia', 'role' => null, 'position' => 10],
    ['firstName' => 'Ihsane', 'lastName' => 'Adjanonhoun', 'role' => null, 'position' => 10],
    ['firstName' => 'Nafissath', 'lastName' => 'Radji', 'role' => null, 'position' => 10],
    ['firstName' => 'Raodath', 'lastName' => 'Aminou', 'role' => null, 'position' => 10],
    ['firstName' => 'Huguette', 'lastName' => 'Gnacadja Bokpè', 'role' => null, 'position' => 10],
    ['firstName' => 'Maryse', 'lastName' => 'Lokossou', 'role' => null, 'position' => 10],
    ['firstName' => 'Conceptia', 'lastName' => 'Ouinsou', 'role' => null, 'position' => 10],
    ['firstName' => 'William', 'lastName' => 'Akle', 'role' => null, 'position' => 10],
    ['firstName' => 'Mohamed', 'lastName' => 'Salifou', 'role' => null, 'position' => 10],
    ['firstName' => 'Cyprien', 'lastName' => 'Tokoudagba', 'role' => null, 'position' => 10],
    ['firstName' => 'Dominique', 'lastName' => 'Zinkpè', 'role' => null, 'position' => 10],

    ['firstName' => 'Karelle', 'lastName' => 'Vignon Vullierme', 'role' => null, 'position' => 10],
    ['firstName' => 'Senam', 'lastName' => 'Beheton', 'role' => null, 'position' => 10],
    ['firstName' => 'Joseph', 'lastName' => 'Aklé', 'role' => null, 'position' => 10],
    ['firstName' => 'Sagbohan', 'lastName' => 'Danialou', 'role' => null, 'position' => 10],
    ['firstName' => 'Zeynab', 'lastName' => 'Abib', 'role' => null, 'position' => 10],
    ['firstName' => 'Nel', 'lastName' => 'Oliver', 'role' => null, 'position' => 10],
    ['firstName' => 'Amir', 'lastName' => 'Alli', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Blaaz', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Vano', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Fanicko', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Madara', 'role' => null, 'position' => 10],
    ['firstName' => '', 'lastName' => 'Kalamoulaï', 'role' => null, 'position' => 10],
    ['firstName' => 'Sakinatou', 'lastName' => 'Harouna', 'role' => null, 'position' => 10],
    ['firstName' => 'Général Bertin', 'lastName' => 'Bada', 'role' => null, 'position' => 10],

    ['firstName' => 'Georgiana', 'lastName' => 'Viou', 'role' => null, 'position' => 10],
    ['firstName' => 'Pamella', 'lastName' => "N'ze Asseko", 'role' => null, 'position' => 10],
    ['firstName' => 'Bertille', 'lastName' => 'Guèdègbé Marcos', 'role' => null, 'position' => 10],
    ['firstName' => 'Jean-Luc', 'lastName' => 'Aplogan', 'role' => null, 'position' => 10],
    ['firstName' => 'Capitaine Elvire', 'lastName' => 'To', 'role' => null, 'position' => 10],
];
