<?php
/**
 * Smart Wishlist Admin
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
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
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			$this->main_options = array(
				'sections' => apply_filters(
					'wlfmc_admin_sections',
					array(
						'button-display' => __( 'Button display', 'wc-wlfmc-wishlist' ),
						'user-dashboard' => __( 'User dashboard', 'wc-wlfmc-wishlist' ),
						'marketing'      => __( 'Marketing', 'wc-wlfmc-wishlist' ),
					)
				),
				'options'  => apply_filters(
					'wlfmc_admin_options',
					array(
						'button-display' => array(
							'tabs'   => array(
								'overview'       => __( 'Overview', 'wc-wlfmc-wishlist' ),
								'single-product' => __( 'Product page setting', 'wc-wlfmc-wishlist' ),
								'list-products'  => __( 'Products list setting', 'wc-wlfmc-wishlist' ),
							),
							'fields' => array(
								'overview'       => array(
									'who_can_see_wishlist_options'   => array(
										'label'   => __( 'Enable Wishlist for', 'wc-wlfmc-wishlist' ),
										'type'    => 'select',
										'options' => array(
											'all'   => __( 'All users', 'wc-wlfmc-wishlist' ),
											'users' => __( 'Logined users', 'wc-wlfmc-wishlist' ),
										),
										'default' => 'all',
									),
									'force_user_to_login'            => array(
										'label'     => __( 'Require login or register', 'wc-wlfmc-wishlist' ),
										'desc'      => __( 'To use Wishlist, force users to login or sign up (not recommended)', 'wc-wlfmc-wishlist' ),
										'type'      => 'switch',
										'dependies' => array(
											'id'    => 'who_can_see_wishlist_options',
											'value' => 'all',
										)
									),
									'click_wishlist_button_behavior' => array(
										'label'   => __( 'After clicking the add to Wishlist button', 'wc-wlfmc-wishlist' ),
										'type'    => 'select',
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
										)
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
									'popup_title'                    => array(
										'label'     => __( 'Pop up title', 'wc-wlfmc-wishlist' ),
										'default'   => __( 'Added to Wishlist', 'wc-wlfmc-wishlist' ),
										'type'      => 'text',
										'dependies' => array(
											'id'    => 'click_wishlist_button_behavior',
											'value' => 'open-popup',
										)

									),
									'popup_content'                  => array(
										'label'     => __( 'Pop up content', 'wc-wlfmc-wishlist' ),
										'type'      => 'wp-editor',
										'default'   => __( 'See your favorite product in Wishlist', 'wc-wlfmc-wishlist' ),
										'dependies' => array(
											'id'    => 'click_wishlist_button_behavior',
											'value' => 'open-popup',
										)
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
												'label'             => __( 'view my Wishlist', 'wc-wlfmc-wishlist' ),
												'background'        => '',
												'background-hover'  => '',
												'label-color'       => '',
												'label-hover-color' => '',
												'link'              => 'wishlist',
												'custom-link'       => '',
											),
										),
										'dependies' => array(
											'id'    => 'click_wishlist_button_behavior',
											'value' => 'open-popup',
										),
									),
									'popup_background_color'         => array(
										'label'     => __( 'Pop up background color', 'wc-wlfmc-wishlist' ),
										'type'      => 'color',
										'dependies' => array(
											'id'    => 'click_wishlist_button_behavior',
											'value' => 'open-popup',
										)
									),
									'popup_border_color'             => array(
										'label'     => __( 'Pop up border color', 'wc-wlfmc-wishlist' ),
										'type'      => 'color',
										'dependies' => array(
											'id'    => 'click_wishlist_button_behavior',
											'value' => 'open-popup',
										),
									),
									'button_type'                    => array(
										'label'   => __( 'Button type', 'wc-wlfmc-wishlist' ),
										'type'    => 'radio',
										'options' => array(
											'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
											'text' => __( 'Text only', 'wc-wlfmc-wishlist' ),
											'both' => __( 'Icon and text', 'wc-wlfmc-wishlist' ),
										),
									),
									'button_label_add'               => array(
										'label'   => __( '"Add to Wishlist" button text', 'wc-wlfmc-wishlist' ),
										'type'    => 'text',
										'default' => __( 'Add to Wishlist', 'wc-wlfmc-wishlist' ),
										'dependies' => array(
											array(
												'id'    => 'button_type',
												'value' => 'text,both',
											),
										),
									),
									/*'button_label_remove'    => array(
										'label'   => __( '"Remove From Wishlist" button text', 'wc-wlfmc-wishlist' ),
										'type'    => 'text',
										'default' => __( 'Remove from list', 'wc-wlfmc-wishlist' ),
									),*/
									'button_label_view'              => array(
										'label'   => __( '"View My Wishlist" button text', 'wc-wlfmc-wishlist' ),
										'type'    => 'text',
										'default' => __( 'view my wishlist', 'wc-wlfmc-wishlist' ),
										'dependies' => array(
											array(
												'id'    => 'button_type',
												'value' => 'text,both',
											),
										),
									),
									'button_theme'                   => array(
										'label'   => __( 'Button theme', 'wc-wlfmc-wishlist' ),
										'desc'    => __( 'Enable if use theme default colors for icon and text', 'wc-wlfmc-wishlist' ),
										'type'    => 'switch',
										'default' => '1',
									),
									'icon_style'                     => array(
										'label'     => __( 'Button icon style', 'wc-wlfmc-wishlist' ),
										'type'      => 'color-style',
										'dependies' => array(
											array(
												'id'    => 'button_type',
												'value' => 'icon,both',
											),
											array(
												'id'    => 'button_theme',
												'value' => '0',
											),
										),
									),
									'text_style'                     => array(
										'label'     => __( 'Button text style', 'wc-wlfmc-wishlist' ),
										'type'      => 'color-style',
										'dependies' => array(
											array(
												'id'    => 'button_theme',
												'value' => '0',
											),
											array(
												'id'    => 'button_type',
												'value' => 'text,both',
											),
										),
									),
									'flash_icon'                     => array(
										'label'     => __( 'Flash icon', 'wc-wlfmc-wishlist' ),
										'desc'      => __( 'The icon will flash when you go to the product.', 'wc-wlfmc-wishlist' ),
										'type'      => 'switch',
										'dependies' => array(
											'id'    => 'button_type',
											'value' => 'icon',
										),
									),
									'seperate_icon_and_text'         => array(
										'label'     => __( 'Seperate icon and text', 'wc-wlfmc-wishlist' ),
										'desc'      => __( 'separate icon and text boxes in single product page', 'wc-wlfmc-wishlist' ),
										'type'      => 'switch',
										'dependies' => array(
											'id'    => 'button_type',
											'value' => 'both',
										),
									),
									'seperator_color'                => array(
										'label'     => __( 'Seperator color', 'wc-wlfmc-wishlist' ),
										'type'      => 'color',
										'dependies' => array(
											array(
												'id'    => 'button_type',
												'value' => 'both',
											),
											array(
												'id'    => 'seperate_icon_and_text',
												'value' => '1',
											),
										),
									),
									'product_added_text'             => array(
										'label'   => __( '"Product added" text', 'wc-wlfmc-wishlist' ),
										'desc'    => __( 'Enter the text of the message displayed when the user adds a product to the Wishlist', 'wc-wlfmc-wishlist' ),
										'default' => __( 'Product added!', 'wc-wlfmc-wishlist' ),
										'type'    => 'text',
									),
									'already_in_wishlist_text'       => array(
										'label'   => __( '"Product already in Wishlist" text', 'wc-wlfmc-wishlist' ),
										'desc'    => __( 'Enter the text for the message displayed when the user will try to add a product that is already in the Wishlist', 'wc-wlfmc-wishlist' ),
										'default' => __( 'The product is already in your Wishlist!', 'wc-wlfmc-wishlist' ),
										'type'    => 'text',
									),
								),
								'single-product' => array(
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
									),
									'shortcode_button'         => array(
										'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
										'type'    => 'copy-text',
										'default' => '[wlfmc_add_to_wishlist]',
										'dependies' => array(
											'id'    => 'wishlist_button_position',
											'value' => 'shortcode',
										),
									),
								),
								'list-products'  => array(
									'show_on_loop'  => array(
										'label' => __( 'Show "add to Wishlist" in loop', 'wc-wlfmc-wishlist' ),
										'desc'  => __( 'Enable the "add to Wishlist" feature in WooCommerce products\' loop', 'wc-wlfmc-wishlist' ),
										'type'  => 'switch',
									),
									'loop_position' => array(
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
									),
									'shortcode_button'         => array(
										'label'   => __( 'Shortcode button', 'wc-wlfmc-wishlist' ),
										'type'    => 'copy-text',
										'default' => '[wlfmc_add_to_wishlist]',
										'dependies' => array(
											'id'    => 'loop_position',
											'value' => 'shortcode',
										),
									),
								),
							),
						),
						'user-dashboard' => array(
							'tabs'   => array(
								'display-configuration' => __( 'Display configuration', 'wc-wlfmc-wishlist' ),
								'shared-buttons'        => __( 'Shared buttons', 'wc-wlfmc-wishlist' ),
							),
							'fields' => array(
								'display-configuration' => array(
									'wishlist_page' => array(
										'label'   => __( 'Choose the Wishlist dashboard page', 'wc-wlfmc-wishlist' ),
										'desc'    => __( 'Add below shortcode to a page.', 'wc-wlfmc-wishlist' ),
										'type'    => 'page-select',
										'default' => get_option( 'wlfmc_wishlist_page_id' ),
									),
									'shortcode_page'         => array(
										'label'   => __( 'Shortcode page', 'wc-wlfmc-wishlist' ),
										'type'    => 'copy-text',
										'default' => '[wlfmc_wishlist]',
									),
								),
								'shared-buttons'        => array(
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
									),
								),
							),
						),
						'marketing'      => array(
							'tabs'   => array(
								'coupon'     => __( 'Coupon', 'wc-wlfmc-wishlist' ),
								'conditions' => __( 'Conditions', 'wc-wlfmc-wishlist' ),
								'emails'     => __( 'Email automation', 'wc-wlfmc-wishlist' ),
							),
							'fields' => array(
								'coupon'     => array(
									'discount-type'        => array(
										'label'   => __( 'Discount type', 'wc-wlfmc-wishlist' ),
										'type'    => 'select',
										'options' => wc_get_coupon_types(),
									),
									'coupon-amount'        => array(
										'label'       => __( 'Coupon amount', 'wc-wlfmc-wishlist' ),
										'placeholder' => wc_format_localized_price( 0 ),
										'type'        => 'text',
										'desc'        => __( 'Value of the coupon.', 'wc-wlfmc-wishlist' ),
									),
									'free-shipping'        => array(
										'label' => __( 'Allow free shipping', 'wc-wlfmc-wishlist' ),
										'type'  => 'checkbox',
										/* translators: %s: woocommerce document url */
										'desc'  => sprintf( __( 'Check this box if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'wc-wlfmc-wishlist' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
									),
									'expiry-date'          => array(
										'label' => __( 'Coupon expiry after days', 'wc-wlfmc-wishlist' ),
										'desc'  => __( 'The coupon will expire after custom days.', 'wc-wlfmc-wishlist' ),
										'type'  => 'number',
									),
									'user-restriction'     => array(
										'label' => __( 'User restriction', 'wc-wlfmc-wishlist' ),
										'desc'  => __( 'Coupon work just for their account email', 'wc-wlfmc-wishlist' ),
										'type'  => 'checkbox',
									),
									'delete-after-expired' => array(
										'label' => __( 'Delete the Coupon', 'wc-wlfmc-wishlist' ),
										'desc'  => __( 'Delete the coupon after the expiry', 'wc-wlfmc-wishlist' ),
										'type'  => 'checkbox',
									),
								),
								'conditions' => array(
									'period-days'            => array(
										'label' => __( 'Repeat promotion period', 'wc-wlfmc-wishlist' ),
										'desc'  => __( 'Based on the number of days', 'wc-wlfmc-wishlist' ),
										'type'  => 'number',
									),
									'minimum-wishlist-total' => array(
										'label' => __( 'Minimum price of total Wishlist items', 'wc-wlfmc-wishlist' ),
										'type'  => 'text',
									),
									'minimum-wishlist-count' => array(
										'label' => __( 'Minimum number of products on the Wishlist', 'wc-wlfmc-wishlist' ),
										'type'  => 'number',
									),
									'include-product'        => array(
										'label' => __( 'Include at least one of these products', 'wc-wlfmc-wishlist' ),
										'type'  => 'search-product',
									),
									'delete-queue-emails'    => array(
										'label'        => __( 'Delete queue emails', 'wc-wlfmc-wishlist' ),
										/* translators: %s: emails count in the send queue */
										'desc'         => sprintf( __( 'If checked, %s emails in queue list will be deleted !', 'wc-wlfmc-wishlist' ), WLFMC_Offer_Emails()->get_count_send_queue() ),
										'type'         => 'checkbox',
										'parent_class' => WLFMC_Offer_Emails()->get_count_send_queue() > 0 ? '' : 'hidden-option',
									),
								),
								'emails'     => array(
									'offer_emails' => array(
										'label'           => __( 'Offer emails', 'wc-wlfmc-wishlist' ),
										'type'            => 'repeator',
										'add_new_label'   => __( 'Add new email', 'wc-wlfmc-wishlist' ),
										'class'           => 'mct-repeater-block',
										'section'         => 'marketing',
										'repeator_fields' => array(
											'enable_email'    => array(
												'label' => __( 'Enable email', 'wc-wlfmc-wishlist' ),
												'type'  => 'switch',
											),
											'send_mail_type'  => array(
												'label'   => __( 'Send email type', 'wc-wlfmc-wishlist' ),
												'default' => 'immediately',
												'type'    => 'radio',
												'options' => array(
													'immediately' => __( 'Immediately', 'wc-wlfmc-wishlist' ),
													'after-days'  => __( 'After some days', 'wc-wlfmc-wishlist' ),
												)
											),
											'send_after_days' => array(
												'label'     => __( 'Send this email after days', 'wc-wlfmc-wishlist' ),
												'type'      => 'number',
												'dependies' => array(
													'id'    => 'send_mail_type',
													'value' => 'after-days',
												),
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
											'mail_heading'    => array(
												'label' => __( 'Email heading', 'wc-wlfmc-wishlist' ),
												'desc'  => __( 'Enter the title for the email notification. Leave blank to use the default heading: "<i>There is a deal for you!</i>". You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code>', 'wc-wlfmc-wishlist' ),
												'type'  => 'text',
											),
											'mail_subject'    => array(
												'label'   => __( 'Email subject', 'wc-wlfmc-wishlist' ),
												'desc'    => __( 'Enter the email subject line. Leave blank to use the default subject: "<i>A product of your Wishlist is on sale</i>". You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code>', 'wc-wlfmc-wishlist' ),
												'default' => '',
												'type'    => 'text',
											),
											'html_content'    => array(
												'label'     => __( 'Email html content', 'wc-wlfmc-wishlist' ),
												'desc'      => __( 'This field lets you modify the main content of the HTML email. You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code>', 'wc-wlfmc-wishlist' ),
												'type'      => 'textarea',
												'default'   => WLFMC_Offer_Emails::get_default_content( 'html', 1 ),
												'dependies' => array(
													'id'    => 'mail_type',
													'value' => 'html',
												),
											),
											'text_content'    => array(
												'label'     => __( 'Email plain content', 'wc-wlfmc-wishlist' ),
												'desc'      => __( 'This field lets you modify the main content of the text email. You can use the following placeholders: <code>{user_name}</code> <code>{user_email}</code> <code>{user_first_name}</code> <code>{user_last_name}</code> <code>{coupon_code}</code> <code>{coupon_amount}</code> <code>{expiry_date}</code> <code>{wishlist_url}</code> <code>{checkout_url}</code> <code>{shop_url}</code>', 'wc-wlfmc-wishlist' ),
												'type'      => 'textarea',
												'default'   => WLFMC_Offer_Emails::get_default_content( 'plain' ),
												'dependies' => array(
													'id'    => 'mail_type',
													'value' => 'plain',
												),
											),
										),
										'default'         => array(
											array(
												'enable_email'    => '0',
												'send_mail_type'  => 'after-days',
												'send_after_days' => '1',
												'mail_type'       => 'html',
												'mail_heading'    => '',
												'mail_subject'    => __( 'Check it out, {user_name}', 'wc-wlfmc-wishlist' ),
												'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 1 ),
												'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 1 ),
											),
											array(
												'enable_email'    => '0',
												'send_mail_type'  => 'after-days',
												'send_after_days' => '3',
												'mail_type'       => 'html',
												'mail_heading'    => '',
												'mail_subject'    => __( 'Deals you’ve been waiting for!', 'wc-wlfmc-wishlist' ),
												'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 2 ),
												'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 2 ),
											),
											array(
												'enable_email'    => '0',
												'send_mail_type'  => 'after-days',
												'send_after_days' => '5',
												'mail_type'       => 'html',
												'mail_heading'    => '',
												'mail_subject'    => __( 'Got {coupon_amount} off your favorites?', 'wc-wlfmc-wishlist' ),
												'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 3 ),
												'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 3 ),
											),
											array(
												'enable_email'    => '0',
												'send_mail_type'  => 'after-days',
												'send_after_days' => '7',
												'mail_type'       => 'html',
												'mail_heading'    => '',
												'mail_subject'    => __( 'The item on your wishlist is almost sold out!', 'wc-wlfmc-wishlist' ),
												'html_content'    => WLFMC_Offer_Emails::get_default_content( 'html', 4 ),
												'text_content'    => WLFMC_Offer_Emails::get_default_content( 'plain', 4 ),
											),
										)
									),
								),
							)
						),
					)
				),
				'title'    => __( 'Manage Settings', 'wc-wlfmc-wishlist' ),
				'type'     => 'simple-panel',
				'id'       => 'wlfmc_options',
			);

			$this->main_panel = new MCT_Admin( $this->main_options );

			// install plugin, or update from older versions.
			add_action( 'init', array( $this, 'install' ) );

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

			// add a post display state for special WC pages.
			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
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
		 * @param array   $post_states An array of post display states.
		 * @param WP_Post $post The current post object.
		 *
		 * @return array
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( get_option( 'wlfmc_wishlist_page_id' ) === $post->ID ) {
				$post_states['wlfmc_wishlist_page_id'] = __( 'Wishlist page', 'wc-wlfmc-wishlist' );
			}

			return $post_states;
		}


		/**
		 * Add admin menu
		 *
		 * @return void
		 */
		public function add_admin_menu() {

			$page = add_menu_page(
				__( 'MC Wishlist', 'wc-wlfmc-wishlist' ),
				__( 'MC Wishlist', 'wc-wlfmc-wishlist' ),
				'manage_options',
				'mc-wishlist',
				array( $this, 'show_panel' ),
				'dashicons-heart',
				null
			);
			add_action( 'load-' . $page, array( $this, 'admin_enqueue_scripts' ) );


		}

		/**
		 * Enqueue script and styles in admin side
		 * Add style and scripts to administrator
		 *
		 * @retrun void
		 */
		public function admin_enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'wlfmc-admin', MC_WLFMC_URL . 'assets/admin/js/admin-panel' . $suffix . '.js', array( 'jquery' ), WLFMC_VERSION, true );

			$locale  = localeconv();
			$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

			$params = array(
				/* translators: %s: decimal */
				'i18n_decimal_error'       => sprintf( __( 'Please enter with one decimal point (%s) without thousand separators.', 'wc-wlfmc-wishlist' ), $decimal ),
				/* translators: %s: price decimal separator */
				'i18n_mon_decimal_error'   => sprintf( __( 'Please enter with one monetary decimal point (%s) without thousand separators and currency symbols.', 'wc-wlfmc-wishlist' ), wc_get_price_decimal_separator() ),
				'i18n_percent_description' => __( 'Value of the coupon(percent).', 'wc-wlfmc-wishlist' ),
				'i18n_amount_description'  => __( 'Value of the coupon(price).', 'wc-wlfmc-wishlist' ),
				'decimal_point'            => $decimal,
				'mon_decimal_point'        => wc_get_price_decimal_separator(),
			);

			wp_localize_script( 'wlfmc-admin', 'wlfmc_wishlist_admin', $params );
			wp_enqueue_script( 'wlfmc-admin' );
			add_action( 'admin_head', array( $this, 'snackbar_style' ) );

		}

		/**
		 * Show admin option panel
		 *
		 * @return void
		 */
		public function show_panel() {
			?>
			<div id="wlfmc_options" class="wrap mct-options">
				<h1><?php esc_attr_e( 'Smart Wishlist settings', 'wc-wlfmc-wishlist' ); ?></h1>
				<span><?php esc_attr_e( 'You can use manually manage them', 'wc-wlfmc-wishlist' ); ?></span>
				<?php
				$this->message();

				$fields = new MCT_Fields( $this->main_options );
				$fields->output();

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
		 */
		public function install() {
			if ( wp_doing_ajax() ) {
				return;
			}

			$stored_db_version = get_option( 'wlfmc_db_version' );

			if ( ! $stored_db_version || ! WLFMC_Install()->is_installed() ) {
				// fresh installation.
				WLFMC_Install()->init();
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
		 * @param bool   $echo Set to true if you want to print the message.
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
