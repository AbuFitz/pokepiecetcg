# PokePiece Editorial theme (v0.2.0)

Standalone native **classic WooCommerce theme**. It replaces the earlier thin WoodMart child-theme stub. Decision: WoodMart is a paid parent whose files/licence are not available to the build, so a child theme could not be installed or tested; every template here is bespoke anyway. **WoodMart stays installed and inactive** for one-click rollback. Before activating on staging, run the parity audit in `docs/build/LEDGER-AND-REGISTER.md` (Elementor/WoodMart shortcodes in page content, header builder, wishlist/quick-view features currently relied on).

Design: colour-blocked editorial system (red / ink / yellow / franchise colours), Archivo (variable width+weight, SIL OFL) + Caveat annotations (OFL), hard-offset "print" shadows, pill CTAs, ticket-cut corners. Native CSS/JS only: marquee ticker, scroll-driven parallax (CSS), reveal and pointer tilt (reduced-motion safe).

Data truth: prices/stock/cart/orders are WooCommerce's. Fulfilment mode/format/condition are merchant-set product meta (never inferred, never default to sealed). Service/add-on products (shipping upgrade) are excluded from discovery rows.

Owner-supplied (6 Nov 2026): TikTok `@pokepiecetcgg`, Discord invite, four reviews copied verbatim from the existing homepage (Vinted seller reviews, not product ratings). Override with options `pokepiece_tiktok_url`, `pokepiece_discord_url` or the `pokepiece_testimonials` filter. Story images: public-domain/CC0 Liebig trade cards (`assets/img/SOURCES.md`), decorative only.

Not verified: IONOS/staging, gateways, mail, consent, real devices, performance. See `docs/build/`.
