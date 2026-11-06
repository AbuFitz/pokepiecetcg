/* Static design-preview shim: makes it obvious what is inert. Not part of the theme. */
(function () {
	function toast(msg) { var t = document.getElementById('pp-demo-toast'); if (!t) { t = document.createElement('p'); t.id = 'pp-demo-toast'; t.setAttribute('role', 'status'); t.style.cssText = 'position:fixed;left:12px;right:12px;bottom:16px;z-index:300;margin:0 auto;max-width:460px;background:#202321;color:#fff;border-radius:18px;padding:14px 18px;font:500 15px/1.4 Inter,Arial,sans-serif'; document.body.appendChild(t); } t.textContent = msg; clearTimeout(t._h); t._h = setTimeout(function () { t.remove(); }, 3500); }
	var msg = 'Preview only: adding to cart needs the live WordPress shop.';
	document.addEventListener('click', function (e) { if (e.target.closest('.add_to_cart_button,.single_add_to_cart_button,[data-pp-sticky-go],.pp-btn--primary[href*="add-to-cart"]')) { e.preventDefault(); e.stopImmediatePropagation(); toast(msg); } }, true);
	document.addEventListener('submit', function (e) { if (e.target.matches('form.cart,.pp-filters,.woocommerce-ordering')) { e.preventDefault(); e.stopImmediatePropagation(); toast('Preview only: this control works on the WordPress build.'); } }, true);
	document.addEventListener('change', function (e) { if (e.target.closest('.woocommerce-ordering')) { e.stopImmediatePropagation(); toast('Preview only: sorting works on the WordPress build.'); } }, true);
})();
