<?php
/**
 * Template for displaying the search product Field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $name                      string Field name
 * @var $class                     string Field class
 * @var $field_id                  string Field Id
 * @var $value                     array Field value
 * @var $data                      string Data attributes
 * @var $custom_attributes         string Custom attributes
 * @var $dependies                 string Dependencies
 * @var $desc                      string Description
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'select2' );

wp_enqueue_script( 'selectWoo' );

wp_enqueue_script( 'wc-enhanced-select' );
?>

<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>[]" class="wc-product-search <?php echo esc_attr( $class ); ?>"  multiple="multiple" style="width: 50%;" data-minimum_input_length="3"  data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations"
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo wp_kses_post( $dependies ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>>
	<?php
	if ( is_array( $value ) && ! empty( $value ) ) {
		foreach ( $value as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) ) {
				echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
			}
		}
	}
	?>
</select>
<?php if ( isset( $desc ) ) : ?>
	<p class='description'><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
