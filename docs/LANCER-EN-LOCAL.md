# Lancer HENNÉ MUSLIM sur ta machine (5 minutes)

Le bac à sable de développement se met en veille entre les sessions de travail :
son lien de prévisualisation n'est donc pas permanent. Pour explorer le site
tranquillement, le plus confortable est de le lancer en local.

## Prérequis

- PHP ≥ 8.2 avec les extensions `sqlite3`, `gd`, `mbstring` (incluses dans la
  plupart des installations : XAMPP, Laragon, Herd, `php` Homebrew, paquets Linux…)
- Composer
- Node.js ≥ 20 (avec npm)
- Git

Vérification rapide :

```bash
php -v && composer -V && node -v
php -m | grep -E "sqlite3|gd|mbstring"
```

## Installation

```bash
# 1. Récupérer le code (branche de travail du projet)
git clone -b arena/01a10ac0-mon-ecole https://github.com/el-anniou/mon_ecole.git henne-muslim
cd henne-muslim

# 2. Dépendances
composer install
npm install && npm run build

# 3. Configuration + base de données SQLite
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # crée aussi le compte admin
php artisan db:seed --class=DemoSeeder   # données de démonstration
php artisan storage:link            # images publiques

# 4. Lancer !
php artisan serve
```

Le site est alors sur **http://localhost:8000** et l'administration sur
**http://localhost:8000/admin** :

| | |
|---|---|
| E-mail | `admin@exemple.test` |
| Mot de passe | `changer-ce-mot-de-passe` |

(Personnalisables via `ADMIN_EMAIL` / `ADMIN_PASSWORD` dans `.env` **avant**
le `migrate --seed`.)

## Astuces

- **Réinitialiser les données de démo** : `php artisan migrate:fresh --seed && php artisan db:seed --class=DemoSeeder && php artisan storage:link`
- **Parcours de test complet** : voir `docs/GUIDE-DE-TEST.md`
- **Suite de tests automatisés** (96 tests) : `php artisan test`
- Si les styles semblent absents : relancer `npm run build`.

## Rester à jour

À chaque étape terminée, le code est poussé sur la même branche :

```bash
git pull
composer install && npm install && npm run build
php artisan migrate
```
