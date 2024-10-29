<?php
/**
 * Create shipment AJAX handler
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * AJAX shipment creation class
 */
class CreateShipment {

	/**
	 * Initialize AJAX shipment creation
	 */
	public function __construct() {
		add_action( 'wp_ajax_beast_create_shipment', array( $this, 'ajax_create_shipment' ) );
	}

	/**
	 * Validate the incoming AJAX request and forward to label creation.
	 */
	public function ajax_create_shipment() {
		check_ajax_referer( 'beast_create_shipment' );

		if ( ! isset( $_POST['post_id'] ) ) {
			return;
		}

		$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );

		if ( ! current_user_can( 'edit_shop_order', $post_id ) ) {
			return;
		}

		$order = wc_get_order( $post_id );

		$num_colli = isset( $_POST[ Plugin::META_NUM_COLLI ] ) ? (int) $_POST[ Plugin::META_NUM_COLLI ] : null;
		if ( $num_colli ) {
			$order->update_meta_data( Plugin::META_NUM_COLLI, $num_colli );
			$order->save();
		}

		$product_id = isset( $_POST[ Plugin::META_PRODUCT_CODE ] ) ? (int) $_POST[ Plugin::META_PRODUCT_CODE ] : null;
		if ( $product_id ) {
			$order->update_meta_data( Plugin::META_PRODUCT_CODE, $product_id );
			$order->save();
		}

		Plugin::create_shipment( $order->get_id(), $order );
	}
}
