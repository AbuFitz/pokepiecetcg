/* Static preview simulation layer (NOT part of the theme, NOT the shop's real behaviour).
 * The WordPress theme talks to the server for cart, search, quick view and sign-in. A static host has no server, so this file answers those same
 * requests in the browser: the cart lives in localStorage, search/filters run over a catalogue snapshot (/demo-catalogue.json). No payment, no orders, no accounts. */
(function () {
	'use strict';
	var KEY = 'pp_demo_cart_v1', money = function (n) { return '£' + (Math.round(n * 100) / 100).toFixed(2); };
	var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };
	function load() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
	function save(c) { localStorage.setItem(KEY, JSON.stringify(c)); }
	var catalogue = null;
	function cat() { return catalogue ? Promise.resolve(catalogue) : fetch('/demo-catalogue.json').then(function (r) { return r.json(); }).then(function (j) { return (catalogue = j); }); }
	var norm = function (s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim(); };

	/* ---------- cart markup identical to inc/cart.php ---------- */
	function lines() {
		var c = load(); if (!c.length) return '<div class="pp-drawer__body" data-pp-cart-body><div class="pp-empty"><p><strong>Your cart is empty.</strong></p><p>Browse the range to find a product.</p><a class="pp-btn pp-btn--primary" href="/shop/">Shop all products</a></div></div>';
		return '<div class="pp-drawer__body" data-pp-cart-body><ul class="pp-lines">' + c.map(function (i) {
			return '<li class="pp-line" data-key="' + i.id + '"><a class="pp-line__img" href="' + esc(i.url) + '"><img src="' + esc(i.img) + '" alt="" width="76" height="76"></a><div class="pp-line__main"><a class="pp-line__name" href="' + esc(i.url) + '">' + esc(i.name) + '</a>' + (i.mode ? '<span class="pp-line__mode">' + esc(i.mode) + '</span>' : '') +
				'<div class="pp-line__row"><div class="pp-qty" role="group" aria-label="Quantity for ' + esc(i.name) + '"><button type="button" data-pp-qty="-1" aria-label="Decrease">−</button><output>' + i.qty + '</output><button type="button" data-pp-qty="1" aria-label="Increase">+</button></div><span class="pp-line__price">' + money(i.price * i.qty) + '</span></div></div><button type="button" class="pp-line__remove" data-pp-remove aria-label="Remove ' + esc(i.name) + '"><svg class="pp-i" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button></li>';
		}).join('') + '</ul></div>';
	}
	function foot() { var c = load(); if (!c.length) return '<div class="pp-drawer__foot" data-pp-cart-foot hidden></div>'; var t = c.reduce(function (a, i) { return a + i.price * i.qty; }, 0); return '<div class="pp-drawer__foot" data-pp-cart-foot>' + sdToggle('drawer') + '<p class="pp-subtotal"><span>Subtotal</span><strong>' + money(t) + '</strong></p><p class="pp-muted">Shipping and tax are calculated by the shop at checkout.</p><a class="pp-btn pp-btn--primary pp-btn--block" href="/checkout/">Checkout</a><a class="pp-btn pp-btn--ghost pp-btn--block" href="/cart/">View cart</a></div>'; }
	/* Special Delivery option (simulated; mirrors the PokePiece Core fee). Price is read from the product-page tick-box so it always matches the theme. */
	var SDK = 'pp_demo_sd', SDP = parseFloat(localStorage.getItem('pp_demo_sd_price') || '10.45');
	function sdOn() { return localStorage.getItem(SDK) === '1' && load().length > 0; }
	function sdSet(v) { if (v) localStorage.setItem(SDK, '1'); else localStorage.removeItem(SDK); }
	function sdToggle(ctx) { if (!load().length) return ''; return '<div class="pp-upgrade-line pp-upgrade-line--' + ctx + '"><span>Need it faster?</span><label class="ppc-sd ppc-sd--' + ctx + '"><input type="checkbox" data-ppc-sd value="1"' + (sdOn() ? ' checked' : '') + '><span class="ppc-sd__box" aria-hidden="true"></span><span class="ppc-sd__txt"><strong>Add Special Delivery</strong> <em>+' + money(SDP) + '</em></span></label></div>'; }
	function count() { var n = load().reduce(function (a, i) { return a + i.qty; }, 0); return '<span class="pp-cart-count" data-count="' + n + '"' + (n ? '' : ' hidden') + '>' + n + '</span>'; }
	function fragments() { return { 'span.pp-cart-count': count(), 'div[data-pp-cart-body]': lines(), 'div[data-pp-cart-foot]': foot() }; }
	function repaint() { var f = fragments(); Object.keys(f).forEach(function (sel) { document.querySelectorAll(sel).forEach(function (n) { var t = document.createElement('div'); t.innerHTML = f[sel]; if (t.firstElementChild) n.replaceWith(t.firstElementChild); }); }); renderPages(); }
	function add(item, qty) { var c = load(), f = c.filter(function (x) { return x.id === item.id; })[0]; if (f) f.qty += qty; else { item.qty = qty; c.push(item); } save(c); repaint(); var b = document.querySelector('.pp-cart-open'); if (b) b.click(); }

	/* ---------- read a product from the page ---------- */
	function priceNum(t) { var m = (t || '').replace(/,/g, '').match(/[\d.]+/); return m ? parseFloat(m[0]) : 0; }
	function fromCard(li) { var a = li.querySelector('.pp-card__title a'), im = li.querySelector('.pp-card__plate img'), pr = li.querySelector('.pp-card__price'), md = li.querySelector('.pp-mode'); return { id: li.dataset.pid, name: a ? a.textContent.trim() : '', url: a ? a.getAttribute('href') : '#', img: im ? (im.currentSrc || im.src) : '', price: priceNum(pr ? pr.textContent : ''), mode: md ? md.textContent.trim() : '' }; }
	function fromPage() { var t = document.querySelector('.product_title'), im = document.querySelector('.woocommerce-product-gallery__image img'), pr = document.querySelector('.summary .price'), id = (document.body.className.match(/postid-(\d+)/) || [])[1]; return t ? { id: id, name: t.textContent.trim(), url: location.pathname, img: im ? im.src : '', price: priceNum(pr ? pr.textContent : ''), mode: (document.querySelector('.summary .pp-callout strong') || {}).textContent || '' } : null; }
	var toast = function (msg) { var t = document.getElementById('pp-demo-toast'); if (!t) { t = document.createElement('p'); t.id = 'pp-demo-toast'; t.setAttribute('role', 'status'); t.style.cssText = 'position:fixed;left:12px;right:12px;bottom:16px;z-index:300;margin:0 auto;max-width:460px;background:#202321;color:#fff;border-radius:18px;padding:14px 18px;font:500 15px/1.4 Archivo,Arial,sans-serif'; document.body.appendChild(t); } t.textContent = msg; clearTimeout(t._h); t._h = setTimeout(function () { t.remove(); }, 3800); };

	/* ---------- intercept: capture phase, before the theme's own handlers ---------- */
	document.addEventListener('click', function (e) {
		var a = e.target.closest('li.pp-card .add_to_cart_button, .pp-qvc .add_to_cart_button');
		if (a) { e.preventDefault(); e.stopImmediatePropagation(); var li = a.closest('li.pp-card'), it = li ? fromCard(li) : null; if (!it) { var q = a.closest('.pp-qvc'); it = { id: a.dataset.product_id, name: q.querySelector('h2').textContent, url: '#', img: (q.querySelector('img') || {}).src || '', price: priceNum((q.querySelector('.pp-qvc__price') || {}).textContent), mode: '' }; } add(it, 1); return; }
		var s = e.target.closest('.single_add_to_cart_button'); if (s) { e.preventDefault(); e.stopImmediatePropagation(); var tick = document.querySelector('input[name=pp_upgrade]'); if (tick && tick.checked) { sdSet(true); var pe = document.querySelector('.pp-opt__txt em'); if (pe) { var pv = priceNum(pe.textContent); if (pv) { SDP = pv; localStorage.setItem('pp_demo_sd_price', String(pv)); } } } var p = fromPage(), q = document.querySelector('form.cart input.qty'); if (p) add(p, Math.max(1, parseInt(q && q.value, 10) || 1)); return; }
		var sg = e.target.closest('[data-pp-sticky-go]'); if (sg) { e.preventDefault(); e.stopImmediatePropagation(); var sp = fromPage(); if (sp) add(sp, 1); return; }
	}, true);
	document.addEventListener('submit', function (e) { if (e.target.matches('form.cart')) { e.preventDefault(); e.stopImmediatePropagation(); var p = fromPage(), q = e.target.querySelector('input.qty'); if (p) add(p, Math.max(1, parseInt(q && q.value, 10) || 1)); } }, true);

	/* ---------- stub the theme's server calls ---------- */
	var realFetch = window.fetch.bind(window);
	var json = function (o, ok) { return Promise.resolve(new Response(JSON.stringify(o), { status: ok === false ? 422 : 200, headers: { 'Content-Type': 'application/json' } })); };
	window.fetch = function (url, opts) {
		var u = typeof url === 'string' ? url : url.url;
		if (u.indexOf('/pokepiece/v1/search') > -1) {
			var q = norm(decodeURIComponent((u.split('q=')[1] || '').split('&')[0]));
			return cat().then(function (c) { var hit = c.filter(function (p) { return norm(p.name).indexOf(q) > -1 || norm(p.name).replace(/ /g, '').indexOf(q.replace(/ /g, '')) > -1; }); return json({ items: hit.slice(0, 6), total: hit.length, all: '/shop/?s=' + encodeURIComponent(q) }); });
		}
		if (u.indexOf('/pokepiece/v1/quickview/') > -1) {
			var id = u.split('quickview/')[1]; var li = document.querySelector('li.pp-card[data-pid="' + id + '"]'); if (!li) return json({ html: '<p>Not found.</p>' }, false);
			var it = fromCard(li), st = li.querySelector('.pp-stock'), inStock = li.dataset.stock === 'in', link = li.querySelector('.pp-card__title a').getAttribute('href');
			return json({ html: '<div class="pp-qvc"><div class="pp-qvc__img"><img src="' + esc(it.img) + '" alt=""></div><div class="pp-qvc__info"><h2>' + esc(it.name) + '</h2>' + (it.mode ? '<p><span class="pp-mode">' + esc(it.mode) + '</span></p>' : '') + '<p class="pp-qvc__price">' + money(it.price) + '</p><p class="pp-stock pp-stock--' + (inStock ? 'in' : 'out') + '"><span class="pp-stock__dot"></span>' + (inStock ? 'In stock' : 'Out of stock') + '</p><div class="pp-qvc__actions">' + (inStock ? '<a class="pp-btn pp-btn--primary add_to_cart_button" href="#" data-product_id="' + esc(id) + '">Add to cart</a>' : '') + '<a class="pp-btn pp-btn--ghost" href="' + esc(link) + '">View full details</a></div></div></div>' });
		}
		if (u.indexOf('admin-ajax.php') > -1 && opts && opts.body instanceof FormData) {
			var act = opts.body.get('action');
			if (act === 'pokepiece_cart_update') { var c = load(), key = opts.body.get('key'), qty = parseInt(opts.body.get('qty'), 10); c = c.map(function (i) { if (i.id === key) i.qty = qty; return i; }).filter(function (i) { return i.qty > 0; }); save(c); setTimeout(renderPages, 0); return json({ success: true, data: { fragments: fragments() } }); }
			if (act === 'pokepiece_login' || act === 'pokepiece_register') return json({ success: false, data: { message: 'This preview has no accounts. On the live shop this signs you in.' } }, false);
		}
		return realFetch(url, opts);
	};

	/* ---------- cart + checkout pages ---------- */
	function renderPages() {
		var host = document.querySelector('.pp-woo-content'); if (!host) return; var p = location.pathname, c = load();
		var css = document.getElementById('pp-demo-css'); if (!css) { css = document.createElement('style'); css.id = 'pp-demo-css'; css.textContent = '.pp-dpage{display:grid;gap:28px}@media(min-width:900px){.pp-dpage{grid-template-columns:1.4fr 1fr;align-items:start;gap:48px}}.pp-dbox{background:#fff;border:2px solid #14171a;border-radius:34px 6px 34px 6px;box-shadow:6px 6px 0 #14171a;padding:clamp(18px,3vw,32px)}.pp-dbox h2{font:900 26px/1 Archivo,Arial;font-stretch:116%;text-transform:uppercase;margin:0 0 14px}.pp-dbox .pp-lines{margin:0}.pp-dtot{display:flex;justify-content:space-between;padding:10px 0;border-top:1px solid #d5d8dc}.pp-dtot--big{font:900 22px Archivo,Arial;border-top:2px solid #14171a}.pp-dform label{display:block;font-weight:700;font-size:14px;margin:12px 0 5px}.pp-dform input{width:100%}.pp-dnote{font-size:14px;color:#555b62;margin:14px 0 0}'; document.head.appendChild(css); }
		var total = c.reduce(function (a, i) { return a + i.price * i.qty; }, 0), fee = sdOn() ? SDP : 0;
		var list = '<ul class="pp-lines">' + c.map(function (i) { return '<li class="pp-line" data-key="' + i.id + '"><a class="pp-line__img" href="' + esc(i.url) + '"><img src="' + esc(i.img) + '" alt="" width="76" height="76"></a><div class="pp-line__main"><a class="pp-line__name" href="' + esc(i.url) + '">' + esc(i.name) + '</a>' + (i.mode ? '<span class="pp-line__mode">' + esc(i.mode) + '</span>' : '') + '<div class="pp-line__row"><div class="pp-qty"><button type="button" data-pp-qty="-1" aria-label="Decrease">−</button><output>' + i.qty + '</output><button type="button" data-pp-qty="1" aria-label="Increase">+</button></div><span class="pp-line__price">' + money(i.price * i.qty) + '</span></div></div><button type="button" class="pp-line__remove" data-pp-remove aria-label="Remove ' + esc(i.name) + '">✕</button></li>'; }).join('') + '</ul>';
		if (/^\/cart\/?$/.test(p)) {
			host.innerHTML = c.length ? '<div class="pp-dpage"><div class="pp-dbox"><h2>Your items</h2>' + list + '</div><div class="pp-dbox"><h2>Cart totals</h2>' + sdToggle('cart') + '<div class="pp-dtot"><span>Subtotal</span><strong>' + money(total) + '</strong></div><div class="pp-dtot"><span>Shipping</span><span>Calculated at checkout</span></div>' + (fee ? '<div class="pp-dtot"><span>Special Delivery Shipping Upgrade</span><span>' + money(fee) + '</span></div>' : '') + '<div class="pp-dtot pp-dtot--big"><span>Total</span><span>' + money(total + fee) + '</span></div><p><a class="pp-btn pp-btn--primary pp-btn--block" href="/checkout/">Proceed to checkout</a></p></div></div>' : '<p class="cart-empty woocommerce-info">Your cart is currently empty.</p><p class="return-to-shop"><a class="pp-btn pp-btn--primary" href="/shop/">Return to shop</a></p>';
		}
		if (/^\/checkout\/?$/.test(p)) {
			if (host.dataset.done) return;
			host.innerHTML = c.length ? '<div class="pp-dpage"><form class="pp-dbox pp-dform" id="pp-dco"><h2>Billing details</h2><label>Full name</label><input required><label>Email address</label><input type="email" required><label>Address</label><input required><label>Town / city</label><input required><label>Postcode</label><input required><label>Order notes (optional)</label><input placeholder="Anything we should know">' + sdToggle('checkout') + (fee ? '<div class="ppc-sd-ref"><label>TikTok username or order reference <abbr title="required">*</abbr></label><input required></div>' : '') + '<p><button class="pp-btn pp-btn--primary pp-btn--block" type="submit">Place demo order</button></p><p class="pp-dnote">Demo checkout: no payment is taken and no order is created. On the live shop this step is WooCommerce\'s own checkout with the shop\'s payment methods.</p></form><div class="pp-dbox"><h2>Your order</h2>' + list + '<div class="pp-dtot"><span>Subtotal</span><strong>' + money(total) + '</strong></div><div class="pp-dtot pp-dtot--big"><span>Total</span><span>' + money(total) + '</span></div></div></div>' : '<p class="cart-empty woocommerce-info">Your cart is currently empty.</p><p class="return-to-shop"><a class="pp-btn pp-btn--primary" href="/shop/">Return to shop</a></p>';
		}
	}
	document.addEventListener('submit', function (e) { if (e.target.id === 'pp-dco') { e.preventDefault(); var h = document.querySelector('.pp-woo-content'), t = load().reduce(function (a, i) { return a + i.price * i.qty; }, 0) + (sdOn() ? SDP : 0); h.dataset.done = '1'; sdSet(false); h.innerHTML = '<div class="pp-dbox"><h2>Thank you, demo order received</h2><p>Order total ' + money(t) + '. This was a demonstration: nothing was charged and no order exists.</p><p><a class="pp-btn pp-btn--primary" href="/shop/">Back to the shop</a></p></div>'; save([]); repaint(); } }, true);
	// page-level qty/remove for cart & checkout pages (the theme only wires the drawer)
	document.addEventListener('click', function (e) { var l = e.target.closest('.pp-woo-content .pp-line'); if (!l) return; var q = e.target.closest('[data-pp-qty]'), r = e.target.closest('[data-pp-remove]'); if (!q && !r) return; e.stopImmediatePropagation(); var c = load(), id = l.dataset.key; c = c.map(function (i) { if (i.id === id) i.qty = r ? 0 : Math.max(0, i.qty + parseInt(q.dataset.ppQty, 10)); return i; }).filter(function (i) { return i.qty > 0; }); save(c); repaint(); }, true);

	/* ---------- shop: filters, sort and search over the static listing ---------- */
	function shop() {
		var ul = document.querySelector('ul.products'); if (!ul || !document.querySelector('.pp-shop')) return;
		var q = new URLSearchParams(location.search), mode = q.get('pp_mode'), stock = q.get('pp_stock'), s = norm(q.get('s') || ''), ob = q.get('orderby') || '';
		var lis = Array.prototype.slice.call(ul.children), shown = 0;
		lis.forEach(function (li) { var ok = (!mode || li.dataset.mode === mode) && (!stock || li.dataset.stock === 'in') && (!s || norm(li.dataset.name).indexOf(s) > -1); li.hidden = !ok; if (ok) shown++; });
		var sorted = lis.slice().sort(function (a, b) { return ob === 'price' ? a.dataset.price - b.dataset.price : ob === 'price-desc' ? b.dataset.price - a.dataset.price : ob === 'date' ? b.dataset.date - a.dataset.date : 0; }); if (ob) sorted.forEach(function (li) { ul.appendChild(li); });
		var cnt = document.getElementById('pp-count'); if (cnt) cnt.textContent = shown + (shown === 1 ? ' product' : ' products');
		var sel = document.querySelector('.woocommerce-ordering select'); if (sel) { sel.value = ob || sel.value; sel.addEventListener('change', function () { var n = new URLSearchParams(location.search); n.set('orderby', sel.value); location.search = n.toString(); }, true); }
		if (!shown) { var m = document.createElement('div'); m.className = 'pp-empty pp-empty--archive'; m.innerHTML = '<p><strong>No products match.</strong></p><p><a class="pp-btn pp-btn--primary" href="' + location.pathname + '">Clear all filters</a></p>'; ul.after(m); }
		var chips = []; if (mode) chips.push([mode.replace('_', ' '), 'pp_mode']); if (stock) chips.push(['In stock only', 'pp_stock']); if (s) chips.push(['“' + (q.get('s')) + '”', 's']);
		if (chips.length && !document.querySelector('.pp-chips')) { var ol = document.createElement('ul'); ol.className = 'pp-chips'; ol.innerHTML = chips.map(function (c) { var n = new URLSearchParams(location.search); n.delete(c[1]); return '<li><a class="pp-chip" href="' + location.pathname + (n.toString() ? '?' + n : '') + '">' + esc(c[0]) + ' ✕</a></li>'; }).join('') + '<li><a class="pp-chip pp-chip--clear" href="' + location.pathname + '">Clear all</a></li>'; var tb = document.querySelector('.pp-toolbar'); if (tb) tb.after(ol); }
		var f = document.querySelector('.pp-filters'); if (f) { f.querySelectorAll('input[name=pp_mode]').forEach(function (i) { i.checked = (i.value === (mode || '')); }); var st = f.querySelector('input[name=pp_stock]'); if (st) st.checked = !!stock; }
	}
	document.addEventListener('DOMContentLoaded', function () { repaint(); shop(); });
	document.addEventListener('change', function (e) { var t = e.target; if (!t.matches || !t.matches('[data-ppc-sd]')) return; e.stopImmediatePropagation(); sdSet(t.checked); var host = document.querySelector('.pp-woo-content'); if (host) delete host.dataset.done; repaint(); }, true);
})();
