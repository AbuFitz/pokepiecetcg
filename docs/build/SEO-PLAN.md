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

## Built in the second pass ("extreme" build)
- **Richer structured data**: product `brand` (the game franchise, from its category) and `itemCondition: NewCondition` (these are sealed new products), price/availability tags in link previews, `CollectionPage` + `ItemList` on shop and category pages. Works through WooCommerce's own schema and through Rank Math's product filter.
- **Internal linking**: every shop, category and product page ends with "Browse more ranges" (descriptive anchors to the other ranges, never to itself).
- **Google Merchant Center feed**: `{site}/?ppc_feed=google` (free product listings). Published, visible products only, never the old shipping-upgrade product; clean originals as images because Merchant Center rejects watermarked photos; price in GBP, stock status, brand, condition, category path. **Needs his Merchant Center account**, with shipping and returns set up inside it. Product identifiers are marked "no" because sealed TCG boxes have none; if he has GTINs, add them.
- **Guides**: two evergreen guides (Japanese vs English boxes; sealed boxes and shrink wrap) are created as **drafts** with `wp pokepiece ensure-guides`. Wording is deliberately general and must be reviewed and corrected by him before publishing. They link to the category pages.
- **Launch safety**: the launch report now flags "Discourage search engines" (a cloned staging site often has it ticked and the live site must not), a non-https address, and the feed URL.
- **Image weight**: logo SVG slimmed from 55 KB to 37 KB; watermark tile is one 51 KB WebP cached across every product image.

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

## Server settings worth asking IONOS or the team to confirm (not something the theme can do)
- Gzip/Brotli compression and long browser caching for CSS, JS, fonts, images (the local measurements above were taken with no compression, so real pages should be lighter).
- PHP 8.2+ and an object/page cache if the plan offers one.
- One HTTPS address (redirect `http://` and `www` to the chosen one).

## Needs the client or a tool we do not have
- Real product descriptions (unique per product).
- Keyword Planner/Ahrefs volumes to prioritise sets and write category copy.
- The 11 old "Live Rip and Ship" listings belong to his previous shop and are not carried into the new one (removed from the fixture and demo). On the live site, when the new shop replaces the old catalogue, make sure their old URLs are not left as broken links: redirect them to the matching category.
