/* Cookie prompt, preferences and promo popup journeys against the LOCAL FIXTURE site (promo switched on, delay 3s).
 * Usage: BASE=http://localhost:8088 node tests/e2e/consent.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8088'; let fail = 0;
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
const cookie = async c => { const k = (await c.cookies()).find(x => x.name === 'pp_consent'); return k ? JSON.parse(decodeURIComponent(k.value)) : null; };
const gated = async p => p.route('**/*', async r => { if (r.request().resourceType() !== 'document') return r.continue(); const resp = await r.fetch(); let b = await resp.text(); b = b.replace('</body>', '<script type="text/plain" data-pp-consent="analytics">window.__a=1</script></body>'); r.fulfill({ response: resp, body: b }); });
(async () => {
  const b = await chromium.launch();
  for (const [lab, opts] of [['desktop', { viewport: { width: 1440, height: 900 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    let ctx = await b.newContext(opts), p = await ctx.newPage(); const errs = []; p.on('pageerror', e => errs.push(e.message));
    await gated(p);
    await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await T(lab + ': first visit shows the cookie prompt with equal Accept / Reject, no cookie yet, no promo yet', async () => {
      assert.ok(await p.locator('#pp-cookie').isVisible());
      const a = await p.locator('#pp-cookie [data-pp-consent-act=all]').boundingBox(), r = await p.locator('#pp-cookie [data-pp-consent-act=none]').boundingBox();
      assert.ok(Math.abs(a.height - r.height) < 2, 'buttons differ in size'); assert.equal(await cookie(ctx), null); assert.equal(await p.locator('#pp-promo').isVisible(), false);
      assert.equal(await p.evaluate(() => window.__a), undefined);
    });
    await T(lab + ': promo waits for a cookie decision even after the delay', async () => { await p.waitForTimeout(4500); assert.equal(await p.locator('#pp-promo').isVisible(), false); });
    await T(lab + ': reject non-essential stores 0/0/0, hides the prompt, keeps gated scripts off', async () => {
      await p.click('#pp-cookie [data-pp-consent-act=none]'); const c = await cookie(ctx); assert.deepEqual([c.p, c.a, c.m], [0, 0, 0]);
      assert.equal(await p.locator('#pp-cookie').isVisible(), false); assert.equal(await p.evaluate(() => window.__a), undefined);
    });
    await T(lab + ': promo appears after the delay, can be closed, and is not shown again on reload', async () => {
      await p.waitForSelector('#pp-promo.is-open', { timeout: 9000 }); assert.match(await p.locator('#pp-promo').innerText(), /Welcome to PokePieceTCG/i);
      await p.click('#pp-promo .pp-close'); await p.waitForSelector('#pp-promo', { state: 'hidden' });
      await p.reload({ waitUntil: 'networkidle' }); await p.waitForTimeout(4500); assert.equal(await p.locator('#pp-promo').isVisible(), false); assert.equal(await p.locator('#pp-cookie').isVisible(), false);
    });
    await T(lab + ': no promo on cart, checkout or account', async () => { for (const u of ['/cart/', '/checkout/', '/my-account/']) { await p.goto(BASE + u, { waitUntil: 'networkidle' }); await p.waitForTimeout(4200); assert.equal(await p.locator('#pp-promo').count(), 0, u); } });
    await T(lab + ': footer link reopens preferences; saving analytics only sets 0/1/0 and activates the gated script live', async () => {
      await p.goto(BASE + '/about/', { waitUntil: 'networkidle' }); await p.locator('footer [data-pp-cookie-open]').click(); await p.waitForSelector('#pp-cookie-pref.is-open');
      assert.equal(await p.locator('#pp-cookie-pref input[name=a]').isChecked(), false); assert.equal(await p.locator('#pp-cookie-pref input[name=necessary]').isDisabled(), true);
      await p.locator('#pp-cookie-pref label:has(input[name=a])').click(); await p.click('#pp-cookie-pref button[type=submit]');
      const c = await cookie(ctx); assert.deepEqual([c.p, c.a, c.m], [0, 1, 0]); assert.equal(await p.evaluate(() => window.__a), 1);
      await p.waitForSelector('#pp-cookie-pref', { state: 'hidden' });
    });
    await T(lab + ': escape closes preferences without changing anything', async () => { await p.locator('footer [data-pp-cookie-open]').click(); await p.waitForSelector('#pp-cookie-pref.is-open'); await p.keyboard.press('Escape'); await p.waitForSelector('#pp-cookie-pref', { state: 'hidden' }); const c = await cookie(ctx); assert.deepEqual([c.p, c.a, c.m], [0, 1, 0]); });
    await T(lab + ': no console errors', async () => assert.deepEqual(errs, [])); await ctx.close();
    // fresh visitor: accept all, preference memory in localStorage, gated script runs at once
    ctx = await b.newContext(opts); p = await ctx.newPage(); await gated(p); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await T(lab + ': accept all stores 1/1/1 and runs gated scripts', async () => { await p.click('#pp-cookie [data-pp-consent-act=all]'); const c = await cookie(ctx); assert.deepEqual([c.p, c.a, c.m], [1, 1, 1]); assert.equal(await p.evaluate(() => window.__a), 1); });
    await T(lab + ': with preferences accepted a closed promo is remembered across a new tab', async () => {
      await p.waitForSelector('#pp-promo.is-open', { timeout: 9000 }); await p.click('#pp-promo [data-pp-promo-no]');
      const p2 = await ctx.newPage(); await p2.goto(BASE + '/', { waitUntil: 'networkidle' }); await p2.waitForTimeout(4500); assert.equal(await p2.locator('#pp-promo').isVisible(), false); await p2.close();
    });
    await ctx.close();
    // product page: prompt never hides the buy bar controls
    ctx = await b.newContext(opts); p = await ctx.newPage(); await p.goto(BASE + '/shop/?pp_stock=1', { waitUntil: 'networkidle' });
    await T(lab + ': on a product page the cookie prompt and the sticky buy bar do not overlap', async () => {
      const href = await p.evaluate(() => document.querySelector('li.pp-card[data-stock=in] .pp-card__title a').href); await p.goto(href, { waitUntil: 'networkidle' });
      await p.evaluate(() => window.scrollTo(0, 900)); await p.waitForTimeout(600);
      const sb = p.locator('.pp-sticky.is-shown'); if (await sb.count()) { const a = await sb.boundingBox(), c = await p.locator('#pp-cookie').boundingBox(); assert.ok(a.y + a.height <= c.y + 1 || c.y + c.height <= a.y + 1, 'overlap'); }
    });
    await ctx.close();
  }
  await b.close(); console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
