<?php
/**
 * Smart Wishlist Admin
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Admin' ) ) {
	/**
	 * This class handles admin for wishlist plugin
	 */
	class WLFMC_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Admin
		 */
		protected static $instance;

		/**
		 * Main panel
		 *
		 * @var MCT_Admin $main_panel Main panel.
		 */
		public $main_panel;

		/**
		 * Main options
		 *
		 * @var Array $main_options Main option.
		 */
		public $main_options;

		/**
		 * Installed state
		 *
		 * @var bool
		 * @since 1.0.1
		 */
		public $installed;

		/**
		 * Constructor
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function __construct() {

			// install plugin, or update from older versions.
			add_action( 'init', array( $this, 'install' ) );


			$this->installed = WLFMC_Install()->is_installed();


			if ( $this->installed ) {

				$this->main_options = array(
					/*'sections' => apply_filters(
						'wlfmc_admin_sections',
						array(
							'button-display' => __( 'Button display', 'wc-wlfmc-wishlist' ),
							'marketing'      => __( 'Marketing', 'wc-wlfmc-wishlist' ),
						)
					),*/
					'options' => apply_filters(
						'wlfmc_admin_options',
						array(
							'button-display' => array(
								'tabs'   => array(
									'general' => __( 'General', 'wc-wlfmc-wishlist' ),
									'button'  => __( 'Button', 'wc-wlfmc-wishlist' ),
									'share'   => __( 'Share', 'wc-wlfmc-wishlist' ),
								),
								'fields' => array(
									'general' => array(

										'start-article1'                 => array(
											'type'  => 'start',
											'title' => __( 'Display Settings', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'These settings is general setting that are related to your wishlist. You only need to set them once after installing the plugin.', 'wc-wlfmc-wishlist' )
										),
										'wishlist_page'                  => array(
											'label'   => __( 'Choose the Wishlist dashboard page', 'wc-wlfmc-wishlist' ),
											'desc'    => sprintf( __( '%s or Add %s shortcode to a any page you want.', 'wc-wlfmc-wishlist' ), '<a class="wlfmc-built-wishlist-page" href="#">' . __( 'click to build a new page', 'wc-wlfmc-wishlist' ) . '</a>', '<code>[wlfmc_shortcode]</code>' ),
											'type'    => 'page-select',
											'default' => get_option( 'wlfmc_wishlist_page_id' ),
											'help'    => __( 'Wishlist page needs to be selected so the plugin knows where it is. You should choose it upon installation of the plugin or create it manually.', 'wc-wlfmc-wishlist' )
										),
										'who_can_see_wishlist_options'   => array(
											'label'   => __( 'Enable Wishlist for', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'help'    => __( 'Do you want the wishlist to be visible only to members or all users of your website', 'wc-wlfmc-wishlist' ),
											'options' => array(
												'all'   => __( 'All users', 'wc-wlfmc-wishlist' ),
												'users' => __( 'Logined users', 'wc-wlfmc-wishlist' ),
											),
											'default' => 'all',

										),
										'force_user_to_login'            => array(
											'label'     => __( 'Require login or register', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'To use Wishlist, force users to login or sign up (not recommended)', 'wc-wlfmc-wishlist' ),
											'help'      => __( 'Select the user membership is mandatory or optional to use the wishlist', 'wc-wlfmc-wishlist' ),
											'type'      => 'switch',
											'dependies' => array(
												'id'    => 'who_can_see_wishlist_options',
												'value' => 'all',
											)
										),
										'click_wishlist_button_behavior' => array(
											'label'   => __( 'After clicking button', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'help'    => __( 'Choose what happens after your user clicks the Add to wishlist button', 'wc-wlfmc-wishlist' ),
											'options' => array(
												'open-popup'   => __( 'Open the pop up', 'wc-wlfmc-wishlist' ),
												'just-add'     => __( 'Just add it to Wishlist', 'wc-wlfmc-wishlist' ),
												'add-redirect' => __( 'Add and redirect to Wishlist page', 'wc-wlfmc-wishlist' ),
											),
										),
										/*'after_add_to_wishlist_behaviour' => array(
											'label'   => __( 'Button text after adding to Wishlist', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Choose the look of the Wishlist button when the product has already been added to a Wishlist', 'wc-wlfmc-wishlist' ),
											'options' => array(
												'view'   => __( 'Show "View Wishlist" button', 'wc-wlfmc-wishlist' ),
												'remove' => __( 'Show "Remove from list" button', 'wc-wlfmc-wishlist' ),
											),
											'default' => 'view',
											'type'    => 'radio',
										),*/
										'end-article1'                   => array(
											'type' => 'end',
										),
										'start-article2'                 => array(
											'type'      => 'start',
											'title'     => __( 'PopUp Settings', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'Only in the pop up selection mode will these settings be displayed for you. With these settings, you can customize your pop up.', 'wc-wlfmc-wishlist' )
										),
										'popup_position'                 => array(
											'label'     => __( 'Pop up position', 'wc-wlfmc-wishlist' ),
											'type'      => 'select',
											'options'   => array(
												'center-center' => __( 'Middle', 'wc-wlfmc-wishlist' ),
												'bottom-left'   => __( 'Down left', 'wc-wlfmc-wishlist' ),
												'bottom-right'  => __( 'Down right', 'wc-wlfmc-wishlist' ),
												'top-right'     => __( 'Top right', 'wc-wlfmc-wishlist' ),
												'top-left'      => __( 'Top left', 'wc-wlfmc-wishlist' ),
											),
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'Specify the position of the pop up on the website page. There are 5 modes for the pop up position. Middle mode is recommended.', 'wc-wlfmc-wishlist' )
										),
										'popup_size'                     => array(
											'label'     => __( 'Pop up size', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'If you want to use a photo in the pop up, choose large size for it.', 'wc-wlfmc-wishlist' ),
											'type'      => 'radio',
											'options'   => array(
												'small' => __( 'Small', 'wc-wlfmc-wishlist' ),
												'large' => __( 'Large', 'wc-wlfmc-wishlist' ),
											),
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'Specify the size of the pop up. There are two modes, small and large. In large mode you can use the product photo for pop up.', 'wc-wlfmc-wishlist' )
										),
										'use_featured_image'             => array(
											'label'     => __( 'Use featured image for pop up', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'Enable if use featured image for pop up', 'wc-wlfmc-wishlist' ),
											'type'      => 'switch',
											'dependies' => array(
												array(
													'id'    => 'popup_size',
													'value' => 'large',
												),
												array(
													'id'    => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												)
											),
										),
										'popup_image'                    => array(
											'label'     => __( 'Pop up image', 'wc-wlfmc-wishlist' ),
											'type'      => 'upload-image',
											'dependies' => array(
												array(
													'id'    => 'popup_size',
													'value' => 'large',
												),
												array(
													'id'    => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												array(
													'id'    => 'use_featured_image',
													'value' => '0',
												),
											),
										),
										'popup_image_size'               => array(
											'label'     => __( 'Pop up image Size', 'wc-wlfmc-wishlist' ),
											'type'      => 'select',
											'options'   => array(
												'thumbnail' => __( 'Thumbnial', 'wc-wlfmc-wishlist' ),
												'medium'    => __( 'Medium', 'wc-wlfmc-wishlist' ),
												'large'     => __( 'Large', 'wc-wlfmc-wishlist' ),
												'manual'    => __( 'Manual', 'wc-wlfmc-wishlist' ),
											),
											'dependies' => array(
												array(
													'id'    => 'popup_size',
													'value' => 'large',
												),
												array(
													'id'    => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												array(
													'id'    => 'use_featured_image',
													'value' => '0',
												),
											),
										),
										'popup_image_width'              => array(
											'label'     => __( 'Pop up image width', 'wc-wlfmc-wishlist' ),
											'type'      => 'number',
											'dependies' => array(
												array(
													'id'    => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												array(
													'id'    => 'popup_image_size',
													'value' => 'manual',
												)

											)

										),
										'popup_image_height'             => array(
											'label'     => __( 'Pop up image height', 'wc-wlfmc-wishlist' ),
											'type'      => 'number',
											'dependies' => array(
												array(
													'id'    => 'click_wishlist_button_behavior',
													'value' => 'open-popup',
												),
												array(
													'id'    => 'popup_image_size',
													'value' => 'manual',
												)

											)

										),
										'popup_title'                    => array(
											'label'     => __( 'Pop up title', 'wc-wlfmc-wishlist' ),
											'default'   => __( 'Added to Wishlist', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'Select the title of the pop up. No need to change and you can use the default text.', 'wc-wlfmc-wishlist' )

										),
										'popup_content'                  => array(
											'label'     => __( 'Pop up content', 'wc-wlfmc-wishlist' ),
											'type'      => 'wp-editor',
											'default'   => __( 'See your favorite product in Wishlist', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'Select the content of the pop up. No need to change and you can use the default text.', 'wc-wlfmc-wishlist' )
										),
										'popup_buttons'                  => array(
											'label'     => __( 'Add pop up button', 'wc-wlfmc-wishlist' ),
											'type'      => 'add-button',
											'links'     => array(
												'back'         => __( 'Close pop up and back', 'wc-wlfmc-wishlist' ),
												'signup-login' => __( 'Sign-up or login', 'wc-wlfmc-wishlist' ),
												'wishlist'     => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
												'custom-link'  => __( 'Custom url', 'wc-wlfmc-wishlist' ),
											),
											'default'   => array(
												array(
													'label'             => __( 'View my Wishlist', 'wc-wlfmc-wishlist' ),
													'background'        => '',
													'background-hover'  => '',
													'label-color'       => '',
													'label-hover-color' => '',
													'link'              => 'wishlist',
													'custom-link'       => '',
												),
											),
											'limit'     => 3,
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'These settings are related to the pop up button. You can consider more than one button for your pop up.', 'wc-wlfmc-wishlist' )
										),
										'popup_background_color'         => array(
											'label'     => __( 'Pop up background color', 'wc-wlfmc-wishlist' ),
											'type'      => 'color',
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'You can change the pop up background color here. Avoid choosing colors that do not match the theme.', 'wc-wlfmc-wishlist' )
										),
										'popup_border_color'             => array(
											'label'     => __( 'Pop up border color', 'wc-wlfmc-wishlist' ),
											'type'      => 'color',
											'desc'      => __( 'To remove the border, set the opacity to zero.', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												'id'    => 'click_wishlist_button_behavior',
												'value' => 'open-popup',
											),
											'help'      => __( 'You can change the pop up border color here. Preferably similar to or close to the pop up background color.', 'wc-wlfmc-wishlist' )
										),
										'end-article2'                   => array(
											'type' => 'end',
										),
										'start-article3'                 => array(
											'type'  => 'start',
											'title' => __( 'Remove data', 'wc-wlfmc-wishlist' ),
										),
										'remove_all_data'                => array(
											'label' => __( 'Remove all data', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Uncheck , if you want to prevent data loss when deleting the plugin', 'wc-wlfmc-wishlist' ),
											'type'  => 'checkbox',
										),
										'end-article3'                   => array(
											'type' => 'end',
										),
									),
									'button'  => array(
										'start-article2' => array(
											'type'  => 'start',
											'title' => __( 'Position', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'These settings are related to the position of the Add to wishlist button on your website.', 'wc-wlfmc-wishlist' )
										),
										'start-article3' => array(
											'type'  => 'start',
											'title' => __( 'Product page', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'button position settings on the product page', 'wc-wlfmc-wishlist' )
										),

										'wishlist_button_position' => array(
											'label'   => __( 'Button position', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'options' => array(
												'before_add_to_cart' => __( 'Before "add to cart"', 'wc-wlfmc-wishlist' ),
												'after_add_to_cart'  => __( 'After "add to cart"', 'wc-wlfmc-wishlist' ),
												'thumbnails'         => __( 'On top of the image', 'wc-wlfmc-wishlist' ),
												'summary'            => __( 'After summary', 'wc-wlfmc-wishlist' ),
												'shortcode'          => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
											),
											'help'    => __( 'button position on the product page', 'wc-wlfmc-wishlist' )
										),
										'shortcode_button'         => array(
											'label'     => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
											'type'      => 'copy-text',
											'default'   => '[wlfmc_add_to_wishlist]',
											'dependies' => array(
												'id'    => 'wishlist_button_position',
												'value' => 'shortcode',
											),
											'help'      => __( 'Use this shortcode to specify a custom position. Just copy this shortcode wherever you want the button to be displayed.', 'wc-wlfmc-wishlist' )
										),
										'end-article3'             => array(
											'type' => 'end',
										),
										// Products
										'start-article4'           => array(
											'type'  => 'start',
											'title' => __( 'Product lists', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'Button position settings in lists, for example your website store ', 'wc-wlfmc-wishlist' )
										),

										'show_on_loop'                  => array(
											'label' => __( 'Show "add to Wishlist" in loop', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Enable the "add to Wishlist" feature in WooCommerce products\' loop', 'wc-wlfmc-wishlist' ),
											'type'  => 'switch',
											'help'  => __( 'By activating this option, the Add to wishlist button will be displayed in all lists.', 'wc-wlfmc-wishlist' )
										),
										'loop_position'                 => array(
											'label'     => __( 'Button position in loop', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'Choose where to show "Add to Wishlist" button or link in WooCommerce products\' loop.', 'wc-wlfmc-wishlist' ),
											'default'   => 'after_add_to_cart',
											'type'      => 'select',
											'options'   => array(
												'before_image'       => __( 'On top of the image', 'wc-wlfmc-wishlist' ),
												'before_add_to_cart' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
												'after_add_to_cart'  => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
												'shortcode'          => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
											),
											'dependies' => array(
												'id'    => 'show_on_loop',
												'value' => '1',
											),
											'help'      => __( 'Select the button position to view in the lists. Preferably similar to the button position on the product page.', 'wc-wlfmc-wishlist' )
										),
										'loop_shortcode_button'         => array(
											'label'     => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
											'type'      => 'copy-text',
											'default'   => '[wlfmc_add_to_wishlist]',
											'dependies' => array(
												array(
													'id'    => 'show_on_loop',
													'value' => '1',
												),
												array(
													'id'    => 'loop_position',
													'value' => 'shortcode',
												),
											),
										),
										'end-article4'                  => array(
											'type' => 'end',
										),
										'end-article2'                  => array(
											'type' => 'end',
										),
										'start-article1'                => array(
											'type'  => 'start',
											'title' => __( 'Appearance', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'These settings are related to the appearance of the Add to wishlist button on your website pages.', 'wc-wlfmc-wishlist' )
										),
										'start-article5'                => array(
											'type'  => 'start',
											'title' => __( 'Product page', 'wc-wlfmc-wishlist' ),
										),
										'button_type_single'            => array(
											'label'   => __( 'Button type', 'wc-wlfmc-wishlist' ),
											'type'    => 'radio',
											'options' => array(
												'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
												'text' => __( 'Text only', 'wc-wlfmc-wishlist' ),
												'both' => __( 'Icon and text', 'wc-wlfmc-wishlist' ),
											),
											'help'    => __( 'Select the button appearance. It is better to test different modes and choose the most compatible with the theme.', 'wc-wlfmc-wishlist' )
										),
										'button_label_add_single'       => array(
											'label'     => __( '"Add to Wishlist" button text', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => __( 'Add to Wishlist', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												array(
													'id'    => 'button_type_single',
													'value' => 'text,both',
												),
											),
										),
										'button_label_view_single'      => array(
											'label'     => __( '"View My Wishlist" button text', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => __( 'view my wishlist', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												array(
													'id'    => 'button_type_single',
													'value' => 'text,both',
												),
											),
										),
										'button_theme_single'           => array(
											'label'   => __( 'Button theme', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Enable if use theme default colors for icon and text', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
											'default' => '1',
											'help'    => __( 'Do you want the button settings to be automatically aligned with your theme?', 'wc-wlfmc-wishlist' )
										),
										'icon_style_single'             => array(
											'label'     => __( 'Button icon style', 'wc-wlfmc-wishlist' ),
											'type'      => 'color-style',
											'dependies' => array(
												array(
													'id'    => 'button_type_single',
													'value' => 'icon,both',
												),
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
											),
										),
										'text_style_single'             => array(
											'label'     => __( 'Button text style', 'wc-wlfmc-wishlist' ),
											'type'      => 'color-style',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_single',
													'value' => 'text,both',
												),
											),
										),
										'icon_font_size_single'         => array(
											'label'     => __( 'Icon font size', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_single',
													'value' => 'icon,both',
												),
											),
										),
										'text_font_size_single'         => array(
											'label'     => __( 'Text font size', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_single',
													'value' => 'text,both',
												),
											),
										),
										'button_line_height_single'     => array(
											'label'     => __( 'Button line height', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
											),
										),
										'button_height_single'          => array(
											'label'     => __( 'Button height', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'auto',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
											),
										),
										'button_width_single'           => array(
											'label'     => __( 'Button width', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'auto',
											'dependies' => array(
												array(
													'id'    => 'button_theme_single',
													'value' => '0',
												),
											),
										),
										'flash_icon_single'             => array(
											'label'     => __( 'Flash icon', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'The icon will flash when you go to the product.', 'wc-wlfmc-wishlist' ),
											'type'      => 'switch',
											'dependies' => array(
												'id'    => 'button_type_single',
												'value' => 'icon',
											),
										),
										'seperate_icon_and_text_single' => array(
											'label'     => __( 'Seperate icon and text', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'separate icon and text boxes in single product page', 'wc-wlfmc-wishlist' ),
											'type'      => 'switch',
											'dependies' => array(
												'id'    => 'button_type_single',
												'value' => 'both',
											),
										),
										'seperator_color_single'        => array(
											'label'     => __( 'Seperator color', 'wc-wlfmc-wishlist' ),
											'type'      => 'color',
											'dependies' => array(
												array(
													'id'    => 'button_type_single',
													'value' => 'both',
												),
												array(
													'id'    => 'seperate_icon_and_text_single',
													'value' => '1',
												),
											),
										),
										'end-article5'                  => array(
											'type' => 'end',
										),
										'start-article6'                => array(
											'type'  => 'start',
											'title' => __( 'Product lists', 'wc-wlfmc-wishlist' ),
										),
										'button_type_loop'              => array(
											'label'   => __( 'Button type', 'wc-wlfmc-wishlist' ),
											'type'    => 'radio',
											'options' => array(
												'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
												'text' => __( 'Text only', 'wc-wlfmc-wishlist' ),
												'both' => __( 'Icon and text', 'wc-wlfmc-wishlist' ),
											),
										),
										'button_label_add_loop'         => array(
											'label'     => __( '"Add to Wishlist" button text', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => __( 'Add to Wishlist', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												array(
													'id'    => 'button_type_loop',
													'value' => 'text,both',
												),
											),
										),
										'button_label_view_loop'        => array(
											'label'     => __( '"View My Wishlist" button text', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => __( 'View my Wishlist', 'wc-wlfmc-wishlist' ),
											'dependies' => array(
												array(
													'id'    => 'button_type_loop',
													'value' => 'text,both',
												),
											),
										),
										'button_theme_loop'             => array(
											'label'   => __( 'Button theme', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Enable if use theme default colors for icon and text', 'wc-wlfmc-wishlist' ),
											'type'    => 'switch',
											'default' => '1',
										),
										'icon_style_loop'               => array(
											'label'     => __( 'Button icon style', 'wc-wlfmc-wishlist' ),
											'type'      => 'color-style',
											'dependies' => array(
												array(
													'id'    => 'button_type_loop',
													'value' => 'icon,both',
												),
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
											),
										),
										'text_style_loop'               => array(
											'label'     => __( 'Button text style', 'wc-wlfmc-wishlist' ),
											'type'      => 'color-style',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_loop',
													'value' => 'text,both',
												),
											),
										),
										'icon_font_size_loop'           => array(
											'label'     => __( 'Icon font size', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_loop',
													'value' => 'icon,both',
												),
											),
										),
										'text_font_size_loop'           => array(
											'label'     => __( 'Text font size', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
												array(
													'id'    => 'button_type_loop',
													'value' => 'text,both',
												),
											),
										),
										'button_line_height_loop'       => array(
											'label'     => __( 'Button line height', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'inherit',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
											),
										),
										'button_height_loop'            => array(
											'label'     => __( 'Button height', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'auto',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
											),
										),
										'button_width_loop'             => array(
											'label'     => __( 'Button width', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'default'   => 'auto',
											'dependies' => array(
												array(
													'id'    => 'button_theme_loop',
													'value' => '0',
												),
											),
										),
										'seperate_icon_and_text_loop'   => array(
											'label'     => __( 'Seperate icon and text', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'separate icon and text boxes in single product page', 'wc-wlfmc-wishlist' ),
											'type'      => 'switch',
											'dependies' => array(
												'id'    => 'button_type_loop',
												'value' => 'both',
											),
										),
										'seperator_color_loop'          => array(
											'label'     => __( 'Seperator color', 'wc-wlfmc-wishlist' ),
											'type'      => 'color',
											'dependies' => array(
												array(
													'id'    => 'button_type_loop',
													'value' => 'both',
												),
												array(
													'id'    => 'seperate_icon_and_text_loop',
													'value' => '1',
												),
											),
										),
										'end-article6'                  => array(
											'type' => 'end',
										),
										'product_added_text'            => array(
											'label'   => __( '"Product added" text', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Enter the text of the message displayed when the user adds a product to the Wishlist', 'wc-wlfmc-wishlist' ),
											'default' => __( 'Product added!', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'help'    => __( 'After the user clicks on the Add to wishlist button, a notification bar will be displayed. You can display the default text or any other text on this notification bar.', 'wc-wlfmc-wishlist' )
										),
										'already_in_wishlist_text'      => array(
											'label'   => __( '"Product already in Wishlist" text', 'wc-wlfmc-wishlist' ),
											'desc'    => __( 'Enter the text for the message displayed when the user will try to add a product that is already in the Wishlist', 'wc-wlfmc-wishlist' ),
											'default' => __( 'The product is already in your Wishlist!', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'help'    => __( 'If the product already exists in the user\'s wishlist and she/he clicks the Add to wishlist button again, this text will be displayed.', 'wc-wlfmc-wishlist' )
										),
										'end-article1'                  => array(
											'type' => 'end',
										),
									),
									'share'   => array(
										'start-article'    => array(
											'type'  => 'start',
											'title' => __( 'Share Settings', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'By activating this feature, your users can share their wishlist with their friends and acquaintances. This is a free branding for your website.', 'wc-wlfmc-wishlist' )
										),
										'enable_share'     => array(
											'label' => __( 'Share Wishlist', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Enable this option to let users share their Wishlist on social media', 'wc-wlfmc-wishlist' ),
											'type'  => 'switch',
										),
										'share_items'      => array(
											'label'     => __( 'Active share buttons', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'Which social media icons show on the sharing bar?', 'wc-wlfmc-wishlist' ),
											'type'      => 'checkbox-group',
											'options'   => array(
												'facebook'  => __( 'Facebook', 'wc-wlfmc-wishlist' ),
												'messenger' => __( 'Facebook messenger', 'wc-wlfmc-wishlist' ),
												'twitter'   => __( 'Twitter', 'wc-wlfmc-wishlist' ),
												'whatsapp'  => __( 'Whatsapp', 'wc-wlfmc-wishlist' ),
												'telegram'  => __( 'Telegram', 'wc-wlfmc-wishlist' ),
												'email'     => __( 'Email', 'wc-wlfmc-wishlist' ),
												'copy'      => __( 'Share link', 'wc-wlfmc-wishlist' ),
											),
											'dependies' => array(
												'id'    => 'enable_share',
												'value' => '1',
											),
											'help'      => __( 'In what medias do you prefer your user to be able to share his / her wishlist?', 'wc-wlfmc-wishlist' )
										),
										'messenger_app_id' => array(
											'label'     => __( 'Facebook messenger app Id', 'wc-wlfmc-wishlist' ),
											'type'      => 'text',
											'dependies' => array(
												array(
													'id'    => 'share_items',
													'value' => 'messenger',
												),
												array(
													'id'    => 'enable_share',
													'value' => '1',
												)
											),
										),
										'socials_title'    => array(
											'label'     => __( 'Sharing title', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'Wishlist title used for sharing', 'wc-wlfmc-wishlist' ),
											/* translators: %s: site name */
											'default'   => sprintf( __( 'My Wishlist on %s', 'wc-wlfmc-wishlist' ), get_bloginfo( 'name' ) ),
											'type'      => 'text',
											'dependies' => array(
												array(
													'id'    => 'share_items',
													'value' => 'twitter,facebook,email',
												),
												array(
													'id'    => 'enable_share',
													'value' => '1',
												),
											),
											'help'      => __( 'Enter the title that you would like to display as your ad when your user is sharing it.', 'wc-wlfmc-wishlist' )
										),
										'socials_text'     => array(
											'label'     => __( 'Twitter text', 'wc-wlfmc-wishlist' ),
											'desc'      => __( 'Type the message you want to publish when you share your Wishlist on twitter', 'wc-wlfmc-wishlist' ),
											'type'      => 'textarea',
											'dependies' => array(
												array(
													'id'    => 'share_items',
													'value' => 'twitter',
												),
												array(
													'id'    => 'enable_share',
													'value' => '1',
												),
											),
											'help'      => __( 'This text will be displayed on your user\'s Twitter when sharing.', 'wc-wlfmc-wishlist' )
										),
										'end-article'      => array(
											'type' => 'end',

										),
									),

								),
							),
							'marketing'      => array(
								'tabs'   => array(
									'marketing-tab' => __( 'Marketing', 'wc-wlfmc-wishlist' ),
									//'reports-tab'   => __( 'Reports', 'wc-wlfmc-wishlist' ),
								),
								'fields' => array(
									'marketing-tab' => array(
										'start-article-1'        => array(
											'type'  => 'start',
											'title' => __( 'Email Automation Conditions', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'In this section you can specify under what conditions automatic emails will be sent to your users.', 'wc-wlfmc-wishlist' )
										),
										'period-days'            => array(
											'label' => __( 'Repeat promotion period', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Based on the number of days', 'wc-wlfmc-wishlist' ),
											'type'  => 'number',
											'help'  => __( 'By entering the number of days you want, this marketing will resume after these days.', 'wc-wlfmc-wishlist' )
										),
										'minimum-wishlist-total' => array(
											'label' => __( 'Minimum price of total Wishlist items', 'wc-wlfmc-wishlist' ),
											'type'  => 'text',
											'desc'  => wp_sprintf( '%s: %s(%s)', __( 'Currency', 'wc-wlfmc-wishlist' ), get_woocommerce_currency(), get_woocommerce_currency_symbol() ),
											'help'  => __( 'Your user\'s wishlist must have the minimum total price entered. If the total price of the user\'s list is less than this number, emails won\'t be sent to the user.', 'wc-wlfmc-wishlist' )
										),
										'minimum-wishlist-count' => array(
											'label' => __( 'Minimum number of products on the Wishlist', 'wc-wlfmc-wishlist' ),
											'type'  => 'number',
											'help'  => __( 'The number of products in your user\'s wishlist must be equal to / greater than your choice number to send emails to. For example, for wishlists containing 5 products and more', 'wc-wlfmc-wishlist' )
										),
										'include-product'        => array(
											'label' => __( 'Include at least one of these products', 'wc-wlfmc-wishlist' ),
											'type'  => 'search-product',
											'help'  => __( 'Your user\'s wishlist must include at least one of the selected products to emails will be sent.', 'wc-wlfmc-wishlist' )
										),
										'delete-queue-emails'    => array(
											'label'        => __( 'Delete queue emails', 'wc-wlfmc-wishlist' ),
											/* translators: %s: emails count in the send queue */
											'desc'         => sprintf( __( 'If checked, %s emails in queue list will be deleted !', 'wc-wlfmc-wishlist' ), WLFMC_Offer_Emails()->get_count_send_queue() ),
											'type'         => 'checkbox',
											'parent_class' => WLFMC_Offer_Emails()->get_count_send_queue() > 0 ? '' : 'hidden-option',
										),
										'end-article-1'          => array(
											'type' => 'end',

										),
										'start-article-2'        => array(
											'type'  => 'start',
											'title' => __( 'Email automation offer', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'These settings are related to the offer you want to send via email to encourage purchase.', 'wc-wlfmc-wishlist' )
										),
										'discount-type'          => array(
											'label'   => __( 'Discount type', 'wc-wlfmc-wishlist' ),
											'type'    => 'select',
											'options' => wc_get_coupon_types(),
											'help'    => __( 'Specify the type of discount you offer in terms of percentage or fixed price.', 'wc-wlfmc-wishlist' )
										),
										'coupon-amount'          => array(
											'label'       => __( 'Coupon amount', 'wc-wlfmc-wishlist' ),
											'placeholder' => wc_format_localized_price( 0 ),
											'type'        => 'text',
											'desc'        => __( 'Value of the coupon.', 'wc-wlfmc-wishlist' ),
											'help'        => __( 'Specify the amount of discount in this section. Notice that this discount applies to wishlist. In percentage mode, enter the percentage instead of the amount.', 'wc-wlfmc-wishlist' )
										),
										'free-shipping'          => array(
											'label' => __( 'Allow free shipping', 'wc-wlfmc-wishlist' ),
											'type'  => 'checkbox',
											/* translators: %s: woocommerce document url */
											'desc'  => sprintf( __( 'Check this box if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'wc-wlfmc-wishlist' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
											'help'  => __( 'If you choose this option, shipping the purchased products will be free and no cost will be deducted from the customer.', 'wc-wlfmc-wishlist' )
										),
										'expiry-date'            => array(
											'label' => __( 'Coupon expiry after days', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'The coupon will expire after custom days.', 'wc-wlfmc-wishlist' ),
											'type'  => 'number',
											'help'  => __( 'If you want this offer to expire after a certain number of days, enter the number of days you want.', 'wc-wlfmc-wishlist' )
										),
										'user-restriction'       => array(
											'label' => __( 'User restriction', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Coupon work just for their account email', 'wc-wlfmc-wishlist' ),
											'type'  => 'checkbox',
										),
										'delete-after-expired'   => array(
											'label' => __( 'Delete the Coupon', 'wc-wlfmc-wishlist' ),
											'desc'  => __( 'Delete the coupon after the expiry', 'wc-wlfmc-wishlist' ),
											'type'  => 'checkbox',
											'help'  => __( 'If you choose this option, this discount will be automatically deleted after the expiration and there is no need to delete it manually.', 'wc-wlfmc-wishlist' )
										),
										'end-article-2'          => array(
											'type' => 'end',

										),
										'start-article-3'        => array(
											'type'  => 'start',
											'title' => __( 'Email sender options', 'wc-wlfmc-wishlist' ),
											'help'  => __( 'In this section you can make settings related to the sender of the email. The name and email address are displayed in the user\'s inbox.', 'wc-wlfmc-wishlist' )
										),
										'email-from-name'        => array(
											'label'   => __( 'From "name"', 'wc-wlfmc-wishlist' ),
											'type'    => 'text',
											'default' => wp_specialchars_decode( get_option( 'woocommerce_email_from_name' ), ENT_QUOTES )
										),
										'email-from-address'     => array(
											'label'   => __( 'From "Email address"', 'wc-wlfmc-wishlist' ),
											'type'    => 'email',
											'default' => sanitize_email( get_option( 'woocommerce_email_from_address' ) )
										),
										'end-article-3'          => array(
											'type' => 'end',

										),
										'offer_emails'           => array(
											'label'        => __( 'Offer emails', 'wc-wlfmc-wishlist' ),
											'type'         => 'manage',
											'title'        => __( 'Email automation sequence', 'wc-wlfmc-wishlist' ),
											'count'        => 5,
											'row-title'    => __( 'Reminder email %s', 'wc-wlfmc-wishlist' ),
											'row-desc'     => __( 'These emails are sent to users who add products to their wishlist depend on email automation conditions', 'wc-wlfmc-wishlist' ),
											'table-fields' => array(
												'enable_email'    => array(
													'label' => __( 'Activation', 'wc-wlfmc-wishlist' ),
													'type'  => 'switch',
													'help'  => __( 'enable email. So the ability to send automatic email is activated.', 'wc-wlfmc-wishlist' )
												),
												'mail_subject'    => array(
													'label' => __( 'Email subject', 'wc-wlfmc-wishlist' ),
													'type'  => 'value',
												),
												'send_after_days' => array(
													'label'        => __( 'Send after', 'wc-wlfmc-wishlist' ),
													'type'         => 'value',
													'value_format' => __( '%s day(s)', 'wc-wlfmc-wishlist' )
												),
												'queue'           => array(
													'label'        => __( 'Queue', 'wc-wlfmc-wishlist' ),
													'type'         => 'value',
													'default'      => 0,
													'value_class'  => array(
														'WLFMC_Offer_Emails::get_count_send_queue_by_days',
													),
													'value_depend' => 'send_after_days'
												),
											),
											'table-action' => array(
												'title' => __( 'Test email', 'wc-wlfmc-wishlist' ),
												'class' => 'wlfmc-send-offer-email-test'
											),
											'fields'       => array(

												'start-article-1' => array(
													'type'  => 'start',
													'title' => __( 'Send setting', 'wc-wlfmc-wishlist' ),
													'help'  => __( 'Specify settings for sending time and type of emails here.', 'wc-wlfmc-wishlist' )
												),
												'send_after_days' => array(
													'label'             => __( 'Send this email after days', 'wc-wlfmc-wishlist' ),
													'type'              => 'number',
													'custom_attributes' => array(
														'min' => 0
													),
													'desc'              => __( 'Zero means send the email immediately after conditions  happen ', 'wc-wlfmc-wishlist' ),
													'help'              => __( 'After how many days will this email be sent? Enter the number of days. If you want the email to be sent as soon as products are added to the wishlist, enter zero (Usually for the first email).', 'wc-wlfmc-wishlist' ),
												),
												'mail_type'       => array(
													'label'   => __( 'Email type', 'wc-wlfmc-wishlist' ),
													'desc'    => __( 'Choose which type of email to send', 'wc-wlfmc-wishlist' ),
													'default' => 'html',
													'type'    => 'select',
													'options' => array(
														'plain' => __( 'Plain', 'wc-wlfmc-wishlist' ),
														'html'  => __( 'HTML', 'wc-wlfmc-wishlist' ),
													),
												),
												'end-article-1'   => array(
													'type' => 'end',
												),
												'start-article-2' => array(
													'type'  => 'start',
													'title' => __( 'Content details', 'wc-wlfmc-wishlist' ),
													'help'  => __( 'In this section, you must enter the title and text of your email.', 'wc-wlfmc-wishlist' )
												),

												'mail_heading'  => array(
													'label' => __( 'Email heading', 'wc-wlfmc-wishlist' ),
													'desc'  => __( 'Enter the title for the email notification. Leave blank to use the default heading: "<i>There is a deal for you!</i>". You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'type'  => 'text',
												),
												'mail_subject'  => array(
													'label'   => __( 'Email subject', 'wc-wlfmc-wishlist' ),
													'desc'    => __( 'Enter the email subject line. Leave blank to use the default subject: "<i>A product of your Wishlist is on sale</i>". You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'default' => '',
													'type'    => 'text',
												),
												'html_content'  => array(
													'label'             => __( 'Email html content', 'wc-wlfmc-wishlist' ),
													'desc'              => __( 'This field lets you modify the main content of the HTML email. You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'type'              => 'textarea',
													'default'           => WLFMC_Offer_Emails::get_default_content( 'html', 1 ),
													'class'             => 'resizeable',
													'custom_attributes' => array(
														'cols' => '120',
														'rows' => '10'
													),
													'dependies'         => array(
														'id'    => 'mail_type',
														'value' => 'html',
													),
												),
												'text_content'  => array(
													'label'             => __( 'Email plain content', 'wc-wlfmc-wishlist' ),
													'desc'              => __( 'This field lets you modify the main content of the text email. You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code> <code>{site_name}</code> <code>{site_description}</code>', 'wc-wlfmc-wishlist' ),
													'type'              => 'textarea',
													'default'           => WLFMC_Offer_Emails::get_default_content( 'plain' ),
													'class'             => 'resizeable',
													'custom_attributes' => array(
														'cols' => '120',
														'rows' => '10'
													),
													'dependies'         => array(
														'id'    => 'mail_type',
														'value' => 'plain',
													),
												),
												'end-article-2' => array(
													'type' => 'end',
												),
											),
											'default'      => array(
												array(
													'enable_email'    => '0',
													'send_after_days' => '1',
													'mail_type'       => 'html',
													'mail_heading'    => '',
													'mail_subject'    => __( 'Check it out, {user_name}', 'wc-wlfmc-wishlist' ),
													'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 1 ),
													'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 1 ),
												),
												array(
													'enable_email'    => '0',
													'send_after_days' => '3',
													'mail_type'       => 'html',
													'mail_heading'    => '',
													'mail_subject'    => __( 'Deals you’ve been waiting for!', 'wc-wlfmc-wishlist' ),
													'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 2 ),
													'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 2 ),
												),
												array(
													'enable_email'    => '0',
													'send_after_days' => '5',
													'mail_type'       => 'html',
													'mail_heading'    => '',
													'mail_subject'    => __( 'Got {coupon_amount} off your favorites?', 'wc-wlfmc-wishlist' ),
													'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 3 ),
													'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 3 ),
												),
												array(
													'enable_email'    => '0',
													'send_after_days' => '7',
													'mail_type'       => 'html',
													'mail_heading'    => '',
													'mail_subject'    => __( 'The item on your wishlist is almost sold out!', 'wc-wlfmc-wishlist' ),
													'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 4 ),
													'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 4 ),
												),
											),
											'help'         => __( 'In this section you can see information about each of the emails. Click the Manage button if you want to change the content of each email.', 'wc-wlfmc-wishlist' )
										),
									),
									/*'reports-tab'   => array(
										'type'  => 'class',
										'class' => array (
											'class_name',
											'function_name'
										)
									)*/
								)
							),
						)
					),
					'title'   => __( 'Manage Settings', 'wc-wlfmc-wishlist' ),
					'type'    => 'simple-panel',
					'id'      => 'wlfmc_options',
				);

				$this->main_panel = new MCT_Admin( $this->main_options );

			}

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

			// add a post display state for special WC pages.
			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );

			$plugin = plugin_basename( MC_WLFMC_MAIN_FILE );
			add_filter( "plugin_action_links_$plugin", array( $this, 'settings_link' ), 10, 1 );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );


			add_action( 'mct_panel_wlfmc_options_item_remove_all_data', array(
				$this,
				'update_remove_all_data_state'
			) );

			add_action( 'mct_panel_wlfmc_options_item_wishlist_page', array(
				$this,
				'update_wishlist_page_id'
			) );

			add_action( 'wp_ajax_wlfmc_create_wishlist_page', array( $this, 'ajax_create_page_callback' ) );
			add_action( 'wp_ajax_wlfmc_send_offer_email_test', array( $this, 'ajax_send_offer_email_test_callback' ) );

		}


		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Add a post display state for special WC pages in the page list table.
		 *
		 * @param array $post_states An array of post display states.
		 * @param WP_Post $post The current post object.
		 *
		 * @return array
		 *
		 * @version 1.0.1
		 */
		public function add_display_post_states( $post_states, $post ) {

			if ( get_option( 'wlfmc_wishlist_page_id' ) == $post->ID ) {
				$post_states[] = __( 'Wishlist page', 'wc-wlfmc-wishlist' );
			}

			return $post_states;
		}

		/**
		 * Update remove all data state after update plugin settigns.
		 *
		 * @since 1.0.1
		 */
		public function update_remove_all_data_state() {
			$state = isset( $_POST['remove_all_data'] ) ? wp_unslash( $_POST['remove_all_data'] ) : '';
			update_option( 'wlfmc_remove_all_data', $state );
		}

		/**
		 * Update wishlist page id after update plugin settigns.
		 *
		 * @since 1.0.1
		 */
		public function update_wishlist_page_id() {
			$id = isset( $_POST['wishlist_page'] ) ? intval( $_POST['wishlist_page'] ) : '';
			update_option( 'wlfmc_wishlist_page_id', $id );
		}

		/**
		 * Create wishlist page ajaxify.
		 *
		 * @since 1.0.1
		 */
		public function ajax_create_page_callback() {

			check_ajax_referer( 'ajax-nonce', 'key' );

			$id = wc_create_page(
				sanitize_title_with_dashes( _x( 'wishlist', 'page_slug', 'wc-wlfmc-wishlist' ) ),
				'wlfmc_wishlist_page_id',
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				'<!-- wp:shortcode -->[wlfmc_wishlist]<!-- /wp:shortcode -->'
			);

			$options = new MCT_Options( 'wlfmc_options' );
			$options->update_option( 'wishlist_page', $id );

			echo wp_json_encode( array(
				'success' => true
			) );
			exit;
		}

		/**
		 * Send offer email for testing
		 *
		 * @since 1.0.1
		 */
		public function ajax_send_offer_email_test_callback() {
			check_ajax_referer( 'ajax-nonce', 'key' );

			$id    = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
			$field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : '';

			if ( '' === $field || '' === $id ) {

				exit;
			}

			$options = new MCT_Options( 'wlfmc_options' );
			$offers  = $options->get_option( $field );

			if ( ! $offers || ! isset( $offers[ $id ] ) ) {

				echo wp_json_encode( array(
					'message' => __( 'Save the email settings first, then try again', 'wc-wlfmc-wishlist' ),
					'success' => false
				) );
				exit;
			}


			$mailer        = WC()->mailer();
			$user          = get_userdata( get_current_user_id() );
			$to            = $user->user_email;
			$email_options = $offers[ $id ];
			$email_content = '';

			switch ( $email_options['mail_type'] ) {
				case 'html':
					$content_type = 'text/html';
					$template     = 'offer.php';
					$email_content = (''=== $email_options['html_content']) ? WLFMC_Offer_Emails::get_default_content( 'html', $id ): $email_options['html_content'] ;


					break;
				case 'plain':
					$content_type = 'text/plain';
					$template     = 'plain/offer.php';
					$email_content = (''=== $email_options['text_content']) ? WLFMC_Offer_Emails::get_default_content( 'plain', $id ): $email_options['html_content'] ;

					break;
			}
			$headers = "Content-Type: {$content_type}\r\n";


			$placeholders  = array(
				'{user_name}'       => $user->user_login,
				'{user_email}'      => $user->user_email,
				'{user_first_name}' => $user->first_name,
				'{user_last_name}'  => $user->last_name,
				'{site_name}'        => get_bloginfo( 'name' ),
				'{site_description}' => get_bloginfo( 'description' ),

			);
			$email_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );

			$email_heading = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_heading'] );
			$email_subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_subject'] );

			$message = wlfmc_get_template( 'emails/' . $template, array(
				'email'         => $mailer,
				'email_heading' => apply_filters( 'wlfmc_offer_email_heading', $email_heading ),
				'email_content' => apply_filters( 'wlfmc_offer_email_content', $email_content )
			), true );

			$WLFMC_Offer_Emails = WLFMC_Offer_Emails();
			add_filter('woocommerce_email_from_name' , array($WLFMC_Offer_Emails , 'get_from_name' ), 10 );
			add_filter('woocommerce_email_from_address' , array($WLFMC_Offer_Emails , 'get_from_address' ), 10  );

			$send_state = $mailer->send( $to, apply_filters( 'wlfmc_offer_email_subject_test', $email_subject ), $message, $headers, '' );

			remove_filter('woocommerce_email_from_name' , array($WLFMC_Offer_Emails , 'get_from_name' ), 10 );
			remove_filter('woocommerce_email_from_address' , array($WLFMC_Offer_Emails , 'get_from_address' ) , 10);


			if ( $send_state ) {
				echo wp_json_encode( array(
					'message' => sprintf(__( 'Email sent to %s.', 'wc-wlfmc-wishlist' ) , $to),
					'success' => true
				) );
			} else {
				echo wp_json_encode( array(
					'message' => __( 'There was a problem sending the email.', 'wc-wlfmc-wishlist' ),
					'success' => false
				) );
			}

			exit;
		}

		/**
		 * Add admin menu
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function add_admin_menu() {

			add_menu_page(
				__( 'MC Wishlist', 'wc-wlfmc-wishlist' ),
				__( 'MC Wishlist', 'wc-wlfmc-wishlist' ),
				'manage_options',
				'mc-wishlist-settings',
				array( $this, 'show_settings' ),
				'dashicons-heart',
				null
			);
			$settings  = add_submenu_page(
				'mc-wishlist-settings',
				__( 'Settings', 'wc-wlfmc-wishlist' ),
				__( 'Settings', 'wc-wlfmc-wishlist' ),
				'manage_options',
				'mc-wishlist-settings',
				array( $this, 'show_settings' )
			);
			$marketing = add_submenu_page(
				'mc-wishlist-settings',
				__( 'Marketing', 'wc-wlfmc-wishlist' ),
				__( 'Marketing', 'wc-wlfmc-wishlist' ),
				'manage_options',
				'mc-wishlist-marketing',
				array( $this, 'show_marketing' ) );

			add_action( 'load-' . $settings, array( $this, 'admin_enqueue_scripts' ) );

			add_action( 'load-' . $marketing, array( $this, 'admin_enqueue_scripts' ) );

		}

		/**
		 * Add Settings Button Beside Plugin Detail.
		 *
		 * @param array $links , Array of links.
		 *
		 * @return array
		 * @since 1.0.1
		 */
		public function settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=mc-wishlist-settings">'
			                 . __( 'Settings', 'wc-wlfmc-wishlist' ) .
			                 '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 * Add Docs and Support Button Beside Plugin Detail.
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 * @since 1.0.1
		 */
		public function plugin_row_meta( $links, $file ) {
			$plugin = plugin_basename( MC_WLFMC_MAIN_FILE );
			if ( $plugin == $file ) {
				$row_meta = array(
					'docs'    => '<a href="' . esc_url( 'https://moreconvert.com/smart-wishlist-for-more-convert-documentation/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Documentation', 'wc-wlfmc-wishlist' ) . '" style="color:green;">' . esc_html__( 'Documentation', 'wc-wlfmc-wishlist' ) . '</a>',
					'support' => '<a href="' . esc_url( 'https://moreconvert.com/support/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Support', 'wc-wlfmc-wishlist' ) . '" style="color:green;">' . esc_html__( 'Support', 'wc-wlfmc-wishlist' ) . '</a>'

				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Enqueue script and styles in admin side
		 * Add style and scripts to administrator
		 *
		 * @retrun void
		 *
		 * @version 1.0.1
		 */
		public function admin_enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'wlfmc-admin', MC_WLFMC_URL . 'assets/admin/js/admin-panel' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );
			wp_register_style( 'wlfmc-admin-css', MC_WLFMC_URL . 'assets/admin/css/admin' . $suffix . '.css' );

			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			$params = array(
				'ajax_url'                 => admin_url( "admin-ajax.php" ),
				'ajax_nonce'               => wp_create_nonce( "ajax-nonce" ),
				'i18n_making_page'         => __( 'Making a page ...', 'wc-wlfmc-wishlist' ),
				'i18n_sending'             => __( 'Sending ...', 'wc-wlfmc-wishlist' ),
				/* translators: %s: decimal */
				'i18n_decimal_error'       => sprintf( __( 'Please enter with one decimal point (%s) without thousand separators.', 'wc-wlfmc-wishlist' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'   => sprintf( __( 'Please enter with one monetary decimal point (%s) without thousand separators and currency symbols.', 'wc-wlfmc-wishlist' ), wc_get_price_decimal_separator() ),
				'i18n_percent_description' => __( 'Value of the coupon(percent).', 'wc-wlfmc-wishlist' ),
				/* translators: %s: currency symbol */
				'i18n_amount_description'  => sprintf( __( 'Value of the coupon(%s).', 'wc-wlfmc-wishlist' ), get_woocommerce_currency_symbol() ),
				'decimal_point'            => $decimal,
				'mon_decimal_point'        => wc_get_price_decimal_separator(),
			);

			wp_localize_script( 'wlfmc-admin', 'wlfmc_wishlist_admin', $params );
			wp_enqueue_script( 'wlfmc-admin' );
			wp_enqueue_style( 'wlfmc-admin-css' );
			add_action( 'admin_head', array( $this, 'snackbar_style' ) );

		}

		/**
		 * Show admin option panel
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function show_settings() {
			?>
			<div id="wlfmc_options" class="wrap mct-options">
				<h1 class="d-flex space-between f-center" style="margin: 15px 0 10px">
					<span style="padding:0;"><?php esc_attr_e( 'Smart Wishlist settings', 'wc-wlfmc-wishlist' ); ?></span>
					<a class="button button-primary" href="https://moreconvert.com/support/"
					   target="_blank"><?php esc_attr_e( 'Ask For Support', 'wc-wlfmc-wishlist' ); ?></a>
				</h1>
				<?php
				if ( $this->installed ) {

					$this->message();
					unset( $this->main_options['options']['marketing'] );
					$fields = new MCT_Fields( $this->main_options );
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}


		/**
		 * Show admin option panel
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function show_marketing() {
			?>
			<div id="wlfmc_options" class="wrap mct-options">
				<h1 class="d-flex space-between f-center" style="margin: 15px 0 10px">
					<span style="padding:0;"><?php esc_attr_e( 'Smart Wishlist Marketing', 'wc-wlfmc-wishlist' ); ?></span>
					<a class="button button-primary" href="https://moreconvert.com/support/"
					   target="_blank"><?php esc_attr_e( 'Ask For Support', 'wc-wlfmc-wishlist' ); ?></a>
				</h1>

				<?php
				if ( $this->installed ) {
					$this->message();
					unset( $this->main_options['options']['button-display'] );
					$fields = new MCT_Fields( $this->main_options );
					$fields->output();
				}
				?>
			</div>
			<div id="snackbar"></div>
			<?php
		}

		/**
		 * Add admin snackbar style.
		 *
		 * @return void
		 */
		public function snackbar_style() {
			?>
			<style>
				#snackbar {
					visibility: hidden;
					min-width: 250px;
					margin-left: -125px;
					background-color: #333;
					color: #fff;
					text-align: center;
					border-radius: 2px;
					padding: 16px;
					position: fixed;
					z-index: 1;
					left: 50%;
					bottom: 30px;
					font-size: 17px;
				}

				#snackbar.show {
					visibility: visible;
					-webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
					animation: fadein 0.5s, fadeout 0.5s 2.5s;
				}

				@-webkit-keyframes fadein {
					from {
						bottom: 0;
						opacity: 0;
					}
					to {
						bottom: 30px;
						opacity: 1;
					}
				}

				@keyframes fadein {
					from {
						bottom: 0;
						opacity: 0;
					}
					to {
						bottom: 30px;
						opacity: 1;
					}
				}

				@-webkit-keyframes fadeout {
					from {
						bottom: 30px;
						opacity: 1;
					}
					to {
						bottom: 0;
						opacity: 0;
					}
				}

				@keyframes fadeout {
					from {
						bottom: 30px;
						opacity: 1;
					}
					to {
						bottom: 0;
						opacity: 0;
					}
				}
			</style>
			<?php
		}

		/**
		 * Run the installation
		 *
		 * @return void
		 *
		 * @version 1.0.1
		 */
		public function install() {
			if ( wp_doing_ajax() ) {
				return;
			}

			$stored_db_version = get_option( 'wlfmc_db_version' );

			if ( ! $stored_db_version || ! $this->installed ) {
				// fresh installation.
				WLFMC_Install()->init();
			} elseif ( version_compare( $stored_db_version, WLFMC_DB_VERSION, '<' ) ) {
				// update database.
				WLFMC_Install()->update( $stored_db_version );
				do_action( 'wlfmc_updated' );
			}


			// Plugin installed.
			do_action( 'wlfmc_installed' );
		}

		/**
		 * Message
		 * define an array of message and show the content od message if
		 * is find in the query string
		 */
		public function message() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$message = apply_filters(
				'wlfmc_panel_messages',
				array(
					'saved'        => $this->get_message( '<strong>' . __( 'Settings saved', 'wc-wlfmc-wishlist' ) . '.</strong>', 'updated', false ),
					'reset'        => $this->get_message( '<strong>' . __( 'Settings reset', 'wc-wlfmc-wishlist' ) . '.</strong>', 'updated', false ),
					'not-validate' => $this->get_message( '<strong>' . __( 'Some fields not validate.', 'wc-wlfmc-wishlist' ) . '</strong>', 'error', false ),
				)
			);

			foreach ( $message as $key => $value ) {
				if ( isset( $_GET[ $key ] ) ) {
					echo wp_kses_post( $message[ $key ] );
				}
			}
			// phpcs:enable
		}

		/**
		 * Get Message
		 * return html code of message
		 *
		 * @param string $message The message.
		 * @param string $type The type of message (can be 'error' or 'updated').
		 * @param bool $echo Set to true if you want to print the message.
		 *
		 * @return string
		 */
		public function get_message( $message, $type = 'error', $echo = true ) {
			$message = '<div id="message" class="' . esc_attr( $type ) . ' fade"><p>' . wp_kses_post( $message ) . '</p></div>';
			if ( $echo ) {
				echo wp_kses_post( $message );
			}

			return $message;
		}


	}
}


/**
 * Unique access to instance of WLFMC_Admin class
 *
 * @return WLFMC_Admin
 */
function WLFMC_Admin() {
	return WLFMC_Admin::get_instance();
}
