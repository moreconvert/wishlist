<?php
/**
 * Shortcodes Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}// Exit if accessed directly

if ( ! class_exists( 'WLFMC_Shortcode' ) ) {
	/**
	 * Woocommerce Smart Wishlist Shortcodes
	 *
	 */
	class WLFMC_Shortcode {

		/**
		 * Init shortcodes available for the plugin
		 *
		 * @return void
		 */
		public static function init() {
			// register shortcodes.
			add_shortcode( 'wlfmc_wishlist', array( 'WLFMC_Shortcode', 'wishlist' ) );
			add_shortcode( 'wlfmc_add_to_wishlist', array( 'WLFMC_Shortcode', 'add_to_wishlist' ) );

		}


		/**
		 * Print the wishlist HTML.
		 *
		 * @param array $atts Array of attributes for the shortcode.
		 * @param string $content Shortcode content (none expected).
		 *
		 * @return string Rendered shortcode
		 *
		 */
		public static function wishlist( $atts, $content = null ) {
			global $wlfmc_is_wishlist, $wlfmc_wishlist_token;

			$atts = shortcode_atts(
				array(
					'per_page'        => 5,
					'current_page'    => 1,
					'pagination'      => 'no',
					'wishlist_id'     => get_query_var( 'wishlist_id', false ),
					'action_params'   => get_query_var( WLFMC()->wishlist_param, false ),
					'no_interactions' => 'no',

				),
				$atts
			);

			/**
			 * @var $per_page int
			 * @var $current_page int
			 * @var $pagination string
			 * @var $wishlist_id int
			 * @var $action_params array
			 * @var $no_interactions string
			 */
			extract( $atts ); // phpcs:ignore

			$columns = apply_filters( 'wlfmc_wishlist_view_table_heading', array(
				'product-checkbox'     => '',
				'product-remove'       => '',
				'product-thumbnail'    => '',
				'product-name'         => __( 'Product name', 'wc-wlfmc-wishlist' ),
				'product-added-price'  => __( 'Added price', 'wc-wlfmc-wishlist' ),
				'product-price'        => __( 'Unit price', 'wc-wlfmc-wishlist' ),
				'product-stock-status' => __( 'Stock status', 'wc-wlfmc-wishlist' ),
				'product-quantity'     => __( 'Quantity', 'wc-wlfmc-wishlist' ),
				'product-add-to-cart'  => __( 'Add To cart', 'wc-wlfmc-wishlist' )
			) );


			// retrieve options from query string.
			$action_params = explode( '/', apply_filters( 'wlfmc_current_wishlist_view_params', $action_params ) );
			$action        = ( isset( $action_params[0] ) ) ? $action_params[0] : 'view';


			// init params needed to load correct template.
			$template_part     = 'view';
			$no_interactions   = 'yes' == $no_interactions;
			$additional_params = array(

				// wishlist data.
				'wishlist'                 => false,
				'wishlist_token'           => '',
				'wishlist_id'              => false,
				'is_private'               => false,

				// wishlist items.
				'count'                    => 0,
				'wishlist_items'           => array(),

				// page data.
				'current_page'             => $current_page,
				'page_links'               => false,

				// user data.
				'is_user_logged_in'        => is_user_logged_in(),
				'is_user_owner'            => true,

				// view data.
				'no_interactions'          => $no_interactions,
				'columns'                  => $columns,

				// share data.
				'share_enabled'            => false,

				// template data.
				'template_part'            => $template_part,
				'additional_info'          => false,
				'available_multi_wishlist' => false,
				'users_wishlists'          => array(),
				'form_action'              => esc_url( WLFMC()->get_wishlist_url( 'view' ) ),
			);

			$wishlist = WLFMC_Wishlist_Factory::get_current_wishlist( $atts );

			if ( $wishlist && $wishlist->current_user_can( 'view' ) ) {
				$options = new MCT_Options( 'wlfmc_options' );

				// set global wishlist token.
				$wlfmc_wishlist_token = $wishlist->get_token();

				// retrieve wishlist params.
				$is_user_owner = $wishlist->is_current_user_owner();
				$count         = $wishlist->count_items();
				$offset        = 0;

				// sets current page, number of pages and element offset.
				$queried_page = get_query_var( 'paged' );
				$current_page = max( 1, $queried_page ? $queried_page : $current_page );

				// sets variables for pagination, if shortcode atts is set to yes.
				if ( 'yes' == $pagination && ! $no_interactions && $count > 1 ) {
					$pages = ceil( $count / $per_page );

					if ( $current_page > $pages ) {
						$current_page = $pages;
					}

					$offset = ( $current_page - 1 ) * $per_page;

					if ( $pages > 1 ) {
						$page_links = paginate_links(
							array(
								'base'     => esc_url( add_query_arg( array( 'paged' => '%#%' ), $wishlist->get_url() ) ),
								'format'   => '?paged=%#%',
								'current'  => $current_page,
								'total'    => $pages,
								'show_all' => true,
							)
						);
					}
				} else {
					$per_page = 0;
				}

				// retrieve items to print.
				$wishlist_items = $wishlist->get_items( $per_page, $offset );

				// retrieve wishlist information.
				$is_default     = $wishlist->get_is_default();
				$wishlist_token = $wishlist->get_token();

				$additional_params = wp_parse_args(
					array(
						// wishlist items.
						'count'          => $count,
						'wishlist_items' => $wishlist_items,

						// wishlist data.
						'wishlist'       => $wishlist,
						'is_default'     => $is_default,
						'wishlist_token' => $wishlist_token,
						'wishlist_id'    => $wishlist->get_id(),
						'is_private'     => $wishlist->has_privacy( 'private' ),

						// page data.Wishlist
						'current_page'   => $current_page,
						'page_links'     => isset( $page_links ) && ! $no_interactions ? $page_links : false,

						// user data.
						'is_user_owner'  => $is_user_owner,

						// template data.
						'form_action'    => $wishlist->get_url(),
					),
					$additional_params
				);
				// share options.
				$enable_share = $options->get_option( 'enable_share' ) == true && ! $wishlist->has_privacy( 'private' );
				$share_items  = $options->get_option( 'share_items' );

				if ( ! $no_interactions && $enable_share && is_array( $share_items ) && ! empty( $share_items ) ) {
					$share_link_title = apply_filters( 'wlfmc_socials_share_link_title', __( 'Share Link:', 'wc-wlfmc-wishlist' ) );
					$share_link_url   = apply_filters( 'wlfmc_shortcode_share_link_url', $wishlist->get_share_url(), $wishlist );
					$socials_title    = apply_filters( 'wlfmc_socials_title', urlencode( $options->get_option( 'socials_title' ) ) );
					$socials_text     = apply_filters( 'wlfmc_socials_text', urlencode( $options->get_option( 'socials_text' ) ) );
					$messenger_id     = $options->get_option( 'messenger_app_id' );

					$share_atts = array(
						'share_socials_title' => $socials_title,
						'share_socials_text'  => $socials_text,
						'share_messenger_id'  => $messenger_id,
						'share_link_title'    => $share_link_title,
						'share_link_url'      => $share_link_url,
					);

					foreach ( $share_items as $item ) {
						$share_atts[ 'share_' . $item . '_icon' ] = apply_filters( 'wlfmc_socials_share_' . $item . '_icon', '<i class="wlfmc-icon-' . $item . '"></i>' );
					}
					if ( wp_is_mobile() ) {
						$share_whatsapp_url = 'whatsapp://send?text=' . $socials_title . ' – ' . urlencode( $share_link_url );
						$share_telegram_url = 'tg://msg_url?url=' . urlencode( $share_link_url ) . '&text=' . $socials_text;
					} else {
						$share_whatsapp_url = 'https://web.whatsapp.com/send?text=' . $socials_title . ' – ' . urlencode( $share_link_url );
						$share_telegram_url = 'https://t.me/share/url?url=' . urlencode( $share_link_url ) . '&text=' . $socials_text;
					}

					$share_atts['share_whatsapp_url'] = $share_whatsapp_url;
					$share_atts['share_telegram_url'] = $share_telegram_url;

					$additional_params['share_enabled'] = true;
					$additional_params['share_items']   = $share_items;
					$additional_params['share_atts']    = $share_atts;
				}
			}

			// filter params.
			$additional_params = apply_filters( 'wlfmc_wishlist_params', $additional_params, $action, $action_params, $pagination, $per_page, $atts );

			$atts = array_merge(
				$atts,
				$additional_params
			);

			$atts['fragment_options'] = WLFMC_Frontend()->format_fragment_options( $atts, 'wishlist' );

			// apply filters for add to cart buttons.
			WLFMC_Frontend()->alter_add_to_cart_button();

			// sets that we're in the wishlist template.
			$wlfmc_is_wishlist = true;

			$template = wlfmc_get_template( 'wishlist.php', $atts, true );

			// we're not in wishlist template anymore.
			$wlfmc_is_wishlist    = false;
			$wlfmc_wishlist_token = null;

			// remove filters for add to cart buttons.
			WLFMC_Frontend()->restore_add_to_cart_button();

			// enqueue scripts.
			WLFMC_Frontend()->enqueue_scripts();

			return apply_filters( 'wlfmc_wishlist_html', $template, array(), true );
		}

		/**
		 * Return "Add to Wishlist" button.
		 *
		 * @param array $atts Array of parameters for the shortcode
		 * @param string $content Shortcode content (usually empty)
		 *
		 * @return string
		 */
		public static function add_to_wishlist( $atts, $content = null ) {
			global $product;

			// product object.
			$current_product = ( isset( $atts['product_id'] ) ) ? wc_get_product( $atts['product_id'] ) : false;
			$current_product = $current_product ? $current_product : $product;

			if ( ! $current_product || ! $current_product instanceof WC_Product ) {
				return '';
			}

			$current_product_id = $current_product->get_id();

			// product parent.
			$current_product_parent = $current_product->get_parent_id();

			// labels & icons settings.

			$options                        = new MCT_Options( 'wlfmc_options' );
			$disable_wishlist               = false;
			$who_can_see_wishlist_options   = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			$force_user_to_login            = $options->get_option( 'force_user_to_login', false );
			$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );
			$added_to_wishlist_behaviour    = apply_filters( 'wlfmc_after_add_to_wishlist_behaviour', 'view' ); // view or remove can be set
			$already_in_wishlist            = $options->get_option( 'already_in_wishlist_text', __( 'The product is already in your wishlist!', 'wc-wlfmc-wishlist' ) );
			$product_added                  = $options->get_option( 'product_added_text', __( 'Product added!', 'wc-wlfmc-wishlist' ) );
			$loop_position                  = $options->get_option( 'loop_position', 'after_add_to_cart' );
			$single_position                = $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
			$button_label_add               = $options->get_option( 'button_label_add', __( 'add to wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_view              = $options->get_option( 'button_label_view', __( 'view my wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_remove            = __( 'remove from list', 'wc-wlfmc-wishlist' );
			$seperate_icon_and_text         = $options->get_option( 'seperate_icon_and_text', false );
			$button_type                    = $options->get_option( 'button_type', 'icon' );
			$flash_icon                     = $options->get_option( 'flash_icon', false );
			$button_theme                   = $options->get_option( 'button_theme', true );

			// button label.
			$label = apply_filters( 'wlfmc_button_add_label', $button_label_add );

			// button icon.
			$icon       = apply_filters( 'wlfmc_button_icon', 'heart' );
			$added_icon = apply_filters( 'wlfmc_button_added_icon', 'heart-o' );

			// button class.
			$is_single = isset( $atts['is_single'] ) ? $atts['is_single'] : wlfmc_is_single();
			$classes   = apply_filters( 'wlfmc_add_to_wishlist_button_classes', 'add_to_wishlist single_add_to_wishlist button alt' );

			if ( ( 'all' === $who_can_see_wishlist_options && true == $force_user_to_login && ! is_user_logged_in() ) ) {
				$disable_wishlist = true;
				$classes          .= ' wlfmc_btn_login_need';
			}

			if ( true == $seperate_icon_and_text && 'both' == $button_type ) {
				$classes .= ' have-sep';
			}
			if ( true == $flash_icon && 'icon' == $button_type && $is_single ) {
				$classes .= ' wlfmc_flash';
			}
			// check if product is already in wishlist.
			$exists            = WLFMC()->is_product_in_wishlist( $current_product_id );
			$container_classes = $exists ? 'exists' : '';
			$found_in_list     = $exists ? wlfmc_get_wishlist( false ) : false;
			$found_item        = $found_in_list ? $found_in_list->get_product( $current_product_id ) : false;


			if ( ( $loop_position === 'before_image' && ! $is_single ) || ( $single_position === 'thumbnails' && $is_single ) ) {
				$container_classes .= ' wlfmc_top_of_image';
				$button_type       = 'icon';
				$classes           = str_replace( 'button', '', $classes );
			}


			switch ( $added_to_wishlist_behaviour ) {
				case 'remove':
					$template_part_after_add = 'remove';
					break;
				case 'view':
					$template_part_after_add = 'browse';
					break;
			}

			$template_part = $exists ? $template_part_after_add : 'button';

			//$template_part = isset( $atts['added_to_wishlist'] ) && $atts['added_to_wishlist'] && 'button' == $template_part_after_add  ?  'added' : $template_part;


			if ( 'remove' == $template_part ) {
				$label   = apply_filters( 'wlfmc_button_remove_label', $button_label_remove );
				$classes = str_replace( array( 'single_add_to_wishlist', 'add_to_wishlist' ), '', $classes );
			}
			if ( 'browse' == $template_part ) {
				$label   = apply_filters( 'wlfmc_button_view_label', $button_label_view );
				$classes = str_replace( array( 'single_add_to_wishlist', 'add_to_wishlist' ), '', $classes );
			}


			if ( 'icon' == $button_type ) {
				$label = '';
			}
			if ( 'text' == $button_type ) {
				$icon       = '';
				$added_icon = '';
			}

			if ( true != $button_theme ) {
				$classes = str_replace( 'button', '', $classes );
			}
			//

			// get wishlist url.
			//$wishlist_url = WLFMC()->get_wishlist_url();
			$wishlist_url = is_user_logged_in() ? wc_get_account_endpoint_url( 'wlfmc-wishlist' ) : WLFMC()->get_wishlist_url();

			// get product type.
			$product_type = $current_product->get_type();

			$additional_params = array(
				'base_url'                  => wlfmc_get_current_url(),
				'wishlist_url'              => $wishlist_url,
				'in_default_wishlist'       => $exists,
				'exists'                    => $exists,
				'container_classes'         => $container_classes,
				'is_single'                 => $is_single,
				'found_in_list'             => $found_in_list,
				'found_item'                => $found_item,
				'product_id'                => $current_product_id,
				'parent_product_id'         => $current_product_parent ? $current_product_parent : $current_product_id,
				'product_type'              => $product_type,
				'label'                     => $label,
				'already_in_wishslist_text' => apply_filters( 'wlfmc_product_already_in_wishlist_text_button', $already_in_wishlist ),
				'product_added_text'        => apply_filters( 'wlfmc_product_added_to_wishlist_message_button', $product_added ),
				'icon'                      => $icon,
				'link_classes'              => $classes,
				'available_multi_wishlist'  => false,
				'disable_wishlist'          => $disable_wishlist,
				'loop_position'             => $loop_position,
				'template_part'             => $template_part,
				'enabled_popup'             => false,

			);
			// let third party developer filter options.

			// popup
			if ( 'open-popup' === $click_wishlist_button_behavior ) {


				$popup_title        = $options->get_option( 'popup_title' );
				$popup_content      = $options->get_option( 'popup_content' );
				$popup_position     = $options->get_option( 'popup_position', 'center-center' );
				$popup_size         = $options->get_option( 'popup_size', 'small' );
				$popup_image        = $options->get_option( 'popup_image' );
				$popup_image_size   = $options->get_option( 'popup_image_size', 'medium' );
				$buttons            = $options->get_option( 'popup_buttons' );
				$use_featured_image = $options->get_option( 'use_featured_image' );
				$image_attributes   = wp_get_attachment_image_src( $popup_image, $popup_image_size );
				$image_attributes   = ( true == $use_featured_image ) ? wp_get_attachment_image_src( $current_product->get_image_id() ) : $image_attributes;
				$popup_image_src    = $image_attributes ? $image_attributes[0] : '';
				$popup_position     = explode( '-', $popup_position );
				$popup_vertical     = $popup_position[0];
				$popup_horizontal   = $popup_position[1];

				$additional_params = array_merge(
					$additional_params,
					array(
						'enabled_popup'    => true,
						'template_part'    => $template_part,
						'popup_vertical'   => $popup_vertical,
						'popup_horizontal' => $popup_horizontal,
						'popup_size'       => $popup_size,
						'popup_image'      => $popup_image_src,
						'popup_title'      => $popup_title,
						'popup_content'    => $popup_content,
						'buttons'          => $buttons,

					)
				);
			}

			$additional_params = apply_filters( 'wlfmc_add_to_wishlist_params', $additional_params, $atts );


			$atts = shortcode_atts(
				$additional_params,
				$atts
			);


			// change icon when item exists in wishlist.
			if ( $atts['exists'] ) {
				$atts['icon'] = $added_icon;
			}


			$icon_html = ! empty( $atts['icon'] ) ? '<i class="wlfmc-icon-' . $atts['icon'] . '"></i>' : '';

			// set fragment options.
			$atts['fragment_options'] = WLFMC_Frontend()->format_fragment_options( $atts, 'add_to_wishlist' );
			$atts['icon']             = apply_filters( 'wlfmc_add_to_wishlist_icon_html', $icon_html, $atts );

			$template = wlfmc_get_template( 'add-to-wishlist.php', $atts, true );

			// enqueue scripts.
			WLFMC_Frontend()->enqueue_scripts();

			return apply_filters( 'wlfmc_add_to_wishlist_button_html', $template, $wishlist_url, $product_type, $exists, $atts );
		}

	}
}

WLFMC_Shortcode::init();
