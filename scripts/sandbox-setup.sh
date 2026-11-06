#!/bin/bash
# =============================================================================
# Installation de l'outillage PHP/Composer dans le sandbox de développement.
# Contexte : le sandbox n'a ni PHP, ni Composer, ni accès apt/Packagist.
# Solution : binaires PHP 8.5 natifs distribués via npm (@libphp/almalinux-9-v85,
# le runtime PHP de Vercel), compatibles Debian 12. Composer est inclus.
# Les dépendances Composer s'installent ensuite via api.github.com (accessible)
# grâce au composer.lock versionné.
# Usage : bash scripts/sandbox-setup.sh
# =============================================================================
set -euo pipefail

TOOLS=/home/user/tools
PHPDIR=$TOOLS/php85

if [ -x "$PHPDIR/bin/php" ] && "$PHPDIR/bin/php" -v >/dev/null 2>&1; then
  echo "PHP déjà installé : $($PHPDIR/bin/php -v | head -1)"
  exit 0
fi

echo "== Téléchargement du runtime PHP via npm =="
rm -rf /tmp/libphp && mkdir -p /tmp/libphp && cd /tmp/libphp
npm pack @libphp/almalinux-9-v85@0.0.3 --silent
tar -xzf libphp-almalinux-9-v85-*.tgz

rm -rf "$PHPDIR" && mkdir -p "$PHPDIR" "$PHPDIR/bin"
cp -r package/native/* "$PHPDIR/"

EXTS=(bcmath ctype curl dom exif fileinfo gd iconv intl mbstring mysqlnd pdo \
      pdo_mysql pdo_sqlite sqlite3 phar posix simplexml tokenizer xml \
      xmlreader xmlwriter zip)
# Le php.ini est écrit à l'emplacement que le binaire charge par défaut
# (via le lien /var/task/php créé plus bas) : ainsi, TOUT processus PHP,
# y compris les sous-processus lancés par `artisan serve` ou Composer avec un
# environnement filtré, charge la même configuration sans dépendre de PHPRC.
{
  echo "extension_dir=$PHPDIR/php/modules"
  for e in "${EXTS[@]}"; do echo "extension=$e"; done
  echo "variables_order=EGPCS"
  echo "memory_limit=512M"
  echo "error_reporting=E_ALL"
  echo "display_errors=1"
  echo "upload_max_filesize=12M"
  echo "post_max_size=20M"
} > "$PHPDIR/php/php.ini"

cat > "$PHPDIR/bin/php" <<EOF
#!/bin/bash
exec $PHPDIR/php/php "\$@"
EOF
chmod +x "$PHPDIR/bin/php"

cat > "$PHPDIR/bin/composer" <<EOF
#!/bin/bash
export COMPOSER_CACHE_DIR=/tmp/composer-cache
exec $PHPDIR/bin/php $PHPDIR/php/composer "\$@"
EOF
chmod +x "$PHPDIR/bin/composer"

rm -rf /tmp/libphp

# Intégration système (le binaire a été compilé pour les serveurs Vercel,
# qui placent PHP dans /var/task) :
#  - bibliothèques partagées enregistrées via ldconfig ;
#  - lien /var/task/php vers notre installation pour que le binaire trouve
#    seul ses modules et son php.ini, même avec un environnement filtré.
if command -v sudo >/dev/null 2>&1; then
  echo "$PHPDIR/lib" | sudo tee /etc/ld.so.conf.d/php85.conf >/dev/null
  sudo ldconfig
  sudo mkdir -p /var/task
  sudo ln -sfn "$PHPDIR/php" /var/task/php
fi

echo "== Installé =="
"$PHPDIR/bin/php" -v | head -1
"$PHPDIR/bin/composer" --version 2>/dev/null | head -1
echo "Ajoute au PATH : export PATH=$PHPDIR/bin:\$PATH"
