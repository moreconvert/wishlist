<?php
/**
 * Template for displaying the Page Select Field
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
}
?>
<div
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>
	<?php echo wp_kses_post( $dependies ); ?>>
	<?php

	wp_dropdown_pages(
		array(
			'id'                => esc_attr( $field_id ),
			'class'             => esc_attr( $class ),
			'name'              => esc_attr( $name ),
			'echo'              => 1,
			'show_option_none'  => esc_attr__( '&mdash; Select a page  &mdash;', 'mct-options' ),
			'option_none_value' => '0',
			'selected'          => esc_attr( $value ),
			'post_type'         => 'page',
		)
	);
	?>
</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
