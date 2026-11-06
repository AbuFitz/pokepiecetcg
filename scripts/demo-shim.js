/* Static design-preview shim: makes it obvious what is inert. Not part of the theme. */
(function () {
	var b = document.createElement('p');
	b.setAttribute('role', 'status');
	b.style.cssText = 'margin:0;padding:8px 16px;background:#202321;color:#f4c641;font:600 13px/1.4 Inter,Arial,sans-serif;text-align:center';
	b.textContent = 'Design preview — static snapshot with fixture data. Cart, search, filters and checkout run on the WordPress build, not here.';
	document.addEventListener('DOMContentLoaded', function () { document.body.insertBefore(b, document.body.firstChild); });
	function toast(msg) { var t = document.getElementById('pp-demo-toast'); if (!t) { t = document.createElement('p'); t.id = 'pp-demo-toast'; t.setAttribute('role', 'status'); t.style.cssText = 'position:fixed;left:12px;right:12px;bottom:16px;z-index:300;margin:0 auto;max-width:460px;background:#202321;color:#fff;border-radius:18px;padding:14px 18px;font:500 15px/1.4 Inter,Arial,sans-serif'; document.body.appendChild(t); } t.textContent = msg; clearTimeout(t._h); t._h = setTimeout(function () { t.remove(); }, 3500); }
	var msg = 'Preview only: adding to cart needs the live WordPress shop.';
	document.addEventListener('click', function (e) { if (e.target.closest('.add_to_cart_button,.single_add_to_cart_button,[data-pp-sticky-go],.pp-btn--primary[href*="add-to-cart"]')) { e.preventDefault(); e.stopImmediatePropagation(); toast(msg); } }, true);
	document.addEventListener('submit', function (e) { if (e.target.matches('form.cart,.pp-filters,.woocommerce-ordering')) { e.preventDefault(); e.stopImmediatePropagation(); toast('Preview only: this control works on the WordPress build.'); } }, true);
	document.addEventListener('change', function (e) { if (e.target.closest('.woocommerce-ordering')) { e.stopImmediatePropagation(); toast('Preview only: sorting works on the WordPress build.'); } }, true);
})();
