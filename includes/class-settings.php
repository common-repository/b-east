<?php
/**
 * Hook into WordPress' settings API.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * Settings class
 */
class Settings {
	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Register settigns and fields.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting( 'b-east', 'beast_apikey', array( 'sanitize_callback' => array( $this, 'sanitize_apikey' ) ) );

		add_settings_section(
			'b-east',
			__( 'B-east', 'b-east' ),
			array( $this, 'settings_section_callback' ),
			'b-east'
		);

		add_settings_field(
			'beast_apikey',
			__( 'API key', 'b-east' ),
			array( $this, 'apikey_callback' ),
			'b-east',
			'b-east'
		);

		add_action( 'updated_option', array( $this, 'invalidate_transient' ) );
	}

	/**
	 * Settings section callback.
	 *
	 * @return void
	 */
	public function settings_section_callback() {}

	/**
	 * API key field callback.
	 *
	 * @return void
	 */
	public function apikey_callback() {
		$options = get_option( 'beast_apikey' );
		?>
		<input type="text" size="48" name="beast_apikey" value="<?php echo esc_attr( $options ); ?>">
		<?php
	}

	/**
	 * API key field sanitizer.
	 *
	 * @param string $apikey API key to sanitize.
	 * @return string
	 */
	public function sanitize_apikey( $apikey ) {
		$headers = array(
			'Authorization' => 'Bearer ' . $apikey,
			'Accept'        => 'application/json',
		);

		$url           = BEAST_API_BASE_URL . '/me';
		$response      = wp_remote_get( $url, compact( 'headers' ) );
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 401 === $response_code ) {
			add_settings_error( 'beast_apikey', 'invalid', __( 'Invalid api key', 'b-east' ) );
		}

		return $apikey;
	}

	/**
	 * Remove account info transient on API key change.
	 *
	 * @param string $option Name of the updated option.
	 * @return void
	 */
	public function invalidate_transient( $option ) {
		if ( 'beast_apikey' === $option ) {
			delete_transient( 'beast_account_info' );
		}
	}
}
