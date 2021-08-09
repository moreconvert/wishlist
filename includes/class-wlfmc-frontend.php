<?php
/**
 * Smart Wishlist Frontend
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Frontend' ) ) {
	/**
	 * This class handles frondend for wishlist plugin
	 */
	class WLFMC_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Frontend
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {


			// init class.
			add_action( 'init', array( $this, 'init' ), 0 );

			// scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_stuffs' ) );

			// templates.
			add_action( 'template_redirect', array( $this, 'add_nocache_headers' ) );
			add_action( 'wp_head', array( $this, 'add_noindex_header' ) );
			add_filter( 'wp_robots', array( $this, 'add_noindex_robots' ) );

			add_action( 'init', array( $this, 'add_button' ) );
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			// dashboard page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'dashboard_wishlist_link' ), 40 );
			add_action( 'init', array( $this, 'add_endpoint' ) );
			add_action(
				'woocommerce_account_wlfmc-wishlist_endpoint',
				array(
					$this,
					'wlfmc_wishlist_endpoint_content',
				)
			);

		}

		/**
		 * Initiator method.
		 *
		 * @return void
		 * @throws Exception Excetion.
		 */
		public function init() {
			// update cookie from old version to new one.
			$this->_update_cookies();
			$this->_destroy_serialized_cookies();
			$this->_convert_cookies_to_session();

			// register assets.
			$this->register_styles_and_stuffs();
		}

		/**
		 * Add wishlist link to woocommerce dashboard.
		 *
		 * @param Array $menu_links array of menu links.
		 *
		 * @return array
		 */
		public function dashboard_wishlist_link( $menu_links ) {
			$menu_links = array_slice( $menu_links, 0, 1, true ) + array( 'wlfmc-wishlist' => __( 'Wishlist', 'wc-wlfmc-wishlist' ) ) + array_slice( $menu_links, 1, null, true );

			return $menu_links;
		}

		/**
		 * Add wishlist dashboard endpoint
		 *
		 * @return void
		 */
		public function add_endpoint() {

			add_rewrite_endpoint( 'wlfmc-wishlist', EP_PAGES );

		}

		/**
		 * Show wishlist in woocommerce dashboard
		 *
		 * @return void
		 */
		public function wlfmc_wishlist_endpoint_content() {

			echo do_shortcode( '[wlfmc_wishlist]' );
		}


		/**
		 * Add the "Add to Wishlist" button. Needed to use in wp_head hook.
		 *
		 * @return void
		 */
		public function add_button() {
			$positions = apply_filters(
				'wlfmc_button_positions',
				array(
					'before_add_to_cart' => array(
						'hook'     => 'woocommerce_single_product_summary',
						'priority' => 29,
					),
					'after_add_to_cart'  => array(
						'hook'     => 'woocommerce_single_product_summary',
						'priority' => 31,
					),
					'thumbnails'         => array(
						'hook'     => 'woocommerce_product_thumbnails',
						'priority' => 21,
					),
					'summary'            => array(
						'hook'     => 'woocommerce_after_single_product_summary',
						'priority' => 11,
					),
				)
			);
			$options   = new MCT_Options( 'wlfmc_options' );

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );


			if ( ( 'users' === $who_can_see_wishlist_options && ! is_user_logged_in() ) ) {
				return;
			}
			// Add the link "Add to wishlist".
			$position = $options->get_option( 'wishlist_button_position', 'add-to-cart' );

			if ( 'shortcode' !== $position && isset( $positions[ $position ] ) ) {

				add_action(
					$positions[ $position ]['hook'],
					array(
						$this,
						'print_button',
					),
					$positions[ $position ]['priority']
				);
			}

			// check if Add to wishlist button is enabled for loop.
			$enabled_on_loop = true == $options->get_option( 'show_on_loop', true );

			if ( ! $enabled_on_loop ) {
				return;
			}

			$positions = apply_filters(
				'wlfmc_loop_positions',
				array(
					'before_image'       => array(
						'hook'     => 'woocommerce_before_shop_loop_item',
						'priority' => 5,
					),
					'before_add_to_cart' => array(
						'hook'     => 'woocommerce_after_shop_loop_item',
						'priority' => 7,
					),
					'after_add_to_cart'  => array(
						'hook'     => 'woocommerce_after_shop_loop_item',
						'priority' => 15,
					),
				)
			);

			// Add the link "Add to wishlist".
			$position = $options->get_option( 'loop_position', 'after_add_to_cart' );

			if ( 'shortcode' !== $position && isset( $positions[ $position ] ) ) {
				add_action(
					$positions[ $position ]['hook'],
					array(
						$this,
						'print_button',
					),
					$positions[ $position ]['priority']
				);
			}
		}


		/**
		 * Alter add to cart button when on wishlist page
		 *
		 * @return void
		 */
		public function alter_add_to_cart_button() {
			add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'alter_add_to_cart_url' ), 10, 2 );
		}

		/**
		 * Restore default Add to Cart button, after wishlist handling
		 *
		 * @return void
		 */
		public function restore_add_to_cart_button() {
			remove_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'alter_add_to_cart_url' ) );
		}


		/**
		 * Filter Add to Cart button url on wishlist page
		 *
		 * @param string $url Url to the Add to Cart.
		 * @param WC_Product $product Current product.
		 *
		 * @return string Filtered url
		 */
		public function alter_add_to_cart_url( $url, $product ) {
			global $wlfmc_wishlist_token;

			if ( $wlfmc_wishlist_token ) {
				$wishlist = wlfmc_get_wishlist( $wlfmc_wishlist_token );

				if ( ! $wishlist ) {
					return $url;
				}

				$wishlist_id = $wishlist->get_id();
				$item        = $wishlist->get_product( $product->get_id() );

				if ( wp_doing_ajax() ) {
					$wishlist_url = is_user_logged_in() ? wc_get_account_endpoint_url( 'wlfmc-wishlist' ) : WLFMC()->get_wishlist_url( 'view/' . $wlfmc_wishlist_token );
					$url          = add_query_arg( 'add-to-cart', $product->get_id(), $wishlist_url );
				}

				if ( $product->is_type( array(
						'simple',
						'variation'
					) ) && true == apply_filters( 'wlfmc_redirect_to_cart', true ) ) {
					$url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );
				}

				if ( ! $product->is_type( 'external' ) && true == apply_filters( 'wlfmc_remove_after_add_to_cart', true ) ) {
					$url = add_query_arg(
						array(
							'remove_from_wishlist_after_add_to_cart' => $product->get_id(),
							'wishlist_id'                            => $wishlist_id,
							'wishlist_token'                         => $wlfmc_wishlist_token,
						),
						$url
					);
				}

				if ( $item && true == apply_filters( 'wlfmc_quantity_show', true ) ) {
					$url = add_query_arg( 'quantity', $item->get_quantity(), $url );
				}
			}

			return apply_filters( 'wlfmc_add_to_cart_redirect_url', esc_url_raw( $url ), $url, $product );
		}


		/**
		 * Print "Add to Wishlist" shortcode
		 *
		 * @return void
		 */
		public function print_button() {
			/**
			 * Developers can use this filter to remove ATW button selectively from specific pages or products
			 * You can use global $product or $post to execute checks
			 */
			if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
				return;
			}

			echo do_shortcode( '[wlfmc_add_to_wishlist]' );
		}

		/**
		 * Add specific body class when the Wishlist page is opened
		 *
		 * @param array $classes Existing boy classes.
		 *
		 * @return array
		 */
		public function add_body_class( $classes ) {
			$wishlist_page_id = WLFMC()->get_wishlist_page_id();

			if ( ! empty( $wishlist_page_id ) && is_page( $wishlist_page_id ) ) {
				$classes[] = 'wlfmc-wishlist';
				$classes[] = 'woocommerce';
				$classes[] = 'woocommerce-page';
			}

			return $classes;
		}

		/**
		 * Send nocache headers on wishlist page
		 *
		 * @return void
		 */
		public function add_nocache_headers() {
			if ( ! headers_sent() && wlfmc_is_wishlist_page() ) {
				wc_nocache_headers();
			}
		}


		/**
		 * Send noindex header on Add To Wishlist url (?add_to_wishlist=12345)
		 * Deprecated since version 5.7 of WordPress.
		 *
		 * @return void
		 */
		public function add_noindex_header() {
			if ( function_exists( 'wp_robots_no_robots' ) || ! isset( $_GET['add_to_wishlist'] ) || apply_filters( 'wlfmc_skip_noindex_headers', false ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			wp_no_robots();
		}

		/**
		 * Disable search engines indexing for Add to Wishlist url.
		 * Uses "wp_robots" filter introduced in WP 5.7.
		 *
		 * @param array $robots Associative array of robots directives.
		 *
		 * @return array Filtered robots directives.
		 */
		public function add_noindex_robots( $robots ) {
			if ( ! isset( $_GET['add_to_wishlist'] ) || apply_filters( 'wlfmc_skip_noindex_headers', false ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				return $robots;
			}

			return wp_robots_no_robots( $robots );
		}


		/**
		 * Register scripts and styles required by the plugin
		 *
		 * @return void
		 */
		public function register_styles_and_stuffs() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_style( 'wlfmc-main', MC_WLFMC_URL . 'assets/css/style' . $suffix . '.css', null, WLFMC_VERSION );

			wp_register_script( 'toastr', MC_WLFMC_URL . '/assets/js/toastr' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			wp_register_script( 'jquery-popupoverlay', MC_WLFMC_URL . '/assets/js/jquery.popupoverlay' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			wp_register_script( 'wlfmc-main', MC_WLFMC_URL . 'assets/js/scripts' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			wp_localize_script( 'wlfmc-main', 'wlfmc_l10n', $this->get_localize() );

		}

		/**
		 * Enqueue styles, scripts and other stuffs needed in the <head>.
		 *
		 * @return void
		 */
		public function enqueue_styles_and_stuffs() {


			// main plugin style.
			wp_enqueue_style( 'wlfmc-main' );


			// custom style.
			$custom_css = $this->build_custom_css();

			if ( $custom_css ) {

				wp_add_inline_style( 'wlfmc-main', $custom_css );
			}

		}

		/**
		 * Enqueue plugin scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( 'toastr' );

			wp_enqueue_script( 'jquery-popupoverlay' );

			wp_enqueue_script( 'wlfmc-main' );
		}

		/**
		 * Return localize array
		 *
		 * @return array Array with variables to be localized inside js
		 */
		public function get_localize() {
			return apply_filters(
				'wlfmc_localize_script',
				array(
					'ajax_url'                  => admin_url( 'admin-ajax.php', 'relative' ),
					'redirect_to_cart'          => apply_filters( 'wlfmc_single_item_redirect_to_cart', 'yes' ),
					'multi_wishlist'            => false,
					'ajax_loader_url'           => MC_WLFMC_URL . 'assets/images/ajax-loader-alt.svg',
					'hide_add_button'           => apply_filters( 'wlfmc_hide_add_button', true ),
					'remove_from_wishlist_after_add_to_cart' => apply_filters( 'wlfmc_remove_after_add_to_cart', true ),
					'is_wishlist_responsive'    => apply_filters( 'wlfmc_is_wishlist_responsive', true ),
					'fragments_index_glue'      => apply_filters( 'wlfmc_fragments_index_glue', '.' ),
					'reload_on_found_variation' => apply_filters( 'wlfmc_reload_on_found_variation', true ),
					'is_rtl'                    => is_rtl(),
					'labels'                    => array(
						'cookie_disabled'       => __( 'We are sorry, but this feature is available only if cookies on your browser are enabled.', 'wc-wlfmc-wishlist' ),
						'added_to_cart_message' => sprintf(
							'<div class="woocommerce-notices-wrapper"><div class="woocommerce-message" role="alert">%s</div></div>',
							apply_filters( 'wlfmc_added_to_cart_message', __( 'Product added to cart successfully', 'wc-wlfmc-wishlist' ) )
						),
						'login_need'            => apply_filters(
							'wlfmc_login_need_message',
							/* translators: %s: login url */
							sprintf( __( 'to use your Wishlist: %s', 'wc-wlfmc-wishlist' ), '<br><a href="' . esc_url( wp_login_url( wc_get_page_permalink( 'myaccount' ) ) ) . '" class="needlogin-btn">' . __( 'Login right now', 'wc-wlfmc-wishlist' ) . '</a>' )
						),
					),
					'actions'                   => array(
						'add_to_wishlist_action'      => 'add_to_wishlist',
						'remove_from_wishlist_action' => 'remove_from_wishlist',
						'load_mobile_action'          => 'load_mobile',
						'delete_item_action'          => 'delete_item',
						'save_title_action'           => 'save_title',
						'save_privacy_action'         => 'save_privacy',
						'load_fragments'              => 'load_fragments',
						'update_item_quantity'        => 'update_item_quantity',
					),
				)
			);
		}

		/* === UTILS === */

		/**
		 * Generate CSS code to append to each page, to apply custom style to wishlist elements
		 *
		 * @param array $rules Array of additional rules to add to default ones.
		 *
		 * @return string Generated CSS code
		 */
		protected function build_custom_css( $rules = array() ) {
			$generated_code = '';
			$rules          = apply_filters(
				'wlfmc_custom_css_rules',
				array_merge(
					array(
						'popup_buttons'          => array(
							'selector' => '.wlfmc_btn_%d',
							'rules'    => array(
								'background'        => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => 'transparent',
								),
								'background-hover'  => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => '#f5f5f5',
									'status'  => ':hover',
								),
								'label-color'       => array(
									'rule'    => 'color: %s',
									'default' => '#333',
								),
								'label-hover-color' => array(
									'rule'    => 'color: %s',
									'default' => '#ccc',
									'status'  => ':hover',
								),
							),
							'type'     => 'repeator',
							'deps'     => array(
								'click_wishlist_button_behavior' => 'open-popup',
							),
						),
						'popup_background_color' => array(
							'selector' => '.wlfmc-popup',
							'rules'    => array(
								'rule'    => 'background-color: %s',
								'default' => '#f9f9f9',
							),
							'deps'     => array(
								'click_wishlist_button_behavior' => 'open-popup',
							),
						),
						'popup_border_color'     => array(
							'selector' => '.wlfmc-popup',
							'rules'    => array(
								'rule'    => 'border-color: %s',
								'default' => '#e2e2e2',
							),
							'deps'     => array(
								'click_wishlist_button_behavior' => 'open-popup',
							),
						),
						'icon_style'             => array(
							'selector' => '.woocommerce .wlfmc-add-button > a i',
							'rules'    => array(
								'background'       => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => 'transparent',
								),
								'background-hover' => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => '#f5f5f5',
									'status'  => ':hover',
								),
								'color'            => array(
									'rule'    => 'color: %s',
									'default' => '#333',
								),
								'color-hover'      => array(
									'rule'    => 'color: %s',
									'default' => '#ccc',
									'status'  => ':hover',
								),
								'border'           => array(
									'rule'    => 'border-color: %s',
									'default' => '#333',
								),
								'border-hover'     => array(
									'rule'    => 'border-color: %s',
									'default' => '#ccc',
									'status'  => ':hover',
								),
							),
							'deps'     => array(),
						),
						'text_style'             => array(
							'selector' => '.woocommerce .wlfmc-add-button > a',
							'rules'    => array(
								'background'       => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => 'transparent',
								),
								'background-hover' => array(
									'rule'    => 'background-color: %1$s; background: %1$s',
									'default' => '#f5f5f5',
									'status'  => ':hover',
								),
								'color'            => array(
									'rule'    => 'color: %s',
									'default' => '#333',
								),
								'color-hover'      => array(
									'rule'    => 'color: %s',
									'default' => '#ccc',
									'status'  => ':hover',
								),
								'border'           => array(
									'rule'    => 'border-color: %s',
									'default' => '#333',
								),
								'border-hover'     => array(
									'rule'    => 'border-color: %s',
									'default' => '#ccc',
									'status'  => ':hover',
								),
							),
							'deps'     => array(),
						),
						'seperator_color'        =>
							array(
								'selector' => '.woocommerce .wlfmc-add-button > a.have-sep span',
								'rules'    => array(
									'rule'    => 'background-color: %s',
									'default' => '#ccc',
									'status'  => ':before',
								),
								'deps'     => array(),
							),
					),
					$rules
				)
			);

			if ( empty( $rules ) ) {
				return $generated_code;
			}
			$options                        = new MCT_Options( 'wlfmc_options' );
			$button_theme                   = $options->get_option( 'button_theme', true );
			$loop_position                  = $options->get_option( 'loop_position', 'after_add_to_cart' );
			$single_position                = $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
			$is_single                      = wlfmc_is_single();
			$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );
			$seperate_icon_and_text         = $options->get_option( 'seperate_icon_and_text', false );
			$button_type                    = $options->get_option( 'button_type', 'icon' );

			if ( true != $seperate_icon_and_text || 'both' != $button_type ) {
				if ( isset( $rules['seperator_color'] ) ) {
					unset( $rules['seperator_color'] );
				}
			}

			if ( true == $button_theme || ( $loop_position === 'before_image' && ! $is_single ) || ( $single_position === 'thumbnails' && $is_single ) ) {

				if ( isset( $rules['icon_style'] ) ) {
					unset( $rules['icon_style'] );
				}
				if ( isset( $rules['text_style'] ) ) {
					unset( $rules['text_style'] );
				}
			}
			if ( 'open-popup' != $click_wishlist_button_behavior ) {
				if ( isset( $rules['popup_buttons'] ) ) {
					unset( $rules['popup_buttons'] );
				}
				if ( isset( $rules['popup_background_color'] ) ) {
					unset( $rules['popup_background_color'] );
				}
				if ( isset( $rules['popup_border_color'] ) ) {
					unset( $rules['popup_border_color'] );
				}
			}
			// retrieve dependencies.
			$deps_list    = wp_list_pluck( $rules, 'deps' );
			$dependencies = array();

			if ( ! empty( $deps_list ) ) {
				foreach ( $deps_list as $rule => $deps ) {
					if ( is_array( $deps ) && ! empty( $deps ) ) {
						foreach ( $deps as $dep_rule => $dep_value ) {
							if ( ! isset( $dependencies[ $dep_rule ] ) ) {
								$dependencies[ $dep_rule ] = $options->get_option( $dep_rule );
							}
						}
					}
				}
			}

			foreach ( $rules as $id => $rule ) {
				// check dependencies first.
				if ( ! empty( $rule['deps'] ) ) {
					foreach ( $rule['deps'] as $dep_rule => $dep_value ) {
						if ( ! isset( $dependencies[ $dep_rule ] ) || $dependencies[ $dep_rule ] != $dep_value ) {
							continue 2;
						}
					}
				}

				// retrieve values from db.
				$values     = $options->get_option( $id );
				$new_rules  = array();
				$rules_code = '';

				if ( isset( $rule['rules']['rule'] ) ) {
					// if we have a single-valued option, just search for the rule to apply.
					$status = isset( $rule['rules']['status'] ) ? $rule['rules']['status'] : '';

					if ( ! isset( $new_rules[ $status ] ) ) {
						$new_rules[ $status ] = array();
					}

					$new_rules[ $status ][] = $this->build_css_rule( $rule['rules']['rule'], $values, $rule['rules']['default'] );
				} elseif ( isset( $rule['type'] ) && 'repeator' === $rule['type'] ) {
					// if we have a repeator field cycle through rules, and generate CSS code.
					if ( $values && is_array( $values ) && ! empty( $values ) ) {
						foreach ( $values as $k => $row ) {
							foreach ( $rule['rules'] as $property => $css ) {
								$status = isset( $css['status'] ) ? $css['status'] : '';

								if ( ! isset( $new_rules[ $status ] ) ) {
									$new_rules[ $status ] = array();
								}

								$new_rules[ $k ][ $status ][] = $this->build_css_rule( $css['rule'], isset( $values[ $k ][ $property ] ) ? $values[ $k ][ $property ] : false, $css['default'] );
							}
						}
					}
				} else {
					// otherwise cycle through rules, and generate CSS code.
					foreach ( $rule['rules'] as $property => $css ) {
						$status = isset( $css['status'] ) ? $css['status'] : '';

						if ( ! isset( $new_rules[ $status ] ) ) {
							$new_rules[ $status ] = array();
						}

						$new_rules[ $status ][] = $this->build_css_rule( $css['rule'], isset( $values[ $property ] ) ? $values[ $property ] : false, $css['default'] );
					}
				}


				// if code was generated, prepend selector.
				if ( ! empty( $new_rules ) ) {
					if ( isset( $rule['type'] ) && 'repeator' === $rule['type'] ) {
						foreach ( $new_rules as $k => $row ) {

							$selector = sprintf( $rule['selector'], $k );
							foreach ( $row as $status => $rules ) {
								//$selector = $rule['selector'];
								if ( ! empty( $status ) ) {
									$updated_selector = array();
									$split_selectors  = explode( ',', $selector );

									foreach ( $split_selectors as $split_selector ) {
										$updated_selector[] = $split_selector . $status;
									}

									$selector = implode( ',', $updated_selector );
								}

								$rules_code .= $selector . '{' . implode( '', $rules ) . '}';
							}
						}
					} else {
						foreach ( $new_rules as $status => $rules ) {
							$selector = $rule['selector'];

							if ( ! empty( $status ) ) {
								$updated_selector = array();
								$split_selectors  = explode( ',', $rule['selector'] );

								foreach ( $split_selectors as $split_selector ) {
									$updated_selector[] = $split_selector . $status;
								}

								$selector = implode( ',', $updated_selector );
							}

							$rules_code .= $selector . '{' . implode( '', $rules ) . '}';
						}
					}
				}

				// append new rule to generated CSS.
				$generated_code .= $rules_code;
			}

			return $generated_code;
		}

		/**
		 * Generate each single CSS rule that will be included in custom plugin CSS
		 *
		 * @param string $rule Rule to use; placeholders may be applied to be replaced with value {@see sprintf}.
		 * @param string $value Value to inject inside rule, replacing placeholders.
		 * @param string $default Default value, to be used instead of value when it is empty.
		 *
		 * @return string Formatted CSS rule
		 */
		protected function build_css_rule( $rule, $value, $default = '' ) {
			$value = ( '0' === $value || ( ! empty( $value ) && ! is_array( $value ) ) ) ? $value : $default;

			return sprintf( rtrim( $rule, ';' ) . ';', $value );
		}

		/**
		 * Format options that will sent through AJAX calls to refresh arguments
		 *
		 * @param array  $options  Array of options.
		 * @param string $context  Widget/Shortcode that will use the options.
		 *
		 * @return array Array of formatted options
		 */
		public function format_fragment_options( $options, $context = '' ) {
			// removes unusable values, and changes options common for all fragments.
			if ( ! empty( $options ) ) {
				foreach ( $options as $id => $value ) {
					if ( is_object( $value ) || is_array( $value ) ) {
						// remove item if type is not supported.
						unset( $options[ $id ] );
					} elseif ( 'ajax_loading' === $id ) {
						$options['ajax_loading'] = false;
					}
				}
			}

			// applies context specific changes.
			if ( ! empty( $context ) ) {
				$options['item'] = $context;

				switch ( $context ) {
					case 'add_to_wishlist':
						unset( $options['template_part'] );
						unset( $options['label'] );
						unset( $options['exists'] );
						unset( $options['icon'] );
						unset( $options['link_classes'] );
						unset( $options['link_popup_classes'] );
						unset( $options['container_classes'] );
						unset( $options['found_in_list'] );
						unset( $options['found_item'] );
						unset( $options['popup_title'] );
						unset( $options['wishlist_url'] );
						break;
				}
			}

			return $options;
		}

		/**
		 * Decode options that comes from the fragment
		 *
		 * @param array $options Options for the fragments.
		 *
		 * @return array Filtered options for the fragment
		 */
		public function decode_fragment_options( $options ) {
			if ( ! empty( $options ) ) {
				foreach ( $options as $id => $value ) {
					if ( 'true' == $value ) {
						$options[ $id ] = true;
					} elseif ( 'false' == $value ) {
						$options[ $id ] = false;
					} else {
						$options[ $id ] = sanitize_text_field( wp_unslash( $value ) );
					}
				}
			}

			return $options;
		}


		/**
		 * Destroy serialize cookies, to prevent major vulnerability
		 *
		 * @return void
		 */
		protected function _destroy_serialized_cookies() {
			$name = 'wlfmc_products';

			if ( isset( $_COOKIE[ $name ] ) && is_serialized( stripslashes( $_COOKIE[ $name ] ) ) ) {
				$_COOKIE[ $name ] = wp_json_encode( array() );
				wlfmc_destroycookie( $name );
			}
		}

		/**
		 * Update old wishlist cookies
		 *
		 * @return void
		 */
		protected function _update_cookies() {
			$cookie     = wlfmc_getcookie( 'wlfmc_products' );
			$new_cookie = array();

			if ( ! empty( $cookie ) ) {
				foreach ( $cookie as $item ) {
					if ( ! isset( $item['add-to-wishlist'] ) ) {
						return;
					}

					$new_cookie[] = array(
						'prod_id'     => $item['add-to-wishlist'],
						'quantity'    => isset( $item['quantity'] ) ? $item['quantity'] : 1,
						'wishlist_id' => false,
					);
				}

				wlfmc_setcookie( 'wlfmc_products', $new_cookie );
			}
		}

		/**
		 * Convert wishlist stored into cookies into.
		 *
		 * @return bool|void
		 * @throws Exception  When not able to load default wishlist or Data Store class.
		 */
		protected function _convert_cookies_to_session() {
			$cookie = wlfmc_getcookie( 'wlfmc_products' );

			if ( ! empty( $cookie ) ) {

				$default_list = WLFMC_Wishlist_Factory::get_default_wishlist();

				if ( ! $default_list ) {
					return false;
				}

				foreach ( $cookie as $item ) {
					if ( $default_list->has_product( $item['prod_id'] ) ) {
						continue;
					}

					$new_item = new WLFMC_Wishlist_Item();

					$new_item->set_product_id( $item['prod_id'] );
					$new_item->set_quantity( $item['quantity'] );

					if ( isset( $item['dateadded'] ) ) {
						$new_item->set_date_added( $item['dateadded'] );
					}

					$default_list->add_item( $new_item );
				}

				$default_list->save();

				wlfmc_destroycookie( 'wlfmc_products' );
			}
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Frontend
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
 * Unique access to instance of WLFMC_Frontend class
 *
 * @return WLFMC_Frontend
 */
function WLFMC_Frontend() {
	return WLFMC_Frontend::get_instance();
}
