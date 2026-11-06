#!/usr/bin/env bash
# Local THROWAWAY WordPress + WooCommerce + SQLite harness for theme development.
# NOT the client's staging. Contains only fixtures. Usage: scripts/dev-wp.sh <dir> [port]
set -euo pipefail
DIR=${1:?target dir}; PORT=${2:-8088}; DL=${DL_CACHE:-$DIR/.dl}
REPO="$(cd "$(dirname "$0")/.." && pwd)"
mkdir -p "$DIR" "$DL"
fetch(){ [ -s "$DL/$2" ] || curl -sSL -o "$DL/$2" "$1"; }
fetch https://wordpress.org/latest.zip wp.zip
fetch https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip woo.zip
fetch https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip sqlite.zip
fetch https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar wp-cli.phar
if [ ! -f "$DIR/wp/wp-load.php" ]; then
  unzip -q "$DL/wp.zip" -d "$DIR" && rm -rf "$DIR/wp" && mv "$DIR/wordpress" "$DIR/wp"
  unzip -q "$DL/woo.zip" -d "$DIR/wp/wp-content/plugins"
  unzip -q "$DL/sqlite.zip" -d "$DIR/wp/wp-content/plugins"
  cp "$DIR/wp/wp-content/plugins/sqlite-database-integration/db.copy" "$DIR/wp/wp-content/db.php"
  sed -i "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$DIR/wp/wp-content/plugins/sqlite-database-integration#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" "$DIR/wp/wp-content/db.php"
  cat > "$DIR/wp/wp-config.php" <<PHP
<?php
define('DB_NAME','x');define('DB_USER','x');define('DB_PASSWORD','x');define('DB_HOST','x');
define('WP_HOME','http://localhost:$PORT');define('WP_SITEURL','http://localhost:$PORT');
define('WP_DEBUG',true);define('WP_DEBUG_DISPLAY',false);define('WP_DEBUG_LOG',true);
define('DISABLE_WP_CRON',true);define('FS_METHOD','direct');
\$table_prefix='wp_';
if(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');
require_once ABSPATH.'wp-settings.php';
PHP
fi
ln -sfn "$REPO/wordpress/theme/pokepiece" "$DIR/wp/wp-content/themes/pokepiece"
WP="php $DL/wp-cli.phar --path=$DIR/wp --allow-root"
$WP core is-installed 2>/dev/null || {
  $WP core install --url="http://localhost:$PORT" --title="PokePieceTCG DEV FIXTURE" --admin_user=dev --admin_password=dev-only-local --admin_email=dev@example.invalid --skip-email
  $WP plugin activate woocommerce
}
echo "Run: php -S localhost:$PORT -t $DIR/wp   (then seed: scripts/seed-fixtures.sh $DIR)"
