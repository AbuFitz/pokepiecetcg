# Build ledger, capability register and decisions (living)

Updated 6 Nov 2026. Environment: cloud session with **no** IONOS, WordPress-admin, SFTP, database or gateway access. Everything below says which side of that line it is on.

## Skills (honest status)
`$onesimplesite-web-designer` and `$onesimplesite-research` are **not installed** in this session (only the synced Anthropic skills exist) and could not be invoked. I read the canonical `SKILL.md`, `CLAUDE.md` and `chatgpt/ONESIMPLESITE_CONTEXT.md` from `AbuFitz/onesimplesite-web-designer` and followed them manually (System gear, ecommerce archetype, fact ledger, rendered verification). Research skill not used; evidence gaps were closed with read-only public fetches. Skill-lab not used.

## Fact ledger
| State | Fact |
|---|---|
| verified (public, 6 Nov) | Live theme assets are `woodmart` **8.4.1** (stylesheet URLs). Live viewport meta disables zoom. Public Store API: 23 products, only #686 shipping upgrade in stock, prices in minor unit 2 (GBP). Category 18 `pokemon-japanese` lists #782,699,686,174,172,158 though their Store API `categories` field is empty. #715 title says LIVE RIP AND SHIP but slug has no mode (OP14 near-duplicate family: #715, #780, #180). Namespaces show Elementor Pro, Rank Math, LiteSpeed, WooPayments/WooPay, All-in-One WP Migration, Jetpack, code-snippets. |
| verified (local) | Theme runs on WP 7.1.4 / WooCommerce 11.2.2 / PHP 8.3 **local throwaway** (SQLite). Client versions are unknown. |
| inferred | Classic vs Blocks checkout on live is unknown; theme styles both, only classic exercised. |
| provisional | Hero copy "Built for collectors."; dispatch line "1 to 3 working days" (from published shipping policy; owner must reconfirm); mode explanations; fixture page text. |
| blocking (needs agency access) | IONOS package/PHP/staging facility; WoodMart licence ownership; active plugin/snippet inventory; payment gateways, mail path, shipping/tax zones, cache, cron/webhooks, newsletter provider, consent tool; backups + protected staging; merchant answers in `docs/redesign/09`. |

## Update 6 Nov (v0.2.0)
Theme moved to `wordpress/theme/pokepiece-editorial` (standalone, replaces the ChatGPT WoodMart child-theme stub, which had no cart/search/filter/PDP logic and used a name-based service exclusion). Art direction changed to colour-blocked editorial; decorative shapes removed on owner instruction. Owner-supplied TikTok, Discord and four homepage reviews added (verified by owner instruction; reuse permission to be confirmed). TikTok *events* are the owner's statement; no event dates/details are shown. No TikTok feed embed (third-party tracking/consent not inventoried); links only. Framer components cannot be embedded natively; equivalent patterns rebuilt in CSS/JS. 20/20 local Playwright journeys pass. Still unverified: everything in 'Not done'.

## Update (later 6 Nov): content, account popup, SEO
- **Wording** now comes from the owner's existing pages (home, About, Contact, Shipping, Returns): "Built for collectors. Driven by adventure.", the four "why collectors choose" points, UK-based/ships from the UK, 1 to 3 working days, returns for unopened items only, info@pokepiecetcg.co.uk with 24 to 48h response. These are the owner's published claims (including "5.0 reviews from verified customers" and "100% genuine"); owner must keep them accurate. Live rip & ship removed as a home-page headline per owner direction; it remains a product label (the TikTok bio and posts show Rip & Ship is a TikTok activity - the owner decides how much to feature it).
- **Account popup**: native `wp_signon` / `wc_create_new_customer`, nonce, honeypot, IP throttle (8/15 min), generic errors. Registration tab only if WooCommerce registration is enabled. Tested locally on desktop and iPhone-13 emulation (14 checks). Not tested: password reset flow, email verification, an extension-based login, social login.
- **SEO**: yields to Rank Math/Yoast when active (live shop has Rank Math). Fallback title/meta/OG, Organization + WebSite(SearchAction) + FAQPage JSON-LD, real visible FAQ from published policies, crawlable category intros. Product/Breadcrumb schema stays WooCommerce's. Not verified: crawl/indexing, Rank Math interplay on staging, Search Console, rich-result validation.
- **TikTok swipe**: my own headless browser got an empty video list from TikTok; a TinyFish remote browser (clicked Refresh) returned 12 recent public posts. Covers (own TikTok content) and one-line summaries of his captions (customer @names removed) are bundled in `assets/social/` (snapshot 6 Nov 2026 - will age; refresh via Settings > PokePiece by pasting video URLs, or `video | cover | caption`). Cards link out to TikTok; no embeds/scripts load from TikTok. Content shows Rip & Ship streams, pulls, a TikTok Shop item and a £1-start 'Surprise Set' - owner decides emphasis. Follower counts deliberately not hard-coded.
- **World panels**: default = tight crops of the shop's own product photos; per-world override field for licensed key art. Franchise posters/key art were not sourced (copyright).
- **Payment marks**: Visa, Mastercard, American Express, Apple Pay, Google Pay shown in the footer by default; they MUST be reduced to the gateways actually enabled (filter `pokepiece_payment_methods`) before launch.

## Theme-route decision
Chosen: **purpose-built classic WooCommerce theme** (`wordpress/theme/pokepiece`), hooks over template copies (one override: `woocommerce/content-product.php`, for the card). Reasons: WoodMart is a paid theme whose licence/files I cannot obtain or redistribute, so a child theme cannot be built or tested here; the brief says existing styling is not to be preserved; classic hooks keep the current checkout/gateways. **Risk, unresolved:** switching theme drops WoodMart/Elementor-provided features (builder-built home page #24, header builder, wishlist, quick view, theme widgets/shortcodes in page content). A parity audit on staging is mandatory before release; the front page is PHP-driven so page #24 content is not used.

## Capability register
| Component | Designed | Connected | Enabled | Verified |
|---|---|---|---|---|
| Global framed header, nav disclosures, mobile menu | yes | n/a | yes | local fixture, Playwright (keyboard, Escape, focus return) |
| Predictive search (REST, OP14/OP-14/Pokémon) | yes | Woo query | yes | local fixture |
| Shop/category/pagination, URL filters, chips, sort, zero-result | yes | Woo query | yes | local fixture; format facet appears only when data set |
| Product card, quick view, PDP facts/mode callouts, delivery disclosure | yes | Woo | yes | local fixture |
| Fulfilment mode / format / condition / service flag | yes | product meta (merchant sets) | yes | local; **no live data set, merchant confirmation needed** |
| Cart drawer (qty/remove/errors, nonce) + full cart | yes | Woo session | yes | local; stock-limit path not executed |
| Sticky phone purchase bar | yes | Woo form | yes | local |
| Lightbox/gallery | Woo's built-in | Woo | yes | **not verified** accessibility of PhotoSwipe |
| Checkout/account/order-received styling | CSS only | Woo | yes | render only; **no gateway, 3DS, mail or order test** |
| Newsletter popup/footer trigger | yes | none | **off** (needs provider filter) | endpoint returns 501 without handler |
| Stock alert, wishlist, promo banner, cookie preferences | not built | none | off |, (needs provider/inventory; cookie tool inventory first) |
| SEO/schema | none emitted by theme (Rank Math/Woo own it) |, |, | not verified; canonical/sitemap checks need staging |
| Contact form / email | page content only | none |, | blocked: existing plugin unknown |

## Not done (explicit)
No staging, backup, deployment, gateway/mail/stock test, real-device test (desktop Chromium emulation only), 200% text pass, Lighthouse/IONOS performance, SEO crawl comparison, consent test, or About/Contact/policy real copy. Reviews/Vinted section and collector story omitted (no approved content). Logo files are the labelled flat reconstructions; visual approval against source pending.
