/* PokePieceTCG progressive enhancement. All essential content and links work without JS. */
(function () {
	'use strict';
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var body = document.body;
	var backdrop = $('.pp-backdrop');
	var current = null; // only one layer at a time
	var lockY = 0, locked = false;
	// iOS-safe scroll lock: overflow:hidden alone lets the page scroll behind overlays on iOS Safari.
	function lockScroll() { if (locked) return; locked = true; lockY = window.scrollY; body.style.top = (-lockY) + 'px'; body.classList.add('pp-lock'); }
	function unlockScroll() { if (!locked) return; locked = false; body.classList.remove('pp-lock'); body.style.top = ''; window.scrollTo(0, lockY); }

	var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]):not([type=hidden]),select,textarea,[tabindex]:not([tabindex="-1"])';

	function openLayer(el, invoker, opts) {
		opts = opts || {};
		if (current && current.el !== el) closeLayer(true);
		el.hidden = false;
		void el.offsetWidth;
		el.classList.add('is-open');
		if (opts.backdrop !== false && backdrop) { backdrop.hidden = false; void backdrop.offsetWidth; backdrop.classList.add('is-open'); }
		lockScroll();
		$$('main,.pp-header,.pp-footer,.pp-service').forEach(function (n) { n.setAttribute('inert', ''); });
		if (invoker) invoker.setAttribute('aria-expanded', 'true');
		current = { el: el, invoker: invoker };
		var f = $(opts.focus || FOCUSABLE, el); if (f) f.focus({ preventScroll: true });
	}
	function closeLayer(immediate) {
		if (!current) return;
		var c = current; current = null;
		c.el.classList.remove('is-open');
		if (backdrop) backdrop.classList.remove('is-open');
		unlockScroll();
		$$('[inert]').forEach(function (n) { n.removeAttribute('inert'); });
		if (c.invoker) { c.invoker.setAttribute('aria-expanded', 'false'); if (!immediate) c.invoker.focus({ preventScroll: true }); }
		var done = function () { if (!c.el.classList.contains('is-open')) c.el.hidden = true; if (!current && backdrop && !backdrop.classList.contains('is-open')) backdrop.hidden = true; };
		if (reduce || immediate) done(); else setTimeout(done, 280);
	}
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { if (current) { e.preventDefault(); closeLayer(); } closeNav(); closeSuggest(); }
		if (e.key === 'Tab' && current) {
			var f = $$(FOCUSABLE, current.el).filter(function (n) { return n.offsetParent !== null; });
			if (!f.length) return;
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}
	});
	if (backdrop) backdrop.addEventListener('click', function () { closeLayer(); });
	document.addEventListener('click', function (e) {
		var m = e.target.closest('.pp-modal'); if (m && e.target === m) closeLayer();
	});

	/* ----- Mobile menu ----- */
	var menu = $('#pp-menu');
	var mo = $('.pp-menu-open');
	if (menu && mo) {
		mo.addEventListener('click', function () { openLayer(menu, mo, { focus: '.pp-close' }); });
		$('.pp-close', menu).addEventListener('click', function () { closeLayer(); });
		window.matchMedia('(min-width: 900px)').addEventListener('change', function (e) { if (e.matches && current && current.el === menu) closeLayer(true); });
	}

	/* ----- Desktop nav disclosures (click + keyboard, never hover-only) ----- */
	function closeNav(except) {
		$$('.pp-nav__btn[aria-expanded=true]').forEach(function (b) { if (b !== except) { b.setAttribute('aria-expanded', 'false'); $('#' + b.getAttribute('aria-controls')).hidden = true; } });
	}
	$$('.pp-nav__btn').forEach(function (b) {
		b.addEventListener('click', function (e) {
			e.stopPropagation();
			var sub = $('#' + b.getAttribute('aria-controls'));
			var open = b.getAttribute('aria-expanded') === 'true';
			closeNav(b); b.setAttribute('aria-expanded', String(!open)); sub.hidden = open;
		});
	});
	document.addEventListener('click', function (e) { if (!e.target.closest('.pp-nav__group')) closeNav(); });
	document.addEventListener('focusin', function (e) { if (!e.target.closest('.pp-nav__group') && !e.target.closest('.pp-search')) { closeNav(); } });

	/* ----- Cart drawer ----- */
	var drawer = $('#pp-cart-drawer');
	var cartBtn = $('.pp-cart-open');
	function openCart() { if (drawer) openLayer(drawer, cartBtn, { focus: '.pp-close' }); }
	if (drawer && cartBtn) {
		cartBtn.addEventListener('click', openCart);
		$('.pp-close', drawer).addEventListener('click', function () { closeLayer(); });
	}
	function drawerStatus(msg) { var s = $('.pp-drawer__status', drawer); if (s) s.textContent = msg || ''; }
	function applyFragments(f) {
		if (!f) return;
		Object.keys(f).forEach(function (sel) { $$(sel).forEach(function (n) { var t = document.createElement('div'); t.innerHTML = f[sel]; if (t.firstElementChild) n.replaceWith(t.firstElementChild); }); });
	}
	if (window.jQuery) {
		window.jQuery(document.body).on('added_to_cart', function () { drawerStatus(''); openCart(); });
	}
	var busy = false;
	function cartUpdate(line, qty) {
		if (busy) return; busy = true; line.classList.add('is-busy'); drawerStatus('');
		var fd = new FormData(); fd.append('action', 'pokepiece_cart_update'); fd.append('nonce', PP.nonce); fd.append('key', line.dataset.key); fd.append('qty', qty);
		fetch(PP.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			busy = false;
			if (j && j.success) { applyFragments(j.data.fragments); drawerStatus(qty === 0 ? '' : ''); }
			else { line.classList.remove('is-busy'); if (j && j.data) { applyFragments(j.data.fragments); drawerStatus(j.data.message || PP.i18n.cartFail); } else drawerStatus(PP.i18n.cartFail); }
			if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
		}).catch(function () { busy = false; line.classList.remove('is-busy'); drawerStatus(PP.i18n.cartFail); });
	}
	document.addEventListener('click', function (e) {
		var line = e.target.closest('.pp-line'); if (!line) return;
		var q = e.target.closest('[data-pp-qty]');
		if (q) { var cur = parseInt($('output', line).textContent, 10) || 1; cartUpdate(line, Math.max(0, cur + parseInt(q.dataset.ppQty, 10))); }
		else if (e.target.closest('[data-pp-remove]')) cartUpdate(line, 0);
	});

	/* ----- Filters drawer (phone) ----- */
	var fp = $('#pp-filter-panel'), fo = $('.pp-filter-open');
	if (fp && fo) {
		fo.addEventListener('click', function () { fp.classList.add('is-open'); openLayer(fp, fo, { focus: '.pp-close' }); fp.classList.add('is-open'); });
		$$('.pp-filter-close', fp).forEach(function (b) { b.addEventListener('click', function () { closeLayer(); fp.classList.remove('is-open'); }); });
		var _close = closeLayer;
		backdrop && backdrop.addEventListener('click', function () { fp.classList.remove('is-open'); });
	}

	/* ----- Predictive search ----- */
	var timer, ctrl, active = -1;
	function closeSuggest() { $$('.pp-suggest').forEach(function (s) { s.hidden = true; }); $$('.pp-search__input').forEach(function (i) { i.removeAttribute('aria-expanded'); }); }
	function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
	$$('.pp-search').forEach(function (form) {
		var input = $('.pp-search__input', form), box = $('.pp-suggest', form), list = $('ul', box), st = $('.pp-suggest__status', box), composing = false;
		input.addEventListener('compositionstart', function () { composing = true; });
		input.addEventListener('compositionend', function () { composing = false; input.dispatchEvent(new Event('input')); });
		input.addEventListener('input', function () {
			if (composing) return;
			clearTimeout(timer); if (ctrl) ctrl.abort();
			var q = input.value.trim();
			if (q.length < 2) { box.hidden = true; return; }
			timer = setTimeout(function () {
				ctrl = new AbortController(); box.hidden = false; list.innerHTML = ''; st.textContent = PP.i18n.searching; active = -1;
				fetch(PP.rest + 'search?q=' + encodeURIComponent(q), { signal: ctrl.signal, credentials: 'same-origin' }).then(function (r) { if (!r.ok) throw 0; return r.json(); }).then(function (j) {
					if (!j.items.length) { st.textContent = PP.i18n.noMatch; return; }
					st.textContent = j.items.length + (j.total > j.items.length ? '+' : '') + ' results';
					list.innerHTML = j.items.map(function (it) {
						return '<li><a href="' + esc(it.url) + '" role="option">' + (it.img ? '<img src="' + esc(it.img) + '" alt="" width="52" height="52" loading="lazy">' : '<span></span>') + '<span><span class="t">' + esc(it.title) + '</span><br><span class="s">' + esc(it.price) + ' · ' + esc(it.stock) + (it.mode ? ' · ' + esc(it.mode) : '') + '</span></span></a></li>';
					}).join('') + '<li><a class="all" href="' + esc(j.all) + '">View all results</a></li>';
				}).catch(function (err) { if (err && err.name === 'AbortError') return; st.textContent = PP.i18n.searchFail; });
			}, 250);
		});
		form.addEventListener('keydown', function (e) {
			var items = $$('.pp-suggest a', form); if (box.hidden || !items.length) return;
			if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(items.length - 1, active + 1); items[active].focus(); }
			else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(-1, active - 1); (active < 0 ? input : items[active]).focus(); }
		});
	});
	document.addEventListener('click', function (e) { if (!e.target.closest('.pp-search')) closeSuggest(); });

	/* ----- Quick view ----- */
	var qv = $('#pp-qv');
	if (qv) {
		$('.pp-close', qv).addEventListener('click', function () { closeLayer(); });
		document.addEventListener('click', function (e) {
			var b = e.target.closest('.pp-qv-open'); if (!b) return;
			var out = $('.pp-modal__body', qv); out.textContent = PP.i18n.loading; openLayer(qv, b, { backdrop: false, focus: '.pp-close' });
			fetch(PP.rest + 'quickview/' + b.dataset.id, { credentials: 'same-origin' }).then(function (r) { if (!r.ok) throw 0; return r.json(); }).then(function (j) { out.innerHTML = j.html; })
				.catch(function () { out.innerHTML = '<p>' + esc(PP.i18n.searchFail) + '</p>'; });
		});
		if (window.jQuery) window.jQuery(document.body).on('added_to_cart', function () { if (current && current.el === qv) closeLayer(true); });
	}

	/* ----- Sticky purchase bar ----- */
	var sticky = $('[data-pp-sticky]');
	var form = $('form.cart');
	if (sticky && form) {
		var target = $('.single_add_to_cart_button', form) || form, ticking = false, shown = false, hideT;
		// Shown only once the real purchase button has scrolled ABOVE the viewport (not while it is still below the fold).
		var check = function () {
			ticking = false;
			var gone = target.getBoundingClientRect().bottom < 0;
			if (gone === shown) return;
			shown = gone; clearTimeout(hideT);
			if (gone) { sticky.hidden = false; requestAnimationFrame(function () { sticky.classList.add('is-shown'); }); }
			else { sticky.classList.remove('is-shown'); hideT = setTimeout(function () { if (!shown) sticky.hidden = true; }, 220); }
		};
		window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(check); } }, { passive: true });
		window.addEventListener('resize', check);
		check();
		var go = $('[data-pp-sticky-go]', sticky);
		if (go) go.addEventListener('click', function () {
			// Required choices are validated by the original form, never bypassed here.
			var btn = $('.single_add_to_cart_button', form);
			if (btn && !btn.classList.contains('disabled') && form.checkValidity()) form.requestSubmit ? form.requestSubmit(btn) : btn.click();
			else form.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
		});
	}

	/* ----- Account popup (sign in / create account) ----- */
	var acc = $('#pp-account');
	if (acc) {
		var tabs = $$('[role=tab]', acc), panes = $$('form[role=tabpanel]', acc);
		function showTab(i) { tabs.forEach(function (t, k) { t.setAttribute('aria-selected', String(k === i)); t.tabIndex = k === i ? 0 : -1; }); panes.forEach(function (p, k) { p.hidden = k !== i; }); var f = $('input', panes[i]); if (f) f.focus({ preventScroll: true }); }
		tabs.forEach(function (t, i) { t.addEventListener('click', function () { showTab(i); }); t.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { var n = (i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length; showTab(n); tabs[n].focus(); } }); });
		$('.pp-close', acc).addEventListener('click', function () { closeLayer(); });
		function openAcc(tab, inv) { openLayer(acc, inv || null, { backdrop: false, focus: 'input' }); showTab(tab || 0); }
		document.addEventListener('click', function (e) { var a = e.target.closest('[data-pp-account]'); if (!a) return; e.preventDefault(); openAcc(0, a); });
		var mm = $('.pp-menu a[href*="my-account"]'); if (mm && !document.body.classList.contains('logged-in')) mm.addEventListener('click', function (e) { e.preventDefault(); closeLayer(true); openAcc(0, null); });
		if (/[?&]account=(login|register)/.test(location.search)) openAcc(/register/.test(location.search) ? 1 : 0, null);
		$$('.pp-pass__toggle', acc).forEach(function (b) { b.addEventListener('click', function () { var i = document.getElementById(b.getAttribute('aria-controls')), show = i.type === 'password'; i.type = show ? 'text' : 'password'; b.setAttribute('aria-pressed', String(show)); b.textContent = show ? 'Hide' : 'Show'; }); });
		panes.forEach(function (f) {
			f.addEventListener('submit', function (e) {
				e.preventDefault(); var msg = $('.pp-form-msg', f), btn = $('button[type=submit]', f); msg.className = 'pp-form-msg'; msg.textContent = '';
				var fd = new FormData(f); fd.append('action', f.dataset.action); fd.append('nonce', PP.accNonce); btn.disabled = true; btn.dataset.t = btn.textContent; btn.textContent = PP.i18n.loading;
				fetch(PP.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
					if (j.success) { msg.className = 'pp-form-msg is-ok'; msg.textContent = 'Success — taking you to your account…'; location.href = j.data.redirect; }
					else { btn.disabled = false; btn.textContent = btn.dataset.t; msg.className = 'pp-form-msg is-error'; msg.textContent = (j.data && j.data.message) || PP.i18n.nlFail; var bad = $('input', f); if (bad) bad.focus({ preventScroll: true }); }
				}).catch(function () { btn.disabled = false; btn.textContent = btn.dataset.t; msg.className = 'pp-form-msg is-error'; msg.textContent = 'Something went wrong. Please try again.'; });
			});
		});
	}

	/* ----- Newsletter (only rendered when a provider handler exists) ----- */
	var nl = $('#pp-nl');
	if (nl) {
		var nlf = $('form', nl), msg = $('.pp-form-msg', nl);
		$$('[data-pp-newsletter-open]').forEach(function (b) { b.addEventListener('click', function () { openLayer(nl, null, { backdrop: false, focus: 'input[type=email]' }); }); });
		$('.pp-close', nl).addEventListener('click', function () { closeLayer(); });
		nlf.addEventListener('submit', function (e) {
			e.preventDefault(); msg.className = 'pp-form-msg'; msg.textContent = '';
			var data = { email: nlf.email.value, consent: nlf.consent.checked ? 1 : 0, website: nlf.website.value };
			var btn = $('button[type=submit]', nlf); btn.disabled = true;
			fetch(PP.rest + 'newsletter', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
				.then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
				.then(function (x) { btn.disabled = false; if (x.ok) { msg.className = 'pp-form-msg is-ok'; msg.textContent = x.j.message || ''; nlf.reset(); } else { msg.className = 'pp-form-msg is-error'; msg.textContent = x.j.message || PP.i18n.nlFail; } })
				.catch(function () { btn.disabled = false; msg.className = 'pp-form-msg is-error'; msg.textContent = PP.i18n.nlFail; });
		});
	}
})();

/* ----- Wheel: snap carousel, centre-weighted, auto-advancing with pause controls (WCAG 2.2.2) ----- */
(function () {
	var rail = document.querySelector('[data-pp-wheel]'); if (!rail) return;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var items = Array.prototype.slice.call(rail.children), btn = document.querySelector('[data-rail-play]');
	var playing = !reduce, hold = false, offscreen = false, timer = null, raf = 0, lastUser = 0;
	function centreOf(li) { return li.offsetLeft + li.offsetWidth / 2; }
	function nearest() { var mid = rail.scrollLeft + rail.clientWidth / 2, best = 0, bd = 1e9; items.forEach(function (li, i) { var d = Math.abs(centreOf(li) - mid); if (d < bd) { bd = d; best = i; } }); return best; }
	function paint() {
		raf = 0; var mid = rail.scrollLeft + rail.clientWidth / 2, n = nearest();
		items.forEach(function (li, i) { var w = li.offsetWidth + 20, off = (centreOf(li) - mid) / w, d = Math.min(Math.abs(off), 2); li.style.setProperty('--d', d.toFixed(3)); li.style.setProperty('--s', off < 0 ? -1 : 1); li.classList.toggle('is-center', i === n); });
	}
	function go(i, smooth) { i = (i + items.length) % items.length; rail.scrollTo({ left: centreOf(items[i]) - rail.clientWidth / 2, behavior: smooth && !reduce ? 'smooth' : 'auto' }); }
	function tick() { if (!playing || hold || offscreen || document.hidden || items.length < 3) return; if (Date.now() - lastUser < 6000) return; go(nearest() + 1, true); }
	function setPlaying(p) { playing = p; if (btn) { btn.setAttribute('aria-pressed', String(p)); btn.setAttribute('aria-label', p ? 'Pause auto-scroll' : 'Start auto-scroll'); } }
	rail.addEventListener('scroll', function () { if (!raf) raf = requestAnimationFrame(paint); }, { passive: true });
	['pointerdown', 'touchstart', 'wheel', 'keydown'].forEach(function (ev) { rail.addEventListener(ev, function () { lastUser = Date.now(); }, { passive: true }); });
	rail.addEventListener('mouseenter', function () { hold = true; }); rail.addEventListener('mouseleave', function () { hold = false; });
	rail.addEventListener('focusin', function () { hold = true; }); rail.addEventListener('focusout', function () { hold = false; });
	rail.addEventListener('keydown', function (e) { if (e.target !== rail) return; if (e.key === 'ArrowRight') { e.preventDefault(); go(nearest() + 1, true); } if (e.key === 'ArrowLeft') { e.preventDefault(); go(nearest() - 1, true); } });
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-rail]'); if (b) { lastUser = Date.now(); go(nearest() + parseInt(b.dataset.rail, 10), true); }
		if (e.target.closest('[data-rail-play]')) setPlaying(!playing);
	});
	if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { offscreen = !en[0].isIntersecting; }).observe(rail);
	if (reduce) setPlaying(false);
	timer = setInterval(tick, 3200);
	window.addEventListener('resize', function () { go(nearest(), false); paint(); });
	go(0, false); paint();
})();

/* ----- Reveal on scroll + pointer tilt (fine pointers only). Content is fully visible without JS. ----- */
(function () {
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	document.documentElement.classList.add('pp-js');
	if (!reduce && 'IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (en) { en.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }); }, { rootMargin: '0px 0px -8% 0px' });
		document.querySelectorAll('.pp-head,.pp-steps li,.pp-know__row,.pp-rev,.pp-comm').forEach(function (n) { n.classList.add('pp-reveal'); io.observe(n); });
	}
	if (!reduce && window.matchMedia('(hover:hover) and (pointer:fine)').matches) {
		document.querySelectorAll('.pp-tile').forEach(function (t) {
			t.addEventListener('pointermove', function (e) { var r = t.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5; t.style.transform = 'translateY(-6px) rotate(' + (x * 4) + 'deg)'; });
			t.addEventListener('pointerleave', function () { t.style.transform = ''; });
		});
	}
})();

/* ----- Mobile header: hides while scrolling down, returns on scroll up or when the search/menu is used ----- */
(function () {
	var h = document.getElementById('pp-header'); if (!h) return;
	var last = window.scrollY, ticking = false, mq = window.matchMedia('(max-width: 899px)');
	function upd() {
		ticking = false; var y = Math.max(0, window.scrollY), dy = y - last;
		if (!mq.matches || document.body.classList.contains('pp-lock')) { h.classList.remove('is-hidden'); last = y; return; }
		if (y < 120 || dy < -6) h.classList.remove('is-hidden'); else if (dy > 8) h.classList.add('is-hidden');
		last = y;
	}
	window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(upd); } }, { passive: true });
	h.addEventListener('focusin', function () { h.classList.remove('is-hidden'); });
})();

/* ----- TikTok swipe: prev/next + progress bar ----- */
(function () {
	var r = document.querySelector('[data-pp-clips]'); if (!r) return;
	var bar = document.querySelector('[data-pp-prog]'), reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	function prog() { var max = r.scrollWidth - r.clientWidth; var vis = r.clientWidth / r.scrollWidth; if (bar) { bar.style.width = Math.max(12, vis * 100) + '%'; bar.style.marginLeft = (max > 0 ? (r.scrollLeft / max) * (100 - Math.max(12, vis * 100)) : 0) + '%'; } }
	r.addEventListener('scroll', function () { requestAnimationFrame(prog); }, { passive: true }); window.addEventListener('resize', prog); prog();
	document.addEventListener('click', function (e) { var b = e.target.closest('[data-clips]'); if (!b) return; var li = r.querySelector('li'); var step = (li ? li.getBoundingClientRect().width + 16 : 300) * 2; r.scrollBy({ left: step * parseInt(b.dataset.clips, 10), behavior: reduce ? 'auto' : 'smooth' }); });
	r.addEventListener('keydown', function (e) { if (e.target !== r) return; if (e.key === 'ArrowRight') r.scrollBy({ left: 300 }); if (e.key === 'ArrowLeft') r.scrollBy({ left: -300 }); });
})();
