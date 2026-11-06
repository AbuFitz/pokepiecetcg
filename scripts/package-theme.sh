#!/usr/bin/env bash
# Builds dist/pokepiece-<version>.zip (installable via Appearance > Themes > Add New > Upload) plus SHA-256 manifest.
set -euo pipefail
cd "$(dirname "$0")/.."
V=$(grep -m1 '^Version:' wordpress/theme/pokepiece-editorial/style.css | awk '{print $2}')
find wordpress/theme/pokepiece-editorial -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
mkdir -p dist; rm -f "dist/pokepiece-editorial-$V.zip"
(cd wordpress/theme && zip -qr "../../dist/pokepiece-editorial-$V.zip" pokepiece-editorial -x '*.DS_Store')
(cd dist && sha256sum "pokepiece-editorial-$V.zip" > "pokepiece-editorial-$V.sha256" && cat "pokepiece-editorial-$V.sha256")
