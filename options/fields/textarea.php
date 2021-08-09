<?php
/**
 * Template for displaying the Textarea Field
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
 * @var $value                     string Field value
 * @var $data                      string Data attributes
 * @var $custom_attributes         string Custom attributes
 * @var $dependies                 string Dependencies
 * @var $desc                      string Description
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="regular-text <?php echo esc_attr( $class ); ?>"
<?php echo wp_kses_post( $dependies ); ?>
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>>
	<?php echo esc_textarea( $value ); ?></textarea>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
