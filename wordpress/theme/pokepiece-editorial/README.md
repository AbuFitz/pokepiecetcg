# PokePiece editorial — implementation status

This folder is the first **native WoodMart child-theme implementation** of the approved PokePieceTCG redesign. It is **not yet verified against the client's IONOS WordPress instance**. Do not activate in production without protected staging and compatibility validation.

## Included
- Responsive editorial homepage using actual WooCommerce product queries and accurate stock state.
- Product/category journeys link to native WooCommerce routes.
- Accessible click-to-open mobile menu with Escape, focus return/trap, backdrop and scroll lock.
- Bundled reconstructed horizontal logo.
- Shared header/footer, styling of native Woo product grids, buttons, fields and product details.
- Checkout, account, shipping, stock and payment processing intentionally remain owned by existing WooCommerce and extensions.

## Known gaps before staging approval
- WordPress admin/IONOS agency access, exact WoodMart version, parent slug, templates, Elementor usage, permissions and active plugins are not yet verified.
- Product/category metadata is not yet guaranteed to distinguish sealed delivery, no-shrink and live opening. These must come from verified merchant data; no default fulfilment promise is shown.
- Existing theme navigation, Elementor header/footer and Woo template overrides may conflict with the child theme. Disable duplicate header/footer templates only after inspecting staging.
- Header/footer policy page paths and category links must be matched against actual registered routes; no old pages may be removed.
- No tested custom cart drawer, predictive search, advanced filters, TikTok stock/order sync, newsletter provider, marketing automation or redesigned Woo checkout flow exists yet.
- No live or staging browser tests have been run. Test at 320/390/430/768/1440, keyboard, 200% zoom and reduced motion, with real cart, checkout, order-confirmation and account journeys.
- Accessibility, visual fit of each product image, PHP syntax, WoodMart hooks, performance and gateway parity remain unverified.
- Source and brand files are public; secrets, customer data and staging backups must remain private.

## Staging installation
1. Confirm that the licensed parent theme directory is actually `woodmart` and remains installed/enabled.
2. Create an access-protected WordPress staging copy with **separate database**, disabled live webhooks, order emails and payment credentials.
3. Copy `wordpress/theme/pokepiece-editorial` into `wp-content/themes/pokepiece-editorial`; preserve the existing themes/plugins/configuration. Activate only on staging.
4. Verify the homepage and all native WooCommerce routes; compare against the route inventory in `docs/redesign`.
5. Test cart, account, payments in sandbox, delivery options, emails, SEO, cookies/consent and all active extensions.
6. Stage further implementation and changes with a file/content manifest. Never promote a stale staging database onto live orders.

This stage is working source code committed to the repository, **not a claim of successful website deployment or completed project**.
