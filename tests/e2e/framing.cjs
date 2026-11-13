/* Product framing: every photo shape must fill the square plate with object-fit: contain (shown whole, centred), never cropped or top-aligned.
 * Needs scripts/framing-fixture.php to have been run. Usage: BASE=http://localhost:8088 node tests/e2e/framing.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8088'; let fail = 0;
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
(async () => {
  const b = await chromium.launch();
  for (const [lab, opts] of [['desktop', { viewport: { width: 1440, height: 1000 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    const c = await b.newContext(opts); await c.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]); const p = await c.newPage();
    await p.goto(BASE + '/product-category/framing-test/', { waitUntil: 'networkidle' });
    await T(lab + ': six framing products listed', async () => assert.equal(await p.locator('ul.products li.pp-card').count(), 6));
    await T(lab + ': every photo shape fills the plate and is fitted with contain (centred, whole)', async () => {
      const r = await p.evaluate(() => [...document.querySelectorAll('ul.products li.pp-card')].map(li => { const pl = li.querySelector('.pp-card__plate').getBoundingClientRect(), im = li.querySelector('.pp-card__plate img'), cs = getComputedStyle(im), r = im.getBoundingClientRect(); return { name: li.querySelector('.pp-card__title').textContent.trim(), fit: cs.objectFit, pos: cs.objectPosition, sq: Math.abs(pl.width - pl.height) < 1.5, dh: Math.abs(r.height - pl.height), dw: Math.abs(r.width - pl.width) }; }));
      for (const x of r) { assert.equal(x.fit, 'contain', x.name); assert.ok(x.sq, x.name + ' plate not square'); assert.ok(x.dh < 4.5 && x.dw < 4.5, x.name + ' image does not fill the plate (height ' + x.dh + ')'); assert.equal(x.pos, '50% 50%', x.name); }
    });
    await T(lab + ': the product page shows a tall photo whole inside the gallery', async () => {
      await p.goto(BASE + '/product/frame-tall/', { waitUntil: 'networkidle' }); const x = await p.evaluate(() => { const im = document.querySelector('.woocommerce-product-gallery__image img'), cs = getComputedStyle(im), r = im.getBoundingClientRect(), pr = im.parentElement.getBoundingClientRect(); return { fit: cs.objectFit, inside: r.top >= pr.top - 1 && r.bottom <= pr.bottom + 1 && r.left >= pr.left - 1 && r.right <= pr.right + 1 }; });
      assert.equal(x.fit, 'contain'); assert.ok(x.inside);
    });
    await c.close();
  }
  await b.close(); console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
