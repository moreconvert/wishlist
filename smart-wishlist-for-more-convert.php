<?php
/**
 * Plugin Name: Smart Wishlist For More Convert
 * Plugin URI: http://moreconvert.com/smart-wishlist-for-more-convert
 * Description: With the help of MC Smart wishlist plugin, the users of your website will be able to add their favorite products to the wishlist. Then you can persuade them to buy products in their wishlist through the magic of email automation and using techniques like FOMO.
 * Version: 1.0.0
 * Author: MoreConvert
 * Author URI: https://moreconvert.com
 * Text Domain: wc-wlfmc-wishlist
 * Domain Path: /languages/
 * WC requires at least: 4.2
 * WC tested up to: 5.4.1
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

/**
 * Copyright 2020  Your Inspiration Solutions (email : info@moreconvert.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! defined( 'MC_WLFMC_URL' ) ) {
	define( 'MC_WLFMC_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MC_WLFMC_DIR' ) ) {
	define( 'MC_WLFMC_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MC_WLFMC_INC' ) ) {
	define( 'MC_WLFMC_INC', MC_WLFMC_DIR . 'includes/' );
}


add_action( 'plugins_loaded', 'wlfmc_wishlist_install', 20 );
register_activation_hook( __FILE__, 'wlfmc_activation_function' );

/***
 * Plugin install
 *
 * @return void
 */
function wlfmc_wishlist_install() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wlfmc_install_woocommerce_admin_notice' );
	} else {
		load_plugin_textdomain( 'wc-wlfmc-wishlist', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Load required classes and functions.
		require_once MC_WLFMC_INC . 'data-stores/class-wlfmc-wishlist-data-store.php';
		require_once MC_WLFMC_INC . 'data-stores/class-wlfmc-wishlist-item-data-store.php';
		require_once MC_WLFMC_INC . 'emails/class-wlfmc-offer-emails.php';
		require_once MC_WLFMC_INC . 'functions.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-exception.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-form-handler.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-ajax-handler.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-session.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-cron.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist-item.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-wishlist-factory.php';
		require_once MC_WLFMC_INC . 'class-wlfmc.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-frontend.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-install.php';
		require_once MC_WLFMC_INC . 'class-wlfmc-shortcode.php';
		require_once MC_WLFMC_DIR . 'options/init.php';

		mct_option_plugin_loader( MC_WLFMC_DIR );

		if ( is_admin() ) {
			require_once MC_WLFMC_INC . 'class-wlfmc-admin.php';
		}

		// Let's start!

		WLFMC();

	}
}

/**
 * Shows admin notice when plugin is activated without WooCommerce
 *
 * @return void
 */
function wlfmc_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php echo esc_html( 'Smart WooCommerce Wishlist For More Convert' . __( 'is enabled but not effective. It requires WooCommerce to work.', 'wc-wlfmc-wishlist' ) ); ?></p>
	</div>
	<?php
}

/**
 * Plugin flush rewrite on activate.
 *
 * @return void
 */
function wlfmc_activation_function() {
	flush_rewrite_rules();
}

if ( ! function_exists( 'log_me' ) ) {
	/**
	 * Manual Log
	 *
	 * @param Array|String|Object $message A message for log.
	 */
	function log_me( $message ) {

		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( print_r( $message, true ) ); // @codingStandardsIgnoreLine.
		} else {
			error_log( $message ); // @codingStandardsIgnoreLine.
		}

	}
}

