<?php
/**
 * Hook into wp-admin's menu.
 *
 * @package Beast\WooCommerce
 */

namespace Beast\WooCommerce;

/**
 * Menu class.
 */
class Menu {

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Add page to menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'B-east Settings', 'b-east' ),
			__( 'B-east', 'b-east' ),
			'manage_options',
			'b-east',
			array( $this, 'options_page_html' )
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function options_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$account = Plugin::get_account_info();

		$src = plugin_dir_url( __DIR__ ) . 'assets/img/logo.png';

		?>
		<div class="wrap">
			<h1><img src="<?php echo esc_url( $src ); ?>" width="160" height="50" alt="B-east"></h1>
			<div style="display: grid;  grid-template-columns: repeat(2, 1fr);">
				<div>
				<?php settings_errors(); ?>
					<form action="options.php" method="post">
						<?php
						settings_fields( 'b-east' );
						do_settings_sections( 'b-east' );
						submit_button( __( 'Save Settings', 'b-east' ) );
						?>
					</form>
				</div>
				<div>
					<?php if ( isset( $account['name'] ) ) : ?>
						<h2>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: team name */
								__( 'Prices and terms for %s', 'b-east' ),
								$account['name']
							)
						);
						?>
						</h2>
						<hr>
						<table>
							<?php foreach ( $account['authorized_countries'] as $country => $prices ) : ?>
								<tr>
									<td colspan="2">
										<h3><?php echo esc_html( \Locale::getDisplayRegion( '-' . $country, get_locale() ) ); ?></h3>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Standard', 'b-east' ); ?></td>
									<td style="text-align: right; font-variant-numeric: tabular-nums;">&euro; <?php echo esc_html( number_format_i18n( $prices['tariff'], 2 ) ); ?></td>
								</tr>
								<?php foreach ( $prices['options'] as $option_id => $option ) : ?>
									<tr>
										<td><?php echo esc_html( $option['name'] ); ?></td>
										<td style="text-align: right; font-variant-numeric: tabular-nums; padding-left: 1rem">&plus; &euro; <?php echo esc_html( number_format_i18n( $option['surcharge'], 2 ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</table>
						<hr>
						<ol>
							<?php foreach ( $account['conditions'] as $condition ) : ?>
								<li>
									<?php
									echo wp_kses(
										$condition,
										array(
											'a' => array(
												'href'   => array(),
												'target' => array(),
											),
										)
									);
									?>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
