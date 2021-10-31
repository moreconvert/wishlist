<?php
/**
 * Static class that will handle all ajax calls for the list
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}// Exit if accessed directly

if ( ! class_exists( 'WLFMC_Ajax_Handler' ) ) {
	/**
	 * Woocommerce Smart Wishlist Ajax Handler
	 */
	class WLFMC_Ajax_Handler {
		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 */
		public static function init() {
			// add to wishlist.
			add_action( 'wp_ajax_add_to_wishlist', array( 'WLFMC_Ajax_Handler', 'add_to_wishlist' ) );
			add_action( 'wp_ajax_nopriv_add_to_wishlist', array( 'WLFMC_Ajax_Handler', 'add_to_wishlist' ) );

			// remove from wishlist.
			add_action( 'wp_ajax_remove_from_wishlist', array( 'WLFMC_Ajax_Handler', 'remove_from_wishlist' ) );
			add_action( 'wp_ajax_nopriv_remove_from_wishlist', array( 'WLFMC_Ajax_Handler', 'remove_from_wishlist' ) );

			// remove from wishlist (button).
			add_action( 'wp_ajax_delete_item', array( 'WLFMC_Ajax_Handler', 'delete_item' ) );
			add_action( 'wp_ajax_nopriv_delete_item', array( 'WLFMC_Ajax_Handler', 'delete_item' ) );

			// update item quantity.
			add_action( 'wp_ajax_update_item_quantity', array( 'WLFMC_Ajax_Handler', 'update_quantity' ) );
			add_action( 'wp_ajax_nopriv_update_item_quantity', array( 'WLFMC_Ajax_Handler', 'update_quantity' ) );

			// load fragments.
			add_action( 'wp_ajax_load_fragments', array( 'WLFMC_Ajax_Handler', 'load_fragments' ) );
			add_action( 'wp_ajax_nopriv_load_fragments', array( 'WLFMC_Ajax_Handler', 'load_fragments' ) );
		}

		/**
		 * Add to wishlist from ajax call
		 *
		 * @return void
		 */
		public static function add_to_wishlist() {
			$options = new MCT_Options( 'wlfmc_options' );
			try {
				WLFMC()->add();

				$return  = 'true';
				$message = $options->get_option( 'product_added_text', __( 'Product added!', 'wc-wlfmc-wishlist' ) );

			} catch ( WLFMC_Exception $e ) {
				$return  = $e->getTextualCode();
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			} catch ( Exception $e ) {
				$return  = 'error';
				$message = apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() );
			}
			$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );
			$product_id                     = isset( $_REQUEST['add_to_wishlist'] ) ? intval( $_REQUEST['add_to_wishlist'] ) : false; // phpcs:ignore WordPress.Security
			$fragments                      = isset( $_REQUEST['fragments'] ) ? wp_unslash( $_REQUEST['fragments'] ) : false;
			$wishlist_url                   = is_user_logged_in() ? wc_get_account_endpoint_url( 'wlfmc-wishlist' ) : WLFMC()->get_last_operation_url();

			$wishlists = WLFMC_Wishlist_Factory::get_wishlists();

			$wishlists_to_prompt = array();

			foreach ( $wishlists as $wishlist ) {
				$wishlists_to_prompt[] = array(
					'id'                       => $wishlist->get_id(),
					'wishlist_name'            => $wishlist->get_formatted_name(),
					'default'                  => $wishlist->is_default(),
					'add_to_this_wishlist_url' => $product_id ? add_query_arg(
						array(
							'add_to_wishlist' => $product_id,
							'wishlist_id'     => $wishlist->get_id(),
						)
					) : '',
				);
			}

			if ( in_array( $return, array( 'exists', 'true' ) ) ) {
				// search for related fragments.
				if ( ! empty( $fragments ) && ! empty( $product_id ) ) {
					foreach ( $fragments as $id => $options ) {
						if ( strpos( $id, 'add-to-wishlist-' . $product_id ) ) {
							$fragments[ $id ]['wishlist_url']      = $wishlist_url;
							$fragments[ $id ]['added_to_wishlist'] = 'true' == $return;
						}
					}
				}
			}

			wp_send_json(
				apply_filters(
					'wlfmc_ajax_add_return_params',
					array(
						'prod_id'        => $product_id,
						'result'         => $return,
						'message'        => $message,
						'fragments'      => self::refresh_fragments( $fragments ),
						'user_wishlists' => $wishlists_to_prompt,
						'wishlist_url'   => $wishlist_url,
						'click_behavior' => $click_wishlist_button_behavior,
					)
				)
			);
		}

		/**
		 * Remove from wishlist from ajax call
		 *
		 * @return void
		 */
		public static function remove_from_wishlist() {
			$fragments = isset( $_REQUEST['fragments'] ) ?  wp_unslash( $_REQUEST['fragments'] ) : false;

			try {
				WLFMC()->remove();
				$message = apply_filters( 'wlfmc_product_removed_text', __( 'Product successfully removed.', 'wc-wlfmc-wishlist' ) );
			} catch ( Exception $e ) {
				$message = $e->getMessage();
			}

			wc_add_notice( $message );

			wp_send_json(
				array(
					'fragments' => self::refresh_fragments( $fragments ),
				)
			);
		}

		/**
		 * Remove item from a wishlist
		 * Differs from remove from wishlist, since this accepts item id instead of product id
		 *
		 * @return void
		 */
		public static function delete_item() {
			$item_id   = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$fragments = isset( $_REQUEST['fragments'] ) ?  wp_unslash( $_REQUEST['fragments'] ) : false;
			$return    = array(
				'result' => false,
			);

			if ( $item_id ) {
				$item = WLFMC_Wishlist_Factory::get_wishlist_item( $item_id );

				if ( $item ) {
					$item->delete();

					$return = array(
						'result'    => true,
						'message'   => apply_filters( 'wlfmc_product_removed_text', __( 'Product successfully removed.', 'wc-wlfmc-wishlist' ) ),
						'fragments' => self::refresh_fragments( $fragments ),
					);
				}
			}

			wp_send_json( $return );
		}

		/**
		 * Update quantity of an item in wishlist
		 *
		 * @return void
		 */
		public static function update_quantity() {
			$wishlist_token = isset( $_POST['wishlist_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wishlist_token'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$product_id     = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$quantity       = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification

			if ( ! $wishlist_token || ! $product_id ) {
				die();
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_token );

			if ( ! $wishlist || ! $wishlist->current_user_can( 'update_quantity' ) ) {
				die();
			}

			$item = $wishlist->get_product( $product_id );

			if ( ! $item ) {
				die();
			}

			$item->set_quantity( $quantity );
			$item->save();

			// stops ajax call from further execution (no return value expected on answer body).
			die();
		}

		/**
		 * Generated fragments to replace in the the page
		 *
		 * @return void
		 */
		public static function load_fragments() {
			$fragments = isset( $_POST['fragments'] ) ? wp_unslash( $_POST['fragments'] ) : false;

			wp_send_json(
				array(
					'fragments' => self::refresh_fragments( $fragments ),
				)
			);
		}


		/**
		 * Generate fragments for the templates that needs to be refreshed after ajax
		 *
		 * @param array $fragments Array of fragments to refresh.
		 *
		 * @return array Array of templates to be replaced on the page
		 */
		public static function refresh_fragments( $fragments ) {
			$result = array();

			if ( ! empty( $fragments ) ) {
				foreach ( $fragments as $id => $options ) {
					$id      = sanitize_text_field( $id );
					$options = WLFMC_Frontend()->decode_fragment_options( $options );
					$item    = isset( $options['item'] ) ? $options['item'] : false;

					if ( ! $item ) {
						continue;
					}

					switch ( $item ) {
						case 'add_to_wishlist':
						case 'wishlist':
							$result[ $id ] = WLFMC_Shortcode::$item( $options );
							break;
						default:
							$result[ $id ] = apply_filters( 'wlfmc_fragment_output', '', $id, $options );
							break;
					}
				}
			}

			return $result;
		}
	}
}
WLFMC_Ajax_Handler::init();
