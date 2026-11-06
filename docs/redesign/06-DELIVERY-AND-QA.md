# 06 — Fourteen-day delivery and acceptance

Relative working plan within a 14-day contract; confirm the actual kickoff date and whether the contract counts calendar days. This is a delivery sequence, not permission to shorten QA to recover unresolved access delays. Account manager owns client decisions; builder owns implementation/evidence; merchant owns product facts, commercial rules and final release approval.

| Day | Work and concrete output | Dependency / exit check |
|---|---|---|
| 1 | Agency/WP/hosting inventory, private backup and isolated staging; URL/function baseline | Confirm package/access, theme/plugins/licences and restore path. |
| 2 | Validate catalogue modes/stock, typo/duplicate queue, legal/contact facts; choose native theme route | Merchant resolves purchase-critical ambiguities. |
| 3 | Render type specimens and desktop/mobile home/listing/product art direction; brand export review | One coherent design selected; no unlabelled invented stock. |
| 4–5 | Build global shell, header/search/menu/footer and homepage merchandising | Real server-rendered routes; responsive structural review. |
| 6–7 | Listing/category/search/filter states, product/gallery/variants/mode labels/quick view | Correct actual catalogue; no shipping add-on in discovery rows. |
| 8 | Cart/full cart, existing checkout and account presentation | Existing gateway/shipping/tax/notes behaviour retained. |
| 9 | About/contact/policies; email visual styling and delivery verification | Owner-approved factual/policy text and real mailbox tests. |
| 10 | Popup/alert/preferences states; connected optional features only; component gallery | Features have truthful enabled/disabled status. |
| 11 | SEO/schema/redirect/sitemap and accessibility/performance pass | No critical crawl, consent, focus or purchasing failures. |
| 12 | Whole-journey staging QA, client review, fixes and installable release package | Evidence complete for actual installed gateways/extensions. |
| 13 | Retest fixes, reconcile live content/orders/stock, approve change manifest and release window | Production access and merchant sign-off. |
| 14 | Selective deployment, smoke checks, handover, recovery readiness | Orders/accounts survive; payment/mail/stock/SEO checks recorded. |

## Dependency rules

Continue design, component states, local theme work and documentation when a provider is unavailable. Do not claim missing integrations work. Core access/payment/stock/policy dependencies can block release; optional wishlist/stock alerts/TikTok sync do not block the included redesign unless already active functionality would be lost. Surface those distinctions early with a decision deadline and consequence. Do not silently promise extra integrations inside the 14 days.

## Meaningful test matrix

| Area | Minimum evidence |
|---|---|
| Route preservation | Pre/post page, category and product URL/status/canonical comparison; Woo page IDs and extension endpoints. |
| Mobile composition | 320, 360/390, 430, 768 and desktop 1280/1440px; long title, empty stock and keyboard-open form. Actual iOS Safari + Android Chrome before final sign-off where devices available; clearly mark any unavailable device. |
| Product discovery | Query actual code/name/accent aliases; game/language/format/mode facets; applied chips; sort; empty/no-results/error; back/forward restore. |
| Catalogue truth | Current stock, unavailable variation, no-shrink and live-opening facts; service excluded from discovery but still purchasable by its current path. |
| Cart | Add valid item/variant, update/remove, refresh/login session, coupon valid/invalid, stock changes between views; accurate Woo totals. |
| Concurrency | Two staging sessions compete for the last unit; no client-side oversell; stock return/cancel behaviour matches configured policy. |
| Checkout | Guest and existing customer; actual enabled gateways in sandbox, success/decline/3DS/redirect/cancel; duplicate submit; retry against existing order. |
| Shipping/tax | Representative allowed/excluded postcode/NI cases matching merchant settings, free threshold if active, mixed modes/service upgrade, coupons/tax/fees. |
| Accounts | Login/register if active/reset, order detail permissions, addresses and installed extension endpoints; one customer cannot view another's order. |
| Forms/email | Actual contact receipt, password reset, order/customer/admin/refund notification; bad address/provider failure; newsletter list/consent/unsubscribe if enabled. No real customer emails from staging. |
| Overlay interactions | Focus trap/return, Escape, scroll lock cleanup, single modal, Back action, 200% text and keyboard-safe phone close/submit. |
| Consent | Fresh session accept/reject/customise/withdraw; verify real network/storage state; no duplicate optional tags/purchase events. |
| SEO | Canonicals/robots/sitemap/breadcrumbs/Product facts; current verification IDs; no staging URLs or production noindex; status of missing pages. |
| Performance | Lab home/listing/product/cart on representative mobile profile; no layout jumps; server/cache exclusions; field metrics explicitly separate. |
| Deployment | Reproducible theme/plugin ZIP, version/checksums, no secrets/test credentials, selective manifest, rollback rehearsal without reverting live orders. |

Automate stable commerce/data rules and a small set of critical regression journeys. Use manual visual/keyboard/device checks for composition and interactions; do not spend the build writing tests that merely repeat CSS values. Screenshots support visual evidence, not payment correctness. An automated test passing in a fixture storefront is not a staging gateway test.

## Release acceptance

- All included templates and active functionality have parity or an explicitly accepted difference.
- No open payment, inventory, privacy, account-isolation, blocked navigation or critical mobile errors.
- Real product data, modes, condition and service eligibility are accurate. Client has reviewed flagged copy/rights issues.
- Existing orders, customers, stock, gateways, email, shipping/tax and active extension configuration survive.
- Every optional component says designed / connected / enabled / verified, with actual provider and owner.
- Owner reviews desktop and mobile journeys and the exact release manifest. Existing service workflows are demonstrated, not assumed.
- Handover includes source, installable release, asset licences, admin editing notes, backups/recovery instructions, extension renewal owners, support contact/process and known limits.

## Post-release verification

Immediately verify key URLs, new clean-session cart/checkout, approved payment smoke arrangement, real mail receipt, stock decrement, account access, SEO directives and cached/personalised page separation. Check the first real orders privately with the merchant; do not make unapproved live purchases. Include a follow-up check of logs, failed scheduled actions, form failures, Search Console and feeds in the agreed support process. No automation has been created by this plan.

## Evidence report format

For each delivered feature: commit/release, exact staging/production target, environment/plugin versions, what was tested, result, evidence path, what remains untested and operational owner. Report blockers separately from optional refinements. Do not describe “production ready” based only on lint, screenshots or an inaccessible demo.
