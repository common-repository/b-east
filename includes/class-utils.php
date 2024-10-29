<?php
/**
 * Random helper functions.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * Undocumented class
 */
final class Utils {
	/**
	 * Checks whether WooCommerce's [HPOS] is enabled.
	 *
	 * [HPOS]: <https://woocommerce.com/document/high-performance-order-storage/> "High-Performance Order Storage"
	 *
	 * @return bool
	 */
	public static function hpos_is_enabled() {
		return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Checks whether the current screen is WooCommerce orders. Becuase of the
	 * introduction of [HPOS] can have two possible values.
	 *
	 * [HPOS]: <https://woocommerce.com/document/high-performance-order-storage/> "High-Performance Order Storage"
	 *
	 * @return bool
	 */
	public static function current_screen_is_orders() {
		$screen = get_current_screen();

		return in_array( $screen->id, array( 'woocommerce_page_wc-orders', 'edit-shop_order' ), true );
	}
}
