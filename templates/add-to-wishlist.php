<?php
/**
 * Add to wishlist template
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

<div
	class="wlfmc-add-to-wishlist add-to-wishlist-<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $container_classes ); ?> wishlist-fragment on-first-load"
	data-fragment-ref="<?php echo esc_attr( $product_id ); ?>"
	data-fragment-options="<?php echo esc_attr( wp_json_encode( $fragment_options ) ); ?>">

	<?php if ( ! ( $disable_wishlist ) ) : ?>

		<!-- ADD TO WISHLIST -->
		<?php wlfmc_get_template( 'add-to-wishlist/' . $template_part . '.php', $var ); ?>

		<?php if ( $enabled_popup ) : ?>

			<?php $unique_id = mt_rand(); ?>

			<!-- WISHLIST POPUP -->
			<div class="wlfmc-popup size_<?php echo esc_attr( $popup_size ); ?>"
				 data-horizontal="<?php echo esc_attr( $popup_horizontal ); ?>"
				 data-vertical="<?php echo esc_attr( $popup_vertical ); ?>"
				 id="add_to_wishlist_popup_<?php echo esc_attr( $product_id ); ?>_<?php echo esc_attr( $unique_id ); ?>">
				<a class="wlfmc-popup-close-absolute" href="#"
				   id="add_to_wishlist_popup_<?php echo esc_attr( $product_id ); ?>_<?php echo esc_attr( $unique_id ); ?>_close">×</a>
				<?php if ( $popup_image && 'large' === $popup_size ) : ?>
					<div class="wlfmc-popup-header">
						<figure>
							<img src="<?php echo esc_url( $popup_image ); ?>" alt="<?php echo esc_html( $popup_title ); ?>"/>
						</figure>
					</div>
				<?php endif; ?>

				<div class="wlfmc-popup-content">

					<strong class="wlfmc-popup-title"><?php echo esc_html( $popup_title ); ?></strong>
					<div class="wlfmc-popup-desc">
						<?php echo esc_html( $popup_content ); ?>
					</div>


				</div>
				<div class="wlfmc-popup-footer">
					<?php if ( $buttons && is_array( $buttons ) && ! empty( $buttons ) ) : ?>
						<?php foreach ( $buttons as $k => $button ) : ?>
							<?php
							switch ( $button['link'] ) {
								case 'back':
									echo '<a href="#!" class="wlfmc-popup-close wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '" id="add_to_wishlist_popup_' . esc_attr( $product_id ) . '_' . esc_attr( $unique_id ) . '_close">' . esc_attr( $button['label'] ) . '</a>';
									break;
								case 'signup-login':
									echo ! is_user_logged_in() ? '<a href="' . esc_url( wp_login_url() ) . '" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
								case 'wishlist':
									echo '<a href="' . esc_url( $wishlist_url ) . '" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>';
									break;
								case 'custom-link':
									echo trim( $button['custom-link'] ) !== '' ? '<a href="' . esc_url( $button['custom-link'] ) . '" rel="nofollow" class="wlfmc-btn wlfmc_btn_' . esc_attr( $k ) . '">' . esc_attr( $button['label'] ) . '</a>' : '';
									break;
							}
							?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="wlfmc-add-button">
			<a href="<?php echo esc_url( add_query_arg( array( 'wishlist_notice' => 'true', 'add_to_wishlist' => $product_id, ), get_permalink( wc_get_page_id( 'myaccount' ) ) ) ); // @codingStandardsIgnoreLine. ?>" rel="nofollow"
				class="disabled_item
				<?php
				echo esc_attr( str_replace( array( 'add_to_wishlist', 'single_add_to_wishlist', ), '', $link_classes ) ); // @codingStandardsIgnoreLine. ?>">
				<?php echo wp_kses_post( $icon ); ?>
				<?php if ( '' !== $label ) : ?>
					<span><?php echo wp_kses_post( $label ); ?></span>
				<?php endif; ?>
			</a>
		</div>
	<?php endif; ?>

</div>
