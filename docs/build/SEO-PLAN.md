# SEO plan and build record

Status: on-page, technical and performance work is built and tested on the local fixture. Nothing here has run on the live IONOS site, and nothing has been measured in Google yet.

## What was not possible from here (be honest with the client)
- **Search volumes and keyword difficulty** need Google Keyword Planner and/or Ahrefs. No access was available, so the keyword map below is built from what actually ranks in UK search results (searched 15 Nov 2026), not from volume data. Treat volumes as unknown until someone with those tools fills the last column.
- **Rankings, Search Console, Analytics** can only be read after launch.
- **Real product copy**: his product descriptions are placeholders. Unique descriptions per product are the largest remaining on-page gain and have to come from him (or be approved by him).

## Keyword map (hypotheses from live UK results)
Competitors' titles follow "`{Set or product}` Japanese Booster Box UK", with the set code (OP-15, FB09, sv2a) and "factory sealed" in the snippet. Category pages are named after the range ("Pokemon Japanese Booster Boxes").

| Page | Primary phrase | Supporting phrases | Volume (fill with Keyword Planner/Ahrefs) |
|---|---|---|---|
| Home | trading cards and collectibles UK | Pokémon, One Piece, Dragon Ball card shop UK | |
| Pokémon Japanese | Pokémon Japanese booster box UK | sealed, no shrink, factory sealed | |
| Pokémon English | Pokémon booster box UK | sealed, English | |
| One Piece Japanese | One Piece Japanese booster box UK | OP-xx set codes | |
| One Piece English | One Piece booster box UK | English | |
| Dragon Ball | Dragon Ball Fusion World booster box UK | FB-xx set codes | |
| Other TCGs | Naruto / Demon Slayer / Solo Leveling card game UK | | |
| Accessories | trading card accessories UK | | |
| Each product | `{exact product name incl. set code}` UK | | |

## Built
- **Titles and previews**: home is exactly "PokePieceTCG | Trading Cards & Collectibles UK" (also the link preview, editable in Settings > PokePiece). Shop, category and product titles follow a pattern, all under 65 characters and unique. Works with Rank Math too (it prints the tags, the theme supplies the home/shop/category wording).
- **Descriptions**: written per page type, 70 to 170 characters. Categories get distinct factual intros (language and shrink wording from his own naming); placeholder product copy is never used as a meta description.
- **Share image**: a proper 1200x630 branded image (was a 192px icon).
- **Canonicals and index control**: canonical on every indexable page; filter, sort and price URLs are `noindex, follow` and point at the clean listing; cart, checkout, account and search are not indexed. robots.txt blocks only cart, checkout, account and internal search (filter URLs are left crawlable so their noindex is seen).
- **Sitemap**: products, categories and key pages; excludes cart, checkout, account, tags and the user list.
- **Structured data**: Organization, WebSite with search, FAQ (from his own wording), plus WooCommerce's Product, Offer and BreadcrumbList. No invented ratings.
- **Links**: a crawl of home, shop, a category and a product finds no 404s (a draft Privacy page was being linked and is now only linked when published).
- **Images**: alt text on product images; photos of any shape framed whole; sub-sizes written as WebP when the server supports it.

## Performance (simulated mobile: 4x CPU slowdown, ~1.6 Mbps, 150 ms latency, no server compression)
| Page | Before | After |
|---|---|---|
| Home | 2.0 MB, largest paint 10.2 s | 1.0 MB, 4.6 s |
| Shop | 1.1 MB, 4.0 s | 0.8 MB, 3.7 s |
| Product | 1.6 MB, 6.1 s | 0.75 MB, 2.8 s |
Layout shift 0 on all three. Real figures on IONOS depend on its server, caching and compression; re-measure there (PageSpeed Insights on the staging URL, then production).

## After install (staging, then live)
1. `wp media regenerate --yes` so every existing photo gets the WebP, uncropped sizes.
2. Confirm Rank Math is active or not, and that only one tool prints titles.
3. Submit `/wp-sitemap.xml` (or Rank Math's sitemap) in Search Console; verify the domain.
4. Check no old URL changed (IDs and slugs are preserved); spot-check 20 product URLs return 200 and the old category URLs resolve.
5. Read 301/404 reports weekly for the first month.
6. His Google Business Profile: keep name, website link and category consistent with the site.

## Needs the client or a tool we do not have
- Real product descriptions (unique per product).
- Keyword Planner/Ahrefs volumes to prioritise sets and write category copy.
- Decision on the 11 sold-out "Live Rip and Ship" pages (hide, redirect or keep).
