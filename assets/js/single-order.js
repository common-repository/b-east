jQuery(function ($) {
	function createShipment(e) {
		e.preventDefault();

		e.target.disabled = true;

		const url = beast_create_shipment_box.ajax_url;

		const data = {
			_ajax_nonce: beast_create_shipment_box.nonce,
			action: "beast_create_shipment",
			post_id: woocommerce_admin_meta_boxes.post_id,
			_beast_num_colli: $("#_beast_num_colli").val(),
			_beast_product_code: $("#_beast_product_code").val(),
		};

		$.post(url, data).then(function () {
			window.location.reload();
		});
	}

	function init() {
		$("#woocommerce-beast-order-actions").on(
			"click",
			"button.create-shipment",
			createShipment
		);
	}

	init();
});
