<?php
/**
 * Smart Wishlist Cron
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Cron' ) ) {
	/**
	 * This class handles cron for wishlist plugin
	 */
	class WLFMC_Cron {
		/**
		 * Array of events to schedule
		 *
		 * @var array
		 */
		protected $_crons = array();

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Cron
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'schedule' ) );
		}

		/**
		 * Returns registered crons
		 *
		 * @return array Array of registered crons ans callbacks
		 */
		public function get_crons() {
			if ( empty( $this->_crons ) ) {
				$this->_crons = array(
					'wlfmc_wishlist_delete_expired_wishlists' =>
						array(
							'schedule' => 'daily',
							'callback' => array( $this, 'delete_expired_wishlists' ),
						),
					'wlfmc_send_offer_emails'      =>
						array(
							'schedule' => 'hourly',
							'callback' => array( $this, 'send_offer_emails' ),
						),
					'wlfmc_delete_expired_coupons' =>
						array(
							'schedule' => 'hourly',
							'callback' => array( $this, 'delete_expired_coupons' ),
						),
				);
			}

			return apply_filters( 'wlfmc_wishlist_crons', $this->_crons );
		}

		/**
		 * Schedule events not scheduled yet; register callbacks for each event
		 *
		 * @return void
		 */
		public function schedule() {
			$crons = $this->get_crons();

			if ( ! empty( $crons ) ) {
				foreach ( $crons as $hook => $data ) {

					add_action( $hook, $data['callback'] );

					if ( ! wp_next_scheduled( $hook ) ) {
						wp_schedule_event( time() + MINUTE_IN_SECONDS, $data['schedule'], $hook );
					}
				}
			}
		}

		/**
		 * Delete expired session wishlist
		 *
		 * @return void
		 */
		public function delete_expired_wishlists() {
			try {
				WC_Data_Store::load( 'wlfmc-wishlist' )->delete_expired();
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Delete expired coupons
		 *
		 * @return void
		 */
		public function delete_expired_coupons() {
			try {
				WLFMC_Offer_Emails()->delete_expired_coupons();
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Send offer emails.
		 */
		public function send_offer_emails() {

			try {
				$execution_limit = apply_filters( 'wlfmc_offer_email_execution_limit', 20 );
				$unsubscribed    = get_option( 'wlfmc_offer_email_unsubscribed_users', array() );

				$queue = WLFMC_Offer_Emails()->get_email_queue( $execution_limit );
				if ( ! empty( $queue ) ) {
					foreach ( $queue as $item_id => $item ) {
						$user = get_user_by( 'id', $item->user_id );

						if ( ! $user || in_array( $user->user_email, $unsubscribed ) ) {
							continue;
						}

						do_action( 'wlfmc_send_offer_mail', $item );


					}
				}
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Cron
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of WLFMC_Cron class
 *
 * @return WLFMC_Cron
 */
function WLFMC_Cron() {
	return WLFMC_Cron::get_instance();
}
