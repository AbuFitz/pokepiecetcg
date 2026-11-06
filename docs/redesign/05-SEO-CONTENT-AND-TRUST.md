# 05 — Search, product truth, accessibility and trust

## SEO objective

Preserve existing discoverability and make the actual catalogue easier for search engines and collectors to understand. This is not a promise of rankings, sales uplift, keyword volumes or a fully audited current backlink/indexing baseline. Search Console, analytics and Merchant Center data have not been inspected.

## Before changes

- Export the current indexable URLs, status codes, canonical URLs, titles/descriptions, H1s, robots directives, sitemap entries, schema types, internal links and image alt text. Include page/category/product pagination and any known redirects.
- Save current Search Console performance/indexing/crawl issues and analytics conversion baseline if accounts exist. Do not invent traffic/conversion numbers.
- Preserve page/product/category IDs, permalinks, assigned Woo pages and account endpoints. Public footer uses an uppercase Privacy path while the page interface resolves lowercase: inspect redirects and normalise internal links to the real canonical without inventing a new page.
- Existing product slugs can contain old names or `copy`; do not rename simply for tidiness. Any essential change needs a one-hop 301 mapping, internal-link/feed update and owner approval.

## Page intent map — candidate queries, no volume claims

| Existing template | Buyer intent | Content direction |
|---|---|---|
| Home | Brand + UK Pokémon/One Piece specialist | Clearly name shop, location served, real product focus and routes into stock. |
| Pokémon English category | English Pokémon sealed products | Useful short introduction, formats genuinely stocked, real products and language explained. |
| Pokémon Japanese category | Japanese Pokémon booster boxes/packs | Separate language and packaging condition; avoid “all factory sealed” if no-shrink stock is present. |
| One Piece English/Japanese categories | Correct language, set and product format | Accurate code/name metadata and links to the matching items. |
| Dragon Ball English category | Actual Dragon Ball range | Keep even with limited stock; do not pretend the retailer specialises in every TCG. |
| Product | Exact set/code + language + format | Specific H1 and concise facts, correct stock/price/contents and fulfilment. |
| About/contact/policies | Trust and support | Real legal/contact/operational information, readable policies and cross-links. |

Provisional title patterns: home `PokePieceTCG | Pokémon & One Piece TCG UK`; category `{Language} {Game} Cards & Booster Boxes | PokePieceTCG`; product `{Verified set/code} {Format} – {Language} | PokePieceTCG`. Adjust to actual inventory; don't force “booster boxes” into a packs-only category. Titles should be readable and useful rather than filled to an arbitrary character quota. Descriptions summarise true products/service; they are not a ranking guarantee.

## Crawl and filter policy

Use semantic anchor links from navigation to categories and products, not JavaScript-only search journeys [S01]. Keep useful pagination crawlable and give each page the appropriate canonical; do not canonicalise every paginated page to page one by reflex. Search engines need a route to every valid product even if JavaScript fails.

Default indexable set: existing valuable pages/categories/products. Proposed internal product search, sort and arbitrary multi-facet combinations should not create unlimited indexable duplicates [S02,S03]. Decide supported parameter handling in the existing SEO/plugin stack, with real examples. Normally use crawlable `noindex` for internal search/filter combinations where deindexing is needed; use canonical only for genuinely duplicate views. `robots.txt` disallow prevents crawling a `noindex`, so do not combine them blindly. For a small catalogue, avoid overengineering crawl suppression.

Keep sold-out product URLs at 200 when they still provide useful product/return-to-stock information. Do not redirect every sold-out product home. Permanently removed pages need a considered replacement/301 if equivalent, or proper 404/410 if not. Preserve internal links and don't create fabricated “in stock” structured data to improve clicks.

## Structured data and feeds

Inventory current Woo/Rank Math output before changes. Assign one owner per entity type to avoid conflicting JSON-LD [S04]. Product pages should publish truthful Product/Offer details: name, image, genuine SKU/GTIN/brand when known, GBP price, availability and canonical URL. Never generate GTINs or use the shop as the manufacturer brand. BreadcrumbList reflects actual hierarchy. Organization uses verified legal/trading information.

Only use ProductGroup/variant markup for genuine product variations that meet Google's supported modelling guidance [S05]. English/Japanese language or sealed/live fulfilment must not be forced into unsupported variant dimensions merely to produce rich results. Separate existing SKUs/pages can remain separate. No invented product aggregateRating derived from Vinted seller testimonials. No LocalBusiness address/hours unless the merchant actually operates an appropriate physical location. Don't promise FAQ stars or site-search rich results.

Retain a working sitemap; compare pre/post URL lists. Validate representative products and breadcrumbs with Google's Rich Results Test and rendered HTML. A valid result is eligibility, not a promise of display. Merchant Center/free listings are optional: verify existing account/feed ownership, shipping/returns, product identifiers and matching price/stock/landing-page state before enabling [S04]. Never include service-only shipping upgrades as ordinary physical TCG stock in a shopping feed.

## Product content model

Use existing native attributes/meta where possible. Proposed new metadata needs a documented editor and mapping; do not infer purchase-critical facts at runtime from names.

| Field | Source/validation |
|---|---|
| Existing ID/SKU/permalink | Woo, immutable unless a separately approved migration. |
| Game/language | Existing taxonomy plus merchant validation; flag conflicts. |
| Set/name/code and format | Actual packaging/manufacturer source/merchant; no invented future release details. |
| Contents/pack counts | Verify exact region/edition; never copy counts across languages. |
| Fulfilment mode | Merchant-confirmed sealed/live/service; do not default unknown to sealed. |
| Shrink/box condition | Merchant evidence; plainly disclosed beside purchase action. |
| Price/tax/currency/stock | Authoritative Woo values only. |
| Release/dispatch window | Merchant/manufacturer source with date; omit unsupported promises. |
| Images/alt/provenance | Merchant-owned/licensed assets, accurate variant image and meaningful alt. |
| Reviews | Correct reviewed entity/source, permissions and genuine verification status. |

Rewrite repeated hype into short verified descriptions and a useful specification table. The sample no-shrink listing repeats another set name and makes broad claims about Japanese stock; flag and correct with client evidence. No “guaranteed strong pulls”, profit promises or unsourced superiority claims. Text must be en-GB and natural; use “Pokémon” consistently in prose while retaining existing URLs.

## Trust and legal content — implementation review points

The current shipping/returns policies include a 48-hour damage-contact request and blanket statements about opened-product returns. Do not present a redesign as validating those restrictions. GOV.UK explains that online cancellation and faulty/not-as-described rights apply, with conditions [L01,L02]. Have the merchant review the actual policy, including sealed collectibles, diminished value and live-opening service terms, with appropriate advice where needed. A request for prompt photos must not imply statutory rights expire after 48 hours.

Before release collect verified trading/legal name, business contact/service address, VAT/company details where applicable, tax-inclusive price presentation, shipping charges and payment methods. Do not publish a guessed home address or invent a registered company. Keep policies accessible before payment and durable order confirmation after purchase [L03].

### Email/signup

Use a clear, optional newsletter request naming the shop and purpose. No pre-ticked consent, required marketing to buy, fake discount or automatic addition of stock-alert subscribers to a newsletter. Record the chosen permission route, source, timestamp and notice version. Give a functioning unsubscribe in marketing messages. ICO's products/services soft opt-in has specific conditions; do not assume every purchaser, contact enquiry or imported list qualifies [L04]. Double opt-in is a recommended evidence/deliverability choice, not a blanket claim that UK law always requires it.

### Cookies and similar storage

Inventory cookies, localStorage, tracking pixels and embedded services; assign purposes/providers/duration. Necessary basket/session and security functions should remain usable while optional tracking stays off until appropriate permission. ICO's current guidance describes purpose-specific exceptions and consent requirements, so assess actual technology rather than labelling all cookies the same [L05]. Default optional analytics/advertising/social tags off until the implementation is verified. A preference screen must actually control scripts, allow rejection and support withdrawal as easily as giving consent.

Newsletter dismissal and recently-viewed storage also need a reasoned duration/purpose decision; “it's localStorage” is not an exemption. Avoid immediate popup collisions and don't cover primary product controls on mobile. Hosted third-party videos should use a poster/link or appropriate consent flow, not load marketing embeds automatically.

## Accessibility and performance acceptance

Target WCAG 2.2 AA for the redesigned experience: semantic headings/landmarks, labelled controls, visible focus, keyboard reachability, modal focus handling, status announcements, meaningful alt, colour-independent states and zoom/reflow. Use 44px practical touch targets where possible, at least the applicable WCAG minimum or spacing exception. Check 320px width, 200% text and 400% browser zoom/reflow; test actual dialogs with a screen reader. Don't disable pinch zoom or use placeholder-only labels.

Core Web Vitals objectives at the 75th percentile: LCP ≤2.5s, INP ≤200ms, CLS ≤0.1 [P01]. These are targets, not results. A Lighthouse lab run is not measured field INP or proof of a real-user pass. Capture lab diagnostics on home/listing/product/cart and field data when enough traffic exists.

Implementation budgets: responsive WebP/AVIF where compatible, retain appropriate originals/fallbacks; hero roughly ≤200KB where quality permits; explicit image dimensions; `srcset`/`sizes`; eager/high-priority only for real LCP imagery, lazy below fold; no autoplay video/3D library; critical fonts small; load popup/search enhancement code when useful. Keep gallery image and CTA stable while assets load. Verify caching exclusions and host response time on IONOS; no promised score of 100.

## Measurement

Use the existing consent-aware analytics if present. Agree funnel events (view_item_list, select_item, view_item, add_to_cart, begin_checkout, purchase) and avoid duplicated purchase events from reloads/gateway return pages. Keep personal details out of analytics. Compare baseline and post-release product discovery, add-to-cart errors and completion trends; do not set uplift claims without data. Search Console/merchant-feed follow-up belongs to handover and launch monitoring, not a fabricated result in the first presentation.
