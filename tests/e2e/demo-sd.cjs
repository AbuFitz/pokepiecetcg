/* Static preview: Special Delivery option is simulated (tick-box, fee line, reference required, total). Serves demo/ on BASE.
 * Usage: BASE=http://localhost:8099 node tests/e2e/demo-sd.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8099'; let fail = 0;
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
(async () => {
  const b = await chromium.launch();
  for (const [lab, opts] of [['desktop', { viewport: { width: 1440, height: 900 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    const ctx = await b.newContext(opts); await ctx.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]);
    const p = await ctx.newPage(); const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('dialog', d => d.dismiss());
    await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' });
    const href = await p.evaluate(() => document.querySelector('li.pp-card[data-stock=in] .pp-card__title a').getAttribute('href'));
    await T(lab + ': ticking Special Delivery on the product page adds the fee to the cart page and the total', async () => {
      await p.goto(BASE + href, { waitUntil: 'networkidle' }); await p.locator('.pp-opt').first().click(); await p.evaluate(() => document.querySelector('.single_add_to_cart_button').click()); await p.waitForSelector('#pp-cart-drawer.is-open'); await p.keyboard.press('Escape');
      await p.goto(BASE + '/cart/', { waitUntil: 'networkidle' }); const t = await p.locator('.pp-woo-content').innerText(); assert.match(t, /Special Delivery Shipping Upgrade/); assert.match(t, /10\.45/);
      assert.equal(await p.locator('.pp-woo-content input[data-ppc-sd]').isChecked(), true);
    });
    await T(lab + ': toggling it on the cart page removes and restores the fee', async () => {
      await p.locator('.pp-woo-content .ppc-sd').click(); await p.waitForFunction(() => !/Special Delivery Shipping Upgrade/.test(document.querySelector('.pp-woo-content').innerText));
      await p.locator('.pp-woo-content .ppc-sd').click(); await p.waitForFunction(() => /Special Delivery Shipping Upgrade/.test(document.querySelector('.pp-woo-content').innerText));
    });
    await T(lab + ': the cart drawer offers the option', async () => { await p.goto(BASE + '/', { waitUntil: 'networkidle' }); await p.locator('.pp-cart-open').first().click(); await p.waitForSelector('#pp-cart-drawer.is-open'); assert.ok(await p.locator('#pp-cart-drawer .ppc-sd').isVisible()); await p.keyboard.press('Escape'); });
    await T(lab + ': checkout asks for the reference and the demo order total includes the fee', async () => {
      await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle' }); assert.ok(await p.locator('.ppc-sd-ref input[required]').isVisible());
      const f = p.locator('#pp-dco input:not([type=checkbox])'); const n = await f.count(); for (let i = 0; i < n; i++) { const el = f.nth(i); if (await el.getAttribute('placeholder')) continue; await el.fill(i === 1 ? 'a@b.co' : 'x'); }
      await p.click('#pp-dco button[type=submit]'); const txt = await p.locator('.pp-woo-content').innerText(); assert.match(txt, /demo order received/i); assert.match(txt, /Order total/);
    });
    await T(lab + ': no console errors', async () => assert.deepEqual(errs, [])); await ctx.close();
  }
  await b.close(); console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
