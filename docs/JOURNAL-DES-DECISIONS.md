# Journal des décisions — Projet HENNÉ MUSLIM

> Ce document trace chaque décision validée par le propriétaire du projet,
> conformément à la méthode de travail convenue : **aucune décision importante
> n'est prise sans validation explicite**.
>
> Référence fonctionnelle : cahier des charges « HENNÉ MUSLIM » v1.0 du 5 octobre 2026.

## Décisions validées

| # | Date | Sujet | Décision | Contexte |
|---|------|-------|----------|----------|
| 1 | 2026-10-05 | Projet | Le projet officiel est **HENNÉ MUSLIM** (plateforme web henné : vitrine + admin). La mention initiale « entreprise de livraison » est caduque. | Validation explicite après détection de l'incohérence. |
| 2 | 2026-10-05 | Cadrage | Projet **personnel**, envergure adaptée, pas de soutenance ni de deadline imposée. Niveau Laravel : intermédiaire. | Réponses au questionnaire de cadrage. |
| 3 | 2026-10-05 | Livrables | **Aucun MCD/MLD** ne sera produit. La conception des données vit dans les migrations, modèles, relations et règles de validation Laravel. | Demande explicite + section 11 et 14 du cahier des charges. |
| 4 | 2026-10-05 | Rendez-vous | Le créneau souhaité est exprimé de façon **souple** : date souhaitée + préférence de moment (matin / après-midi / soir) + texte libre optionnel. Pas d'heure précise imposée, cohérent avec l'absence de confirmation automatique. | Validation explicite. |
| 5 | 2026-10-05 | Langue | Site public et administration en **français uniquement**. | Validation explicite. |

| 6 | 2026-10-05 | Galerie | Une réalisation peut appartenir à **plusieurs catégories** (relation many-to-many, cases à cocher en admin). | Recommandation validée en bloc. |
| 7 | 2026-10-05 | Base de données | **SQLite en développement** (contrainte sandbox), migrations 100 % compatibles MySQL, configuration MySQL documentée pour la production. | Recommandation validée en bloc. |
| 8 | 2026-10-05 | Authentification | **Auth admin sur mesure minimale** : page de connexion unique, mécanismes natifs Laravel (hachage, middleware, throttling), pas d'inscription publique. | Recommandation validée en bloc. |
| 9 | 2026-10-05 | Démarrage | **Approche mixte** : socle technique d'abord (neutre visuellement), puis propositions de directions visuelles à choisir. | Recommandation validée en bloc. |

| 10 | 2026-11-02 | Direction visuelle | **B — Élégance nuit** : fond brun profond (#201613), ivoire (#f3eadf), accents cuivre (#c98a5b) et or (#e8c893). Titres en Cormorant Garamond auto-hébergée. Aperçus comparatifs dans `docs/maquettes/`. | Choix exprimé via la demande d'adaptation du template d'accueil au design B. |

| 11 | 2026-11-02 | Stratégie de code | Le template d'accueil fourni était un **prototype de référence**. L'application complète est **reconstruite dans ce dépôt**, phase par phase, en reprenant les bonnes idées du prototype (helper `Parametre::valeur()`, rail de galerie, lightbox, CTA flottant, SEO JSON-LD…). | Validation explicite. |
| 12 | 2026-11-02 | Conventions | Nommage **français** pour le domaine métier (modèles `Realisation`, `Categorie`, `Prestation`, `Promotion`, `Temoignage`, `RendezVous`, `Cliente`, `Visite`, `Message`, `Parametre` ; colonnes françaises), cohérent avec le prototype et le site francophone. Code d'infrastructure Laravel en anglais standard. | Proposé ; à confirmer avec le schéma de données. |

| 13 | 2026-11-02 | Schéma de données | **Validation intégrale** de `docs/SCHEMA-DONNEES.md` v1 : nommage français, table `rendez_vous` unique (statut + source), 5 types de prix, visites à crédit manuel traçable, verrou d'autorisation des témoignages, paramètres clé/valeur. Ordre de construction en 9 étapes validé. | Validation explicite (« Je valide tout tel quel »). |

| 14 | 2026-11-02 | Rendez-vous — CTA | Le bouton principal « Prendre rendez-vous » (en-tête, hero, bandeau final) pointe vers le **formulaire de demande** `/rendez-vous` ; WhatsApp reste proposé en alternative sur la page du formulaire et dans le pied de page. | Validation explicite (option « Vers le formulaire »). |

| 15 | 2026-11-02 | Rendez-vous — calendrier admin | **Deux vues** : grille mensuelle (pastilles par statut, demandes en attente en pointillés sur leur date souhaitée, clic sur un jour → détail) + vue liste filtrable par statut. 100 % serveur, sans librairie JS. | Validation explicite (option « Les deux vues »). |

| 16 | 2026-11-02 | Rendez-vous — réponse WhatsApp | Bouton « Répondre sur WhatsApp » prérempli dans l'admin, affiché **seulement si le contact laissé ressemble à un numéro de téléphone**. Jamais d'envoi automatique. | Validation explicite (« Oui »). |

## Contraintes d'environnement constatées (sandbox de développement)

- Pas de PHP/Composer système ni d'accès aux dépôts apt / Packagist.
- Solution mise en place : PHP 8.5.2 natif + Composer 2.9.3 (binaires distribués via npm,
  installés dans `/home/user/tools/php85`), dépendances Composer téléchargées via
  `api.github.com` (accessible) grâce au `composer.lock`.
- Aucun serveur MySQL/MariaDB installable dans le sandbox (réseau restreint).

## Points en attente de validation

- Relation réalisation ↔ catégories (simple ou multiple).
- Base de données de développement (SQLite en dev / MySQL documenté pour la prod).
- Version de Laravel (13.x proposé).
- Approche de l'authentification administrateur.
- Ordre des prochaines phases (maquette d'abord ou socle d'abord).

## Points ouverts du cahier des charges (section 16) — à traiter plus tard

Identité visuelle, coordonnées réelles (WhatsApp…), détails prix/devise, catégories
initiales, processus témoignages, récompense fidélité, champs clientes, exploitation
(hébergement, domaine), conformité légale. Pendant le développement : valeurs
d'exemple clairement factices + paramètres administrables.
