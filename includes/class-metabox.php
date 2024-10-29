<?php
/**
 * Single order Meta Box.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

use WC_Order;

/**
 * Meta Box Class.
 */
class MetaBox {
	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Add script to the page that handles B-east's meta box.
	 */
	public function enqueue_scripts() {
		if ( ! Utils::current_screen_is_orders() ) {
			return;
		}

		wp_enqueue_script(
			'beast_page_wc-orders',
			plugin_dir_url( __DIR__ ) . 'assets/js/single-order.js',
			array( 'jquery' ),
			Plugin::$version,
			array( 'in_footer' => true )
		);

		wp_localize_script(
			'beast_page_wc-orders',
			'beast_create_shipment_box',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'beast_create_shipment' ),
			)
		);
	}

	/**
	 * Add B-east meta box to the order detail page.
	 */
	public function add_meta_box() {
		$src = plugin_dir_url( __DIR__ ) . 'assets/img/logo.png';

		add_meta_box(
			'woocommerce-beast-order-actions',
			'<img src="' . esc_url( $src ) . '" height="16" alt="B-east">',
			array( $this, 'render_meta_box' ),
			array( 'shop_order', 'woocommerce_page_wc-orders' )
		);
	}

	/**
	 * Render the B-east meta box.
	 *
	 * @param int|\WC_Order $order_id_or_order The order ID (old order) or the order object (HPOS).
	 * @return void
	 */
	public function render_meta_box( $order_id_or_order ) {
		$order = $order_id_or_order instanceof WC_Order ? $order_id_or_order : wc_get_order( $order_id_or_order );

		$account = Plugin::get_account_info();
		$options = isset( $account['authorized_countries'][ mb_strtolower( $order->get_shipping_country() ) ]['options'] )
			? $account['authorized_countries'][ mb_strtolower( $order->get_shipping_country() ) ]['options']
			: [];
		$order   = \wc_get_order( $order );

		$beast = $order->get_meta( 'b-east' );
		?>
		<?php if ( isset( $beast->barcode ) ) : ?>
			<p>
				<?php if ( isset( $beast->carrier_barcode ) ) : ?>
					<?php echo esc_html( $beast->carrier ); ?> &mdash; <?php echo esc_html( $beast->carrier_barcode ); ?>
				<?php else : ?>
					<?php echo esc_html( $beast->barcode ); ?>
				<?php endif; ?>
				<br>
				<a target="_blank" href="<?php echo esc_url( wp_get_upload_dir()['baseurl'] . '/b-east/' . Plugin::label_filename_for_order( $order ) ); ?>"><?php esc_html_e( 'Show label', 'b-east' ); ?></a>
				<br>
				<a href="<?php echo esc_url( BEAST_BASE_URL . '/shipments/' . $beast->id ); ?>" target="_blank">
					<?php esc_html_e( 'Show on B-east', 'b-east' ); ?>
				</a>
			</p>
		<?php else : ?>
			<p>
				<?php if ( isset( $beast->errors ) ) : ?>
					<?php
					$errors = (array) $beast->errors;
					$errors = array_merge( ...array_values( $errors ) );
					?>
					<?php foreach ( $errors as $error ) : ?>
						<p><?php echo esc_html( $error ); ?></p>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php
				woocommerce_wp_text_input(
					array(
						'data_type' => 'stock',
						'id'        => Plugin::META_NUM_COLLI,
						'value'     => $order->get_meta( Plugin::META_NUM_COLLI ) ? $order->get_meta( Plugin::META_NUM_COLLI ) : 1,
						'label'     => __( 'Number of parcels', 'b-east' ),
					)
				);
				?>

				<?php
				woocommerce_wp_select(
					array(
						'id'      => Plugin::META_PRODUCT_CODE,
						'value'   => $order->get_meta( Plugin::META_PRODUCT_CODE ),
						'label'   => __( 'Product', 'b-east' ),
						'options' => array_combine(
							array( '' ) + array_keys( $options ),
							array( __( 'Standard', 'b-east' ) ) + array_column( $options, 'name' ),
						),
					)
				);
				?>

				<button type="button" class="button button-primary create-shipment"><?php esc_html_e( 'Send to B-east', 'b-east' ); ?></button>
			</p>
		<?php endif ?>
		<?php
	}
}
