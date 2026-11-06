# Handover, PokePieceTCG theme 0.1.0 (work in progress)

**Install (staging only first):** `scripts/package-theme.sh` → upload `dist/pokepiece-0.1.0.zip`. Activate only on the protected staging copy. Do not activate on production before the parity audit and merchant sign-off.

**Merchant-editable data (product edit screen → "PokePiece storefront facts"):** fulfilment mode, format, shrink/condition note, service flag. Unset mode shows no label (never "sealed"). `wp pokepiece suggest-modes` prints title-based proposals, writes nothing; `wp pokepiece apply-modes --file=confirmed.csv` writes only merchant-confirmed rows. Options: `pokepiece_service_product_ids` (default `[686]`), `pokepiece_dispatch_text`, `pokepiece_trading_details`.
**Staging flag:** `define('PP_STAGING', true);` adds noindex/nofollow (still protect by access control).
**Newsletter:** attach `add_filter('pokepiece_newsletter_subscribe', ...)` returning `true|WP_Error`; until then UI is absent.
**Rollback:** reactivate the previous theme (WoodMart), theme-only change; the theme writes only product meta keys `_pp_mode _pp_format _pp_condition _pp_is_service` and options above, no tables. Never restore a DB to undo presentation.
**Local harness:** `scripts/dev-wp.sh <dir>`, `PP_SNAPSHOT=… scripts/seed-fixtures.php` (refuses non-localhost), `BASE=… node tests/e2e/storefront.cjs`. Fixture data/product images were fetched from the public shop for local review only and are not committed.
