/* Logo watermark: drawn over every product photo (cards, search tiles, quick view, product page), click-through, and no route to the unmarked original.
 * Optional: WPCLI="php /path/wp-cli.phar --allow-root" + WPDIR to also prove a brand-new listing gets it automatically.
 * Usage: BASE=http://localhost:8088 node tests/e2e/watermark.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict'); const cp = require('node:child_process');
const BASE = process.env.BASE || 'http://localhost:8088'; let fail = 0;
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
const overlay = (p, sel) => p.evaluate(s => { const e = document.querySelector(s); if (!e) return null; const c = getComputedStyle(e, '::after'); return { bg: c.backgroundImage, op: parseFloat(c.opacity), pe: c.pointerEvents, pos: c.position, w: parseFloat(c.width), h: parseFloat(c.height), pw: e.getBoundingClientRect().width }; }, sel);
(async () => {
  let created = false; if (process.env.WPCLI) { cp.execSync(process.env.WPCLI + ' eval-file ' + __dirname + '/../../scripts/framing-fixture.php', { cwd: process.env.WPDIR, stdio: 'ignore' }); created = true; }
  const b = await chromium.launch();
  for (const [lab, opts] of [['desktop', { viewport: { width: 1440, height: 900 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    const c = await b.newContext(opts); await c.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]); const p = await c.newPage();
    await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' });
    await T(lab + ': shop cards have a transparent logo layer over the whole photo, and it does not block clicks', async () => {
      const o = await overlay(p, '.pp-card__plate'); assert.match(o.bg, /watermark-tile/); assert.ok(o.op > 0.05 && o.op < 0.6, 'opacity ' + o.op); assert.equal(o.pe, 'none'); assert.equal(o.pos, 'absolute'); assert.ok(Math.abs(o.w - (o.pw - 2)) < 6, 'covers the plate ' + o.w + ' vs ' + o.pw);
      const href = await p.evaluate(() => document.querySelector('li.pp-card .pp-card__title a').href); const el = p.locator('.pp-card__plate').first(); await el.click({ position: { x: 60, y: 120 } }); await p.waitForURL(u => u.href !== BASE + '/shop/', { timeout: 10000 }); assert.equal(p.url(), href); await p.goBack({ waitUntil: 'networkidle' });
    });
    await T(lab + ': search results and quick view carry it too', async () => {
      if (lab === 'desktop') { await p.locator('.pp-qv-open').first().click({ force: true }); await p.waitForSelector('#pp-qv.is-open'); await p.waitForSelector('.pp-qvc__img'); const o = await overlay(p, '.pp-qvc__img'); assert.match(o.bg, /watermark-tile/); await p.keyboard.press('Escape'); }
      await p.locator('.pp-searchbtn').click(); await p.waitForSelector('#pp-search-pop.is-open'); const s = await overlay(p, '#pp-search-pop .pp-sp__img'); assert.match(s.bg, /watermark-tile/); await p.keyboard.press('Escape');
    });
    await T(lab + ': product page photo is marked, has no link to the unmarked original, and no zoom popup', async () => {
      const href = await p.evaluate(() => document.querySelector('li.pp-card .pp-card__title a').href); await p.goto(href, { waitUntil: 'networkidle' });
      const o = await overlay(p, '.woocommerce-product-gallery__image'); assert.match(o.bg, /watermark-tile/);
      assert.equal(await p.locator('.woocommerce-product-gallery__image a').count(), 0, 'photo still links to the original'); assert.equal(await p.locator('.pswp').count(), 0, 'zoom popup present');
    });
    if (created) await T(lab + ': a brand-new listing is marked automatically', async () => { await p.goto(BASE + '/product-category/framing-test/', { waitUntil: 'networkidle' }); assert.ok(await p.locator('.pp-card__plate').count() >= 6); for (const o of await p.evaluate(() => [...document.querySelectorAll('.pp-card__plate')].map(e => getComputedStyle(e, '::after').backgroundImage))) assert.match(o, /watermark-tile/); });
    await c.close();
  }
  await b.close(); if (created) cp.execSync(process.env.WPCLI + ' eval-file ' + __dirname + '/../../scripts/framing-fixture.php cleanup', { cwd: process.env.WPDIR, stdio: 'ignore' });
  console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
