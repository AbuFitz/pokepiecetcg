# 08 — Research sources and evidence limits

Accessed 6 November 2026. Live shop data changes; re-check at implementation. First-party pages support platform/technical/legal statements; Baymard is the research publisher for the UX recommendation. Art direction, dimensions, budgets and sequence are project recommendations, not proven conversion results.

## Store audit

| ID | Source | Used for |
|---|---|---|
| A01 | https://pokepiecetcg.co.uk/ | Published positioning, homepage merchandising, Vinted review provenance, newsletter UI and viewport metadata. |
| A02 | https://pokepiecetcg.co.uk/shop/ and https://pokepiecetcg.co.uk/shop/page/2/ | 23-result catalogue snapshot, stock labels, fulfilment names, taxonomy discrepancy and near-duplicate OP14 titles. |
| A03 | https://pokepiecetcg.co.uk/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,link,title | 11 public page IDs, canonical links and retained page structure. |
| A04 | https://pokepiecetcg.co.uk/wp-json/wc/store/v1/products/categories?per_page=100 | Five public category slugs/links; counts overlap. |
| A05 | https://pokepiecetcg.co.uk/wp-json/ | WP/Woo and other public plugin namespace evidence, front-page configuration; not proof of private settings/licences. |
| A06 | https://pokepiecetcg.co.uk/wp-sitemap.xml , https://pokepiecetcg.co.uk/page-sitemap.xml , https://pokepiecetcg.co.uk/product-sitemap.xml | Public sitemap route cross-check. Output is not a complete private content/export inventory. |
| A07 | https://pokepiecetcg.co.uk/product/special-delivery-shipping-upgrade/ | Service-only shipping SKU, observed price and order/TikTok reference instructions. |
| A08 | https://pokepiecetcg.co.uk/product/one-piece-eb-04-egghead-crisis-booster-box-japanese-sealed/ | Sample sealed product, current facts/stock UI and repetitive copy to verify. |
| A09 | https://pokepiecetcg.co.uk/product/pokemon-abyss-eye-booster-box-no-shrink-japanese-sealed-copy/ | No-shrink disclosure and mismatched set wording; claims need merchant verification. |
| A10 | https://pokepiecetcg.co.uk/shipping-policy/ | Published dispatch range and damage-reporting request. |
| A11 | https://pokepiecetcg.co.uk/refund-return-policy/ | Published returns restrictions requiring owner review, not legal endorsement. |
| A12 | https://pokepiecetcg.co.uk/about/ and https://pokepiecetcg.co.uk/contact/ | Existing story, contact fields and response-time wording. |

## Retail references

| ID | Source | Used for |
|---|---|---|
| R01 | https://antiripripclub.com/ | Product-first new/popular merchandising and straightforward navigation; sold-out featured examples. |
| R02 | https://reevytcg.com/ | Game/language/format organisation and live-opening intent; catalogue complexity and popup interruption. |
| R03 | https://coddyscollection.com/ | Game gateways, narrative identity, trust/contact details. |
| R04 | https://onthepulltcg.com/ | Discovery, arrivals and community structure; dense navigation/services beyond this project. |
| R05 | https://magicmadhouse.co.uk/ | Public search prominence and set/game information architecture; content inspection, not full responsive visual audit. |
| R06 | https://www.chaoscards.co.uk/ | Public product-format/language organisation; content inspection, not full responsive visual audit. |
| U01 | https://baymard.com/research-articles/how-to-design-applied-filters | Visible applied-filter overview on desktop and mobile. Qualitative decision only; no claimed uplift for this shop. |
| U02 | https://baymard.com/research/mcommerce-usability | Mobile search, listing and filtering as distinct usability concerns. |

## Official implementation guidance

| ID | Source | Used for |
|---|---|---|
| W01 | https://developer.woocommerce.com/docs/theming/theme-development/classic-theme-developer-handbook/ | Native theme integration and preference for hooks over unnecessary template overrides. |
| W02 | https://woocommerce.com/document/woocommerce-store-editing/customizing-cart-and-checkout/ | Classic/Blocks choice and extension compatibility. |
| W03 | https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/checkout-payment-methods/payment-method-integration | Gateway integration requirements when Blocks are used. |
| W04 | https://developer.woocommerce.com/docs/apis/store-api/ | Customer-facing products/cart/checkout API, sessions and write protections; not an admin API. |
| W05 | https://woocommerce.com/document/back-in-stock-notifications/ | Genuine extension-backed alerts, variations, restock queue and mail-deliverability dependency. No subscription price assumed. |
| W06 | https://woocommerce.com/document/email-authentication/ | Branded sending and SPF/DKIM/DMARC verification. |
| W07 | https://woocommerce.com/document/tiktok-for-woocommerce/ and https://woocommerce.com/products/tiktok-for-woocommerce/ | TikTok integration exists; exact UK Shop/order/inventory functionality still requires current region/account/connector verification. |
| W08 | https://woodmarttheme.com/docs/configure-the-promo-popup/ | Existing-theme popup capability to inspect if installed/licensed; not a reason to install a second popup system. |
| W09 | https://developer.wordpress.org/advanced-administration/security/hardening/ | Private backups, updates, access and operational security. |
| I01 | https://www.ionos.com/help/partner-program/working-with-the-ionos-partner-portal/creating-and-managing-web-projects/ | Agency project access and CMS links. |
| I02 | https://www.ionos.co.uk/help/partner-program/working-with-the-ionos-partner-portal/adding-clients/ and https://www.ionos.co.uk/help/partner-program/ | Client acceptance/access workflow; verify actual grant and hosting capabilities. |
| I03 | https://www.ionos.co.uk/digitalguide/hosting/blogs/setting-up-wordpress-staging/ | Separate staging copy/database concept. Older tutorial: verify current host/plugin facilities; do not assume its old compatibility details still apply. |
| I04 | https://www.ionos.co.uk/help/wordpress/wordpress-at-ionos/restoring-a-wordpress-installation-and-database-in-your-ionos-account/ | Restore overwrites subsequent changes, including shop orders. Backup feature eligibility depends on actual package. |

## Search, type, accessibility and UK consumer/privacy guidance

| ID | Source | Used for |
|---|---|---|
| S01 | https://developers.google.com/search/docs/specialty/ecommerce/help-google-understand-your-ecommerce-site-structure | Crawlable navigation/category/product discovery. |
| S02 | https://developers.google.com/crawling/docs/faceted-navigation | Control unnecessary faceted crawl space; do not conflate blocking with noindex. |
| S03 | https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites | Consistent URLs and duplicate/query handling. |
| S04 | https://developers.google.com/search/docs/specialty/ecommerce/include-structured-data-relevant-to-ecommerce | Relevant truthful ecommerce structured-data entities. |
| S05 | https://developers.google.com/search/docs/appearance/structured-data/product-variants/ | ProductGroup requirements and supported variant modelling; avoid forcing unsupported dimensions. |
| F01 | https://github.com/jpt/barlow and https://github.com/jpt/barlow/blob/main/OFL.txt | Barlow family provenance and OFL; verify exact downloaded font build. |
| F02 | https://rsms.me/inter/ and https://github.com/rsms/inter/blob/master/LICENSE.txt | Inter provenance and OFL; retain actual distribution licence. |
| P01 | https://web.dev/articles/vitals and https://developers.google.com/search/docs/appearance/core-web-vitals | CWV objectives and real-user measurement distinction. |
| P02 | https://www.w3.org/TR/WCAG22/ | Accessibility target and applicable criteria; practical 44px targets exceed the 24px AA minimum in many cases. |
| L01 | https://www.gov.uk/accepting-returns-and-giving-refunds | Online cancellation and faulty/not-as-described rights; current store wording needs review. |
| L02 | https://www.gov.uk/online-and-distance-selling-for-businesses | Pre-contract identity, description, costs/delivery and cancellation information. |
| L03 | https://www.gov.uk/online-and-distance-selling-for-businesses/online-selling | Clear paid order, optional extras, confirmation and terms accessibility. |
| L04 | https://ico.org.uk/for-organisations/direct-marketing-and-privacy-and-electronic-communications/guidance-on-direct-marketing-using-electronic-mail/ | Newsletter consent/soft opt-in conditions and unsubscribe. Guidance updated April 2026. |
| L05 | https://ico.org.uk/for-organisations/direct-marketing-and-privacy-and-electronic-communications/guidance-on-the-use-of-storage-and-access-technologies/how-do-we-manage-consent-in-practice | Purpose-specific exceptions, consent, granularity and withdrawal; assess real technologies. |
| C01 | https://github.com/AbuFitz/onesimplesite-web-designer/blob/main/chatgpt/ONESIMPLESITE_CONTEXT.md | Current suite conventions, appropriate builder/research invocation and truth ledger. |

## Limits and unresolved evidence

- No admin access, package inspection, licence verification, checkout/payment test, mail delivery test, performance measurement or Search Console analysis was completed in this research phase.
- A read-only browser research run inspected rendered desktop pages for the five supplied shops. It could not control viewport width; it supplied no downloadable screenshots. Its qualitative judgments are research notes, not visual QA evidence for the new design. See `visual-audit.md`.
- WoodMart class prefixes strongly identify the theme family, but do not prove active parent/child versions or licence entitlement. Admin remains authoritative. Plugin namespace presence similarly does not prove all advertised functions are enabled.
- Public product API output was partly reformatted by extraction and is not preserved as a verified catalogue export. Use admin/native exports privately during implementation.
- Pokémon Center was script-limited and Cardmarket returned 403. Do not cite unobserved layouts. A requested WoodMart child-theme documentation URL was unavailable; a different XTemos child-theme result belonged to Basel and was not used as WoodMart authority.
- Original source logo: user-attached `pptcg.png`, 798×549, transparent indexed PNG. No original font or editable vector source was supplied. Production SVG derivatives are labelled reconstructions.
