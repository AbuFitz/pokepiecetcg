/* Accessibility audit with axe-core (WCAG 2.1 A/AA + best practice) on every page AND on the interactive states (menus, popups, drawer, quick view, cookie prompt).
 * axe-core is not a repo dependency: AXE=/path/to/axe.min.js (npm i axe-core somewhere). LOCAL FIXTURE. Usage: AXE=... BASE=http://localhost:8088 node tests/e2e/a11y.cjs */
const { chromium, devices } = require('playwright'); const fs = require('fs'); const BASE = process.env.BASE || 'http://localhost:8088';
const axeSrc = fs.readFileSync(process.env.AXE || require.resolve('axe-core/axe.min.js'), 'utf8'); let bad = 0, n = 0; const seen = new Set();
const run = async (p, label) => {
  await p.evaluate(axeSrc); const r = await p.evaluate(async () => (await axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'best-practice'] }, resultTypes: ['violations'] })).violations.map(v => ({ id: v.id, impact: v.impact, help: v.help, nodes: v.nodes.slice(0, 3).map(x => x.target.join(' ').slice(0, 90) + ' :: ' + (x.failureSummary || '').split('\n')[1]?.trim().slice(0, 110)) })));
  n++; const fresh = r.filter(v => { const k = v.id + '|' + v.nodes[0]; if (seen.has(k)) return false; seen.add(k); return true; });
  if (fresh.length) { bad++; console.log('FINDINGS ' + label); for (const v of fresh) console.log('   [' + v.impact + '] ' + v.id + ': ' + v.help + '\n        ' + v.nodes.join('\n        ')); }
};
(async () => {
  const b = await chromium.launch(); const j = await (await (await b.newContext()).request.get(BASE + '/wp-json/wc/store/v1/products?per_page=5')).json(); const pdp = new URL(j.find(x => x.is_in_stock && x.id !== 686).permalink).pathname;
  const pages = ['/', '/shop/', '/product-category/one-piece-japanese/', pdp, '/cart/', '/checkout/', '/my-account/', '/my-account/lost-password/', '/about/', '/contact/', '/shipping-policy/', '/refund-return-policy/', '/terms-conditions/', '/shop/?s=pokemon&post_type=product', '/zz-missing/'];
  for (const [lab, o] of [['desktop', { viewport: { width: 1440, height: 900 } }], ['mobile', { ...devices['iPhone 13'] }]]) {
    const c = await b.newContext(o); const p = await c.newPage();
    for (const u of pages) { await p.goto(BASE + u, { waitUntil: 'networkidle' }); await p.waitForTimeout(250); await run(p, lab + ' ' + u + ' (first visit: cookie prompt showing)'); }
    await c.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]);
    await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    const st = async (name, f) => { try { await f(); await p.waitForTimeout(450); await run(p, lab + ' state: ' + name); } catch (e) { console.log('COULD NOT OPEN ' + lab + ' ' + name + ': ' + String(e.message).split('\n')[0]); } finally { await p.keyboard.press('Escape'); await p.waitForTimeout(250); } };
    await st('search popup', async () => { await p.locator('.pp-searchbtn').click(); await p.waitForSelector('#pp-search-pop.is-open'); });
    await st('cart drawer (empty)', async () => { await p.locator('.pp-cart-open').first().click(); await p.waitForSelector('#pp-cart-drawer.is-open'); });
    if (lab === 'desktop') await st('account popup', async () => { await p.locator('.pp-act-account').click(); await p.waitForSelector('.pp-modal--account.is-open, #pp-acc.is-open'); });
    if (lab === 'mobile') await st('mobile menu', async () => { await p.locator('.pp-menu-open, [aria-controls=pp-menu]').first().click(); await p.waitForSelector('#pp-menu.is-open'); });
    await p.goto(BASE + '/shop/', { waitUntil: 'networkidle' });
    if (lab === 'desktop') await st('quick view', async () => { await p.locator('.pp-qv-open').first().click({ force: true }); await p.waitForSelector('#pp-qv.is-open .pp-qvc__img'); });
    await st('filter panel / sort', async () => { const f = p.locator('.pp-filter-open'); if (await f.count() && await f.first().isVisible()) { await f.first().click(); await p.waitForSelector('.pp-filter-panel.is-open'); } });
    await p.goto(BASE + pdp, { waitUntil: 'networkidle' }); await p.locator('.single_add_to_cart_button').click(); await p.waitForSelector('.woocommerce-message'); await p.goto(BASE + '/cart/', { waitUntil: 'networkidle' }); await run(p, lab + ' /cart/ with an item'); await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle' }); await run(p, lab + ' /checkout/ with an item');
    await p.goto(BASE + '/', { waitUntil: 'networkidle' }); await st('cart drawer (with item)', async () => { await p.locator('.pp-cart-open').first().click(); await p.waitForSelector('#pp-cart-drawer.is-open'); });
    await p.context().clearCookies(); await p.goto(BASE + '/', { waitUntil: 'networkidle' });
    await st('cookie preferences', async () => { await p.locator('#pp-cookie [data-pp-cookie-open]').click(); await p.waitForSelector('#pp-cookie-pref.is-open'); });
    await c.close();
  }
  await b.close(); console.log(bad ? `${bad}/${n} views have findings` : `ALL ${n} views clean`); process.exit(bad ? 1 : 0);
})();
