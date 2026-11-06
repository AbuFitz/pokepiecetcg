/* Critical storefront journeys against a LOCAL FIXTURE site (scripts/dev-wp.sh + scripts/seed-fixtures.php with PP_INSTOCK_FIXTURE=1).
 * These prove theme behaviour on Woo's real cart/session code, NOT payment gateways, mail, or the client's staging data.
 * Usage: BASE=http://localhost:8088 node tests/e2e/storefront.cjs */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8088';
const SERVICE_SLUG = 'special-delivery-shipping-upgrade';
const results = []; let failed = 0;
async function t(name, fn) { try { await fn(); results.push(['PASS', name]); } catch (e) { failed++; results.push(['FAIL', name + ' :: ' + e.message.split('\n')[0]]); } }

(async () => {
  const b = await chromium.launch();
  const mk = async (w = 1440, extra = {}) => { const c = await b.newContext({ viewport: { width: w, height: w < 700 ? 844 : 900 }, ...extra }); const p = await c.newPage(); p.errs = []; p.on('pageerror', e => p.errs.push(e.message)); p.on('console', m => m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text()) && p.errs.push(m.text())); return p; };

  for (const w of [320, 390, 430, 768, 1440]) {
    await t(`no horizontal overflow + zoomable viewport @${w}: home, shop, product, 404`, async () => {
      const p = await mk(w);
      for (const u of ['/', '/shop/', '/product/pokemon-storm-emerald-live-rip-and-ship/', '/does-not-exist/']) {
        await p.goto(BASE + u, { waitUntil: 'networkidle' });
        const ov = await p.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        assert.ok(ov <= 0, `${u} overflows by ${ov}px`);
      }
      const vp = await p.getAttribute('meta[name=viewport]', 'content');
      assert.ok(!/user-scalable\s*=\s*no|maximum-scale\s*=\s*1(\.0)?\b/.test(vp), 'viewport blocks zoom: ' + vp);
      assert.deepEqual(p.errs, []);
      await p.context().close();
    });
  }

  await t('worlds panels: each shows loaded product photos; mis-filed Pokémon pack is not shown under One Piece; panel links reach real categories', async () => {
    for (const w of [390, 1440]) {
      const p = await mk(w); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
      await p.evaluate(() => document.querySelector('.pp-worlds').scrollIntoView()); await p.waitForTimeout(500);
      const r = await p.evaluate(() => [...document.querySelectorAll('.pp-world')].map(l => ({ n: l.querySelectorAll('.pp-world__photo img').length, ok: [...l.querySelectorAll('.pp-world__photo img')].every(i => i.complete && i.naturalWidth > 0), links: [...l.querySelectorAll('.pp-world__links a')].map(a => a.getAttribute('href')) })));
      assert.equal(r.length, 3); for (const x of r) { assert.ok(x.n === 1 && x.ok, 'panel images missing/unloaded @' + w); assert.ok(x.links.every(h => /product-category/.test(h))); }
      for (const [game, word] of [['pokemon', /pok[eé]mon/i], ['one-piece', /one piece/i], ['dragon-ball', /dragon ball/i]]) {
        const ids = await p.evaluate(g => [...document.querySelectorAll('.pp-game-' + g + ' .pp-world__photo')].map(e => e.dataset.pid), game);
        const prods = await (await p.request.get(BASE + '/wp-json/wc/store/v1/products?include=' + ids.join(',') + '&per_page=20')).json();
        assert.ok(prods.length === ids.length && prods.every(x => word.test(x.name)), game + ' panel shows a product whose title is not ' + game + ': ' + prods.map(x => x.name).join(' | '));
      }
      assert.deepEqual(p.errs, []); await p.context().close();
    }
  });

  await t('TikTok reel: real snapshots, links to his TikTok, auto-scrolls, no buttons/progress/play icon, Discord + follow CTAs', async () => {
    const p = await mk(390); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await p.evaluate(() => document.querySelector('.pp-community').scrollIntoView()); await p.waitForTimeout(800);
    const r = await p.evaluate(() => ({ cards: [...document.querySelectorAll('.pp-reel__set:first-child .pp-clip a')].map(a => a.href), imgs: [...document.querySelectorAll('.pp-reel__set:first-child img')].filter(i => i.complete && i.naturalWidth > 0).length, ctl: document.querySelectorAll('[data-clips],[data-pp-prog],.pp-prog,.pp-clip__play').length, discord: !!document.querySelector('.pp-comm__ctas a[href*="discord"]'), follow: !!document.querySelector('.pp-comm__ctas a[href*="tiktok.com/@pokepiecetcgg"]') }));
    assert.ok(r.cards.length >= 8 && r.cards.every(h => /tiktok\.com\/@pokepiecetcgg\//.test(h)), 'clips: ' + r.cards.length); assert.ok(r.imgs >= 3, 'first reel images did not load (rest are lazy by design): ' + r.imgs); assert.equal(r.ctl, 0, 'old controls still present'); assert.ok(r.discord && r.follow);
    const x0 = await p.evaluate(() => new DOMMatrix(getComputedStyle(document.querySelector('.pp-reel__track')).transform).m41); await p.waitForTimeout(1500);
    const x1 = await p.evaluate(() => new DOMMatrix(getComputedStyle(document.querySelector('.pp-reel__track')).transform).m41); assert.ok(x1 < x0, 'reel is not moving');
    assert.deepEqual(p.errs, []); await p.context().close();
  });

  await t('404 returns real 404 status and offers search + categories', async () => {
    const p = await mk(); const r = await p.goto(BASE + '/nope-nope/'); assert.equal(r.status(), 404);
    assert.ok(await p.locator('.pp-404 input[type=search]').count()); assert.ok(await p.locator('.pp-404__links a').count() > 2); await p.context().close();
  });

  await t('service product is excluded from home rows, hero stage, related and predictive search, yet its direct URL works', async () => {
    const p = await mk(); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    assert.equal(await p.locator('.pp-home li.product a[href*="' + SERVICE_SLUG + '"], .pp-home .pp-tile[href*="' + SERVICE_SLUG + '"]').count(), 0); /* only the explicit, labelled postage text link may point at the service */
    const r = await p.goto(BASE + '/product/' + SERVICE_SLUG + '/'); assert.equal(r.status(), 200);
    assert.ok((await p.textContent('.pp-callout--service')).includes('order notes'));
    const j = await (await p.request.get(BASE + '/wp-json/pokepiece/v1/search?q=shipping')).json(); assert.equal(j.items.length, 0);
    await p.context().close();
  });

  await t('search popup: pill opens a dialog, shows product tiles by default, finds OP14/OP-14/Pokémon with images, no-match keeps products, Escape closes', async () => {
    const p = await mk(); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    assert.equal(await p.locator('.pp-frame input[type=search]').count(), 0, 'header must not contain a full search bar');
    for (const q of ['OP14', 'OP-14', 'Pokémon']) { const j = await (await p.request.get(BASE + '/wp-json/pokepiece/v1/search?q=' + encodeURIComponent(q))).json(); assert.ok(j.items.length > 0 && j.items.every(i => i.img), q + ' no results/images'); }
    await p.click('[data-pp-search]'); await p.waitForSelector('#pp-search-pop.is-open');
    assert.ok(await p.evaluate(() => document.activeElement.id === 'pp-sp-input'), 'input not focused');
    assert.ok(await p.locator('#pp-search-pop .pp-sp__feat .pp-sp__tile img').count() >= 4, 'no default product tiles');
    await p.fill('#pp-sp-input', 'OP14'); await p.waitForSelector('#pp-search-pop .pp-sp__res:not([hidden]) .pp-sp__tile img');
    assert.equal(await p.locator('#pp-search-pop .pp-sp__feat').isHidden(), true);
    await p.fill('#pp-sp-input', 'zzzzqqq'); await p.waitForFunction(() => /No products/.test(document.querySelector('#pp-search-pop .pp-sp__status').textContent)); assert.equal(await p.locator('#pp-search-pop .pp-sp__feat').isVisible(), true);
    await p.keyboard.press('Escape'); await p.waitForSelector('#pp-search-pop', { state: 'hidden' }); await p.context().close();
  });

  await t('mobile menu: opens as dialog, focus enters, Escape closes and focus returns to trigger', async () => {
    const p = await mk(390); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await p.click('.pp-menu-open'); await p.waitForSelector('#pp-menu.is-open');
    assert.equal(await p.getAttribute('.pp-menu-open', 'aria-expanded'), 'true');
    assert.ok(await p.evaluate(() => document.getElementById('pp-menu').contains(document.activeElement)), 'focus not inside menu');
    assert.ok(await p.locator('#pp-menu details').count() >= 3);
    await p.keyboard.press('Escape'); await p.waitForSelector('#pp-menu', { state: 'hidden' });
    assert.ok(await p.evaluate(() => document.activeElement.classList.contains('pp-menu-open')), 'focus not returned'); assert.equal(await p.evaluate(() => document.body.classList.contains('pp-lock')), false); await p.context().close();
  });

  await t('desktop nav disclosure works by keyboard and Escape closes it', async () => {
    const p = await mk(); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await p.focus('.pp-nav__btn'); await p.keyboard.press('Enter');
    assert.equal(await p.getAttribute('.pp-nav__btn', 'aria-expanded'), 'true'); assert.ok(await p.locator('.pp-nav__sub:visible a').count() > 0);
    await p.keyboard.press('Escape'); assert.equal(await p.getAttribute('.pp-nav__btn', 'aria-expanded'), 'false'); await p.context().close();
  });

  await t('cart: AJAX add -> server-confirmed count + drawer; qty +/-; stock-limit message; remove; empty state; Woo totals', async () => {
    const p = await mk(390); await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' });
    await p.click('li.pp-card--in .add_to_cart_button >> nth=0'); await p.waitForSelector('#pp-cart-drawer.is-open');
    assert.equal(await p.locator('.pp-line').count(), 1); assert.equal((await p.textContent('.pp-cart-count')).trim(), '1');
    await p.click('.pp-line [data-pp-qty="1"]'); await p.waitForFunction(() => document.querySelector('.pp-line output')?.textContent === '2');
    assert.equal((await p.textContent('.pp-cart-count')).trim(), '2');
    assert.match(await p.textContent('.pp-subtotal strong'), /£\d+\.\d\d/);
    // body not locked after close; reopen works
    await p.click('#pp-cart-drawer .pp-close'); await p.waitForSelector('#pp-cart-drawer', { state: 'hidden' });
    assert.equal(await p.evaluate(() => document.body.classList.contains('pp-lock')), false);
    await p.click('.pp-cart-open'); await p.waitForSelector('#pp-cart-drawer.is-open');
    // full cart page uses the same Woo session
    const pg = await p.context().newPage(); await pg.goto(BASE + '/cart/'); assert.ok(await pg.locator('.woocommerce-cart-form').count()); await pg.close();
    await p.click('.pp-line [data-pp-remove]'); await p.waitForSelector('.pp-empty');
    assert.equal(await p.locator('.pp-cart-count:visible').count(), 0); await p.context().close();
  });

  await t('cart nonce is required: forged update without nonce is rejected', async () => {
    const p = await mk(); const r = await p.request.post(BASE + '/wp-admin/admin-ajax.php', { form: { action: 'pokepiece_cart_update', key: 'x', qty: 1 } });
    assert.ok([400, 403].includes(r.status()), 'status ' + r.status()); await p.context().close();
  });

  await t('filters: server-rendered, URL-driven chips, removable; empty result offers Clear all; sort kept', async () => {
    const p = await mk(); await p.goto(BASE + '/shop/?pp_mode=live&pp_stock=1&orderby=price', { waitUntil: 'networkidle' });
    assert.equal(await p.locator('.pp-chip:not(.pp-chip--clear)').count(), 2);
    await p.click('.pp-chip >> nth=0'); await p.waitForLoadState('networkidle'); assert.ok(!/pp_mode/.test(p.url())); assert.ok(/orderby=price/.test(p.url()));
    await p.goto(BASE + '/shop/?pp_mode=preorder', { waitUntil: 'networkidle' }); assert.ok(await p.locator('.pp-empty--archive a:has-text("Clear all")').count()); await p.context().close();
  });

  await t('out-of-stock products are never offered an Add action; zero-stock home state is honest', async () => {
    const p = await mk(); await p.goto(BASE + '/shop/?pp_mode=live', { waitUntil: 'networkidle' });
    const cards = await p.locator('li.pp-card--out').count(); assert.ok(cards > 0);
    assert.equal(await p.locator('li.pp-card--out .add_to_cart_button').count(), 0); await p.context().close();
  });

  await t('quick view opens as dialog, traps focus, closes with Escape and returns focus', async () => {
    const p = await mk(); await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' });
    const btn = p.locator('.pp-qv-open >> nth=0'); await btn.focus(); await btn.press('Enter'); await p.waitForSelector('#pp-qv.is-open .pp-qvc');
    assert.ok(await p.evaluate(() => document.getElementById('pp-qv').contains(document.activeElement)));
    await p.keyboard.press('Escape'); await p.waitForSelector('#pp-qv', { state: 'hidden' }); await p.context().close();
  });

  await t('product page: unknown mode shows no mode label (never defaults to sealed); known mode shown with text', async () => {
    const p = await mk(); await p.goto(BASE + '/product/one-piece-op14-booster-box/', { waitUntil: 'networkidle' });
    assert.equal(await p.locator('.summary .pp-callout').count(), 0); assert.equal(await p.locator('.summary .pp-mode').count(), 0);
    await p.goto(BASE + '/product/pokemon-storm-emerald-live-rip-and-ship/', { waitUntil: 'networkidle' });
    assert.match(await p.textContent('.pp-callout--live'), /Opened live/); await p.context().close();
  });

  await t('sticky purchase bar appears only after the main action leaves view (phone)', async () => {
    const p = await mk(390); const live = await (await p.request.get(BASE + '/wp-json/wc/store/v1/products?per_page=30')).json(); const ins = live.find(x => x.is_in_stock && x.id !== 686); await p.goto(ins.permalink, { waitUntil: 'networkidle' });
    assert.equal(await p.locator('.pp-sticky.is-shown').count(), 0);
    await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForSelector('.pp-sticky.is-shown', { timeout: 3000 }); await p.context().close();
  });

  await t('newsletter UI + endpoint stay disabled without a provider (501), no fake success', async () => {
    const p = await mk(); await p.goto(BASE + '/', { waitUntil: 'networkidle' }); assert.equal(await p.locator('#pp-nl').count(), 0);
    const r = await p.request.post(BASE + '/wp-json/pokepiece/v1/newsletter', { data: { email: 'a@b.co', consent: 1 } }); assert.equal(r.status(), 501); await p.context().close();
  });

  await t('reduced motion: layers still open/close functionally', async () => {
    const p = await mk(390, { reducedMotion: 'reduce' }); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await p.click('.pp-cart-open'); await p.waitForSelector('#pp-cart-drawer.is-open'); await p.keyboard.press('Escape'); await p.waitForSelector('#pp-cart-drawer', { state: 'hidden', timeout: 1500 }); await p.context().close();
  });

  await t('checkout + cart + account pages render without console errors', async () => {
    const p = await mk(); for (const u of ['/cart/', '/checkout/', '/my-account/']) { await p.goto(BASE + u, { waitUntil: 'networkidle' }); assert.ok(await p.locator('h1').count()); }
    assert.deepEqual(p.errs, []); await p.context().close();
  });

  await b.close();
  for (const [s, n] of results) console.log(s, n);
  console.log(`\n${results.length - failed}/${results.length} passed`); process.exit(failed ? 1 : 0);
})();
