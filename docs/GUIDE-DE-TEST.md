# HENNÉ MUSLIM — Guide de test (étapes 1 à 6)

> Dernière mise à jour : 02/11/2026 — après l'étape 6 (rendez-vous & calendrier).
> Ce guide couvre tout ce qui est construit : authentification admin, paramètres,
> galerie, prestations & promotions, témoignages, rendez-vous.

---

## 1. Préparer l'environnement

### Accès

| Quoi | Où |
|---|---|
| Site public | `/` (prévisualisation du serveur, port 8000) |
| Administration | `/admin` (redirige vers la connexion si besoin) |
| Connexion admin | `/admin/connexion` |

**Identifiants de développement** : `admin@exemple.test` / `changer-ce-mot-de-passe`
(définis par le seeder, modifiables via les variables `ADMIN_EMAIL` / `ADMIN_PASSWORD` du `.env`).

### Données de démonstration

Déjà chargées. Pour les recharger (sans doublon, le seeder est idempotent) :

```bash
php artisan db:seed --class=DemoSeeder
```

Contenu : 9 réalisations (dont 1 brouillon et 3 sans titre), 4 catégories,
4 prestations (un exemple de chaque type de prix), 2 promotions (1 active,
1 expirée), 4 témoignages (2 publiés, 1 autorisé non publié, 1 sans autorisation),
6 rendez-vous (2 demandes en attente, 2 confirmés **volontairement en conflit**,
1 terminé, 1 refusé). Tout est étiqueté « (exemple) » — jamais de fausse donnée réaliste.

### Tests automatisés (96 tests, 278 assertions)

```bash
php artisan test
```

---

## 2. Tester le site public

### 2.1 Accueil `/`

- [ ] Hero avec titre/texte administrables et collage d'images.
- [ ] Section **« Offres du moment »** : l'« Offre découverte (exemple) » s'affiche ; l'« Offre passée (exemple) » est **introuvable** (expirée → masquée automatiquement).
- [ ] Section **« Mes réalisations »** : 6 vignettes max + filtres par catégorie.
- [ ] Section **« Mes prestations »** : 3 cartes avec prix doré.
- [ ] Section **« Elles en parlent »** : uniquement Nora et S. (les 2 témoignages publiés).
- [ ] Les boutons « Prendre rendez-vous » (en-tête, hero, bandeau final) ouvrent `/rendez-vous`.

💡 **Principe à vérifier partout** : une section sans contenu disparaît complètement (pas de bloc vide).

### 2.2 Galerie `/realisations`

- [ ] 8 réalisations publiées (le brouillon R-0009 est invisible).
- [ ] Filtres par catégorie (pilules) — l'URL reste propre (`?categorie=mains`).
- [ ] Clic sur une vignette → fiche du modèle avec référence stable (ex. R-0001).
- [ ] Visionneuse plein écran (clic sur l'image), navigation clavier ← →.
- [ ] Bouton **« Je veux ce modèle »** : ouvre WhatsApp avec le message prérempli
      `Bonjour ! Je veux ce modèle : {titre} ({référence}) {url}`.
- [ ] Bouton **« Demander un rendez-vous »** : ouvre le formulaire avec le modèle pré-attaché.
- [ ] Bouton « Partager » (copie du lien ou partage natif mobile).
- [ ] Les réalisations **sans titre** s'affichent proprement (référence seule).

### 2.3 Prestations `/prestations`

Vérifier le rendu des **5 types de prix** (CDC §7) :

| Prestation démo | Type | Rendu attendu |
|---|---|---|
| Henné mariée | fourchette | `80 € – 150 €` |
| Henné fête | à partir de | `À partir de 30 €` |
| Motif minimaliste | fixe | `15 €` |
| Projet personnalisé | sur demande | `Prix sur demande` |
| *(à créer en admin)* | non affiché | aucun prix visible |

- [ ] Durées lisibles (« 1 h 30 », « 45 min »).
- [ ] Encadré « Offres du moment » en haut (promotion active uniquement).

### 2.4 Témoignages `/temoignages`

- [ ] Seuls **Nora** et **S.** apparaissent (publiés + autorisés).
- [ ] **Imane** (autorisée mais non publiée) et **Cliente discrète** (sans autorisation) sont invisibles.
- [ ] Mention « affiché avec l'accord explicite de sa cliente ».

### 2.5 Demande de rendez-vous `/rendez-vous`

- [ ] Formulaire **sans compte** : nom, contact, prestation (facultative, avec prix), date souhaitée (passé refusé), moment préféré (pilules Matin/Après-midi/Soir), message.
- [ ] Envoyer une demande valide → message de remerciement précisant
      **« Ce message ne vaut pas confirmation »** (aucune confirmation automatique, CDC §8).
- [ ] Envoyer sans nom → erreurs de validation en français, saisie conservée.
- [ ] La demande apparaît **« En attente »** dans l'admin, source « Via le site ».
- [ ] Lien alternatif « ou écrivez-moi directement sur WhatsApp ».

---

## 3. Tester l'administration

### 3.1 Authentification

- [ ] `/admin` sans connexion → redirection vers `/admin/connexion`.
- [ ] Mauvais mot de passe → erreur en français, pas d'indice sur le champ fautif.
- [ ] Limitation d'essais (5 tentatives puis blocage temporaire).
- [ ] Connexion OK → tableau de bord ; déconnexion → retour connexion.
- [ ] Aucune page d'inscription n'existe (admin mono-utilisatrice).

### 3.2 Paramètres

Test le plus parlant : **tout le site réagit immédiatement**.

- [ ] Changer la **devise** `€` → `Ar` → tous les prix publics suivent.
- [ ] Vider le **numéro WhatsApp** → tous les boutons WhatsApp du site disparaissent
      (jamais de numéro inventé) ; le remettre → ils reviennent.
- [ ] Modifier titre du hero, texte d'accueil, nom de marque → visibles sur `/`.

### 3.3 Réalisations & catégories

- [ ] Créer une réalisation avec photo → vignette + grande image WebP générées, référence auto (R-0010…).
- [ ] Publier/dépublier → effet immédiat sur la galerie publique.
- [ ] Réordonner (boutons ↑ ↓) → l'ordre public suit.
- [ ] Supprimer → les fichiers images disparaissent aussi du stockage.
- [ ] Catégories : créer/renommer/masquer ; une catégorie vide n'apparaît pas dans les filtres publics.

### 3.4 Prestations & promotions

- [ ] Créer une prestation de chaque type : les champs de montant exigés changent selon le type
      (fourchette → min **et** max, max ≥ min ; sur demande → aucun montant).
- [ ] Créer une promotion avec une date de fin **demain** → en ligne ; passer la fin à **hier** → badge « Expirée » en admin et disparition du site public.
- [ ] L'historique des promotions passées reste consultable en admin.

### 3.5 Témoignages — le verrou de consentement (CDC §8)

- [ ] « Cliente discrète (exemple) » : la publication est **« verrouillée »** tant que l'autorisation n'est pas cochée.
- [ ] Cocher l'autorisation **sans date** → la date du jour est tracée automatiquement.
- [ ] Publier « Imane (exemple) » → elle apparaît sur le site public.
- [ ] Éditer « Nora » et **décocher** l'autorisation → dépubliée automatiquement,
      message « retiré du site », disparue de l'accueil et de `/temoignages`.

### 3.6 Rendez-vous — liste, calendrier, conflits (CDC §9)

**Vue liste** (`Admin → Rendez-vous`) :
- [ ] Filtres par statut avec compteurs ; demandes en attente épinglées en haut.
- [ ] **Badge rouge « ⚠ Conflit de créneau »** sur Faniry et Mirana (exemples qui se chevauchent) — simple signalement, rien n'est bloqué.
- [ ] **« Répondre sur WhatsApp »** présent pour les contacts téléphoniques, absent pour les e-mails (Hasina, Lova).
- [ ] Changement rapide de statut (menu + OK) ; confirmer une demande sans heure → rappel « Pensez à fixer la date et l'heure ».

**Vue calendrier** :
- [ ] Navigation mois précédent/suivant ; aujourd'hui encadré d'or.
- [ ] Pastilles : vert = confirmé, bleu = terminé, barré = refusé/annulé, **pointillés dorés = demande en attente** sur sa date souhaitée.
- [ ] Clic sur un jour → détail en dessous avec accès « Modifier ».

**Création manuelle** :
- [ ] « Nouveau rendez-vous » → statut confirmé par défaut, date/heure + durée ; la fiche est marquée « Ajout manuel ».
- [ ] Notes privées : visibles en admin uniquement, jamais sur le site.

---

## 4. Les verrous métier à vérifier absolument (critères CDC)

| # | Verrou | Comment le constater |
|---|---|---|
| 1 | Promotion expirée/dépubliée **jamais** visible | « Offre passée (exemple) » absente de `/` et `/prestations` |
| 2 | Témoignage **jamais** publié sans autorisation | Bouton verrouillé + test automatisé qui tente de forcer en base |
| 3 | **Aucune** confirmation automatique de RDV | Message public explicite ; la demande naît « En attente » |
| 4 | Numéro WhatsApp jamais inventé | Vider le paramètre → tous les boutons disparaissent |
| 5 | Contenu vide → section absente | Dépublier tout → l'accueil reste propre |
| 6 | Brouillons invisibles | R-0009 absent de la galerie publique |

---

## 5. Récapitulatif technique

- **96 tests automatisés** (`php artisan test`) couvrent chaque module, y compris les verrous ci-dessus.
- Commits par étape : `0c15c58` (auth) → `e4b9d52` (paramètres) → `b28a88d` (galerie) → `342679f` (prestations/promotions) → `e2294d3` (témoignages) → `0867665` (rendez-vous).
- Reste à construire : étape 7 (clientes & fidélité), étape 8 (messages & tableau de bord enrichi), étape 9 (finitions : page contact, SEO, documentation de déploiement MySQL).
