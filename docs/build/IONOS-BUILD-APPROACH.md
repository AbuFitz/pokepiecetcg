# IONOS build approach (our standard, his data)

Status: plan plus locally tested code. Nothing here has been run on the client's IONOS site.

## Decision
Keep his WooCommerce data in place (products, orders, customers, stock, gateways, shipping zones, tax). Run it on our own standard:

- Theme `pokepiece-editorial` for everything visual.
- Plugin `pokepiece-core` for business rules (8-hour free shipping, category setup, data audit), so rules survive a theme change.
- The old theme, page builder and Code Snippets are retired only after parity checks pass. We do not depend on them.

## Order of work (staging first)
Same database, so there is no product, order or customer migration. The long part is verification, not copying.

1. Full IONOS backup (files and database), stored outside the public repo. Clone to staging (or use IONOS's staging if the plan has it).
2. Upload `pokepiece-core` and activate. Run the report (needs SSH; otherwise WooCommerce > PokePiece launch checks in the admin):
   ```bash
   wp pokepiece preflight
   wp pokepiece audit
   ```
   `preflight` lists the real payment gateways, shipping methods, taxes, required pages (including Privacy), the old Code Snippets entries, tracking or cookie plugins, and Special Delivery state. `audit` lists products with no image, small or odd-shaped images, wrong category, duplicates, unset fulfilment labels, placeholder descriptions. Both are read-only.
3. `wp pokepiece ensure-categories` (adds Other TCGs with Naruto, Demon Slayer, Solo Leveling, and Accessories; never edits or deletes).
4. Upload and activate the theme on staging. IDs and URLs are untouched; check permalinks and 404s. Then re-cut product image sizes once, because the theme stops WooCommerce hard-cropping photos:
   ```bash
   wp media regenerate --yes
   ```
   (or the Regenerate Thumbnails plugin, then remove it). Photos of any shape then show whole inside the square frame; the audit flags photos under 600px.
5. Special Delivery: WooCommerce > PokePiece rules > set the price (his current product's price), tick Enabled, set the reference label. Then set the old upgrade product to Draft or Private. The cart converts any old product line into the option, so nobody is charged twice. Decide whether it is taxable.
6. 8-hour free shipping: compare his exported Code Snippets entry with the preflight report (which statuses count, which methods go free, guest vs account, tax). Enable ours and switch his snippet off. Never run both.
7. Cookies: list what the live site actually sets (preflight names tracking plugins). Keep one consent tool: either the PokePiece prompt (gate scripts with `type="text/plain" data-pp-consent="analytics"`) or the existing one, not both. Review the wording and the Privacy Policy with the client.
8. Promo popup stays off until he supplies a real offer and code (Settings > PokePiece).
9. Test on staging: each payment gateway in its own test mode, accounts (register, sign in, reset email), orders, shipping and tax, Privacy link, basket and checkout, order emails arriving (IONOS may need SMTP), mobile on real devices.
10. Customer walkthrough, then go live in a quiet period.

## Rollback
Re-activate the previous theme, deactivate `pokepiece-core`, re-enable his snippet. Data is untouched by our code, apart from the order meta `_ppc_window_start` and `_ppc_window_free`, which is harmless.

## Verified vs not
- Verified locally (throwaway fixture data): plugin and theme load; free-shipping logic (11 assertions); Special Delivery fee, toggle, reference and order meta; cookie prompt, preferences and promo popup; account pages; product-image framing for six awkward shapes; audit, preflight and category setup; static demo simulation.
- Not verified: anything on IONOS, real gateways, mail, the original snippet's exact behaviour, real devices.

## Open items
- His exported snippet (to match the 8-hour rule exactly).
- Whether "LIVE RIP AND SHIP" titled products stay listed without the badge.
- Abyss Eye re-categorisation, needs his confirmation.
- SEO pass (quoted separately) is not started.
