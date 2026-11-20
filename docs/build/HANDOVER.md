# Handover, PokePieceTCG theme 0.1.0 (work in progress)

**Install (staging only first):** `scripts/package-theme.sh` → upload `dist/pokepiece-0.1.0.zip`. Activate only on the protected staging copy. Do not activate on production before the parity audit and merchant sign-off.

**Merchant-editable data (product edit screen → "PokePiece storefront facts"):** fulfilment mode, format, shrink/condition note, service flag. Unset mode shows no label (never "sealed"). `wp pokepiece suggest-modes` prints title-based proposals, writes nothing; `wp pokepiece apply-modes --file=confirmed.csv` writes only merchant-confirmed rows. Options: `pokepiece_service_product_ids` (default `[686]`), `pokepiece_dispatch_text`, `pokepiece_trading_details`.
**Staging flag:** `define('PP_STAGING', true);` adds noindex/nofollow (still protect by access control).
**Newsletter:** attach `add_filter('pokepiece_newsletter_subscribe', ...)` returning `true|WP_Error`; until then UI is absent.
**Rollback:** reactivate the previous theme (WoodMart), theme-only change; the theme writes only product meta keys `_pp_mode _pp_format _pp_condition _pp_is_service` and options above, no tables. Never restore a DB to undo presentation.
**Local harness:** `scripts/dev-wp.sh <dir>`, `PP_SNAPSHOT=… scripts/seed-fixtures.php` (refuses non-localhost), `BASE=… node tests/e2e/storefront.cjs`. Fixture data/product images were fetched from the public shop for local review only and are not committed.


## Logo, favicon and the product watermark (added 20 Nov 2026)
- **Logo**: the new wordmark-only logo (no Poké Ball) is used in the header, footer, sign-in popup and the link-preview image. The full pack he supplied is kept in `brand/logo-pack/`. The pack's SVG is a trace of a 793 px PNG; if an original vector exists, swap it in for large prints.
- **Favicon / app icons**: rebuilt from the red "P" of the new logo (a full wordmark is unreadable at 32 px). Files are in `assets/brand/`.
- **Watermark**: a transparent, tilted pattern of the logo is drawn over every product photo in the shop (cards, search, quick view, product page), automatically for every product including new ones. Settings > PokePiece: on/off and strength (default 15%). The photo files are not changed.
  - It is a visual layer. Someone who saves the raw image file rather than a screenshot gets the unmarked original. To make it permanent in the files themselves we would need to generate marked copies on upload and regenerate existing photos; that changes how the photos look in Google Images and Merchant Center (which rejects watermarked photos), so it is a decision for him, not a default.
  - While it is on, the product page photo has no zoom popup and does not link to the original file (otherwise one click would show the clean image).
