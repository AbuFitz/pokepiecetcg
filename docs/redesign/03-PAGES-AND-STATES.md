# 03 — Templates, components and interaction contracts

“Designed” means layout and all relevant states exist. “Integrated” means real provider/data behaviour is verified. “Live” means enabled on the production shop. Track these separately in `component-register.csv`; a working visual demo does not prove an integration.

## Global behaviour

Single coherent header/footer across every template. Header logo links home; search searches products, not posts. Desktop game navigation works by click and keyboard, not hover alone. Phone menu opens a full-height off-canvas panel with game/language disclosure groups and visible account/help links. Preserve every active route even when links move.

All overlays use a shared visual language: ink heading, white/paper surface, 20–24px panel radii, rounded controls, clear close control, 16–24px padding and consistent input/error/button rules. Desktop panel max widths: menu 400px, cart 440px, quick view 960px, newsletter 720px. Phone drawers fill the width with safe areas and internal scrolling, rounding exposed top corners where appropriate. A short bottom sheet may suit filters; longer content must not be trapped in a tiny sheet. Follow the concrete motion/state contracts in `10-CURVED-NAV-AND-MOTION.md`.

Only one modal at a time. Opening another returns/cleans up the first. Set accessible name, focus entry/trap/return, Escape dismissal, inert background and scroll lock. Backdrops are not the only close method. A toast is a status message; it must not steal focus. Handle browser Back, restored scroll, long names, 200% text, landscape keyboard and reduced motion. Avoid two independent cart implementations competing for state.

## Home

- Core hero and game gateways as specified in `02-DESIGN-SYSTEM.md`.
- Editorial product collections must query real WooCommerce data. Prefer merchant-selected eligible products, stock-first and mode-aware. Exclude service/add-on IDs from recommendations, new arrivals, search suggestions and hero collections while retaining their direct service path.
- “New arrivals” derives from genuine publish dates/merchant curation. “Popular” needs reliable sales evidence or change label to “Collector picks” with owner selection. No fake trending/low-stock counters.
- Each row has a title, meaningful “View all” destination and a grid or swipe rail with visible continuation; no mandatory auto carousel.
- If fewer than four eligible items exist, render a deliberate smaller grid. If no purchasable merchandise exists, use an honest “Explore the range” selection with stock labels, helpful contact path and an optional provider-backed alert. Do not feature postage to fill a slot.
- Reviews retain Vinted provenance and owner permission. No site-wide aggregate score unless evidence and methodology support it.
- Footer: retained policy links, real legal/trading details supplied by owner, email and verified social links. Payment logos only for active methods. Newsletter only if connected.

## Shop, categories and product search

Retain `/shop/`, all five categories and pagination links. Keep the existing full catalogue accessible. Improve hierarchy instead of exposing every display setting: title/short introduction, visible product count, filter and sort controls, applied-filter overview, grid, pagination. Existing optional grid/page-size controls may be simplified visually but confirm that no relied-on customer capability is lost.

Recommended facets: existing game/language categories; product format (box/pack/ETB/collection); fulfilment (sealed delivery/live opening); availability; price. Set code only if there is enough accurate data to make it useful. For 23 results, do not add a huge facet platform by default. Use existing theme controls or server-rendered filters first. Unsupported attributes are omitted, not filled from AI guesses.

Desktop left filter column 220–240px; mobile visible Filter and Sort buttons with selected count, a drawer, explicit Apply and Clear actions. Applied chips stay visible above results and individually removable [U01]. Preserve filter and sort state in query parameters, URLs and browser history; reset pagination after a filter changes. “In stock only” is explicit and reversible, not a silent filter hiding the whole range. Disable impossible choices with accurate counts or hide them consistently; never imply unavailable merchandise is purchasable.

Sorting: retain working Woo choices; surface newest and price low/high plus relevance for search. Do not imply a popularity score without source data. Server-render the first result set and pagination so links work without JavaScript. Enhancements may update asynchronously with `aria-live` result count and preserved focus/scroll.

Product card: complete descriptive image alt, real title, language written out, format, fulfilment/condition label, price/range, availability and relevant action. For a simple purchasable product use Add; variants use Select options; unavailable items use View product or provider-backed Notify me. Do not show “Read more” as the main retail state label. At most two essential badges; avoid a cloud of pills. Colour or flags alone cannot carry language/stock.

Quick view is optional convenience, never the only route to details. Show image, title, critical facts, price/current variation, availability and View full details. If required live-opening choices cannot be supported reliably, link to the product page instead of an incomplete Add flow.

Search: desktop inline search and phone full panel. Support set code/SKU/name/language where the installed search engine permits; test `OP14`, `OP-14`, `pokemon`, `Pokémon` and actual verified aliases. Do not promise fuzzy matching until tested. Debounce about 250ms; cancel stale requests. Suggestions show 4–6 actual products with stock/mode, plus all results. States: idle prompt, loading, results, no match, slow/error fallback to normal product-search results. No results offers removing restrictive terms and existing categories, not invented products.

## Product detail

Always show: exact product title, gallery, GBP price, sale price only with genuine comparison data, stock/backorder status, product language, set/code when verified, product format, shrink/condition, fulfilment mode, quantity/limits and the Woo purchase action. The theme never owns prices, tax, stock arithmetic or payment state.

Use a concise verified “What you receive” facts list instead of repetitive SEO paragraphs. Required choices should be above the Add action. No missing field may default to a misleading assumption. Variation selection updates price, image and stock, and the server validates the combination. Explicitly unavailable combinations cannot be added.

### Fulfilment modes

| Mode | Required presentation | Behaviour constraint |
|---|---|---|
| Sealed delivery | “Delivered sealed”, exact shrink/box-condition disclosure and verified contents | Do not promise flawless collectible outer packaging without merchant policy. |
| No shrink / altered packaging | Plain-language condition next to purchase controls, with accurate merchant explanation | Do not call it shrink-sealed or claim all Japanese boxes are supplied this way. |
| Live rip and ship | “Opened live — cards shipped”, where/how stream happens, what cards/customer receives, shipping timing and identity requirement | Reuse current flow; clarify details before enabling any new acknowledgement. |
| Preorder/backorder | Expected date/window with source, charging timing, allocation and mixed-order handling | Do not add preorder capability just because a badge is designed. |
| Shipping upgrade | Clearly identified service, eligibility and order/TikTok reference instructions | Preserve existing SKU/price/flow. Do not silently convert it to a shipping method or force it into all orders. |

Live-opening context may need a TikTok username or existing order reference; currently the shipping product asks for this in checkout notes. Keep that field and order/email visibility working. A new required field or explicit acknowledgement changes behaviour: use only after merchant validation, implement through the current checkout extension mechanism, save to order metadata and test admin/email views. Do not treat acknowledgement as automatic removal of statutory rights.

Delivery: policy-backed dispatch summary and link; calculated charges come from Woo at the correct stage. Returns: concise approved summary plus retained policy link. Below: gallery zoom/lightbox, specifications, verified description, genuine reviews if present and related items filtered by relevance, eligibility and availability. Never attach general seller ratings to product schema.

Sticky phone bar is state-aware: select options, Add, unavailable or preorder as appropriate; no competing action while keyboard/modal is open.

## Cart and checkout

Cart drawer uses the existing authoritative Woo session. Show thumbnail, exact item/variation, mode/condition facts, unit price, quantity/remove, subtotal and qualified shipping/tax wording. Undo remove where feasible; quantity update preserves old value on failure. Empty cart offers shop/categories. Error/stock change is visible; totals stay server-owned. A free-delivery progress bar stays off unless real zone-specific thresholds and exclusions can be calculated accurately.

Full `/cart/` remains available and functional without the drawer. Retain coupons, shipping selection, tax treatment, fees and supported express methods. Cart drawer cannot bypass a required live-opening choice.

Style the existing `/checkout/` implementation (classic shortcode or Blocks) after verifying plugin compatibility [W02,W03]. Preserve guest/account behaviour, order notes, gift/other active fields, coupons, tax, shipping, terms acceptance, email opt-in and existing gateway callbacks. No card data in custom fields/JS. Payment form remains gateway-owned.

Checkout states: field validation; invalid coupon; no shipping zone; rate loading/error; price/stock change; declined/authentication-required payment; interrupted redirect; pending payment; successful confirmation; duplicate click; retry against existing order. Do not replace payment success with a client-side animation or success page before confirmed order state.

Order confirmation preserves order key/security, correct totals/address/mode, instructions and status. Account order details and emails must agree. A test purchase belongs in staging/sandbox; a live smoke purchase/refund requires the owner's agreed test arrangement.

## Account and service pages

My account: login, allowed registration, lost/reset password, dashboard, orders/detail, addresses and any active payment/download endpoints. Preserve extension endpoints. Password errors must not expose other customers. Use large inputs, visible labels, password-manager/autofill support and clear status. Never build a separate account database.

About: compact real collector story, actual product focus, approved photographs/packaging evidence, service links and shop CTA. No invented founders/warehouse/team size.

Contact: preserve active form fields and delivery workflow; verify whether the extracted page omitted an actual message textarea. Recommended fields: name/email, topic, order reference when relevant, message, optional phone only if useful. Adding/removing fields needs validation against existing handling. Spam protection and rate limits should fit current tooling; server validation and real mailbox receipt required. Preserve response-time wording only if confirmed.

Shipping/returns/privacy/terms: clean, readable 65–75-character line length, clear H1/sections, update date only if known, print-friendly. Improve design freely; legally material wording needs owner review. Do not quietly re-publish concerning rights restrictions as approved advice.

404: useful search/categories/support, real 404 status. No-results archive: explain filters and offer Clear all; do not change it into a soft-404 homepage. Service error: retry and safe route. There are no invented Help/FAQ/Blog pages; useful FAQ sections may live within existing pages, with verified answers.

## Optional component designs — disabled by default

| Component | Presentation/trigger | Complete states and activation dependency |
|---|---|---|
| Newsletter popup | Desktop split compact copy/brand ring; phone short sheet; manual footer trigger by default | Idle, invalid email, submitting, already subscribed, confirmation pending, success, provider failure. Real mailing provider/list and consent text; never claim a discount without an active offer. |
| Delayed newsletter prompt | Optional after meaningful browsing, never immediate entrance | Off initially; merchant may approve e.g. second page + 30s, once per session and longer dismissal suppression only with suitable storage/consent. Never checkout/account/payment; suppress while another panel is open. |
| Stock alert | Product-specific inline or compact modal | Real product/variation subscription, request, verification if required, unsubscribe, provider failure, no stock reservation. Separate permission from newsletter marketing; retention/deletion policy. |
| Image lightbox | User-invoked image zoom | Accessible next/previous, caption, full close/keyboard control; no purchase state inside. |
| Delivery information | Inline disclosure or short sheet | Approved dispatch and shipping-policy link; no calculated delivery date without rate/provider evidence. |
| Cookie preferences | Compact non-obscuring first layer | Accept optional, Reject optional, Settings; granular providers/purposes, save/error/reopen, honour withdrawal; inventory actual storage first. |
| Wishlist/recently viewed | Existing capability only unless separately agreed | Preserve existing if found. New feature requires storage/account strategy and privacy decision; no extra standalone page without scope agreement. |
| Promotional banner | Single dismissible message | Real content/start/end, no fake expiry timer, accessible dismiss and suppression. |

“Subscription popup” in this brief means email newsletter signup. Recurring paid product subscriptions are a separate commercial feature and are not included by implication.

## Definition of complete

Every component has mobile/desktop layout, long-content case, loading/empty/success/failure where applicable, keyboard/focus rules, data/provider owner, enabled/disabled control, non-JS/safe fallback and recorded verification. A hidden control without a connected handler is unfinished; a deliberately disabled optional component with documented dependency is an honest deliverable.
