#!/usr/bin/env bash
# Runs every local test suite in the right order against the throwaway fixture site and finishes by reading the PHP error log.
# Needs: the local WordPress fixture running (scripts/dev-wp.sh), Playwright, and WP-CLI.
#   BASE=http://localhost:8088 WPDIR=/path/to/wp WPCLI="php /path/to/wp-cli.phar --allow-root" AXE=/path/to/axe.min.js scripts/run-tests.sh
# Static-demo suites (demo.cjs, demo-sd.cjs) are run separately against a served demo/ folder (see tests/README).
set -u
cd "$(dirname "$0")/.."
BASE=${BASE:-http://localhost:8088}; : "${WPDIR:?set WPDIR to the fixture WordPress folder}"; : "${WPCLI:?set WPCLI, e.g. 'php wp-cli.phar --allow-root'}"
export BASE WPCLI WPDIR
wp() { (cd "$WPDIR" && $WPCLI "$@" 2>&1 | grep -v -E "^(Notice|PHP Notice|Deprecated)" ); }
fails=0; run() { local n=$1; shift; local out; out=$("$@" 2>&1); local rc=$?; local last; last=$(echo "$out" | grep -E "ALL PASSED|passed|clean|FAILED|findings|FINDINGS" | tail -1); if [ $rc -eq 0 ]; then echo "PASS  $n   ${last:-ok}"; else echo "FAIL  $n   ${last:-see output}"; echo "$out" | grep -E "^FAIL|FINDINGS|   " | head -12; fails=$((fails+1)); fi; }
clear_limits() { cat > /tmp/ppc-clear.php <<'PHP'
<?php global $wpdb; $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%pp_acc_%' OR option_name LIKE '%pp_s_%'" );
PHP
  (cd "$WPDIR" && $WPCLI eval-file /tmp/ppc-clear.php >/dev/null 2>&1); }
LOG="$WPDIR/wp-content/debug.log"; [ -f "$LOG" ] && : > "$LOG"
echo "== PHP syntax"; bad=0; for f in $(find wordpress scripts tests -name '*.php'); do php -l "$f" >/dev/null 2>&1 || { echo "syntax error: $f"; bad=1; }; done; [ $bad -eq 0 ] && echo "PASS  all PHP files parse" || fails=$((fails+1))
echo "== JS syntax"; for f in wordpress/theme/pokepiece-editorial/assets/js/*.js wordpress/plugin/pokepiece-core/assets/*.js scripts/demo-shim.js tests/e2e/*.cjs; do node --check "$f" 2>/dev/null || { echo "FAIL  js syntax: $f"; fails=$((fails+1)); }; done
echo "== PHP unit (fixture orders)"; out=$(wp eval-file "$PWD/tests/php/free-shipping.test.php"); echo "$out" | grep -q "ALL PASSED" && echo "PASS  free-shipping rules" || { echo "FAIL  free-shipping rules"; echo "$out" | tail -5; fails=$((fails+1)); }
echo "== browser suites"
clear_limits; run storefront node tests/e2e/storefront.cjs
run special-delivery node tests/e2e/special-delivery.cjs
run responsiveness node tests/e2e/responsiveness.cjs
clear_limits; run security node tests/e2e/security.cjs
run seo node tests/e2e/seo.cjs
run a11y env AXE="${AXE:-}" node tests/e2e/a11y.cjs
run mobile-scroll node tests/e2e/mobile-scroll.cjs
clear_limits; run account node tests/e2e/account.cjs
wp option update pokepiece_promo_on yes >/dev/null; run consent node tests/e2e/consent.cjs; wp option update pokepiece_promo_on no >/dev/null
wp eval-file "$PWD/scripts/framing-fixture.php" >/dev/null; run framing node tests/e2e/framing.cjs; run watermark node tests/e2e/watermark.cjs; wp eval-file "$PWD/scripts/framing-fixture.php" cleanup >/dev/null
run resilience node tests/e2e/resilience.cjs
run sweep node tests/e2e/sweep.cjs
echo "== PHP error log after everything above"
if [ -f "$LOG" ]; then n=$(grep -c -E "PHP (Warning|Fatal|Parse|Deprecated|Notice)" "$LOG"); if [ "$n" -eq 0 ]; then echo "PASS  no PHP warnings, notices or errors logged"; else echo "FAIL  $n PHP messages logged:"; sed -E 's/^\[[^]]*\] //' "$LOG" | cut -c1-200 | sort | uniq -c | sort -rn | head -8; fails=$((fails+1)); fi; else echo "(no debug.log: enable WP_DEBUG_LOG on the fixture to include this check)"; fi
echo; [ $fails -eq 0 ] && echo "ALL SUITES PASSED" || echo "$fails SUITE(S) FAILED"; exit $fails
