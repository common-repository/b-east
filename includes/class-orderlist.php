<?php
/**
 * Hook into WooCommerce's order list
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * Order list class.
 */
class OrderList {

	const COLUMN_NAME = 'b-east';

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'add_options_modal_to_footer' ) );

		if ( Utils::hpos_is_enabled() ) {
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_beast_column' ) );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'fill_beast_column' ), 10, 2 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_bulk_action' ), 10, 3 );
		} else {
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_beast_column' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'fill_beast_column' ), 10, 2 );
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk_action' ), 10, 3 );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! Utils::current_screen_is_orders() ) {
			return;
		}

		add_thickbox();

		wp_enqueue_script(
			'beast_order-list',
			plugin_dir_url( __DIR__ ) . 'assets/js/order-list.js',
			array( 'thickbox' ),
			Plugin::$version,
			array( 'in_footer' => true )
		);

		wp_localize_script(
			'beast_order-list',
			'beast_order_list',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'beast_order_list' ),
			)
		);
	}

	/**
	 * Add a B-east column to the order list.
	 *
	 * @param string[] $columns Columns array.
	 * @return string[]
	 */
	public function add_beast_column( $columns ) {
		$src = plugin_dir_url( __DIR__ ) . 'assets/img/logo.png';

		$columns[ static::COLUMN_NAME ] = '<img src="' . esc_url( $src ) . '" height="20" alt="B-east">';

		return $columns;
	}

	/**
	 * Fill the B-east colum in the order list.
	 *
	 * @param string        $column_name       Name of the column.
	 * @param int|\WC_Order $order_or_order_id The order.
	 * @return void
	 */
	public function fill_beast_column( $column_name, $order_or_order_id ) {
		if ( static::COLUMN_NAME !== $column_name ) {
			return;
		}

		$order = $order_or_order_id instanceof \WC_Order ? $order_or_order_id : wc_get_order( $order_or_order_id );

		$beast_shipment = $order->get_meta( 'b-east' );

		if ( isset( $beast_shipment->barcode ) ) {
			$label_url = wp_get_upload_dir()['baseurl'] . '/b-east/' . Plugin::label_filename_for_order( $order );
			$beast_url = BEAST_BASE_URL . '/shipments/' . $beast_shipment->id;

			if ( isset( $beast_shipment->carrier_barcode ) ) {
				echo esc_html( $beast_shipment->carrier ) . ' &mdash; ' . esc_html( $beast_shipment->carrier_barcode ) . PHP_EOL;
			} else {
				echo esc_html( $beast_shipment->barcode );
			}

			echo '<br>';
			echo '<a target="_blank" href="' . esc_url( $label_url ) . '">' . esc_html__( 'Label', 'b-east' ) . '</a>' . PHP_EOL;
			echo '|' . PHP_EOL;
			echo '<a target="_blank" href="' . esc_url( $beast_url ) . '">' . esc_html__( 'B-east', 'b-east' ) . '</a>' . PHP_EOL;
		} elseif ( isset( $beast_shipment->errors ) ) {
			$errors = (array) $beast_shipment->errors;
			$errors = array_merge( ...array_values( $errors ) );

			echo '<span class="dashicons dashicons-warning help-tip" data-tooltip title="' . esc_attr( join( "\n", $errors ) ) . '"></span>';
		}
	}

	/**
	 * Add B-east bulk action.
	 *
	 * @param array $actions Actions array.
	 * @return array
	 */
	public function add_bulk_actions( $actions ) {
		$actions['to_beast']      = __( 'Send to B-east', 'b-east' );
		$actions['beast_options'] = __( 'Set B-east options', 'b-east' );

		return $actions;
	}

	/**
	 * Handle B-east bulk action.
	 *
	 * @param string $sendback The redirect URL.
	 * @param string $doaction The action being taken.
	 * @param array  $items The items to take the action on. Accepts an array of IDs of posts, comments, terms, links, plugins, attachments, or users.
	 * @return string The redirect URL.
	 */
	public function handle_bulk_action( $sendback, $doaction, $items ) {
		if ( 'to_beast' === $doaction ) {
			foreach ( $items as $order_id ) {
				$order = wc_get_order( $order_id );

				$barcode = Plugin::create_shipment( $order->get_id(), $order );
			}
		} elseif ( 'beast_options' === $doaction ) {
			foreach ( $items as $order_id ) {
				$account = Plugin::get_account_info();
				$order   = wc_get_order( $order_id );

				$num_colli = isset( $_REQUEST['beast_num_colli'] ) ? (int) $_REQUEST['beast_num_colli'] : null;
				if ( $num_colli ) {
					$order->update_meta_data( Plugin::META_NUM_COLLI, $num_colli );
					$order->save();
				}

				if ( array_key_exists( mb_strtolower( $order->get_shipping_country() ), $_REQUEST['beast_product_id'] ) ) {
					$product_id = (int) $_REQUEST['beast_product_id'][ mb_strtolower( $order->get_shipping_country() ) ];

					if ( $product_id ) {
						$order->update_meta_data( Plugin::META_PRODUCT_CODE, $product_id );
						$order->save();
					}
				}
			}
		}

		return $sendback;
	}

	/**
	 * Prepare the modal contenst for the 'Set options' bulk action.
	 */
	public function add_options_modal_to_footer() {
		if ( ! Utils::current_screen_is_orders() ) {
			return;
		}

		$account = Plugin::get_account_info();
		?>
		<div id="beast-options-modal" style="display:none">
			<form>
				<table>
					<tr>
						<th scope="row">
							<label for="num-colli"><?php esc_html_e( 'Number of parcels', 'b-east' ); ?></label>
						</th>
						<td>
							<input name="beast_num_colli" id="num-colli" type="text" inputmode="numeric" pattern="\d*" value="1" style="width:100%" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<hr>
						</td>
					</tr>
					<?php foreach ( $account['authorized_countries'] as $country => $prices ) : ?>
						<tr>
							<th scope="row">
								<label for="product-<?php echo esc_attr( $country ); ?>">
									<?php echo esc_html( \Locale::getDisplayRegion( '-' . $country, get_locale() ) ); ?>
								</label>
							</th>
							<td>
								<select name="beast_product_id[<?php echo esc_attr( $country ); ?>]" id="product-<?php echo esc_attr( $country ); ?>" style="width:100%">
									<option value=""><?php esc_html_e( 'Standard', 'b-east' ); ?></option>
									<?php foreach ( $prices['options'] as $option_id => $option ) : ?>
										<option value="<?php echo esc_attr( $option_id ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<td colspan="2">
							<hr>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<button class="button primary" type="submit"><?php echo esc_html__( 'Submit' ); ?></button>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?php
	}
}
