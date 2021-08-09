<?php
/**
 * Admin Class
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MCT_Admin' ) ) {
	/**
	 * This class handles admin for options plugin
	 */
	class MCT_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var MCT_Admin
		 */
		protected static $instance;


		/**
		 * Plugin Version
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Options
		 *
		 * @var array
		 */
		private $options;

		/**
		 * MCT_Admin constructor.
		 *
		 * @param array $args all options.
		 * @return void
		 */
		public function __construct( $args = array() ) {
			if ( $args && is_array( $args ) && ! empty( $args ) ) {
				$this->options = $args;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
			add_action( 'admin_init', array( $this, 'save_option' ) );
		}


		/**
		 * Returns single instance of the class
		 *
		 * @return MCT_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Enqueue admin style and js
		 */
		public function enqueue_admin_js() {

			if ( is_admin() ) {
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				// Add the color picker css file.
				wp_enqueue_style( 'wp-color-picker' );

				// Include WordPress Color Picker script.
				wp_enqueue_script( 'wp-color-picker' );

				wp_register_script( 'wp-color-picker-alpha', MCT_OPTION_PLUGIN_URL . '/assets/js/wp-color-picker-alpha.js', array( 'wp-color-picker' ), $this->version, true );
				wp_enqueue_script( 'wp-color-picker-alpha' );

				// Include WordPress Media Uploader.

				wp_enqueue_media();

				if ( function_exists( 'WC' ) ) {
					/* Register select2 stylesheet */
					if ( ! wp_style_is( 'select2', 'registered' ) ) {

						wp_register_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), WC()->version );
					}
					/* Register select2 script */
					if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
						if ( ! wp_script_is( 'selectWoo', 'registered' ) ) {
							wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), WC()->version, true );
						}
						wp_register_script(
							'wc-enhanced-select',
							WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js',
							array(
								'jquery',
								'selectWoo',
							),
							WC()->version,
							true
						);
						wp_localize_script(
							'wc-enhanced-select',
							'wc_enhanced_select_params',
							array(
								'i18n_no_matches'         => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
								'i18n_ajax_error'         => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_short_1'  => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_short_n'  => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_long_1'   => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
								'i18n_input_too_long_n'   => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
								'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
								'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
								'i18n_load_more'          => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
								'i18n_searching'          => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
								'ajax_url'                => admin_url( 'admin-ajax.php' ),
								'search_products_nonce'   => wp_create_nonce( 'search-products' ),
								'search_customers_nonce'  => wp_create_nonce( 'search-customers' ),
								'search_categories_nonce' => wp_create_nonce( 'search-categories' ),
							)
						);
					}
				} else {
					if ( ! wp_style_is( 'select2', 'registered' ) ) {

						wp_register_style( 'select2', MCT_OPTION_PLUGIN_URL . '/assets/css/select2.css', array(), WC()->version );
					}
					if ( ! wp_script_is( 'select2', 'registered' ) ) {

						wp_enqueue_script( 'select2', MCT_OPTION_PLUGIN_URL . '/assets/js/select2/select2.min.js', array( 'jquery' ), $this->version, true );

					}
				}

				wp_enqueue_script( 'mct-repeator', MCT_OPTION_PLUGIN_URL . '/assets/js/repeator.js', array( 'jquery' ), $this->version, true );

				wp_enqueue_script( 'mct-admin', MCT_OPTION_PLUGIN_URL . '/assets/js/option-scripts.js', array( 'jquery' ), $this->version, true );

				wp_enqueue_style( 'mct-admin', MCT_OPTION_PLUGIN_URL . '/assets/css/option-style.css', array(), $this->version );
			}
		}


		/**
		 * Save wishlist options
		 *
		 * @return void
		 */
		public function save_option() {

			if ( isset( $_POST['mct-action'] ) ) {

				if ( isset( $this->options['sections'] ) && is_array( $this->options['sections'] ) ) {

					$options       = $this->get_main_key_options();
					$saved_options = $this->get_options();

					foreach ( $this->options['sections'] as $section => $title ) {

						if ( $_POST['mct-action'] === $section && isset( $_POST[ 'mct-' . $section . '-nonce' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'mct-' . $section . '-nonce' ] ) ), 'mct-' . $section ) ) {

							$new_options = array();

							do_action( 'mct_panel_before_' . $this->options['id'] . '_update' );
							foreach ( $options[ $section ] as $name => $value ) {

								$new_options[ $value ] = isset( $_POST[ $value ] ) ? wp_unslash( $_POST[ $value ] ) : ''; // phpcs:ignore WordPress.Security

							}

							$validate = apply_filters( 'mct_options_' . $this->options['id'] . '_validate', true, $new_options );

							if ( $validate ) {
								$saved_options[ $section ] = $new_options;
								update_option( $this->options['id'], $saved_options );

							}

							$state = true === $validate ? 'saved' : $validate;

							do_action( 'mct_panel_after_' . $this->options['id'] . '_update' );

							$url = apply_filters(
								'mct_panel_redirect_' . $this->options['id'],
								add_query_arg(
									array(
										'page' => isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '',
										$state => 1,
									)
								),
								admin_url( '/' )
							);

							header( 'Location: ' . $url );
							exit;
						}
					}
				}
			}

		}


		/**
		 * Get main array options
		 * return an array with all key options
		 *
		 * @return array
		 */
		public function get_main_key_options() {
			$all_fields = array();
			if ( is_array( $this->options['options'] ) ) {
				foreach ( $this->options['options'] as $section => $value ) {
					if ( isset( $value['tabs'] ) ) {
						$section_fields = array();
						foreach ( $value['tabs'] as $tab => $fields ) {
							$section_fields = array_merge( $section_fields, array_keys( $this->options['options'][ $section ]['fields'][ $tab ] ) );
						}
					} else {
						$section_fields = array_keys( $value );
					}
					$all_fields[ $section ] = $section_fields;
				}
			}

			return $all_fields;
		}

		/**
		 * Get option from DB
		 *
		 * @return mixed
		 */
		public function get_options() {
			return get_option( $this->options['id'], array() );
		}


	}
}

