# 01 — Scope, audit and market position

## Contract baseline — verified from the project brief

Full redesign of PokePieceTCG's existing website. Existing page number/structure, domain and hosting remain. Existing functionality must remain operational. Content is a joint OneSimpleSite/client responsibility. Turnaround: 14 days; kickoff date and dependency handling need recording by the account manager. Themes and visual direction can be discussed throughout delivery.

This pack is an implementation recommendation inside that baseline. It does not authorise new subscriptions, a different commerce platform, removal of live-opening products, changes to refunds/shipping, or replacement of working payment methods.

## Evidence and method

Read the public homepage, shop, page-two listing, sample product pages, policies, WordPress page/category interfaces and sitemap outputs on 6 November 2026. Reviewed the four supplied reference homepages and official platform, search, UX and UK regulatory guidance. A separate rendered-desktop research pass is recorded in `visual-audit.md` if completed. No authenticated administration or transactional testing was available. Source IDs below resolve in `08-SOURCES.md`.

## Findings that change the build

| Finding | Evidence/status | Implementation consequence |
|---|---|---|
| WordPress and WooCommerce exist | Public `wp-json` exposes WP and `wc/store/v1`, `wc/v3`; verified public evidence [A01–A05] | Native WordPress implementation. Do not default to Shopify/Next.js. |
| Elementor/Elementor Pro, Rank Math, WooPayments, LiteSpeed-related and migration interfaces are exposed | Public namespace names; capability signals, not proof of active licences/configuration [A05] | Audit installed/active extensions, versions, settings and ownership before choosing replacements. |
| WoodMart-style `wd` namespace, product-tab anchors and catalogue controls | Inferred from public markup/interfaces | Strong theme lead, not an installed-theme fact until admin/stylesheet confirms. |
| Homepage features only a £10.45 Special Delivery Shipping Upgrade | Verified observation [A01,A07] | Exclude service/add-on products from discovery collections; preserve its existing purchase route. |
| First shop page displays 12 out-of-stock products; catalogue reports 23 results | Verified snapshot [A02] | Build an honest zero-stock homepage and empty-filter states. Do not invent stock for screenshots or publish fake Add buttons. |
| Catalogue includes live rip-and-ship alongside sealed items | Verified names [A02,A07–A09] | Fulfilment mode is first-class product information throughout the journey. |
| A Pokémon item appears under One Piece Japanese | Verified listing [A02] | Flag taxonomy correction for owner approval; never silently bulk-reclassify from titles. |
| Two OP14 products have near-identical displayed names, with different prices/URLs | Verified listing [A02] | Resolve what differentiates them: mode, size, edition, batch or actual duplicate. Preserve both IDs until resolved. |
| No-shrink description includes a different set name | Verified sample copy [A09] | Product-copy QA is essential. Do not reuse claims about factory condition, untouched packs, pull rates or pack counts without verification. |
| Home reviews explicitly identify Vinted customers | Verified [A01] | Keep provenance visible; do not convert seller reviews into product verified-purchase ratings. |
| Public viewport metadata disables user scaling | Verified extracted metadata [A01,A08] | Replace with `width=device-width, initial-scale=1`; test pinch/text zoom. |
| Shipping policy states dispatch normally within 1–3 working days | Verified published wording [A10] | Use this exact operational range only after owner reconfirms; no same-day/next-day delivery promises. |
| Newsletter/contact UI exists; delivery provider and success path are unknown | Verified UI, blocking configuration | Reuse a working provider or choose one; test receipt, failure and unsubscribe. |

## Audience and purchase logic — inferred

1. Repeat collectors arriving from social: identify the exact set/code, language, sealed status and price, then buy fast.
2. Sealed collectors: packaging condition, shrink status, authenticity evidence and careful dispatch matter more than animation.
3. Live-opening customers: need unmistakable service explanation, correct account/stream identity, shipping handling and expectations for received cards.
4. Newer/gift buyers: need understandable product format, contents and language. Do not force them to decode acronyms or flags.

Primary paths: search → product → cart → existing checkout; game/language → filtered listing → product; social deep link → exact fulfilment mode → product. A customer should not need to read the About page to know what they are purchasing.

## Client references — translate attributes, do not copy skins

| Reference | Publicly observed strength | Limitation/opportunity | Borrow |
|---|---|---|---|
| Anti Rip Rip Club [R01] | Direct new/popular product sections and straightforward cart flow | Several featured products sold out in snapshot; narrow navigation | Fast access to the assortment, with stronger stock-aware curation. |
| Reevy TCG [R02] | Organises Pokémon by language and product format; live-opening link prominent | Navigation becomes very extensive for a smaller catalogue | Language/format clarity and separation of live intent, scaled to 23 results. |
| Coddys Collection [R03] | Game gateways, store story, business/contact details and external review link | Long narrative competes with shopping | Readable game gateways, compact credibility and real company details. |
| OnThePullTCG [R04] | Product-oriented arrivals and broader discovery/support features | Some language entries point home; added services would expand this contract | Arrival merchandising and community link when verified, never irrelevant grading/rewards machinery. |

Additional patterns: Magic Madhouse exposes prominent search and deep set/game organisation [R05]; Chaos Cards exposes product-format and language paths [R06]. These are information-design references, not claims that their entire visual systems fit a small specialist. Pokémon Center and Cardmarket were attempted but could not be sufficiently inspected in this environment; no detailed recommendations depend on their appearance.

## Proposed positioning

**A considered UK shop for collectors who know what they are looking for, with enough guidance for those who do not.** Calm retail clarity with a small amount of collector energy. Typography, cropped circular geometry from the supplied symbol, real packaging and one composed hero make it distinctive. Product facts and operable shopping controls earn trust.

Do not add invented size/revenue/review claims, fake scarcity, trading-card investment promises, confetti, rotating discount wheels, autoplay streams or a page full of giant franchise character art.

## Page/route preservation

Public pages response: Home `/` (page 24, stored slug `home-2`), Shop `/shop/` (29), About `/about/` (47), Contact `/contact/` (53), Cart `/cart/` (63), Checkout `/checkout/` (64), My account `/my-account/` (65), Terms `/terms-conditions/` (66), Privacy `/privacy-policy/` (3), Shipping `/shipping-policy/` (432), Returns `/refund-return-policy/` (437).

Five existing category routes: Pokémon English/Japanese, One Piece English/Japanese, Dragon Ball English under `/product-category/…/`. Preserve every product permalink and product ID. See `existing-routes.csv` for the public starting inventory, including account/payment endpoints that need authenticated confirmation.

Game grouping can improve navigation without creating new pages or changing taxonomies: use disclosure groups linking existing language categories; an “all Pokémon” discovery shortcut can use a non-indexed shop filter when verified. New indexable landing pages require explicit scope agreement. Existing product, account, cart and checkout screens are templates/endpoints within the retained structure, not permission to invent new marketing pages.
