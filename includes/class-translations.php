<?php
/**
 * Loads translations.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * Undocumented class
 */
class Translations {

	/**
	 * Undocumented function
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'b-east', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
	}
}
