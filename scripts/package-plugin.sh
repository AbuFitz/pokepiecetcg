#!/usr/bin/env bash
# Builds dist/pokepiece-core-<version>.zip (Plugins > Add New > Upload) plus SHA-256.
set -euo pipefail
cd "$(dirname "$0")/.."
V=$(grep -m1 'Version:' wordpress/plugin/pokepiece-core/pokepiece-core.php | awk '{print $NF}')
find wordpress/plugin/pokepiece-core -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
mkdir -p dist; rm -f "dist/pokepiece-core-$V.zip"
(cd wordpress/plugin && zip -qr "../../dist/pokepiece-core-$V.zip" pokepiece-core -x '*.DS_Store')
(cd dist && sha256sum "pokepiece-core-$V.zip" > "pokepiece-core-$V.sha256" && cat "pokepiece-core-$V.sha256")
