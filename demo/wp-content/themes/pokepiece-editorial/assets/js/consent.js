/* Cookie prompt, preferences and promo popup. First-party cookie only; no third-party requests. */
(function () {
	'use strict';
	var C = window.PPC || {}, KEY = 'pp_consent', VER = 1;
	function $(s, r) { return (r || document).querySelector(s); }
	function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
	function read() { var m = document.cookie.match(new RegExp('(?:^|; )' + KEY + '=([^;]*)')); if (!m) return null; try { var o = JSON.parse(decodeURIComponent(m[1])); return o && o.v === VER ? o : null; } catch (e) { return null; } }
	function write(o) {
		o.v = VER; o.t = Math.floor(Date.now() / 1000);
		document.cookie = KEY + '=' + encodeURIComponent(JSON.stringify(o)) + '; Max-Age=' + (60 * 60 * 24 * 180) + '; Path=/; SameSite=Lax' + (C.secure ? '; Secure' : '');
		activate(); document.dispatchEvent(new CustomEvent('pp:consent', { detail: o }));
	}
	var MAP = { preferences: 'p', analytics: 'a', marketing: 'm' };
	function has(cat) { if (cat === 'necessary') return true; var o = read(); return !!(o && o[MAP[cat]]); }
	// Scripts written as <script type="text/plain" data-pp-consent="analytics"> run only once that category is accepted.
	function activate() {
		$$('script[type="text/plain"][data-pp-consent]').forEach(function (s) {
			if (s.dataset.ppDone || !has(s.dataset.ppConsent)) return; s.dataset.ppDone = '1';
			var n = document.createElement('script'); Array.prototype.forEach.call(s.attributes, function (a) { if (a.name !== 'type' && a.name !== 'data-pp-consent') n.setAttribute(a.name, a.value); });
			n.text = s.text; s.parentNode.insertBefore(n, s.nextSibling);
		});
	}
	window.ppConsent = { get: read, has: has, open: function () { openPref(); } };

	var bar = $('#pp-cookie'), pref = $('#pp-cookie-pref');
	function showBar(on) { if (!bar) return; bar.hidden = !on; document.body.classList.toggle('pp-cookie-on', on); if (on) document.documentElement.style.setProperty('--pp-cookie-h', bar.offsetHeight + 'px'); }
	function openPref() { if (!pref || !window.ppOpenLayer) return; var o = read() || {}; $$('input[type=checkbox]:not([disabled])', pref).forEach(function (i) { i.checked = !!o[i.name]; }); window.ppOpenLayer(pref, null, { backdrop: false, focus: 'input:not([disabled])' }); }
	function decide(o) { write(o); showBar(false); if (pref && !pref.hidden && window.ppCloseLayer) window.ppCloseLayer(true); schedulePromo(); }
	if (C.prompt && bar) {
		$$('[data-pp-consent-act]').forEach(function (b) { b.addEventListener('click', function () { decide(b.dataset.ppConsentAct === 'all' ? { p: 1, a: 1, m: 1 } : { p: 0, a: 0, m: 0 }); }); });
		$$('[data-pp-cookie-open]').forEach(function (b) { b.addEventListener('click', openPref); });
		$('.pp-close', pref).addEventListener('click', function () { window.ppCloseLayer(); });
		$('form', pref).addEventListener('submit', function (e) { e.preventDefault(); var f = e.target; decide({ p: f.p.checked ? 1 : 0, a: f.a.checked ? 1 : 0, m: f.m.checked ? 1 : 0 }); });
		if (!read()) showBar(true);
	}
	activate();

	/* ---- Promo popup ---- */
	var promo = $('#pp-promo'), pr = C.promo, started = false;
	function seen() { try { var st = has('preferences') ? localStorage : sessionStorage, v = JSON.parse(st.getItem('pp_promo') || 'null'); return v && v.id === pr.id && (Date.now() - v.t) < pr.days * 864e5; } catch (e) { return false; } }
	function remember() { try { (has('preferences') ? localStorage : sessionStorage).setItem('pp_promo', JSON.stringify({ id: pr.id, t: Date.now() })); } catch (e) {} }
	function tryShow() {
		if (!promo || seen()) return;
		// never stack on another open layer, the cookie prompt, or while someone is typing
		if (document.body.classList.contains('pp-lock') || (bar && !bar.hidden) || /INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '')) { return setTimeout(tryShow, 4000); }
		window.ppOpenLayer(promo, null, { backdrop: false, focus: 'a.pp-btn, .pp-close' }); remember();
	}
	function schedulePromo() { if (started || !promo || !pr) return; if (C.prompt && !read()) return; started = true; setTimeout(tryShow, pr.delay * 1000); }
	if (promo && pr) {
		$('.pp-close', promo).addEventListener('click', function () { window.ppCloseLayer(); });
		$('[data-pp-promo-no]', promo).addEventListener('click', function () { window.ppCloseLayer(); });
		var cp = $('[data-pp-copy]', promo); if (cp) cp.addEventListener('click', function () { var t = cp.dataset.ppCopy; (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () { cp.textContent = 'Copied'; }, function () { var r = document.createRange(); r.selectNodeContents(cp.previousElementSibling); var s = getSelection(); s.removeAllRanges(); s.addRange(r); cp.textContent = 'Press copy'; }); });
		schedulePromo();
	}
})();
