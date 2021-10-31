<?php
/**
 * Template for displaying the Color style Field
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
} ?>

<div id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>"
	<?php echo wp_kses_post( $dependies ); ?>>
	<table class="mct-border-table">
		<thead>
		<tr>
			<th><?php esc_attr_e( 'Color', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Hover color', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Background', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Background hover', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Border', 'mct-options' ); ?></th>
			<th><?php esc_attr_e( 'Border hover', 'mct-options' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td data-title="<?php esc_attr_e( 'Color', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[color]" value="<?php echo isset( $value['color'] ) ? esc_attr( $value['color'] ) : ''; ?>"/>
			</td>
			<td data-title="<?php esc_attr_e( 'Hover color', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[color-hover]" value="<?php echo isset( $value['hover-color'] ) ? esc_attr( $value['hover-color'] ) : ''; ?>"/>
			</td>
			<td data-title="<?php esc_attr_e( 'Background', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[background]" value="<?php echo isset( $value['background'] ) ? esc_attr( $value['background'] ) : ''; ?>"/>
			</td>
			<td data-title="<?php esc_attr_e( 'Background Hover', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[background-hover]" value="<?php echo isset( $value['background-hover'] ) ? esc_attr( $value['background-hover'] ) : ''; ?>"/>
			</td>
			<td data-title="<?php esc_attr_e( 'Border', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[border]" value="<?php echo isset( $value['border'] ) ? esc_attr( $value['border'] ) : ''; ?>"/>
			</td>
			<td data-title="<?php esc_attr_e( 'Border hover', 'mct-options' ); ?>">
				<input class="mct-color-picker" data-alpha-enabled="true" type="text" name="<?php echo esc_attr( $name ); ?>[border-hover]" value="<?php echo isset( $value['border-hover'] ) ? esc_attr( $value['border-hover'] ) : ''; ?>"/>
			</td>
		</tr>
		</tbody>
	</table>
</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
