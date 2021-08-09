<?php
/**
 * Add to wishlist button template
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $base_url                  string Current page url
 * @var $wishlist_url              string Url to wishlist page
 * @var $exists                    bool Whether current product is already in wishlist
 * @var $found_in_list             WLFMC_Wishlist Wishlist
 * @var $found_item                WLFMC_Wishlist_Item Wishlist item
 * @var $product_id                int Current product id
 * @var $parent_product_id         int Parent for current product
 * @var $product_type              string Current product type
 * @var $is_single                 bool Whether you're currently on single product template
 * @var $label                     string Button label
 * @var $already_in_wishslist_text string Already in wishlist text
 * @var $product_added_text        string Product added text
 * @var $icon                      string Icon for Add to Wishlist button
 * @var $link_classes              string Classed for Add to Wishlist button
 * @var $available_multi_wishlist  bool Whether add to wishlist is available or not
 * @var $disable_wishlist          bool Whether wishlist is disabled or not
 * @var $enabled_popup             bool Whether popup is enabled or not
 * @var $template_part             string Template part
 * @var $container_classes         string Container classes
 * @var $fragment_options          array Array of data to send through ajax calls
 * @var $popup_vertical            string Vertically position of popup
 * @var $popup_horizontal          string Horizontally position of popup
 * @var $popup_size                string Popup size
 * @var $popup_image               string Popup image
 * @var $popup_title               string Popup title
 * @var $popup_content             string Popup content
 * @var $buttons                   array Array of buttons to show in popup
 * @var $var                       array Array of attributes that needs to be sent to sub-template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $product;
?>

<div class="wlfmc-add-button">
	<a href="<?php echo esc_url( add_query_arg( 'add_to_wishlist', $product_id, $base_url ) ); ?>" rel="nofollow" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-product-type="<?php echo esc_attr( $product_type ); ?>" data-original-product-id="<?php echo esc_attr( $parent_product_id ); ?>" class="<?php echo esc_attr( $link_classes ); ?>" data-title="<?php echo esc_attr( apply_filters( 'wlfmc_add_to_wishlist_title', $label ) ); ?>">
		<?php echo wp_kses_post( $icon ); ?>
		<?php if ( '' !== $label ) : ?>
			<span><?php echo wp_kses_post( $label ); ?></span>
		<?php endif; ?>
	</a>
</div>
