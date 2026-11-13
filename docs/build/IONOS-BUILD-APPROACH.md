# IONOS build approach (our standard, his data)

Status: plan plus locally tested code. Nothing here has been run on the client's IONOS site.

## Decision
Keep his WooCommerce data in place (products, orders, customers, stock, gateways, shipping zones, tax). Run it on our own standard:

- Theme `pokepiece-editorial` for everything visual.
- Plugin `pokepiece-core` for business rules (8-hour free shipping, category setup, data audit), so rules survive a theme change.
- The old theme, page builder and Code Snippets are retired only after parity checks pass. We do not depend on them.

## Order of work (staging first)
1. Take a full IONOS backup (files and database). Clone to staging. Keep the backup outside the public repo.
2. Install `pokepiece-core`, run `wp pokepiece audit` (read-only). Review: missing images, no category, wrong game category (e.g. Abyss Eye under One Piece), duplicates, unset fulfilment labels, placeholder descriptions.
3. Run `wp pokepiece ensure-categories` (adds Other TCGs with children and Accessories; never edits or deletes).
4. Activate the theme on staging. Preserve product, page and category IDs and URLs; check permalinks and 404s.
5. 8-hour free shipping: export his existing snippet from Code Snippets and compare behaviour with ours (statuses counted, which methods are free, tax, guest vs account). Enable ours in WooCommerce > PokePiece rules, then deactivate his snippet so the rule is not applied twice.
6. Test on staging: payments in each gateway's test mode, accounts, orders, shipping and tax, Privacy link, basket and checkout (classic and blocks), mobile on real devices, emails.
7. Customer walkthrough, then go live in a quiet period.

## Rollback
Re-activate the previous theme, deactivate `pokepiece-core`, re-enable his snippet. Data is untouched by our code, apart from the order meta `_ppc_window_start` and `_ppc_window_free`, which is harmless.

## Verified vs not
- Verified locally (throwaway fixture data): plugin and theme load; free-shipping logic, 11 assertions; audit and category setup.
- Not verified: anything on IONOS, real gateways, mail, the original snippet's exact behaviour, real devices.

## Open items
- His exported snippet (to match the 8-hour rule exactly).
- Whether "LIVE RIP AND SHIP" titled products stay listed without the badge.
- Abyss Eye re-categorisation, needs his confirmation.
- SEO pass (quoted separately) is not started.
