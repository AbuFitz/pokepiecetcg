# Customer feedback round 2: breakdown

Source: customer message (12 Nov 2026). Status as of this commit.

| # | Request | Type | Status |
|---|---|---|---|
| 1 | Keep the **8-hour free shipping** (follow-up orders within 8 h of a first order). Built as a custom snippet in the **Code Snippets** plugin. | Existing functionality, not theme | **Not touched, not yet verified.** Code Snippets runs independently of the active theme, so switching theme should not remove it. Needs the snippet text and a staging test (see below). |
| 2 | Owner can change the **homepage banner image** for new set releases, without a developer. | Theme setting | **Built.** Settings > PokePiece > "Home page banner image" (Media Library picker, alt text, falls back to built-in art). Verified locally by setting and clearing the option. |
| 3 | Owner manages **featured products and listings**. | Theme + native Woo | **Built.** Products starred as Featured in WooCommerce appear first on the home page product row; everything else is native Woo product editing. |
| 4 | Add an **Other TCGs** category (Naruto, Demon Slayer, Solo Leveling, future games). | Theme + categories | **Built (theme side).** Shows in the nav, menu, footer, search chips, home panels, and colour-codes pages. The category itself must be created in WooCommerce (slug `other-tcgs`, children for each game). |
| 5 | Dedicated **Accessories** category (binders, sleeves, toploaders, starter bundles). | Theme + categories | **Built (theme side).** Same behaviour. Create a category with slug `accessories`. |
| 6 | Homepage wording should reflect a **wider range** of TCGs. | Copy | **Done** for the hero line, ticker, section heading ("Shop by category"), banner, SEO descriptions, structured data and footer notice. About page and policy wording are the owner's own text and were not changed. |
| 7 | Nav = **Shop All, Pokémon, One Piece, Dragon Ball, Other TCGs, Accessories**, with the owner able to **add categories himself**. | Theme | **Built and tested.** The nav and home panels are generated from WooCommerce categories, so any new top-level category appears automatically (children become its sub-menu). |
| 8 | A **run-through** once finished. | Delivery | Not started. Needs a finished staging site. |

## What is not verified
* Items 4, 5, 7 were tested on a local fixture site with sample categories, not on his WordPress. His real category slugs may differ (the theme recognises `other-tcgs`/`other-tcg`/`other` and `accessories`/`accessory`; any other top-level category still appears under its own name).
* Empty categories show a count of 0 and no photo until products are added.
* No change to checkout, payments, shipping, tax or orders.

## 8-hour free shipping: what we need
1. Export or copy the snippet from WordPress admin > Snippets (the snippet itself, no passwords).
2. Read it for theme dependencies (it will normally use WooCommerce hooks such as shipping-rate filters and the customer's order history).
3. On the private staging copy: place a first order, then a second within 8 hours (rate becomes free), and one after 8 hours (rate does not). Confirm guest vs logged-in behaviour matches today.
4. Only if the owner agrees, show a short note in the cart/checkout. Nothing is shown now because the exact rules are not confirmed.

## Where the owner changes things
* Banner image and intro line: Settings > PokePiece.
* Banner (top strip) messages: edit via the `pokepiece_banner_messages` filter or ask us to expose a field.
* New category: Products > Categories. Top-level categories appear in the nav automatically.
* Featured products: star in Products list (Woo "Featured").

## Round 3 (customer reply, 13 Nov)
| Request | Status |
|---|---|
| Public site focuses on sealed; remove LIVE OPENING badges, keep WooCommerce data/inventory | **Done.** The Live label is hidden on cards, product pages, cart, search, filters and home. Product data, stock and the `_pp_mode` field are untouched (still visible to staff in the product editor). Re-enable with option `pokepiece_show_live_label = yes`. Product *titles* that say "LIVE RIP AND SHIP" are product data and were not edited. |
| Rename "On the Shelf" to New Arrivals/Featured; showcase latest sealed Pokémon and One Piece | **Done.** "New arrivals": products starred Featured first, then newest, Pokémon and One Piece only, excluding live/rip-and-ship listings (by the mode field or title wording). |
| Some new category links 404 | **Cause:** the static demo had no pages for empty categories (Other TCGs, Naruto, etc.). **Fixed in the demo** (all categories and the password-reset page are exported; a link check finds none missing). On the real site these are WordPress category pages. |
| Pokémon Abyss Eye under One Piece Japanese | **Data issue, not changed.** Public data shows product 754 filed under One Piece Japanese. Owner confirms, then re-categorise in WooCommerce. The home page already uses product titles, so it does not show under the wrong game there. |
| Consistent sealed / no-shrink labels | Same labels everywhere (cards, product page, cart, search). Per-product values still come from the owner's mode field; unset shows nothing. |
| Manage listings, prices, stock; walkthrough at handover | Native WooCommerce, unchanged. Walkthrough to be scheduled. |
| Pre-launch checks: 8-hour shipping, payments, accounts, orders, shipping, Privacy link, placeholder descriptions, images, mobile, basket/checkout | **Not done.** Need the staging site. The demo's product descriptions are placeholder fixtures; real descriptions come from his WooCommerce. |

Open question for the customer/team: should products named "LIVE RIP AND SHIP" stay listed publicly (just without the badge) or be hidden from the shop? Currently they stay listed.
