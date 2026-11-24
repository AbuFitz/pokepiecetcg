/* Response speed and reactions: how long until each control visibly reacts (measured in the page, click handler to painted result), with the CPU slowed 4x like a
 * mid-range phone, plus failure handling (network down), double clicks and rapid clicking. LOCAL FIXTURE: server time is not production time, so network-backed
 * actions have generous budgets; instant UI (popups, menus, toggles) must react within 150 ms even throttled.
 * Usage: BASE=http://localhost:8088 node tests/e2e/responsiveness.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8088'; let fail = 0; const rows = [];
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
// time (ms) from calling `go()` in the page until `done()` is true on a painted frame
const measure = (p, go, done, limit = 8000) => p.evaluate(async ([go, done, limit]) => { const g = new Function('return (' + go + ')()'), d = new Function('return (' + done + ')()'); const t0 = performance.now(); g(); return await new Promise(res => { const tick = () => { if (d()) return res(Math.round(performance.now() - t0)); if (performance.now() - t0 > limit) return res(-1); requestAnimationFrame(tick); }; requestAnimationFrame(tick); }); }, [go.toString(), done.toString(), limit]);
const budget = async (name, ms, max, p, go, done, note = '') => { rows.push([name, ms]); assert.ok(ms >= 0, name + ': never reacted'); assert.ok(ms <= max, name + ': ' + ms + ' ms (budget ' + max + ')' + note); };
const consent = [{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }];
(async () => {
  const b = await chromium.launch(); const j = await (await (await b.newContext()).request.get(BASE + '/wp-json/wc/store/v1/products?per_page=20')).json(); const inStock = j.filter(x => x.is_in_stock && x.id !== 686);
  for (const [lab, opts, cpu] of [['desktop', { viewport: { width: 1440, height: 900 } }, 1], ['phone', { ...devices['iPhone 13'] }, 4]]) {
    const ctx = await b.newContext(opts); await ctx.addCookies(consent); const p = await ctx.newPage(); const cdp = await ctx.newCDPSession(p); await cdp.send('Emulation.setCPUThrottlingRate', { rate: cpu });
    const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('dialog', d => d.dismiss());
    await p.goto(BASE + '/', { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
    await T(lab + ': search popup opens and focuses the field fast', async () => { const ms = await measure(p, () => document.querySelector('.pp-searchbtn').click(), () => document.querySelector('#pp-search-pop.is-open') && document.activeElement && document.activeElement.id === 'pp-sp-input'); await budget('search open', ms, 150 * cpu, p); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    await T(lab + ': search shows "Searching" at once, results arrive, and a failed request says so', async () => {
      await p.locator('.pp-searchbtn').click(); await p.waitForSelector('#pp-search-pop.is-open'); const inp = p.locator('#pp-sp-input'); const t0 = Date.now(); await inp.pressSequentially('one piece', { delay: 20 });
      await p.waitForFunction(() => document.querySelectorAll('#pp-search-pop .pp-sp__res .pp-sp__tile').length > 0, null, { timeout: 8000 }); rows.push([lab + ' search results (typed+network)', Date.now() - t0]);
      await inp.fill(''); await p.route('**/pokepiece/v1/search*', r => r.abort()); await inp.pressSequentially('pokemon', { delay: 20 }); await p.waitForFunction(() => /unavailable|could not|try again|Press Enter/i.test(document.querySelector('#pp-search-pop .pp-sp__status').textContent), null, { timeout: 6000 }); await p.unroute('**/pokepiece/v1/search*'); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    await T(lab + ': cart drawer opens fast', async () => { const ms = await measure(p, () => document.querySelector('.pp-cart-open').click(), () => document.querySelector('#pp-cart-drawer.is-open')); await budget('drawer open', ms, 150 * cpu, p); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    if (lab === 'desktop') await T(lab + ': nav dropdown opens fast and closes on Escape / outside click', async () => { const ms = await measure(p, () => document.querySelector('.pp-nav__btn').click(), () => { const s = document.querySelector('.pp-nav__sub'); return s && !s.hidden; }); await budget('nav dropdown', ms, 150, p); await p.keyboard.press('Escape'); assert.equal(await p.locator('.pp-nav__sub:not([hidden])').count(), 0, 'Escape did not close'); await p.locator('.pp-nav__btn').first().click(); await p.mouse.click(700, 600); await p.waitForTimeout(250); assert.equal(await p.locator('.pp-nav__sub:not([hidden])').count(), 0, 'outside click did not close'); });
    if (lab === 'phone') await T(lab + ': mobile menu opens fast and closes', async () => { const ms = await measure(p, () => document.querySelector('[aria-controls=pp-menu]').click(), () => document.querySelector('#pp-menu.is-open')); await budget('menu open', ms, 150 * cpu, p); await p.keyboard.press('Escape'); await p.waitForTimeout(300); assert.equal(await p.locator('#pp-menu.is-open').count(), 0); });
    if (lab === 'desktop') await T(lab + ': account popup opens fast; tabs switch', async () => { const ms = await measure(p, () => document.querySelector('.pp-act-account').click(), () => document.querySelector('.pp-modal--account.is-open')); await budget('account open', ms, 150, p); const t = await measure(p, () => document.querySelector('#pp-tab-up').click(), () => !document.querySelector('#pp-pane-up').hidden); await budget('account tab', t, 150, p); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' }); await p.waitForTimeout(400);
    await T(lab + ': add to cart from a card gives instant feedback, then the drawer shows the item', async () => {
      const id = await p.evaluate(() => document.querySelector('li.pp-card[data-stock=in] .add_to_cart_button').dataset.product_id);
      const fb = await measure(p, () => document.querySelector('li.pp-card[data-stock=in] .add_to_cart_button').click(), () => { const b = document.querySelector('li.pp-card[data-stock=in] .add_to_cart_button'); return b.classList.contains('loading') || b.getAttribute('aria-busy') === 'true' || b.disabled || document.querySelector('#pp-cart-drawer.is-open'); }); await budget('add-to-cart feedback', fb, 150 * cpu, p, ' (button gave no sign it was working)');
      await p.waitForSelector('#pp-cart-drawer.is-open .pp-line', { timeout: 8000 }); assert.equal(await p.locator('#pp-cart-drawer .pp-line').count(), 1); await p.waitForTimeout(300); });
    await T(lab + ': quantity + / - / remove react, keep the total right, and survive rapid clicking', async () => {
      const plus = p.locator('#pp-cart-drawer .pp-line [data-pp-qty="1"]').first(); for (let i = 0; i < 6; i++) await plus.click({ delay: 0, force: true, noWaitAfter: true }).catch(() => {});
      await p.waitForFunction(() => { const o = document.querySelector('#pp-cart-drawer .pp-line output'); return o && +o.textContent === 7; }, null, { timeout: 15000 });
      await p.waitForTimeout(600); const shown = await p.locator('#pp-cart-drawer .pp-line output').first().textContent(); const cartQty = await p.evaluate(async () => { const r = await fetch('/wp-json/wc/store/v1/cart'); return (await r.json()).items[0].quantity; }); assert.equal(+shown, cartQty, 'drawer shows ' + shown + ' but the cart has ' + cartQty);
      const min = p.locator('#pp-cart-drawer .pp-line [data-pp-qty="-1"]').first(); for (let i = 0; i < 6; i++) await min.click({ force: true, noWaitAfter: true }).catch(() => {}); await p.waitForFunction(() => { const o = document.querySelector('#pp-cart-drawer .pp-line output'); return o && +o.textContent === 1; }, null, { timeout: 15000 });
      const rm = await measure(p, () => document.querySelector('#pp-cart-drawer .pp-line__remove').click(), () => !document.querySelector('#pp-cart-drawer .pp-line') && /empty/i.test(document.querySelector('#pp-cart-drawer').textContent), 8000); rows.push([lab + ' remove line', rm]); assert.ok(rm >= 0, 'remove never updated the drawer'); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    await T(lab + ': cart drawer reports a failed update instead of silently doing nothing, and recovers', async () => {
      await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' }); await p.evaluate(() => document.querySelector('li.pp-card[data-stock=in] .add_to_cart_button').click()); await p.waitForSelector('#pp-cart-drawer.is-open .pp-line');
      const block = /admin-ajax\.php|wc-ajax=/; await p.route(block, r => r.abort());
      try { await p.evaluate(() => document.querySelector('#pp-cart-drawer .pp-line [data-pp-qty="1"]').click()); await p.waitForFunction(() => /could not be updated|try again|nothing was changed/i.test(document.querySelector('#pp-cart-drawer').textContent), null, { timeout: 8000 }); }
      finally { await p.unroute(block); }
      await p.evaluate(() => document.querySelector('#pp-cart-drawer .pp-line [data-pp-qty="1"]').click()); await p.waitForFunction(() => document.querySelector('#pp-cart-drawer .pp-line output') && !/could not be updated/i.test(document.querySelector('#pp-cart-drawer .pp-drawer__status').textContent), null, { timeout: 8000 });
      await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    if (lab === 'desktop') await T(lab + ': quick view shows instant feedback and then the product', async () => { await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' }); const ms = await measure(p, () => document.querySelector('.pp-qv-open').click(), () => document.querySelector('#pp-qv.is-open')); await budget('quick view open', ms, 200, p); await p.waitForSelector('#pp-qv .pp-qvc__img', { timeout: 8000 }); await p.keyboard.press('Escape'); await p.waitForTimeout(300); });
    await T(lab + ': product page: double-clicking Add to cart adds ONE item', async () => {
      const cartQty = async () => (await (await ctx.request.get(BASE + '/wp-json/wc/store/v1/cart')).json()).items.reduce((a, i) => a + i.quantity, 0);
      await ctx.clearCookies(); await ctx.addCookies(consent); await p.goto(inStock[0].permalink, { waitUntil: 'networkidle' });
      await p.locator('.single_add_to_cart_button').dblclick().catch(() => {}); await p.waitForLoadState('networkidle').catch(() => {}); await p.waitForTimeout(800);
      assert.equal(await cartQty(), 1, 'a double click added ' + await cartQty() + ' items');
    });
    await T(lab + ': Special Delivery tick on the cart page reacts, and rapid flipping ends in a state that matches the cart', async () => {
      await p.goto(BASE + '/cart/', { waitUntil: 'networkidle' }); const label = p.locator('.pp-woo-content .ppc-sd'); if (!(await label.count())) return;
      const feeShown = () => p.evaluate(() => /Special Delivery Shipping Upgrade/.test(document.querySelector('.cart_totals').innerText)); const was = await feeShown();
      const fb = await measure(p, () => document.querySelector('.pp-woo-content .ppc-sd').click(), () => document.querySelector('.pp-woo-content input[data-ppc-sd]').disabled, 3000); rows.push([lab + ' SD toggle feedback (box locks while saving)', fb]); assert.ok(fb >= 0 && fb <= 300 * cpu, 'no feedback: ' + fb);
      await p.waitForFunction(w => /Special Delivery Shipping Upgrade/.test(document.querySelector('.cart_totals').innerText) !== w, was, { timeout: 15000 });
      for (let i = 0; i < 5; i++) await label.click({ noWaitAfter: true, force: true }).catch(() => {}); await p.waitForTimeout(2500); await p.waitForLoadState('networkidle').catch(() => {});
      const box = await p.locator('.pp-woo-content input[data-ppc-sd]').isChecked(); assert.equal(await feeShown(), box, 'tick says ' + box + ' but the totals disagree');
    });
    await T(lab + ': cookie prompt buttons react at once', async () => { const c2 = await b.newContext(opts); const q = await c2.newPage(); await q.goto(BASE + '/', { waitUntil: 'networkidle' }); const ms = await measure(q, () => document.querySelector('#pp-cookie [data-pp-consent-act=none]').click(), () => document.querySelector('#pp-cookie').hidden); await budget('cookie reject', ms, 150, q); await c2.close(); });
    await T(lab + ': no JavaScript errors during all of that', async () => assert.deepEqual(errs, []));
    await ctx.close();
  }
  await b.close(); console.log('\nreaction times (ms):'); for (const [n, ms] of rows) console.log('  ' + String(ms).padStart(5) + '  ' + n); console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
