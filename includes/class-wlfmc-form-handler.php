<?php
/**
 * Static class that will handle all form submission from customer
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Form_Handler' ) ) {
	/**
	 * WooCommerce Smart Wishlist Form Handler
	 */
	class WLFMC_Form_Handler {
		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 */
		public static function init() {
			/**
			 * This check was added to prevent bots from accidentaly executing wishlist code
			 */
			if ( ! self::process_form_handling() ) {
				return;
			}

			// add to wishlist when js is disabled.
			add_action( 'init', array( 'WLFMC_Form_Handler', 'add_to_wishlist' ) );

			// remove from wishlist when js is disabled.
			add_action( 'init', array( 'WLFMC_Form_Handler', 'remove_from_wishlist' ) );

			// remove from wishlist after add to cart.
			add_action(
				'woocommerce_add_to_cart',
				array(
					'WLFMC_Form_Handler',
					'remove_from_wishlist_after_add_to_cart',
				)
			);

			// these actions manage cart, and needs to hooked to wp_loaded.
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'apply_bulk_actions' ), 15 );
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'add_all_to_cart' ), 15 );


		}

		/**
		 * Return true if system can process request; false otherwise
		 *
		 * @return bool
		 */
		public static function process_form_handling() {
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : false;

			if ( $user_agent && apply_filters( 'wlfmc_block_user_agent', preg_match( '/bot|crawl|slurp|spider|wordpress/i', $user_agent ), $user_agent ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Apply bulk actions to wishlist items
		 *
		 * @return void
		 */
		public static function apply_bulk_actions() {


			if ( ! isset( $_POST['wlfmc_edit_wishlist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlfmc_edit_wishlist'] ) ), 'wlfmc_edit_wishlist_action' ) || ( ! isset( $_POST['add_selected_to_cart'] ) && ! isset( $_POST['apply_bulk_actions'] ) ) || empty( $_POST['items'] ) ) {
				return;
			}

			$wishlist_id = isset( $_POST['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wishlist_id'] ) ) : false;
			$action      = isset( $_POST['bulk_actions'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_actions'] ) ) : false;
			$items       = isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : false; // phpcs:ignore

			if ( isset( $_POST['add_selected_to_cart'] ) ) {
				$action = 'add_to_cart';
			}

			if ( ! $wishlist_id || ! $action ) {
				return;
			}

			if ( empty( $items ) ) {
				wc_add_notice( __( 'You have to select at least one product', 'wc-wlfmc-wishlist' ), 'error' );
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$remove_after_add_to_cart = apply_filters( 'wlfmc_remove_after_add_to_cart', true );
			$redirect_to_cart         = apply_filters( 'wlfmc_redirect_to_cart', true );
			$processed                = array();
			$result                   = false;

			foreach ( $items as $item_id => $prop ) {
				if ( empty( $prop['cb'] ) ) {
					continue;
				}

				$item = $wishlist->get_product( $item_id );

				if ( ! $item ) {
					continue;
				}

				switch ( $action ) {
					case 'add_to_cart':
						try {
							$product = $item->get_product();

							if ( $product && $product->is_type( 'variable' ) ) {
								// translators: 1. Product title.
								wc_add_notice( apply_filters( 'wlfmc_add_all_to_cart_error_message_for_variable', sprintf( __( 'Error, you cannot add "%s" to the cart if you don\'t select a variation first', 'wc-wlfmc-wishlist' ), $product->get_title() ), $product ), 'error' );
								continue 2;
							}

							$result = (bool) WC()->cart->add_to_cart( $item->get_product_id(), $item->get_quantity() );

							if ( ! $remove_after_add_to_cart ) {
								break;
							}
						} catch ( Exception $e ) {
							continue 2;
						}
						break;
					// break only happens we don't need to remove item after add to cart.
					case 'delete':
						$result = $item->delete();
						break;
					default:
						// maybe customer wants to move items to another list.
						$destination_wishlist = wlfmc_get_wishlist( $action );

						if ( ! $destination_wishlist ) {
							continue 2;
						}

						$item->set_wishlist_id( $destination_wishlist->get_id() );
						$item->set_date_added( current_time( 'mysql' ) );
						$result = $item->save();
				}

				if ( $result ) {
					$processed[] = $item;
				}
			}

			if ( ! empty( $processed ) ) {
				switch ( $action ) {
					case 'add_to_cart':
						$message = __( 'The items have been correctly added to the cart', 'wc-wlfmc-wishlist' );
						break;
					case 'delete':
						$message = __( 'The items have been correctly removed', 'wc-wlfmc-wishlist' );
						break;
					default:
						// translators: 1. Destination wishlist name.
						$message = sprintf( __( 'The items have been correctly moved to %s', 'wc-wlfmc-wishlist' ), $destination_wishlist->get_formatted_name() );
				}

				wc_add_notice( $message, 'success' );
			} else {
				wc_add_notice( __( 'An error occurred while processing this action', 'wc-wlfmc-wishlist' ), 'error' );
			}

			$cart_url     = wc_get_cart_url();
			$redirect_url = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : $wishlist->get_url();
			$redirect_url = ( 'add_to_cart' === $action && $redirect_to_cart ) ? $cart_url : $redirect_url;

			wp_redirect( $redirect_url );
			die();
		}

		/**
		 * Add all items of a wishlist to cart
		 *
		 * @return void
		 */
		public static function add_all_to_cart() {

			/*if ( ! isset( $_POST['wlfmc_edit_wishlist'] ) ||
			     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlfmc_edit_wishlist'] ) ), 'wlfmc_edit_wishlist_action' ) || ! isset( $_REQUEST['add_all_to_cart'] ) ) {
				return;
			}*/

			if ( ! isset( $_REQUEST['add_all_to_cart'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$wishlist_id = isset( $_REQUEST['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wishlist_id'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$wishlists   = array();

			if ( $wishlist_id ) {
				$wishlist = wlfmc_get_wishlist( $wishlist_id );

				if ( ! $wishlist ) {
					return;
				}

				$wishlists[] = $wishlist;
			} else {
				$wishlists = WLFMC()->get_current_user_wishlists();
			}

			$remove_after_add_to_cart = apply_filters( 'wlfmc_remove_after_add_to_cart', true );
			$redirect_to_cart         = apply_filters( 'wlfmc_redirect_to_cart', true );

			$processed = array();

			remove_action(
				'woocommerce_add_to_cart',
				array(
					'WLFMC_Form_Handler',
					'remove_from_wishlist_after_add_to_cart',
				)
			);

			do_action( 'wlfmc_before_add_all_to_cart_from_wishlist', $wishlists );

			if ( apply_filters( 'wlfmc_add_all_to_cart_from_wishlist', ! empty( $wishlists ) ) ) {
				foreach ( $wishlists as $wishlist ) {
					if ( $wishlist->has_items() ) {
						foreach ( $wishlist->get_items() as $item ) {

							$product = wc_get_product( $item->get_product_id() );

							if ( $product && $product->is_type( 'variable' ) ) {
								// translators: 1. Product title.
								wc_add_notice( apply_filters( 'wlfmc_add_all_to_cart_error_message_for_variable', sprintf( __( 'Error, you cannot add "%s" to the cart if you don\'t select a variation first', 'wc-wlfmc-wishlist' ), $product->get_title() ), $product ), 'error' );
								continue;
							}

							try {
								$result = (bool) WC()->cart->add_to_cart( $item->get_product_id(), $item->get_quantity() );

								if ( $result ) {
									$processed[] = $item;

									if ( $remove_after_add_to_cart ) {
										$item->delete();
									}
								}
							} catch ( Exception $e ) {
								continue;
							}
						}
					}
				}
			}

			if ( ! empty( $processed ) ) {
				wc_add_notice( __( 'Items correctly added to the cart', 'wc-wlfmc-wishlist' ), 'success' );
			} else {
				wc_add_notice( apply_filters( 'wlfmc_add_all_to_cart_error_message', __( 'An error occurred while adding the items to the cart; please, try again later.', 'wc-wlfmc-wishlist' ) ), 'error' );
			}

			$cart_url     = wc_get_cart_url();
			$redirect_url = $wishlist_id ? $wishlist->get_url() : remove_query_arg( array( 'add_all_to_cart' ) );
			$redirect_url = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : $redirect_url; // phpcs:ignore WordPress.Security.NonceVerification
			$redirect_url = $redirect_to_cart ? $cart_url : $redirect_url;

			wp_redirect( $redirect_url );
			die;
		}


		/**
		 * Adds a product to wishlist when js is disabled
		 *
		 * @return void
		 */
		public static function add_to_wishlist() {
			// add item to wishlist when javascript is not enabled.
			if ( isset( $_GET['add_to_wishlist'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				try {
					WLFMC()->add();

					$options                        = new MCT_Options( 'wlfmc_options' );
					$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );

					wc_add_notice( $options->get_option( 'product_added_text', __( 'Product added!', 'wc-wlfmc-wishlist' ) ), 'success' );

					if ( 'add-redirect' === $click_wishlist_button_behavior ) {
						$wishlist_url = is_user_logged_in() ? wc_get_account_endpoint_url( 'wlfmc-wishlist' ) : WLFMC()->get_last_operation_url();
						wp_redirect( $wishlist_url );
						die();
					}
				} catch ( Exception $e ) {
					wc_add_notice( apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() ), 'error' );
				}
			}
		}

		/**
		 * Removes from wishlist when js is disabled
		 *
		 * @return void
		 */
		public static function remove_from_wishlist() {
			// remove item from wishlist when javascript is not enabled.
			if ( isset( $_GET['remove_from_wishlist'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				try {
					WLFMC()->remove();
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Remove from wishlist after adding to cart
		 *
		 * @return void
		 */
		public static function remove_from_wishlist_after_add_to_cart() {
			if ( false == apply_filters( 'wlfmc_remove_after_add_to_cart', true ) ) {
				return;
			}

			$args = array();
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_REQUEST['remove_from_wishlist_after_add_to_cart'] ) ) {

				$args['remove_from_wishlist'] = intval( $_REQUEST['remove_from_wishlist_after_add_to_cart'] );

				if ( isset( $_REQUEST['wishlist_id'] ) ) {
					$args['wishlist_id'] = sanitize_text_field( wp_unslash( $_REQUEST['wishlist_id'] ) );
				}
			} elseif ( wlfmc_is_wishlist() && isset( $_REQUEST['add-to-cart'] ) ) {
				$args['remove_from_wishlist'] = intval( $_REQUEST['add-to-cart'] );

				if ( isset( $_REQUEST['wishlist_id'] ) ) {
					$args['wishlist_id'] = sanitize_text_field( wp_unslash( $_REQUEST['wishlist_id'] ) );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification
			if ( ! empty( $args['wishlist_id'] ) ) {

				try {
					WLFMC()->remove( $args );
				} catch ( Exception $e ) {
					// we were unable to remove item from the wishlist; no follow up is provided.
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

	}
}

WLFMC_Form_Handler::init();
