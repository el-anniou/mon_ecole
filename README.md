# HENNÉ MUSLIM

Plateforme web de présentation, de prise de contact et de gestion des rendez-vous
pour une activité de tatouage au henné.

- **Site public** : accueil, galerie de réalisations filtrable, prestations et
  promotions, témoignages autorisés, contact et demande de rendez-vous sans compte.
- **Administration privée** (propriétaire uniquement) : tableau de bord, gestion des
  contenus, demandes, calendrier, fiches clientes et fidélité (carte de 5 visites).

> Référence fonctionnelle : cahier des charges v1.0 du 5 octobre 2026.
> Journal des décisions : [`docs/JOURNAL-DES-DECISIONS.md`](docs/JOURNAL-DES-DECISIONS.md).

## Stack technique

| Composant | Choix |
|---|---|
| Framework | Laravel 13 (PHP 8.5) |
| Base de données | SQLite en développement, MySQL en production |
| Vues | Blade |
| Interface | Bootstrap 5 + SCSS propre |
| Build front | Vite |

## Démarrage en développement

```bash
# 1. Outillage PHP/Composer (spécifique au sandbox — voir le script pour le contexte)
bash scripts/sandbox-setup.sh
export PATH=/home/user/tools/php85/bin:$PATH

# 2. Dépendances
composer install
npm install

# 3. Environnement
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 4. Lancer
npm run build        # ou `npm run dev` pour le rechargement à chaud
php artisan serve
```

## Compte administrateur

Le seeder (`php artisan db:seed`) crée l'unique compte administrateur à partir
des variables d'environnement `ADMIN_EMAIL`, `ADMIN_NOM` et `ADMIN_PASSWORD`.
Les valeurs par défaut (`admin@exemple.test` / `changer-ce-mot-de-passe`) sont
**réservées au développement local** : en production, définir ces variables
dans `.env` **avant** le premier seed. Connexion : `/admin/connexion`.

## Production (MySQL)

Dans `.env`, remplacer la configuration SQLite par :

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=henne_muslim
DB_USERNAME=...
DB_PASSWORD=...
```

Les migrations sont écrites pour être 100 % compatibles MySQL.

## Conventions du projet

- **Toutes les routes sont nommées** : familles `public.*` et `admin.*` ;
  aucune URL en dur dans les vues ou redirections.
- Principe directeur : **« moins, mais mieux »** — chaque fonctionnalité doit servir
  la visiteuse ou la gestion réelle de l'activité.
- Conception **mobile-first**, images optimisées.
- Interface entièrement en **français**.
