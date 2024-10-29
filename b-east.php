<?php
/**
 * Plugin Name:      B-east for WooCommerce
 * Plugin URI:       https://app.b-east.nl/login
 * Description:      Integrate B-east shipping method with WooCommerce.
 * Version:          0.2.1
 * Author:           B-east Netherlands B.V.
 * License:          GPLv2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      b-east
 * Domain Path:      /languages
 * Requires Plugins: woocommerce
 *
 * @package         beast
 */

namespace Beast\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

	add_action(
		'plugins_loaded',
		function () {
			if ( file_exists( $path = __DIR__ . '/.env.php' ) ) {
				require $path;
			} else {
				define( 'Beast\WooCommerce\BEAST_BASE_URL', 'https://app.b-east.nl' );
				define( 'Beast\WooCommerce\BEAST_API_BASE_URL', 'https://app.b-east.nl/api' );
			}

			Plugin::load();
		}
	);
}
