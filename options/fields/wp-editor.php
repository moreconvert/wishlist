<?php
/**
 * Template for displaying the Wp Editor Field
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

$args = array(
	'wpautop'       => true,
	// Choose if you want to use wpautop.
	'media_buttons' => true,
	// Choose if showing media button(s).
	'textarea_name' => $name,
	// Set the textarea name to something different, square brackets [] can be used here.
	'textarea_rows' => 20,
	// Set the number of rows.
	'tabindex'      => '',
	'editor_css'    => '',
	// Intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
	'editor_class'  => '',
	// Add extra class(es) to the editor textarea.
	'teeny'         => false,
	// Output the minimal editor config used in Press This.
	'dfw'           => false,
	// Replace the default fullscreen with DFW (needs specific DOM elements and css).
	'tinymce'       => true,
	// Load TinyMCE, can be used to pass settings directly to TinyMCE using an array().
	'quicktags'     => true,
	// Load Quicktags, can be used to pass settings directly to Quicktags using an array().
);
?>
<div class="editor  <?php echo esc_attr( $class ); ?>" data-type="wp_editor"
	<?php echo wp_kses_post( $dependies ); ?>
	<?php echo wp_kses_post( $custom_attributes ); ?>
	<?php echo isset( $data ) ? wp_kses_post( $data ) : ''; ?>><?php wp_editor( $value, $name, $args ); ?></div>
<?php if ( isset( $desc ) ) : ?>
	<br/><p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
