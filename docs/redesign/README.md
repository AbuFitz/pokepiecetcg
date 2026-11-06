# PokePieceTCG — redesign build pack

Prepared by OneSimpleSite · 6 November 2026 · Research and specification, not a built shop.

## The decision

Build a collector-focused retailer with a memorable product-led opening, excellent mobile browsing and transparent fulfilment. Keep WordPress, WooCommerce, IONOS, existing page IDs, URLs and commercial behaviour. The brand's red/yellow identity stays; neutral surfaces, disciplined type and real product imagery carry the site.

The surprise should be the quality of the complete shopping experience, not extra systems the owner has not agreed to operate.

## Read in this order

1. [Scope and evidence](01-SCOPE-AND-AUDIT.md): verified findings, reference analysis, current routes and access questions.
2. [Art direction](02-DESIGN-SYSTEM.md): visual thesis, type, colour, desktop/mobile composition, asset rules.
3. [Pages and components](03-PAGES-AND-STATES.md): every template, overlay and state, including optional components.
4. [Architecture and integrations](04-WORDPRESS-AND-INTEGRATIONS.md): hosting decision, native commerce, provider choices and deployment.
5. [SEO and content](05-SEO-CONTENT-AND-TRUST.md): URL preservation, metadata, product data, accessibility and consent.
6. [Delivery and acceptance](06-DELIVERY-AND-QA.md): 14-day sequence, responsibilities, launch/rollback and evidence.
7. [Claude build prompt](07-CLAUDE-BUILD-PROMPT.md): pasteable implementation instructions.
8. [Research sources](08-SOURCES.md): first-party references, access dates, decisions and limitations.
9. [Access checklist](09-ACCESS-AND-DECISIONS.md): small set of facts the account manager needs to resolve.
10. [Curved navigation and motion](10-CURVED-NAV-AND-MOTION.md): latest user direction, framed header and component interaction choreography, with Framer/Figma study references.

Machine-readable starting inventories: `existing-routes.csv`, `component-register.csv`. Logo files and usage guidance: `../../brand/`.

## Guardrails

- Research findings are time-stamped observations, not a claim about current private settings.
- No live WordPress, IONOS, payment, customer or inventory changes were made during this work.
- No website has been implemented, transferred or performance-tested by this pack.
- Private accounts, exports, licence keys, customer data and backups must never enter this public repository.
- Optional features have complete design requirements but remain disabled until their provider and operating process are verified.
- The 14-day contract does not automatically include new paid extensions, recurring subscriptions, multichannel synchronisation or a platform migration.

## Evidence status

**Verified:** user-supplied requirements or public-site observations. **Inferred:** reversible design/technical recommendation. **Provisional:** draft content or a proposal requiring owner validation. **Blocking:** a fact/access dependency required before production activation.

The existing site has 11 published WordPress pages in the public pages response, five public product categories and a shop reporting 23 results. Category counts overlap and must not be added to estimate unique products. Preserve the actual admin inventory when access arrives; endpoint views do not prove a complete inventory of redirects, hidden pages or extension endpoints.
