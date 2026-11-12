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
