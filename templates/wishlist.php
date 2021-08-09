<?php
/**
 * Wishlist pages template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

/**
 * Template Variables:
 *
 * @var $form_action                   string from action
 * @var $template_part                 string Template part currently being loaded (manage)
 * @var $user_wishlists                WLFMC_WCWL_Wishlist[] Array of user wishlists
 * @var $fragment_options              array Array of items to use for fragment generation
 * @var $wishlist                      WLFMC_WCWL_Wishlist Current wishlist
 * @var $wishlist_items                array Array of items to show for current page
 * @var $wishlist_token                string Current wishlist token
 * @var $wishlist_id                   int Current wishlist id
 * @var $users_wishlists               array Array of current user wishlists
 * @var $pagination                    string yes/no
 * @var $per_page                      int Items per page
 * @var $current_page                  int Current page
 * @var $page_links                    array Array of page links
 * @var $is_user_owner                 bool Whether current user is wishlist owner
 * @var $additional_info               bool Whether to show Additional info textarea in Ask an estimate form
 * @var $columns                       array Array of Columns table
 * @var $available_multi_wishlist      bool Whether multi wishlist is enabled and available
 * @var $no_interactions               bool
 * @var $share_enabled                 bool Whether share buttons should appear
 * @var $share_atts                    array Array of options; shows which share links should be shown
 * @var $share_items                   array Array of active share buttons
 * @var $var                           array Array of attributes that needs to be sent to sub-template
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<form id="wlfmc-wishlist-form" action="<?php echo esc_attr( $form_action ); ?>" method="post" class="wlfmc-wishlist-form wishlist-fragment" data-fragment-options="<?php echo esc_attr( wp_json_encode( $fragment_options ) ); ?>">

	<?php wc_print_notices(); ?>
	<div class="wlfmc_wishlist_table_wrapper">
		<table
			class="wlfmc_wishlist_table"
			data-pagination="<?php echo esc_attr( $pagination ); ?>"
			data-per-page="<?php echo esc_attr( $per_page ); ?>"
			data-page="<?php echo esc_attr( $current_page ); ?>"
			data-id="<?php echo esc_attr( $wishlist_id ); ?>"
			data-token="<?php echo esc_attr( $wishlist_token ); ?>">

			<thead>
			<tr>
				<?php foreach ( $columns as $key => $label ) : ?>
					<th class="<?php echo esc_attr( $key ); ?>">

						<?php if ( 'product-checkbox' === $key ) : ?>
							<label class="checkbox-label">
								<input type="checkbox" value="" name="" id="bulk_add_to_cart"/>
								<span></span>
							</label>
						<?php else : ?>

							<?php echo esc_html( apply_filters( 'wlfmc_wishlist_view_' . $key . '_heading_label', $label ) ); ?>

						<?php endif; ?>

					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody class="wishlist-items-wrapper">
			<?php if ( $wishlist && $wishlist->has_items() ) : ?>
				<?php foreach ( $wishlist_items as $item ) : ?>
					<?php
					// phpcs:ignore Generic.Commenting.DocComment
					/**
					 * @var $item WLFMC_Wishlist_Item
					 */
					global $product;

					$product      = $item->get_product();
					$availability = $product->get_availability();
					$stock_status = isset( $availability['class'] ) ? $availability['class'] : false;
					?>
					<?php if ( $product && $product->exists() ) : ?>
						<tr id="wlfmc-row-<?php echo esc_attr( $item->get_product_id() ); ?>" data-row-id="<?php echo esc_attr( $item->get_product_id() ); ?>">
							<?php foreach ( $columns as $key => $label ) : ?>
								<td class="<?php echo esc_attr( $key ); ?>" data-title="<?php echo esc_html( apply_filters( 'wlfmc_wishlist_view_' . $key . '_heading_label', $label ) ); ?>">
									<?php if ( 'product-checkbox' === $key ) : ?>
										<label class="checkbox-label">
											<input type="checkbox" value="yes" name="items[<?php echo esc_attr( $item->get_product_id() ); ?>][cb]">
											<span></span>
										</label>
									<?php endif; ?>
									<?php if ( 'product-remove' === $key ) : ?>
										<div>
											<a href="<?php echo esc_url( add_query_arg( 'remove_from_wishlist', $item->get_product_id() ) ); ?>" class="wlfmc_remove_from_wishlist" title="<?php echo esc_html( apply_filters( 'wlfmc_remove_product_wishlist_message_title', __( 'Remove this product', 'wc-wlfmc-wishlist' ) ) ); ?>">×</a>
										</div>
									<?php endif; ?>
									<?php if ( 'product-thumbnail' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_thumbnail', $item, $wishlist ); ?>

										<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $item->get_product_id() ) ) ); ?>">
											<?php echo wp_kses_post( $product->get_image() ); ?>
										</a>

										<?php do_action( 'wlfmc_table_after_product_thumbnail', $item, $wishlist ); ?>
									<?php endif; ?>
									<?php if ( 'product-name' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_name', $item, $wishlist ); ?>

										<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $item->get_product_id() ) ) ); ?>">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_in_cartproduct_obj_title', $product->get_title(), $product ) ); ?>
										</a>

										<?php
										if ( $product->is_type( 'variation' ) ) {
											// phpcs:ignore Generic.Commenting.DocComment
											/**
											 * @var $product WC_Product_Variation
											 */
											echo wc_get_formatted_variation( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
										?>

										<?php do_action( 'wlfmc_table_after_product_name', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php if ( 'product-added-price' === $key ) : ?>
										<?php
										echo wp_kses_post( $item->get_original_price() );
										?>
									<?php endif; ?>

									<?php if ( 'product-price' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_price', $item, $wishlist ); ?>

										<?php

										echo wp_kses_post( $item->get_formatted_product_price() );

										echo wp_kses_post( $item->get_price_variation() );

										?>

										<?php do_action( 'wlfmc_table_after_product_price', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php if ( 'product-stock-status' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_stock', $item, $wishlist ); ?>

										<?php
										$quantity = $product->get_stock_quantity();
										// translators: %s: Product Quantity.
										$instock = $quantity && $quantity < 10 ? '<span class="wishlist-left-stock">' . esc_html( apply_filters( 'wlfmc_left_stock_label', sprintf( __( '%s Left', 'wc-wlfmc-wishlist' ), $quantity ), $quantity ) ) . '</span>' : '<span class="wishlist-in-stock">' . esc_html( apply_filters( 'wlfmc_in_stock_label', __( 'In Stock', 'wc-wlfmc-wishlist' ) ) ) . '</span>';
										echo 'out-of-stock' === $stock_status ? '<span class="wishlist-out-of-stock">' . esc_html( apply_filters( 'wlfmc_out_of_stock_label', __( 'Out of stock', 'wc-wlfmc-wishlist' ) ) ) . '</span>' : $instock; // @codingStandardsIgnoreLine. ?>

										<?php do_action( 'wlfmc_table_after_product_stock', $item, $wishlist ); ?>
									<?php endif; ?>

									<?php if ( 'product-quantity' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_quantity', $item, $wishlist ); ?>

										<?php if ( ! $no_interactions && $wishlist->current_user_can( 'update_quantity' ) ) : ?>
											<input type="number" min="1" step="1" name="items[<?php echo esc_attr( $item->get_product_id() ); ?>][quantity]" value="<?php echo esc_attr( $item->get_quantity() ); ?>"/>
										<?php else : ?>
											<?php echo esc_html( $item->get_quantity() ); ?>
										<?php endif; ?>

										<?php do_action( 'wlfmc_table_after_product_quantity', $item, $wishlist ); ?>
									<?php endif; ?>
									<?php if ( 'product-add-to-cart' === $key ) : ?>
										<?php do_action( 'wlfmc_table_before_product_cart', $item, $wishlist ); ?>

										<!-- Date added -->
										<?php
										if ( $item->get_date_added() ) :
											// translators: date added label: 1 date added.
											echo '<span class="dateadded">' . esc_html( sprintf( __( 'Added on: %s', 'wc-wlfmc-wishlist' ), $item->get_date_added_formatted() ) ) . '</span>';
										endif;
										?>

										<?php do_action( 'wlfmc_table_product_before_add_to_cart', $item, $wishlist ); ?>

										<!-- Add to cart button -->
										<?php if ( isset( $stock_status ) && 'out-of-stock' !== $stock_status ) : ?>
											<?php woocommerce_template_loop_add_to_cart( array( 'quantity' => $item->get_quantity() ) ); ?>
										<?php endif ?>

										<?php do_action( 'wlfmc_table_product_after_add_to_cart', $item, $wishlist ); ?>

									<?php endif; ?>
								</td>
							<?php endforeach; ?>

						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="<?php echo esc_attr( count( $columns ) ); ?>" class="wishlist-empty"><?php echo esc_html( apply_filters( 'wlfmc_no_product_to_remove_message', __( 'No products added to the wishlist', 'wc-wlfmc-wishlist' ), $wishlist ) ); ?></td>
				</tr>
			<?php endif; ?>

			<?php if ( ! empty( $page_links ) ) : ?>
				<tr class="pagination-row wishlist-pagination">
					<td colspan="<?php echo esc_attr( count( $columns ) ); ?>"><?php echo  wp_kses_post( $page_links ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>

		</table>
	</div>
	<div class="wlfmc_wishlist_footer">
		<div class="action-wrapper space-between d-flex f-wrap">
			<!-- Bulk actions form -->
			<div class="wlfmc_wishlist_bulk_action d-flex">
				<select name="bulk_actions" id="bulk_actions">
					<option value=""><?php esc_html_e( 'Actions', 'wc-wlfmc-wishlist' ); ?></option>
					<option value="add_to_cart"><?php esc_html_e( 'Add to cart', 'wc-wlfmc-wishlist' ); ?></option>

					<?php if ( $wishlist && $wishlist->has_items() && $wishlist->current_user_can( 'remove_from_wishlist' ) ) : ?>
						<option value="delete"><?php esc_html_e( 'Remove from wishlist', 'wc-wlfmc-wishlist' ); ?></option>
					<?php endif; ?>
				</select>
				<input type="submit" class="apply-btn button" name="apply_bulk_actions" value="<?php esc_html_e( 'Apply', 'wc-wlfmc-wishlist' ); ?>"/>
			</div>
			<input type="submit" class="add-all-to-cart-btn button" name="add_all_to_cart" value="<?php esc_html_e( 'Add all to cart', 'wc-wlfmc-wishlist' ); ?>"/>

		</div>
		<?php if ( $share_enabled ) : ?>
			<div class="share-wrapper space-between d-flex f-wrap">
				<!-- Sharing section -->
				<div class="wlfmc-link d-flex f-center-item">
					<?php if ( in_array( 'copy', $share_items ) ) : ?>
						<strong class="wlfmc-share-title"><?php esc_attr_e( 'Share Link:', 'wc-wlfmc-wishlist' ); ?></strong>
						<div class="field-wrapper">
							<input class="copy-target" readonly="readonly" type="url" name="wlfmc_share_url" id="wlfmc_share_url" value="<?php echo esc_attr( $share_atts['share_link_url'] ); ?>"/>
							<a class="copy-trigger " href="#!"><?php echo  wp_kses_post( $share_atts['share_copy_icon'] ); ?></a>
						</div>
					<?php endif; ?>
					<?php $share_items = array_diff( $share_items, array( 'copy' ) ); ?>
				</div>
				<?php if ( is_array( $share_items ) && ! empty( $share_items ) ) : ?>
					<div class="wlfmc-share d-flex f-center-item">
						<strong class="wlfmc-share-title"><?php esc_attr_e( 'Share on:', 'wc-wlfmc-wishlist' ); ?></strong>
						<ul>
							<?php foreach ( $share_items as $k => $share_item ) : ?>
								<?php if ( 'facebook' === $share_item ) : ?>
									<li class="share-button">
										<a target="_blank" rel="noopener" class="facebook" href="https://www.facebook.com/sharer.php?u=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>&p[title]=<?php echo esc_attr( $share_atts['share_socials_title'] ); ?>" title="<?php esc_html_e( 'Facebook', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_facebook_icon'] ? wp_kses_post( $share_atts['share_facebook_icon'] ) : esc_html__( 'Facebook', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( 'twitter' === $share_item ) : ?>
									<li class="share-button">
										<a target="_blank" rel="noopener" class="twitter" href="https://twitter.com/share?url=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>&amp;text=<?php echo esc_attr( $share_atts['share_socials_text'] ); ?>" title="<?php esc_html_e( 'Twitter', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_twitter_icon'] ? wp_kses_post( $share_atts['share_twitter_icon'] ) : esc_html__( 'Twitter', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( 'messenger' === $share_item ) : ?>
									<li class="share-button">
										<a target="_blank" rel="noopener" class="messenger" href="fb-messenger://share/?link=<?php echo rawurlencode( $share_atts['share_link_url'] ); ?>&amp;app_id=<?php echo esc_attr( $share_atts['share_messenger_id'] ); ?>" title="<?php esc_html_e( 'Messenger', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_messenger_icon'] ? wp_kses_post( $share_atts['share_messenger_icon'] ) : esc_html__( 'Messenger', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( 'email' === $share_item ) : ?>
									<li class="share-button">
										<a class="email" href="mailto:?subject=<?php echo esc_attr( apply_filters( 'wlfmc_email_share_subject', $share_atts['share_socials_title'] ) ); ?>&amp;body=<?php echo esc_attr( apply_filters( 'wlfmc_email_share_body', rawurlencode( $share_atts['share_link_url'] ) ) ); ?>&amp;title=<?php echo esc_attr( $share_atts['share_socials_title'] ); ?>" title="<?php esc_html_e( 'Email', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_email_icon'] ? wp_kses_post( $share_atts['share_email_icon'] ) : esc_html__( 'Email', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( 'whatsapp' === $share_item ) : ?>
									<li class="share-button">
										<a class="whatsapp" href="<?php echo esc_url( $share_atts['share_whatsapp_url'] ); ?>" data-action="share/whatsapp/share" target="_blank" rel="noopener" title="<?php esc_html_e( 'WhatsApp', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_whatsapp_icon'] ? wp_kses_post( $share_atts['share_whatsapp_icon'] ) : esc_html__( 'Whatsapp', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

								<?php if ( 'telegram' === $share_item ) : ?>
									<li class="share-button">
										<a class="telegram" href="<?php echo esc_url( $share_atts['share_telegram_url'] ); ?>" target="_blank" rel="noopener" title="<?php esc_html_e( 'Telegram', 'wc-wlfmc-wishlist' ); ?>">
											<?php echo $share_atts['share_telegram_icon'] ? wp_kses_post( $share_atts['share_telegram_icon'] ) : esc_html__( 'Telegram', 'wc-wlfmc-wishlist' ); ?>
										</a>
									</li>
								<?php endif; ?>

							<?php endforeach; ?>
						</ul>


					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>

	<?php wp_nonce_field( 'wlfmc_edit_wishlist_action', 'wlfmc_edit_wishlist' ); ?>
	<input type="hidden" value="<?php echo esc_attr( $wishlist_token ); ?>" name="wishlist_id" id="wishlist_id">

</form>
