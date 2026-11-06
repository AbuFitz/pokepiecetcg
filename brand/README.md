# PokePieceTCG — supplied identity production pack

Prepared 6 November 2026 from the user-supplied transparent `pptcg.png` (798×549). This is an asset-preparation pack retaining the existing name and symbol, not a new brand concept.

## What is authoritative

`source/pptcg-original.png` is the exact supplied PNG, unchanged. It contains transparency; the green seen in some previews is not an intended brand background. No original vector master or font was supplied.

`svg/` contains **labelled flat vector reconstructions**: the supplied wordmark was vector-traced and smoothed; the circular symbol was reconstructed geometrically. Texture, gradient, small irregularities and some proportions are intentionally simplified for production. These are not recovered original SVG files, and the source PNG remains available for exact appearance. Check the comparison board and obtain visual acceptance before replacing the live logo.

## Files and uses

| Files | Use |
|---|---|
| `svg/stacked-colour.svg` | Supplied stacked arrangement, flat reconstruction. |
| `svg/horizontal-colour.svg` | Derived horizontal lockup for a readable website header. |
| `svg/wordmark-colour.svg` | Name alone where the symbol already appears nearby. |
| `svg/symbol-colour.svg` | Icon-only mark for favicon and avatars. |
| `svg/*-ink.svg`, `svg/*-white.svg` | One-colour derivatives with real cutouts. Wordmark uses simpler fill letterforms without the coloured logo's heavy outer outline. |
| `png/` | Transparent raster exports at useful sizes; scalable source remains SVG. |
| `web/favicon.svg`, `web/favicon.ico` | Browser icon: symbol only, **no name**. ICO contains 16/32/48px versions. |
| `web/favicon-16/32/48.png` | PNG browser fallbacks, symbol only. |
| `web/apple-touch-icon.png` | 180px icon on a cream tile. |
| `web/icon-192.png`, `web/icon-512.png`, `web/maskable-512.png` | Optional web icon assets; not a claim that the shop is an offline app. |
| `web/head-snippet.html` | Integration example; replace sample paths using actual WordPress theme helpers. |
| `web/site.webmanifest` | Optional icon metadata; relative paths assume the icons share its folder. |
| `previews/logo-board.png` | Comparison and usage board with original/reconstructed layouts, backgrounds and favicon sizes. |
| `checksums.sha256` | Integrity list for source and export files. |

## Reconstructed artwork palette

Ink `#202321`; red `#C1232C`; yellow `#F4C641`; warm light `#FAF7EE`. These are chosen flat approximations, not measured original Pantone/CMYK specifications. UI action red is darker (`#B51F2A`) for readable white text. Do not assume logo colours are automatically suitable for body text.

## Usage

- Keep proportions. No stretching, extra outlines, gradient recolouring or drop shadows.
- Prefer the coloured mark on light neutral surfaces. For dark surfaces use the white derivative or a light backing with enough clear space; the coloured mark's dark outer outline can disappear on dark backgrounds.
- Clear space: at least one central button diameter around the symbol; for a full lockup, at least half the letter height around the artwork where practical. Maintain padding inside the nav.
- Suggested minimums to test in context: horizontal lockup around 180–200px wide; stacked around 140px; standalone symbol 24px for normal UI. Favicon has a separate 16px export and has been inspected at native size.
- Website UI fonts are separate from the logo lettering. No original font identity is asserted, and SVGs contain paths rather than font dependencies.
- No new trademark clearance is asserted by this asset conversion. This pack preserves client-supplied identity; no claim of affiliation with franchise owners is added.

## Verification

Source checksum equality, SVG structure/no embedded bitmap or live text, transparent export dimensions, icon-only files and ICO frame sizes are checked during delivery. Visual board inspected at small and large sizes. The traced wordmark necessarily has more paths than a hand-drawn original; future professional redrawing could simplify it further. Do not describe it as a recovered editable original.
