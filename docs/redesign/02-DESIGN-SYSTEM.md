# 02 — Art direction and responsive system

## Creative thesis — The Collector's Counter

Make it feel like a well-run specialist shop: product-led, confident, compact enough to browse, with tactile warmth. Use the supplied identity as an accent system rather than painting every surface red and yellow. This is an ecommerce **System** build with one editorial signature, not an immersive agency portfolio.

Signature: one quiet, oversized cropped ring from the logo behind a carefully composed, real product scene. On desktop, the scene balances short left-aligned copy; on mobile it becomes a shallow, deliberate product stage. Keep type and actions in semantic HTML. The ring is decoration with `aria-hidden`, never a fake shopping control or canvas dependency. Use at most two products and only merchant-approved artwork. No fan-art generation or invented pack artwork.

## Visual rules

| Element | Default | Reason |
|---|---|---|
| Main shop surfaces | Warm near-white, white image plates | Real packaging stays legible; broad colour assortment looks coherent. |
| Header and hero | Ink on cream, optional ink hero stage | Supports colourful logo without turning the catalogue into a dark dashboard. |
| Red | Primary action, selected accent, small editorial rules | Recognisable brand energy, used sparingly. |
| Yellow | Marker/ring detail, small highlighted panels | Collector warmth; dark text only. |
| Containers | Open product layout; soft framed navigation, 14–24px image/panel corners | User direction: curved, well-composed controls without a box around every section. |
| Imagery | Object-fit contain on consistent image plate | Show the whole purchased pack/box; do not crop contents or branding. |
| Shadows | Gentle image-stage grounding only | No heavy floating-card wall or glass effect. |
| Motion | 140–220ms UI transitions; optional 400ms initial stage reveal | No scroll hijack, autoplay, continuous spinning ring or purchase-blocking intro. |

Suggested UI tokens (design choices, not claimed exact original logo colours):

```css
:root {
  --ink: #202321;
  --muted: #62655f;
  --paper: #f7f5ef;
  --surface: #ffffff;
  --line: #d9d8d0;
  --action: #b51f2a;
  --action-hover: #941924;
  --brand-yellow: #f4c641;
  --success: #236246;
  --danger: #b51f2a;
  --focus: #155eb0;
  --radius-control: 999px;
  --radius-field: 14px;
  --radius-image: 18px;
  --radius-panel: 24px;
  --radius-nav: 28px;
  --space: 4px;
  --content-max: 1320px;
}
```

Use white text on action red, ink on yellow/paper/white. Text contrast must reach 4.5:1 for normal text and 3:1 for qualifying large text; UI boundaries/focus need appropriate non-text contrast. The faint divider token is not sufficient for an input boundary: use a stronger border such as `#858880` for fields. Check the final real pairings, hover/disabled states and image overlays rather than assuming these proposed tokens guarantee accessibility.

## Typography — recommended, subject to rendered specimen

**Barlow Condensed 600/700 for short display headings; Inter 400/500/600 for all shopping UI and body copy.** Barlow adds compact collector/publication character; Inter keeps long names, prices, forms and tiny navigation easy to read. Both have open-source distribution; retain the specific font licence files and verify glyph coverage in the exact downloaded builds [F01,F02].

Use display type only for the hero and major section headings, not every label. Product titles use the UI face. Avoid all-caps product names and condensed checkout forms. No imitation Pokémon wordmark font. The supplied logo lettering is artwork, not a substitute for the website type system.

Before locking, compare on actual copy against **Barlow Semi Condensed + Inter** and a **single Inter** direction. Required specimen: “Pokémon”, “One Piece OP-14 The Azure Sea's Seven”, “Japanese · Booster box · No shrink”, “£104.95”, “Out of stock”, a checkout address and a 70-character product title. Check é, apostrophes, yen signs if needed, numerals, 200% text and Android rendering. Choose based on readability/voice, not font novelty.

Proposed sizes: hero 64–76px desktop/36–42px phone; section 32–40/28–32; product title 15–16 with 1.4 leading; body/input 16px; secondary facts 13–14px. Body leading 1.5. Use fluid sizes with `clamp`; no minimum-width hacks. Prices use tabular numerals. Self-host small WOFF2 subsets on IONOS, only needed weights; `font-display: swap`; preload only a genuinely critical face. Target combined font transfer below 160KB, verify actual output. Fallback `Arial, sans-serif`; tune metrics after comparison.

## Layout tokens

Desktop: 12-column conceptual grid, max 1320px, 40px outer gutter and 24–32px section gaps where appropriate. Tablet: 24px gutter. Phone: 16px gutter, 12px product gap, compact 24–40px vertical section spacing. Screens below 360px may use a single catalogue column if long content becomes unreadable; do not shrink text to preserve two columns.

Phone product width at 390px: `(390 - 32 - 12) / 2 = 173px`. At 320px: 138px; test the longest names and mode labels there. Grid uses equal image stages, natural title height or a reserved 3-line rhythm, with prices/actions aligned. Essential facts may not be ellipsised. Product H1 remains complete.

## Desktop home composition — recommendation

1. One 28–32px service line, using verified dispatch wording; no rotating ticker.
2. A softly framed header inset 24–32px from the viewport, with a readable horizontal logo, visible primary game links, large search and account/cart. Approximately 80–96px total height where space permits; at smaller desktop widths search may occupy a second row within the same coherent frame. Use a 24–28px radius, quiet border/shadow and solid readable surface. Do not cram all controls into a decorative capsule. Contact/help is a utility link. See `10-CURVED-NAV-AND-MOTION.md` for sticky/open states.
3. Hero approximately 400–470px tall, split 5/7. Short H1 “Built for collectors.”, supporting line explaining the actual assortment, primary “Shop all products”, secondary existing category link. Product stage right; preserve the supplied full tagline in a subordinate position if desired. This is draft copy, not a new operational claim.
4. Two clear language-aware game gateways and a smaller Dragon Ball path, then the first useful product collection. Do not postpone products beneath a long About story.
5. Verified product collection, compact service strip, short collector story/provenance reviews, optional newsletter, complete footer. Approximate homepage target 5–7 purposeful sections, not a forced pixel length.

## Mobile home composition — separate art direction

At 390×844, before cookie UI: a soft framed header inset 12px, with a 56–64px top row for horizontal logo/menu/cart and a 44–48px search row beneath. Keep outer radius 20–24px, enough touch space and a compact total frame. No crowded desktop link bar. A shallow hero approximately 300–350px combines a 2–3-line heading, one 2-line sentence, one primary action and an approximately 140px product scene. Fit a useful category action and the beginning of product discovery within roughly the first 1–1.3 screens, depending on text size. This is a composition goal, not permission to clip content at 320px/zoom.

Copy and buttons are never baked into hero artwork. Put mobile subject focal points in an explicit asset variant instead of `background-size: cover` guessing. Two product columns; accessible tap-to-open menu accordions for game → existing language routes; full-width search. No horizontal page overflow. Navigation keeps its identity across all pages.

## Product detail composition

Desktop: gallery about 55%, purchase information about 45%, contained by the main grid. Product title, price, language, format, shrink/condition, fulfilment mode and availability appear before the primary purchase action. Delivery summary follows, then descriptive content/specifications and real related products. Mobile: breadcrumb, compact title, gallery, facts, price/action; test the most useful order rather than blindly preserving desktop DOM visual placement.

A mobile sticky purchase bar appears only once the main action scrolls away, includes current price/state and opens/targets required choices. It cannot cover consent controls, form errors or the keyboard. Respect `safe-area-inset-bottom`; prevent two active Add buttons with conflicting validation.

## Imagery and brand delivery

- Use original merchant/manufacturer assets with permission. Existing filenames referencing another retailer indicate an ownership/provenance check, not free reuse.
- Keep the exact source PNG unchanged. The supplied transparent asset contains a small rough outline; avoid treating that as a UI texture.
- Flat vector production derivatives are labelled as reconstructions, not recovered original vector masters. Compare against source before use.
- Use full horizontal lockup in header at readable dimensions; stacked lockup where space permits. Favicon is symbol only, with no brand name.
- Never upscale the original 798×549 bitmap and call it high-resolution. Prefer the reconstructed vector for large applications after visual approval.
- Decorative assets: one ring family and subtle paper/ink contrast. No busy background behind products, unlicensed franchise hero collage or generated counterfeit product renders.
- Have a text/neutral fallback when an image is missing. No misleading substitute set photo.

## A first-presentation rule

Show the owner a polished home, real listing, real product detail, mobile menu, cart drawer and newsletter/error examples together. Use accurate out-of-stock examples if live stock remains depleted. A clearly labelled fixture may demonstrate an in-stock state in a private component gallery, but must never be mistaken for a merchant price/availability claim or imported into production.
