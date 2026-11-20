/* Detail sweep: every page x five widths. Fails on horizontal overflow, JS errors, failed requests, missing/duplicate h1, broken or alt-less images,
 * and unlabelled buttons/links/inputs. LOCAL FIXTURE. Usage: BASE=http://localhost:8088 node tests/e2e/sweep.cjs */
const { chromium } = require('playwright'); const BASE = process.env.BASE || 'http://localhost:8088'; let bad = 0, checks = 0;
const WIDTHS = process.env.SWEEP_WIDTHS ? process.env.SWEEP_WIDTHS.split(',').map(Number) : [360, 390, 768, 900, 960, 1024, 1180, 1440];
(async () => {
  const b = await chromium.launch(); const c0 = await b.newContext(); const j = await (await c0.request.get(BASE + '/wp-json/wc/store/v1/products?per_page=3')).json();
  const cats = await (await c0.request.get(BASE + '/wp-json/wc/store/v1/products/categories?per_page=20&hide_empty=true')).json();
  const urls = ['/', '/shop/', '/product/frame-solo-pokemon-mega-evolution-ascended-heroes-poster-collection-poster-10-booster-packs/', ...cats.map(x => new URL(x.permalink).pathname), ...j.map(x => new URL(x.permalink).pathname), '/cart/', '/checkout/', '/my-account/', '/my-account/lost-password/', '/about/', '/contact/', '/shipping-policy/', '/refund-return-policy/', '/terms-conditions/', '/privacy-policy/', '/shop/?s=pokemon&post_type=product', '/this-page-does-not-exist/'];
  const uniq = [...new Set(urls)].filter(u => !process.env.SWEEP_ONLY || new RegExp(process.env.SWEEP_ONLY).test(u)); await c0.close();
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
      // Overlap check: scroll through the page and look for text drawn over other text, and buttons/links covered by something that is not an intended overlay.
      const ov = await p.evaluate(async () => {
        const out = new Set(); const label = e => e.tagName.toLowerCase() + (e.id ? '#' + e.id : '') + '.' + String(e.className).trim().split(/\s+/).slice(0, 2).join('.') + ' "' + (e.textContent || '').trim().slice(0, 28) + '"';
        const fixed = e => { for (let n = e; n && n.nodeType === 1; n = n.parentElement) { const c = getComputedStyle(n).position; if (c === 'fixed' || c === 'sticky') return true; } return false; };
        const hid = e => { for (let n = e; n && n.nodeType === 1; n = n.parentElement) { const c = getComputedStyle(n); if (c.display === 'none' || c.visibility === 'hidden' || +c.opacity < 0.05 || n.hasAttribute('hidden') || n.getAttribute('aria-hidden') === 'true' || (n.tagName === 'DETAILS' && !n.open && !e.closest('summary')) || (n.getBoundingClientRect().width <= 2 && n.getBoundingClientRect().height <= 2 && c.overflow === 'hidden') || n.classList.contains('screen-reader-text') || n.matches('[data-pp-drag],.pp-ticker,.pp-reel,.pp-clips,.pp-banner,svg')) return true; } return false; };
        const H = innerHeight; const total = document.documentElement.scrollHeight;
        for (let y = 0; y < total; y += Math.round(H * 0.7)) {
          scrollTo(0, y); await new Promise(r => setTimeout(r, 140));
          const boxes = [];
          const tw = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
          for (let n; (n = tw.nextNode());) { if (!n.textContent.trim()) continue; const el = n.parentElement; if (!el || hid(el) || fixed(el) || /^(SCRIPT|STYLE|NOSCRIPT|OPTION)$/.test(el.tagName)) continue; const rg = document.createRange(); rg.selectNodeContents(n); for (const r of rg.getClientRects()) { if (r.width < 4 || r.height < 4 || r.bottom < 0 || r.top > H) continue; boxes.push({ el, r }); } }
          for (let i = 0; i < boxes.length; i++) for (let j = i + 1; j < boxes.length; j++) { const a = boxes[i], b = boxes[j]; if (a.el === b.el || a.el.contains(b.el) || b.el.contains(a.el)) continue; const w = Math.min(a.r.right, b.r.right) - Math.max(a.r.left, b.r.left), h = Math.min(a.r.bottom, b.r.bottom) - Math.max(a.r.top, b.r.top); if (w > 3 && h > 3 && h > 0.4 * Math.min(a.r.height, b.r.height) && w * h > 0.3 * Math.min(a.r.width * a.r.height, b.r.width * b.r.height)) out.add('text overlaps text: ' + label(a.el) + ' <> ' + label(b.el)); }
          for (const el of document.querySelectorAll('a[href],button,input:not([type=hidden]),select,textarea')) { if (hid(el) || fixed(el)) continue; const r = el.getBoundingClientRect(); if (r.width < 6 || r.height < 6 || r.top < 0 || r.bottom > H || r.left < 0 || r.right > innerWidth) continue; const t = document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2); if (t && t !== el && !el.contains(t) && !t.contains(el) && !fixed(t) && !t.closest('label') && !(el.closest('label') && el.closest('label').contains(t))) out.add('covered: ' + label(el) + ' by ' + label(t)); }
        }
        scrollTo(0, 0); return [...out];
      });
      const all = [...new Set([...probs, ...r, ...ov])]; checks++; if (all.length) { bad++; console.log('FAIL ' + w + 'px ' + u + '\n   ' + all.slice(0, 6).join('\n   ')); }
      await p.close();
    }
    await ctx.close();
  }
  await b.close(); console.log(bad ? `${bad}/${checks} page checks have findings` : `ALL ${checks} page checks clean`); process.exit(bad ? 1 : 0);
})();
