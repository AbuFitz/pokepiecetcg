# Tests

Everything here runs against a **local throwaway WordPress + WooCommerce fixture** (see `scripts/dev-wp.sh` and `scripts/seed-fixtures.php`), never against the client's live or staging site. A passing run proves the theme and plugin behave on WooCommerce's real code. It does **not** prove payment gateways, real email delivery, the client's own data, or behaviour on IONOS; those need staging (see `docs/build/IONOS-BUILD-APPROACH.md`).

## Run everything

```bash
BASE=http://localhost:8088 WPDIR=/path/to/fixture/wp WPCLI="php /path/to/wp-cli.phar --allow-root" AXE=/path/to/axe.min.js scripts/run-tests.sh
```

It parses every PHP and JS file, runs the PHP unit tests, then every browser suite in the right order (clearing sign-in limits and test fixtures between them), and finishes by reading the PHP error log: **a clean run means zero PHP warnings, notices or errors across all of it.** `AXE` is axe-core (`npm i axe-core` anywhere); without it the accessibility suite is skipped by failing loudly.

## What each suite proves

| Suite | What it checks |
|---|---|
| `php/free-shipping.test.php` | The 8-hour free standard shipping rule on throwaway orders (window start, no extension, statuses, only the "standard" method, switch-off) |
| `php/emails.test.php` | Real order emails: logo, brand colours, order lines, Special Delivery fee, reference visible to staff only, no PHP warnings |
| `e2e/storefront.cjs` | Core journeys: navigation, home sections, shelf, filters, search, cart, quick view, sealed focus, reduced motion |
| `e2e/special-delivery.cjs` | Special Delivery option from product page, drawer, cart and checkout; fee, reference, order meta |
| `e2e/responsiveness.cjs` | How fast every control visibly reacts (CPU slowed 4x), instant feedback, failed-request messages, double clicks, rapid clicking |
| `e2e/security.cjs` | Browser protection headers, no username/version leaks, expired-token recovery, per-account sign-in limits, bounded search |
| `e2e/seo.cjs` | Titles, descriptions, canonicals, noindex rules, structured data, robots.txt, sitemap, feed, internal links |
| `e2e/a11y.cjs` | axe-core (WCAG 2.1 A/AA + best practice) on every page and every popup/drawer/menu state, phone and desktop |
| `e2e/mobile-scroll.cjs` | Real touch scrolling, scroll lock and restore |
| `e2e/account.cjs` | Sign-in/register popup journeys |
| `e2e/consent.cjs` | Cookie prompt, preferences, gated scripts, promo popup |
| `e2e/framing.cjs`, `e2e/watermark.cjs` | Product photos of any shape fit the frame; the watermark is on every product photo and nothing links to the unmarked file |
| `e2e/resilience.cjs` | Awkward data (no image, sale, variable, HTML in titles, sold out, long titles), hostile search input, keyboard-only use and focus traps, no-JavaScript, checkout errors, resize/Back with popups open |
| `e2e/sweep.cjs` | Every page at 8 widths: overflow, JS errors, failed requests, h1, alt text, names, **text overlapping text, covered buttons, PHP error pages** |
| `e2e/demo.cjs`, `e2e/demo-sd.cjs` | The static Vercel preview (run against a served `demo/` folder, `BASE=http://localhost:8099`) |

## Fixtures (all deleted by the suites that create them)

`scripts/framing-fixture.php` (odd photo shapes) and `scripts/stress-fixture.php` (awkward product data) create and clean up throwaway products. Never run them on a real shop.

## Lessons baked in

- A cache can hide a crash, so the sweep fails on any PHP error page and the run ends by reading the error log.
- Detectors are only trusted once shown to catch the bug they exist for (the overlap check was proven against the real broken layout).
- Tests clean up after themselves, including network blocks (a failed test must never leave the browser offline for the next one).
