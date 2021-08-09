<?php
/**
 * Smart Wishlist Uninstall
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

/**
 * Uninstall WlFMC
 *
 * @return void
 */
function wlfmc_uninstall() {
	global $wpdb;
	/**
	 * Only remove ALL data if WLFMC_REMOVE_ALL_DATA constant is set to true in user's
	 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
	 * and to ensure only the site owner can perform this action.
	 */
	if ( defined( 'WLFMC_REMOVE_ALL_DATA' ) && true === WLFMC_REMOVE_ALL_DATA ) {

		// define local private attribute.
		$wpdb->wlfmc_items     = $wpdb->prefix . 'wlfmc_wishlist_items';
		$wpdb->wlfmc_wishlists = $wpdb->prefix . 'wlfmc_wishlists';
		$wpdb->wlfmc_offers    = $wpdb->prefix . 'wlfmc_wishlist_offers';
		// delete pages created for this plugin.
		wp_delete_post( get_option( 'wlfmc_wishlist_page_id' ), true );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'wlfmc_%' ) ); // @codingStandardsIgnoreLine.

		// remove any additional options and custom table.
		$sql = 'DROP TABLE IF EXISTS `' . $wpdb->wlfmc_items . '`';
		$wpdb->query( $sql ); // @codingStandardsIgnoreLine.
		$sql = 'DROP TABLE IF EXISTS `' . $wpdb->wlfmc_wishlists . '`';
		$wpdb->query( $sql ); // @codingStandardsIgnoreLine.
		$sql = 'DROP TABLE IF EXISTS `' . $wpdb->wlfmc_offers . '`';
		$wpdb->query( $sql ); // @codingStandardsIgnoreLine.
	}

}

if ( ! is_multisite() ) {
	wlfmc_uninstall();
} else {
	global $wpdb;
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // @codingStandardsIgnoreLine.
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog__id ) {
		switch_to_blog( $blog__id );
		wlfmc_uninstall();
	}

	switch_to_blog( $original_blog_id );
}
