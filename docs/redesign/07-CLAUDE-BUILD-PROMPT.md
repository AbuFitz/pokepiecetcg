# Claude implementation prompt

Paste the block below into Claude with access to `AbuFitz/pokepiecetcg` and the required hosting/admin connections. This research pack authorises no paid purchase or production change on its own; the project owner's live instructions control release authorisation.

---

Use **$onesimplesite-web-designer** to build the PokePieceTCG redesign in `AbuFitz/pokepiecetcg`. Use **$onesimplesite-research** for targeted evidence gaps. Canonical suite: https://github.com/AbuFitz/onesimplesite-web-designer — read `chatgpt/ONESIMPLESITE_CONTEXT.md` and the actual installed skill instructions. Do not use the skill-lab workflow to redesign the shop. If a named skill is missing, report it and follow your applicable missing-skill rule; do not pretend to have invoked it.

Read repository instructions and all of `docs/redesign/README.md`, 01–06, 08–10, inventories, `visual-audit.md` and `brand/README.md`. Treat this as a **System ecommerce** project with one editorial signature. Start implementation after discovery; don't stop with another generic plan or homepage-only shell.

## Non-negotiable outcome

Full native WordPress/WooCommerce redesign on the client's existing IONOS setup, preserving the domain, existing page structure, published page/product/category IDs and URLs, active functionality, orders, customers, stock, gateways, shipping, tax and account endpoints. Contract: 14-day turnaround. Rebuild the visual experience deliberately; existing styling is not a reference to preserve.

The repository currently contains research/brand assets, not a verified export of the client's WordPress installation. Get the real staging source/configuration through authorised agency access. Do not assume the empty/new repo means permission to invent a replacement backend. Do not create a Next.js/Shopify/Vercel storefront and call it ready to transfer to IONOS. Build-time tooling is acceptable; deployment must run in the actual supported WordPress/PHP environment.

## Discovery that must happen first

Confirm exact IONOS package and deployment capabilities, WordPress/Woo/PHP versions, active parent/child theme (public evidence strongly indicates WoodMart), Elementor/template setup, licence ownership, active plugins and custom snippets. Record checkout type (classic/Blocks), payment and express methods, mail path, tax/shipping zones, cache, webhooks/cron, newsletter provider, consent tooling and all current functionality.

Make private backups and a protected isolated staging environment. Disable real customer mail, payments/webhooks and feeds before staging tests. Never commit customer data, production dumps, keys, backups or licences to this public repository. Audit what currently works before replacing anything. Carry on with independent design/component work if one external connection is unavailable and report only the dependent blocker.

## Art direction

Follow “The Collector's Counter”: warm off-white shopping surfaces, ink typography, controlled red actions and yellow accents, authentic product photography, soft framed navigation, rounded buttons and fluid useful component transitions. Latest user direction: avoid boxy sections; keep the product layout open, use 14–24px component radii and pill-shaped action buttons, with a thoughtfully framed header. Follow `10-CURVED-NAV-AND-MOTION.md`. Recommended starting fonts: Barlow Condensed for short display headings and Inter for UI; render the requested specimens and verify current licence/files before committing. No cartoon UI, cosmic glitter wallpaper, generic SaaS cards, purple glow, glass panels, gratuitous 3D, full-screen scrolling intro or fake metrics.

Hero: short left-aligned statement and one clear shop action; a real product composition with one cropped circular brand motif. Mobile gets its own shallow composition; don't merely stack a tall desktop scene. Products should become reachable early. Use the supplied logo pack; favicon is symbol only. Full-colour vector derivatives are clearly labelled reconstructions; compare with source. Do not invent product renders, prices or availability. No unlicensed competitor imagery.

## Deliver the whole experience

Build home; shop/category/search and pagination; every product state; cart drawer and full cart; retained checkout and order confirmation; account/login/reset/orders/addresses/active extension endpoints; About; Contact; all retained policies; useful 404/errors. Keep the global navigation coherent across pages.

Complete mobile menu, predictive search, filters/sort/applied chips, product cards/quick view, image lightbox, quantity/variation selection, sticky phone purchase action, service info, cart errors, forms, newsletter popup and consent preferences. Optional stock-alert/promo/wishlist components get honest dependency flags. Follow the component/state specification rather than making decorative buttons.

Critical domain rules: sealed delivery, live opening and no-shrink/condition must be unmistakable. Do not default unknown mode to sealed. The shipping-upgrade product currently asks for TikTok username/order reference in checkout notes: preserve its workflow and direct product path, but exclude it from hero/new/related merchandise discovery. Merchant must resolve the category conflict and near-duplicate OP14 names before data changes. The site has many out-of-stock items: build an honest zero-stock state. Never fake stock to make the preview look busy.

Use Woo as the authority for prices/tax/stock/cart/order state. Reuse the supported current theme/checkout features where sensible; verify all extension compatibility. Prefer hooks over broad template copies. Never expose Woo admin API keys or create an independent cart/payment implementation. Test proper currency minor units and variation/session behaviour.

## Integration and scope discipline

You may use the connected plugins, official documentation tools, browser tools, terminal, installed design/font/assets skills and other relevant authorised capabilities. Inspect their real availability and use the right one for each gap. Research current official documentation instead of relying on memorised APIs. Never claim a tool action or integration you did not perform. Do not purchase services/extensions, send client messages, alter DNS/hosting contracts or enable live payments without the necessary project-owner instruction.

Keep a short fact ledger: verified / inferred / provisional / blocking. Maintain a capability register with designed / connected / enabled / verified columns. UI-only optional components must stay off in production until their real provider and operating workflow are connected. Newsletter signup is not recurring paid subscriptions. TikTok catalogue/advertising integration is not proof of bidirectional UK Shop stock/order sync; new sync is separately scoped.

Preserve SEO with exact URLs, crawlable category/product links, meaningful metadata, correct canonicals and sitemaps, truthful Product/Offer/Breadcrumb/Organization data and no duplicated conflicting schema. Seller Vinted reviews are not product verified-purchase ratings. No GTIN guesses, keyword stuffing or copied unsupported claims. Verify mobile zoom, keyboard/focus, real consent blocking/withdrawal, image sizing, font loading and performance on IONOS.

## Working sequence and evidence

1. Inventory/staging/fact ledger and theme-route decision with concrete reasons.
2. Runnable native spine, typography specimen and rendered home/listing/product at 390 and 1440px. Use the chosen direction as the system, not a one-page art exercise.
3. Full commerce/component implementation using real staging data, safe labelled fixtures only for otherwise unavailable states.
4. Service pages, forms/email, optional component activation rules, SEO/a11y/performance.
5. Meaningful automated regression checks and production build/package; inspect the actual rendered screens and critical journeys. Test widths 320/390/430/768/1440, 200% text, reduced motion and keyboard. Record actual phone/browser availability rather than claiming device testing from desktop emulation.
6. Staging gateway/stock/mail/account/consent tests matching the installed extension stack. Screenshots alone are not proof. Fix defects before reporting completion.
7. Deliver source, installable theme/plugin ZIPs, editable content/configuration instructions, change manifest, rollback and test evidence. Production promotion is selective: never replace the live database with the staging database. Preserve orders arriving during the build.

Work persistently, make routine reversible decisions using this pack, and avoid repeated broad questionnaires. Ask only for an actual missing fact, new spend or release decision that cannot be safely inferred. Show the exact dependency and consequence. Give concise progress updates: what now works, what was verified, material blocker and next deliverable. Final report must distinguish implemented, verified, disabled optional features and anything still blocking release. Do not label a mock integration, fixture, untested gateway or screenshots-only preview “complete”.

---
