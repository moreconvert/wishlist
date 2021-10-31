<?php
/**
 * Smart Wishlist Install
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Install' ) ) {
	/**
	 * Install plugin table and create the wishlist page
	 *
	 */
	class WLFMC_Install {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Install
		 */
		protected static $instance;

		/**
		 * Items table name
		 *
		 * @var string
		 * @access private
		 */
		private $_table_items;

		/**
		 * Items table name
		 *
		 * @var string
		 * @access private
		 */
		private $_table_wishlists;

		/**
		 * Email table name
		 *
		 * @var string
		 * @access private
		 */
		private $_table_offers;

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Install
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 */
		public function __construct() {
			global $wpdb;

			// define local private attribute.
			$this->_table_items     = $wpdb->prefix . 'wlfmc_wishlist_items';
			$this->_table_wishlists = $wpdb->prefix . 'wlfmc_wishlists';
			$this->_table_offers    = $wpdb->prefix . 'wlfmc_wishlist_offers';

			// add custom field to global $wpdb.
			$wpdb->wlfmc_items     = $this->_table_items;
			$wpdb->wlfmc_wishlists = $this->_table_wishlists;
			$wpdb->wlfmc_offers    = $this->_table_offers;

		}

		/**
		 * Init db structure of the plugin
		 *
		 */
		public function init() {
			$this->_add_tables();
			$this->_add_pages();

			$this->register_current_version();
		}


		/**
		 * Register current version of plugin and database sctructure
		 */
		public function register_current_version() {
			delete_option( 'wlfmc_version' );
			update_option( 'wlfmc_version', WLFMC_VERSION );

			delete_option( 'wlfmc_db_version' );
			update_option( 'wlfmc_db_version', WLFMC_DB_VERSION );
		}

		/**
		 * Check if the table of the plugin already exists.
		 *
		 * @return bool
		 */
		public function is_installed() {
			global $wpdb;
			$number_of_tables = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . "wlfmc_wishlist%" ) );

			return (bool) ( 3 == $number_of_tables );
		}

		/**
		 * Add tables for a fresh installation
		 *
		 * @return void
		 * @access private
		 *
		 * @version 1.0.1
		 */
		private function _add_tables() {
			if ( ! $this->is_installed() ) {
				$this->_add_wishlists_table();
				$this->_add_items_table();
				$this->_add_offer_table();
			}
		}

		/**
		 * Add the wishlists table to the database.
		 *
		 * @return void
		 * @access private
		 */
		private function _add_wishlists_table() {


			$sql = "CREATE TABLE {$this->_table_wishlists} (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							user_id BIGINT( 20 ) NULL DEFAULT NULL,
							session_id VARCHAR( 255 ) DEFAULT NULL,
							wishlist_slug VARCHAR( 200 ) NOT NULL,
							wishlist_name TEXT,
							wishlist_token VARCHAR( 64 ) NOT NULL UNIQUE,
							wishlist_privacy TINYINT( 1 ) NOT NULL DEFAULT 0,
							is_default TINYINT( 1 ) NOT NULL DEFAULT 0,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							expiration timestamp NULL DEFAULT NULL,
							PRIMARY KEY  ( ID ),
							KEY wishlist_slug ( wishlist_slug )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			return;
		}

		/**
		 * Add the items table to the database.
		 *
		 * @return void
		 * @access private
		 */
		private function _add_items_table() {


			$sql = "CREATE TABLE {$this->_table_items} (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							prod_id BIGINT( 20 ) NOT NULL,
							quantity INT( 11 ) NOT NULL,
							user_id BIGINT( 20 ) NULL DEFAULT NULL,
							wishlist_id BIGINT( 20 ) NULL,
							position INT( 11 ) DEFAULT 0,
							note VARCHAR (250),
							importance TINYINT( 1 ) NOT NULL DEFAULT 0,
							original_price DECIMAL( 9,3 ) NULL DEFAULT NULL,
							original_currency CHAR( 3 ) NULL DEFAULT NULL,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							on_sale tinyint NOT NULL DEFAULT 0,
							PRIMARY KEY  ( ID ),
							KEY prod_id ( prod_id )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			return;
		}

		/**
		 * Add the email table to the database.
		 *
		 * @return void
		 * @access private
		 *
		 * @since 1.0.0
		 *
		 * @version 1.0.1
		 */
		private function _add_offer_table() {


			$sql = "CREATE TABLE {$this->_table_offers} (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							user_id BIGINT( 20 ) NULL DEFAULT NULL,
							wishlist_id BIGINT( 20 ) NULL,
							has_coupon  TINYINT( 1 ) NOT NULL DEFAULT 0,
							coupon_id BIGINT( 20 ) NULL,
							product_id BIGINT( 20 ) NULL,
							email_options LONGTEXT,
							days SMALLINT( 3 ) NULL DEFAULT 0,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							datesend timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							datesent timestamp NULL  ON UPDATE CURRENT_TIMESTAMP,
							is_sent TINYINT( 1 ) NOT NULL DEFAULT 0,
							PRIMARY KEY  ( ID )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );


			return;
		}

		/**
		 * Update db structure of the plugin
		 *
		 * @param string $current_version Version from which we're updating.
		 *
		 * @since 1.0.1
		 */
		public function update( $current_version ) {
			if ( version_compare( $current_version, '1.0.1', '<' ) ) {
				$this->_update_1_0_1();
			}

			$this->register_current_version();
		}


		/**
		 * Update from 1.0.0 to 1.0.1
		 *
		 * @since 1.0.1
		 */
		private function _update_1_0_1() {
			global $wpdb;

			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->wlfmc_offers}';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->wlfmc_offers}` LIKE 'days';" ) ) {
					$wpdb->query( "ALTER TABLE {$wpdb->wlfmc_offers} ADD `days` SMALLINT( 3 ) NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->wlfmc_offers}` LIKE 'product_id';" ) ) {
					$wpdb->query( "ALTER TABLE {$wpdb->wlfmc_offers} ADD `product_id` BIGINT( 20 ) NULL;" );
				}
			}
		}

		/**
		 * Add a page "Wishlist".
		 *
		 * @return void
		 */
		private function _add_pages() {
			wc_create_page(
				sanitize_title_with_dashes( _x( 'wishlist', 'page_slug', 'wc-wlfmc-wishlist' ) ),
				'wlfmc_wishlist_page_id',
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				'<!-- wp:shortcode -->[wlfmc_wishlist]<!-- /wp:shortcode -->'
			);
		}
	}
}

/**
 * Unique access to instance of WLFMC_Install class
 *
 * @return WLFMC_Install
 */
function WLFMC_Install() {
	return WLFMC_Install::get_instance();
}
