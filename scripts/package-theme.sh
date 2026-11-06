#!/usr/bin/env bash
# Builds dist/pokepiece-<version>.zip (installable via Appearance > Themes > Add New > Upload) plus SHA-256 manifest.
set -euo pipefail
cd "$(dirname "$0")/.."
V=$(grep -m1 '^Version:' wordpress/theme/pokepiece/style.css | awk '{print $2}')
find wordpress/theme/pokepiece -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
mkdir -p dist; rm -f "dist/pokepiece-$V.zip"
(cd wordpress/theme && zip -qr "../../dist/pokepiece-$V.zip" pokepiece -x '*.DS_Store')
(cd dist && sha256sum "pokepiece-$V.zip" > "pokepiece-$V.sha256" && cat "pokepiece-$V.sha256")
