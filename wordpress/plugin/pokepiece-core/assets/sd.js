/* Special Delivery option: one handler for every [data-ppc-sd] checkbox (drawer, cart, checkout). */
(function () {
	'use strict';
	var busy = false;
	document.addEventListener('change', function (e) {
		var t = e.target; if (!t || !t.matches || !t.matches('[data-ppc-sd]')) return;
		var on = t.checked; if (busy) { t.checked = !on; return; } busy = true;
		var boxes = document.querySelectorAll('[data-ppc-sd]'); boxes.forEach(function (b) { b.disabled = true; });
		var fd = new FormData(); fd.append('nonce', PPC_SD.nonce); fd.append('on', on ? '1' : '0');
		fetch(PPC_SD.url, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			if (!j || !j.success) throw new Error('fail');
			boxes.forEach(function (b) { b.checked = j.data.on; });
			var ref = document.getElementById('ppc_sd_field') || document.getElementById('ppc_sd_ref_field'); if (ref) ref.hidden = !j.data.on;
			var $ = window.jQuery; if ($) { var b = $(document.body); b.trigger('wc_fragment_refresh'); b.trigger('update_checkout'); if (document.querySelector('.woocommerce-cart-form')) b.trigger('wc_update_cart'); }
		}).catch(function () { t.checked = !on; boxes.forEach(function (b) { b.checked = !on; }); window.alert('Special Delivery could not be updated. Nothing was changed, please try again.'); })
		.then(function () { busy = false; boxes.forEach(function (b) { b.disabled = false; }); });
	});
})();
