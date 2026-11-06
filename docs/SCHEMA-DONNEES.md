# Schéma de données — proposition v1 (en attente de validation)

> Conformément à la décision n°3, **pas de MCD/MLD** : ce document est un plan
> de travail pour les migrations/modèles Laravel, qui seront la source de vérité.
> Types indiqués en syntaxe migration Laravel, 100 % compatibles MySQL.

## Tables

### `users` — administratrice (table Laravel standard)
Une seule utilisatrice en V1. Pas d'inscription publique.

### `parametres` — réglages administrables (clé/valeur)
| Colonne | Type | Notes |
|---|---|---|
| cle | string, unique | ex. `nom_marque`, `telephone_whatsapp`, `hero_titre`, `instagram_url` |
| valeur | text, nullable | |

Helper `Parametre::valeur(cle, defaut)` avec cache, repris du prototype.
Aucune coordonnée réelle n'est inventée : tout reste vide ou exemple factice
jusqu'à fourniture par la propriétaire.

### `categories`
| Colonne | Type | Notes |
|---|---|---|
| nom | string | |
| slug | string, unique | pour les URL et filtres |
| position | unsignedInteger | ordre d'affichage administrable |
| visible | boolean, défaut vrai | masquer sans supprimer |

### `realisations`
| Colonne | Type | Notes |
|---|---|---|
| reference | string, unique | identifiant stable pour liens + message WhatsApp (ex. `R-0012`) |
| titre | string, nullable | **facultatif** (CDC) |
| description | text, nullable | |
| texte_alternatif | string, nullable | accessibilité |
| image_path | string | original ; variantes générées à côté |
| publiee | boolean, défaut faux | brouillon/publiée |
| position | unsignedInteger | |
| realisee_le | date, nullable | date de création du motif, pour tri |

Pivot `categorie_realisation` (many-to-many, décision n°6).

### `prestations`
| Colonne | Type | Notes |
|---|---|---|
| nom | string | |
| description | text, nullable | |
| type_prix | string (enum applicatif) | `fixe`, `a_partir_de`, `fourchette`, `sur_demande`, `non_affiche` |
| prix_min | decimal(10,2), nullable | sert aussi de prix fixe |
| prix_max | decimal(10,2), nullable | pour `fourchette` |
| duree_minutes | unsignedInteger, nullable | indicatif |
| position | unsignedInteger | |
| publiee | boolean, défaut faux | |

Devise : paramètre global administrable (`parametres.devise`), pas par prestation.

### `promotions`
| Colonne | Type | Notes |
|---|---|---|
| titre | string | |
| texte | text | |
| debut / fin | date, nullable | période de validité |
| lien_action | string, nullable | |
| publiee | boolean, défaut faux | visible publiquement seulement si publiée ET dans la période |

### `temoignages`
| Colonne | Type | Notes |
|---|---|---|
| auteur | string | prénom ou pseudonyme selon consentement |
| contenu | text | |
| autorisation_affichage | boolean, défaut faux | **verrou CDC : jamais publié sans ça** |
| autorisation_notee_le | date, nullable | trace du consentement |
| autorisation_note | string, nullable | canal/preuve (ex. « accord WhatsApp 12/01 ») |
| publie | boolean, défaut faux | contrainte applicative : publiable seulement si autorisation |
| cliente_id | FK nullable | lien facultatif |

### `rendez_vous` — demandes ET rendez-vous (une seule table)
Une demande publique crée un enregistrement `en_attente` ; la propriétaire peut
aussi créer un rendez-vous manuellement. Évite la double saisie demande→RDV.
| Colonne | Type | Notes |
|---|---|---|
| statut | string (enum applicatif) | `en_attente`, `confirme`, `refuse`, `annule`, `termine` (CDC) |
| source | string | `site` ou `manuel` |
| nom_contact | string | nom donné dans la demande |
| moyen_contact | string | téléphone/WhatsApp/e-mail fourni |
| date_souhaitee | date, nullable | souhait exprimé (décision n°4) |
| periode_souhaitee | string, nullable | `matin`, `apres_midi`, `soir` |
| message | text, nullable | |
| prestation_id / realisation_id | FK nullables | prestation ou modèle concerné |
| cliente_id | FK nullable | associée lors du traitement |
| debut | datetime, nullable | fixé à la confirmation ou création manuelle |
| duree_minutes | unsignedInteger, nullable | |
| notes_privees | text, nullable | visibles uniquement en admin |

Signalement applicatif des conflits de créneaux (sans moteur de disponibilité).

### `clientes`
| Colonne | Type | Notes |
|---|---|---|
| nom | string | |
| telephone / email | string, nullables | |
| adresse, complement, ville, code_postal, pays | string, nullables | granularité CDC ; tout facultatif tant que non précisé |
| notes_privees | text, nullable | |

### `visites` — fidélité (carte de 5)
| Colonne | Type | Notes |
|---|---|---|
| cliente_id | FK | |
| visite_le | date | |
| prestation_id | FK nullable | |
| rendez_vous_id | FK nullable | trace, mais **jamais de crédit automatique** (CDC) |
| commentaire | string, nullable | |
| deleted_at | softDeletes | correction/annulation traçable |

Compteur = visites actives modulo 5 ; état « carte complète » ; récompense non
implémentée tant que sa règle n'est pas définie (CDC).

### `messages` — formulaire de contact
| Colonne | Type | Notes |
|---|---|---|
| nom | string | |
| contact | string | e-mail ou téléphone |
| contenu | text | |
| statut | string | `nouveau`, `lu`, `traite` |

## Ordre de construction proposé

1. **Auth admin + layout admin** (termine la phase « socle » du CDC)
2. **Paramètres** (+ helper cache) — beaucoup de pages en dépendent
3. **Catégories + Réalisations** (upload, variantes d'images, galerie publique, accueil réel)
4. **Prestations + Promotions** (pages publiques)
5. **Témoignages** (verrou d'autorisation)
6. **Rendez-vous** (formulaire public → traitement admin → calendrier)
7. **Clientes + Fidélité**
8. **Messages + Tableau de bord**
9. **Finitions** : SEO, performances, accessibilité, tests, documentation
