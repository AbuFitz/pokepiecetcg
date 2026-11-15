/* Detail sweep: every page x five widths. Fails on horizontal overflow, JS errors, failed requests, missing/duplicate h1, broken or alt-less images,
 * and unlabelled buttons/links/inputs. LOCAL FIXTURE. Usage: BASE=http://localhost:8088 node tests/e2e/sweep.cjs */
const { chromium } = require('playwright'); const BASE = process.env.BASE || 'http://localhost:8088'; let bad = 0, checks = 0;
const WIDTHS = [360, 390, 768, 900, 960, 1024, 1180, 1440];
(async () => {
  const b = await chromium.launch(); const c0 = await b.newContext(); const j = await (await c0.request.get(BASE + '/wp-json/wc/store/v1/products?per_page=3')).json();
  const cats = await (await c0.request.get(BASE + '/wp-json/wc/store/v1/products/categories?per_page=20&hide_empty=true')).json();
  const urls = ['/', '/shop/', ...cats.map(x => new URL(x.permalink).pathname), ...j.map(x => new URL(x.permalink).pathname), '/cart/', '/checkout/', '/my-account/', '/my-account/lost-password/', '/about/', '/contact/', '/shipping-policy/', '/refund-return-policy/', '/terms-conditions/', '/privacy-policy/', '/shop/?s=pokemon&post_type=product', '/this-page-does-not-exist/'];
  const uniq = [...new Set(urls)]; await c0.close();
  for (const w of WIDTHS) {
    const ctx = await b.newContext({ viewport: { width: w, height: 900 } }); await ctx.addCookies([{ name: 'pp_consent', value: encodeURIComponent(JSON.stringify({ v: 1, p: 0, a: 0, m: 0, t: 1 })), url: BASE }]);
    for (const u of uniq) {
      const p = await ctx.newPage(); const probs = []; const is404 = u.includes('does-not-exist');
      p.on('pageerror', e => probs.push('js error: ' + e.message)); p.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/.test(m.text())) probs.push('console: ' + m.text().slice(0, 100)); });
      p.on('requestfailed', r => { if (!/favicon/.test(r.url())) probs.push('request failed: ' + r.url().slice(-80)); });
      p.on('response', r => { if (r.status() >= 400 && r.request().resourceType() !== 'document' && !/favicon/.test(r.url())) probs.push(r.status() + ' ' + r.url().slice(-80)); });
      await p.goto(BASE + u, { waitUntil: 'networkidle' }); await p.evaluate(() => window.scrollTo(0, document.body.scrollHeight)); await p.waitForTimeout(300); await p.evaluate(() => window.scrollTo(0, 0));
      const r = await p.evaluate(() => {
        const out = []; const de = document.documentElement; if (de.scrollWidth > de.clientWidth + 1) { const wide = [...document.querySelectorAll('body *')].filter(e => e.getBoundingClientRect().right > de.clientWidth + 1 && getComputedStyle(e).position !== 'fixed' && !e.closest('[aria-hidden=true],.pp-ticker,[data-pp-drag],.pp-menu,.pp-drawer,.pp-modal,.pp-filter-panel,.pp-suggest')).slice(0, 3).map(e => e.tagName + '.' + String(e.className).slice(0, 30)); if (wide.length) out.push('horizontal overflow: ' + wide.join(', ')); }
        const h1 = document.querySelectorAll('h1').length; if (h1 !== 1) out.push('h1 count ' + h1);
        document.querySelectorAll('img').forEach(i => { if (!i.hasAttribute('alt')) out.push('img without alt: ' + (i.currentSrc || i.src).slice(-50)); else if (i.complete && i.naturalWidth === 0 && i.currentSrc) out.push('broken img: ' + i.currentSrc.slice(-50)); });
        document.querySelectorAll('button,a[href],input:not([type=hidden]),select,textarea').forEach(e => { if (e.closest('[hidden],[aria-hidden=true]') || getComputedStyle(e).display === 'none') return; const name = (e.getAttribute('aria-label') || e.textContent || e.value || e.getAttribute('title') || '').trim() || (e.id && document.querySelector('label[for="' + e.id + '"]')) || (e.closest('label')) || e.getAttribute('aria-labelledby') || e.getAttribute('placeholder'); if (!name && !e.querySelector('img[alt]:not([alt=""]),svg title')) out.push('no accessible name: ' + e.tagName + ' ' + String(e.className).slice(0, 30)); });
        if (!document.querySelector('meta[name=viewport]')) out.push('no viewport meta'); if (!document.title.trim()) out.push('empty <title>'); if (document.documentElement.lang === '') out.push('no html lang');
        return out; });
      const all = [...new Set([...probs, ...r])]; checks++; if (all.length) { bad++; console.log('FAIL ' + w + 'px ' + u + '\n   ' + all.slice(0, 6).join('\n   ')); }
      await p.close();
    }
    await ctx.close();
  }
  await b.close(); console.log(bad ? `${bad}/${checks} page checks have findings` : `ALL ${checks} page checks clean`); process.exit(bad ? 1 : 0);
})();
