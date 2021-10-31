<?php
/**
 * Smart Wishlist Elementor Comability
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Plugin;

if ( ! class_exists( 'WLFMC_Elementor' ) ) {
	/**
	 * WooCommerce Wishlist Elementor Main
	 */
	class WLFMC_Elementor {
		/**
		 * Current Dir
		 *
		 * @var string $dir
		 */
		private $dir;

        /**
		 * Single instance of the class
		 *
		 * @var WLFMC_Elementor
		 */
		protected static $instance;


        /**
		 * Returns single instance of the class
		 *
		 * @access public
		 * @since 1.0.1
		 *
		 * @return WLFMC_Elementor
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


        /**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->dir = __DIR__;

			add_action( 'elementor/init', [ $this, 'setupCategories' ], 0 );

			add_action( 'elementor/widgets/widgets_registered', [ $this, 'registerWidgets' ], 0 );

			add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueueStyle' ], 0 );
        }


		 /**
		 * Register Widgets
		 *
		 * @access public
		 * @since 1.0.1
		 *
		 * @since 1.0.1
		 * @return void
		 */
		public function registerWidgets() {
			// Widgets
			$build_widgets_filename = [
				'add-to-wishlist',
				'wishlist',
				'wishlist-link',
			];

			foreach ( $build_widgets_filename as $widget_filename ) {
				include( $this->dir . '/widgets/class-wlfmc-' . $widget_filename . '.php' );

				$class_name = ucwords(  $widget_filename, '-' );

				$class_name = str_replace( '-', '', $class_name );

				$class_name = '\WLFMC_Widget' . $class_name;

				Plugin::instance()->widgets_manager->register_widget_type( new $class_name() );
			}

		}


		 /**
		 * Enqueue Elementor Editor Style.
		 *
		 * @access public
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public function enqueueStyle() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'wlfmc-elementor-editor', MC_WLFMC_URL . 'assets/admin/css/elementor-editor' . $suffix . '.css', null, WLFMC_VERSION );
		}


		/**
		 * Setup Elementor Categories
		 *
		 * @access public
		 * @since 1.0.1
		 *
		 * @return void
		 */
		public function setupCategories() {
			Plugin::instance()->elements_manager->add_category(
				'WLFMC_WishList',
				[
					'title' => __( 'MC WishList', 'wc-wlfmc-wishlist' ),
					'icon'  => 'eicon-heart',
				],
				1
			 );
		}
	}

}

/**
 * Unique access to instance of WLFMC_Elementor class
 *
 * @return WLFMC_Elementor
 */
function WLFMC_Elementor() {
	return WLFMC_Elementor::get_instance();
}
