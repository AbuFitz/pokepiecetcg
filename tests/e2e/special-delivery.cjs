/* Special Delivery option journeys against the LOCAL FIXTURE site (PokePiece Core enabled, price 10.45, flat-rate Standard 3.95).
 * Proves fee/cart/checkout/order behaviour on WooCommerce's real code. NOT payment gateways or the client's data.
 * Usage: BASE=http://localhost:8088 node tests/e2e/special-delivery.cjs */
const { chromium, devices } = require('playwright'); const assert = require('node:assert/strict');
const BASE = process.env.BASE || 'http://localhost:8088'; let fail = 0;
const T = async (n, f) => { try { await f(); console.log('PASS', n); } catch (e) { fail++; console.log('FAIL', n, '\n   ', String(e.message).split('\n')[0]); } };
const money = s => parseFloat(String(s).replace(/[^0-9.]/g, ''));
(async () => {
  const b = await chromium.launch();
  for (const [lab, opts] of [['desktop', { viewport: { width: 1440, height: 900 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    const ctx = await b.newContext(opts); await ctx.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]); const p = await ctx.newPage(); const errs = []; p.on('pageerror', e => errs.push(e.message));
    p.on('dialog', d => d.dismiss());
    await p.goto(BASE + '/shop/?pp_stock=1', { waitUntil: 'networkidle' });
    const href = await p.evaluate(() => document.querySelector('li.pp-card[data-stock=in] .pp-card__title a').getAttribute('href'));
    await T(lab + ': no upgrade product in the shop listing, and the old product is not offered', async () => { const html = await p.content(); assert.ok(!/special delivery shipping upgrade/i.test(await p.locator('ul.products').innerText())); });
    await T(lab + ': product page has the tick-box with the configured price', async () => { await p.goto(href, { waitUntil: 'networkidle' }); const t = await p.locator('.pp-opt').first().innerText(); assert.match(t, /Special Delivery/); assert.match(t, /10\.45/); });
    await T(lab + ': ticking it while adding puts the fee in the cart, nothing extra in the lines', async () => {
      await p.locator('.pp-opt').first().click(); await p.evaluate(() => document.querySelector('.single_add_to_cart_button').click()); await p.waitForSelector('.woocommerce-message');
      await p.goto(BASE + '/cart/', { waitUntil: 'networkidle' }); assert.equal(await p.locator('.woocommerce-cart-form .cart_item').count(), 1);
      assert.match(await p.locator('.cart_totals').innerText(), /Special Delivery Shipping Upgrade[\s\S]*10\.45/);
      assert.equal(await p.locator('input[data-ppc-sd]').first().isChecked(), true);
    });
    await T(lab + ': untick on the cart page removes the fee; tick adds it back', async () => {
      await p.locator('.pp-woo-content .ppc-sd').click(); await p.waitForFunction(() => !/Special Delivery Shipping Upgrade/.test(document.querySelector('.cart_totals').innerText), null, { timeout: 15000 });
      await p.locator('.pp-woo-content .ppc-sd').click(); await p.waitForFunction(() => /Special Delivery Shipping Upgrade/.test(document.querySelector('.cart_totals').innerText), null, { timeout: 15000 });
    });
    await T(lab + ': checkout requires the reference when it is on, and the total includes the fee', async () => {
      await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle' });
      assert.ok(await p.locator('#ppc_sd_ref_field').isVisible()); assert.match(await p.locator('.woocommerce-checkout-review-order-table').innerText(), /10\.45/);
      const fill = { billing_first_name: 'Test', billing_last_name: 'Buyer', billing_address_1: '1 Test Street', billing_city: 'Leeds', billing_postcode: 'LS1 1AA', billing_phone: '07000000000', billing_email: 'sd-test@example.invalid' };
      for (const [k, v] of Object.entries(fill)) { const el = p.locator('#' + k); if (await el.count()) await el.fill(v); }
      await p.locator('#payment_method_cod').check({ force: true }).catch(() => {});
      const terms = p.locator('#terms'); if (await terms.count()) await terms.check({ force: true });
      await p.click('#place_order'); await p.waitForSelector('.woocommerce-error', { timeout: 20000 });
      assert.match(await p.locator('.woocommerce-error').innerText(), /required for Special Delivery/);
    });
    await T(lab + ': with a reference the order is placed with the fee line and meta', async () => {
      await p.fill('#ppc_sd_ref', '@test_buyer'); await p.click('#place_order'); await p.waitForURL(/order-received/, { timeout: 30000 });
      const txt = await p.locator('.woocommerce-order').innerText(); assert.match(txt, /Special Delivery Shipping Upgrade/); assert.match(txt, /10\.45/);
      const id = (p.url().match(/order-received\/(\d+)/) || [])[1]; assert.ok(id); require('fs').writeFileSync(process.env.SD_IDS || '/tmp/sd-order-ids.txt', id + '\n', { flag: 'a' });
    });
    await T(lab + ': switching it off at checkout drops the fee and hides the reference box', async () => {
      await p.goto(href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.querySelector('.single_add_to_cart_button').click()); await p.waitForSelector('.woocommerce-message');
      await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle' }); assert.equal(await p.locator('#ppc_sd_ref_field').isVisible(), false);
      await p.locator('.ppc-sd-block .ppc-sd').click(); await p.waitForFunction(() => /10\.45/.test(document.querySelector('.woocommerce-checkout-review-order-table').innerText), null, { timeout: 15000 });
      assert.equal(await p.locator('#ppc_sd_ref_field').isVisible(), true);
      await p.locator('.ppc-sd-block .ppc-sd').click(); await p.waitForFunction(() => !/10\.45/.test(document.querySelector('.woocommerce-checkout-review-order-table').innerText), null, { timeout: 15000 });
    });
    await T(lab + ': the cart drawer offers the option and ticking it there adds the fee', async () => {
      await p.goto(BASE + '/', { waitUntil: 'networkidle' }); await p.locator('.pp-cart-open').first().click(); await p.waitForSelector('#pp-cart-drawer.is-open');
      const box = p.locator('#pp-cart-drawer .ppc-sd'); await box.waitFor({ state: 'visible' }); assert.equal(await box.locator('input').isChecked(), false);
      await box.click(); await p.waitForFunction(() => document.querySelector('#pp-cart-drawer .ppc-sd input').checked, null, { timeout: 15000 });
      await p.goto(BASE + '/cart/', { waitUntil: 'networkidle' }); assert.match(await p.locator('.cart_totals').innerText(), /Special Delivery Shipping Upgrade[\s\S]*10\.45/);
    });
    await T(lab + ': no console errors', async () => assert.deepEqual(errs, [])); await ctx.close();
  }
  await b.close(); console.log(fail ? 'FAILED: ' + fail : 'ALL PASSED'); process.exit(fail ? 1 : 0);
})();
