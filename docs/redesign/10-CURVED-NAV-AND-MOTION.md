# 10 — Curved navigation and useful motion

Latest user direction, 6 November 2026: a nicely framed navigation, rounded buttons and smooth animated/functional components; avoid a boxy website. This updates the initial conservative corner guidance. It does not change the native WordPress/WooCommerce architecture or page scope.

## The shape system

Frame the **navigation**, not every section. A shallow, solid floating header above an open product layout gives the interface a composed outline while leaving products room to breathe. Rounded image plates, pill-shaped purchase/filter controls and soft drawers connect the system. Use one deliberate frame for a featured scene if useful; avoid repeated panel-inside-panel layouts and a border around every product description.

| Part | Proposed shape | Responsive rule |
|---|---|---|
| Desktop nav | 24–28px outer radius, quiet 1px border, soft low shadow | Inset 24–32px; max width tracks content grid; no oversized empty gutters. |
| Phone nav | 20–24px outer radius, 12px viewport inset | Logo/menu/cart first row, large search second row; no compressed desktop navigation. |
| Buttons | Full pill, 44–48px practical height | Content-driven width; phone primary action may fill available width; never force a single line at zoom. |
| Search/inputs | 14–16px radius, visible boundary | Label remains outside or persistent; do not use colour/placeholder alone. |
| Product image plate | 16–18px radius | Image contained; product itself stays square/unaltered. Text sits openly below. |
| Cart/filter/menu/quick-view | 20–24px exposed corners | Viewport/safe-area aware; full-height phone panels can have squared screen-edge corners. |
| Badges | Soft small radius or compact pill | Keep essential mode/condition readable and limited; not every fact needs a capsule. |

## Header states

At top: framed header rests in normal reserved space above the hero. Scroll: sticky position with stable dimensions, a little stronger separation and, if justified, a compact search treatment after the full search row has left view. Prefer one stable frame over large scroll-triggered resizing; no layout jump or disappearing search while typing. Never hide navigation merely because the user opens a product.

Menu/search open: anchored desktop dropdown under the frame; mobile panel owns focus. Layer above content but below browser/payment context; no transform on a parent that breaks sticky/fixed positioning. User can close via button/Escape and return to the invoking control. Expanded game navigation links to current language categories; it must not become an unreadable floating list.

## Interaction choreography — proposal to build and verify

| Component | Trigger → state change | Motion/feedback | Edge and accessibility behaviour |
|---|---|---|---|
| Navigation | Click/keyboard → expanded game/menu | 160–220ms fade + 4–8px translate, subtle chevron turn | No hover-only dependency; focus stays meaningful, Escape/close, no accidental overlay collision. |
| Search | Focus/type → query/results | 180ms panel reveal, clear loading state; no infinite shimmer | Cancel stale requests, respect IME typing, useful server-search fallback. |
| Buttons | Hover/press → feedback | 120–160ms colour, at most 1–2px icon translation; press scale at most .98 | No magnetic cursor, no moving target; disabled/error distinct from loading. |
| Product image | Pointer hover or gallery swipe | At most 1.02 image scale or real second-image crossfade | Only if second image is the same actual product; touch never hides the purchase action. |
| Add to cart | Submit → pending → server-confirmed result | Stable-width button, then cart count/status update and optional drawer | No fake success; if request fails preserve input and show retry. No flying-pack animation delaying feedback. |
| Cart drawer | Open/close → panel state | 220–280ms translate with eased deceleration and backdrop opacity | Rapid repeated taps cannot leave body locked or duplicate add requests. Announce result. |
| Filters | Change selection → pending → apply/reset | Selection indicator 120ms; result region update keeps geometry stable | Apply count accurate, focus retained, Back restores query; no entire grid entrance animation on every change. |
| Quick view | Explicit action → dialog | 180–220ms opacity and very slight scale (.98→1) | Full product route stays available; purchase prerequisites validated. |
| Gallery | Previous/next/swipe → exact image | 180–240ms slide/fade, no overscroll trap | Buttons and keyboard alternatives; labelled image count, usable zoom. |
| FAQ/details | Click → expanded content | 160–220ms measured height and opacity if robust | Real disclosure semantics; no fixed max-height clipping translated or enlarged text. |
| Newsletter | Explicit request → form → actual response | Compact sheet/dialog reveal; error or confirmation replaces only needed content | No fake confetti/subscribed state; optional automatic trigger off initially. |
| Sticky purchase bar | Main action leaves view → secondary bar visible | 160–200ms translate + opacity | Avoid keyboard/consent overlap; required options route to original controls. |

Use a consistent ease such as `cubic-bezier(.22, 1, .36, 1)` for panels; timings are suggested design tokens, not externally proven conversion values. CSS transitions and small progressive JS enhancements are the default; do not add a React animation framework to a native theme just to imitate Framer. Respect `prefers-reduced-motion` with immediate state changes or simple short fades. Reduced motion removes choreography, not functionality. Do not animate content opacity from zero unless the no-JS path reveals it.

## Inspiration references — what was actually inspected

| Reference | Evidence available | Use in the build |
|---|---|---|
| [Framer ecommerce gallery](https://www.framer.com/marketplace/templates/categories/ecommerce/) | Current catalogue of ecommerce references; not a complete visual audit of every template | Source shortlist for hierarchy/spacing studies; templates do not imply Woo compatibility. |
| [Ecomiz listing](https://www.framer.com/marketplace/templates/ecomiz/) and [live demo](https://ecomiz.framer.website/) | Marketplace description and live page structure inspected | Study how product categories and featured products are separated; do not copy demo claims, long testimonial sections or add a blog outside scope. Exact curvature/animation remains a rendered-build comparison task. |
| [Mercur FLEEK design-system announcement](https://www.mercurjs.com/updates/figma-design-system-for-b2c-marketplace) | First-party component inventory and mobile-layout description inspected | Study complete menu/search/listing/product/cart/account states. Multi-vendor, seller/chat and split-order functions do not belong to this project. |
| [FLEEK Figma Community file](https://www.figma.com/community/file/1509107652560364956) | Exact creator-linked file resolved, but direct file inspection was blocked here | Claude may inspect with available Figma/browser access and record what it actually sees; do not claim this file has already been opened or imported. Verify licence before copying assets. |
| [Framer component interactions](https://www.framer.com/academy/lessons/framer-animations-component-interactions) | Official state/transition workflow inspected | Keep clear open/closed/loading/success/error variants and test repeated inputs across breakpoints. |
| [Framer motion hierarchy](https://www.framer.com/academy/lessons/bringing-websites-to-life-with-animation) | Official motion guidance inspected | Use feedback and transitions to explain changes, with consistent timing and reduced-motion alternatives. |
| [Figma interactive components](https://help.figma.com/hc/en-us/articles/360061175334-Create-interactive-components-with-variants) | Official variant-interaction documentation inspected | Document reusable control states instead of disconnected pretty screens. |

Figma and Framer are reference/prototyping sources here, not new production platforms or purchased dependencies. There is no requirement to connect a new Figma account before coding. If Claude has Figma/Framer/browser capabilities available, it may inspect targeted examples and verify design decisions using them. No fabricated file edits, animation inspections or integration claims.

## Required presentation proof

Before duplicating the style across the shop, render the actual header at 390 and 1440px and demonstrate: menu opening/closing; active game route; live search success/no-match; product hover/touch; confirmed cart update; filter apply/reset; one newsletter failure state. Include reduced motion, keyboard access and long content. A short recording may help review motion, but real data and interaction tests determine functionality. Curvature should be judged on the actual logo/product mix and practical hit areas, not a trend board alone.
