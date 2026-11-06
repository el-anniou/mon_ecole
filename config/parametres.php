<?php

/*
|--------------------------------------------------------------------------
| Registre des paramètres administrables
|--------------------------------------------------------------------------
| Source de vérité de l'écran « Paramètres » de l'administration :
| groupes, libellés, types de champ et textes d'aide.
|
| Types disponibles : text, textarea, email, tel, url.
| Tous les paramètres sont facultatifs : le site s'adapte (une section ou
| un bouton disparaît plutôt que d'afficher une donnée inventée).
*/

return [

    'Identité & textes du site' => [
        'nom_marque' => [
            'libelle' => 'Nom de la marque',
            'type' => 'text',
            'aide' => 'Affiché dans l’en-tête, le pied de page et les titres de pages.',
        ],
        'slogan' => [
            'libelle' => 'Slogan',
            'type' => 'text',
            'aide' => 'Phrase courte qui accompagne le nom de la marque.',
        ],
        'hero_titre' => [
            'libelle' => 'Titre d’accueil',
            'type' => 'text',
            'aide' => 'Grand titre de la page d’accueil. À défaut : nom de la marque.',
        ],
        'hero_texte' => [
            'libelle' => 'Texte d’accueil',
            'type' => 'textarea',
            'aide' => 'Paragraphe d’introduction sous le titre d’accueil.',
        ],
        'presentation' => [
            'libelle' => 'Présentation de l’activité',
            'type' => 'textarea',
            'aide' => 'Texte plus complet (page contact, référencement).',
        ],
        'cta_texte' => [
            'libelle' => 'Texte du bouton d’action',
            'type' => 'text',
            'aide' => 'À défaut : « Prendre rendez-vous ».',
        ],
    ],

    'Contact' => [
        'telephone' => [
            'libelle' => 'Téléphone affiché',
            'type' => 'tel',
            'aide' => 'Tel qu’il doit apparaître sur le site.',
        ],
        'telephone_whatsapp' => [
            'libelle' => 'Numéro WhatsApp',
            'type' => 'tel',
            'aide' => 'Au format international (ex. +XXX XX XXX XX). Sans numéro, les boutons WhatsApp n’apparaissent pas.',
        ],
        'email' => [
            'libelle' => 'Adresse e-mail',
            'type' => 'email',
            'aide' => 'Adresse de contact publique.',
        ],
    ],

    'Réseaux sociaux' => [
        'instagram_url' => [
            'libelle' => 'Instagram',
            'type' => 'url',
            'aide' => 'Lien complet vers le profil.',
        ],
        'facebook_url' => [
            'libelle' => 'Facebook',
            'type' => 'url',
            'aide' => 'Lien complet vers la page.',
        ],
        'tiktok_url' => [
            'libelle' => 'TikTok',
            'type' => 'url',
            'aide' => 'Lien complet vers le profil.',
        ],
    ],

    'Informations pratiques' => [
        'adresse_publique' => [
            'libelle' => 'Adresse ou zone affichée',
            'type' => 'textarea',
            'aide' => 'Adresse publique ou zone desservie, telle qu’affichée sur le site.',
        ],
        'horaires' => [
            'libelle' => 'Horaires',
            'type' => 'textarea',
            'aide' => 'Jours et heures de disponibilité.',
        ],
        'devise' => [
            'libelle' => 'Devise des prix',
            'type' => 'text',
            'aide' => 'Symbole ou code affiché avec les prix (ex. €, Ar, MAD).',
        ],
    ],

];
