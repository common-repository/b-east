<?php
/**
 * Main Plugin Class.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

use WC_Order;

/**
 * Plugin Class.
 */
class Plugin {

	const META_NUM_COLLI = '_beast_num_colli';

	const META_PRODUCT_CODE = '_beast_product_code';

	/**
	 * Undocumented variable
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $version = '0.2.1';

	/**
	 * Get singleton instance of plugin.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load the plugin.
	 *
	 * @return void
	 */
	public static function load() {
		new CreateShipment();
		new Menu();
		new MetaBox();
		new OrderList();
		new Settings();
		new Translations();
	}

	/**
	 * Undocumented function
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order The Order.
	 * @return void
	 */
	public static function create_shipment( $order_id, $order ) {
		global $wp_filesystem;

		$body = array(
			'first_name'       => $order->get_shipping_first_name(),
			'last_name'        => $order->get_shipping_last_name(),
			'company'          => $order->get_shipping_company(),
			'address1'         => $order->get_shipping_address_1(),
			'address2'         => $order->get_shipping_address_2(),
			'postal_code'      => $order->get_shipping_postcode(),
			'city'             => $order->get_shipping_city(),
			'region'           => $order->get_shipping_state(),
			'country'          => strtolower( $order->get_shipping_country() ),
			'email_address'    => $order->get_billing_email(),
			'phone_number'     => $order->get_billing_phone(),
			'reference'        => "WooCommerce order #{$order->get_id()}",
			'collo'            => $order->get_meta( self::META_NUM_COLLI ) ? $order->get_meta( self::META_NUM_COLLI ) : 1,
			'source'           => 'woocommerce',
			'source_reference' => $order->get_id(),
			'product_code'     => $order->get_meta( self::META_PRODUCT_CODE ),
			'request_label'    => true,
		);

		$url      = BEAST_API_BASE_URL . '/shipments';
		$headers  = array(
			'Authorization' => 'Bearer ' . get_option( 'beast_apikey' ),
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
		);
		$body     = wp_json_encode( $body );
		$response = wp_remote_post( $url, compact( 'headers', 'body' ) );

		$beast_shipment = json_decode( wp_remote_retrieve_body( $response ) );

		$order->update_meta_data( 'b-east', $beast_shipment );
		$order->save();

		if ( ! isset( $beast_shipment->barcode ) ) {
			return;
		}

		$url      = BEAST_API_BASE_URL . '/shipments/' . $beast_shipment->id . '/label';
		$headers  = array(
			'Authorization' => 'Bearer ' . get_option( 'beast_apikey' ),
			'Accept'        => 'application/json',
		);
		$response = wp_remote_get( $url, compact( 'headers' ) );

		$upload_dir = wp_upload_dir();

		if ( ! empty( $upload_dir['basedir'] ) ) {
			$dirname = $upload_dir['basedir'] . '/b-east';
			if ( ! file_exists( $dirname ) ) {
				wp_mkdir_p( $dirname );
			}
		}

		$filename = self::label_filename_for_order( $order );

		WP_Filesystem();
		$wp_filesystem->put_contents( $dirname . '/' . $filename, wp_remote_retrieve_body( $response ) );

		return $beast_shipment->barcode;
	}

	/**
	 * Get the label URL for the order.
	 *
	 * @param  WC_Order $order The Order.
	 * @return string
	 */
	public static function label_filename_for_order( WC_Order $order ) {
		return 'beast-' . $order->get_id() . '-' . $order->get_meta( 'b-east' )->barcode . '.pdf';
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public static function get_account_info() {
		$transient = 'beast_account_info';

		$team = get_transient( $transient );
		if ( ! $team ) {
			$headers = array(
				'Authorization' => 'Bearer ' . get_option( 'beast_apikey' ),
				'Accept'        => 'application/json',
			);

			$url      = BEAST_API_BASE_URL . '/me';
			$response = wp_remote_get( $url, compact( 'headers' ) );
			$team     = json_decode( wp_remote_retrieve_body( $response ), true );

			set_transient( $transient, $team, 12 * HOUR_IN_SECONDS );
		}

		return $team;
	}
}
