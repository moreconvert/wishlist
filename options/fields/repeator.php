<?php
/**
 * Template for displaying the repeator field
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
 * @var $repeator_fields           array Array of all fields
 * @var $add_new_label             string Label of "add new row"
 * @var $section                   string active Section
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$mct_fields = new MCT_Fields();

?>

<div id="<?php echo esc_attr( $field_id ); ?>" class="mct-repeater  <?php echo esc_attr( $class ); ?>"
	<?php echo wp_kses_post( $dependies ); ?>>
	<table class="mct-border-table" data-repeater-list="<?php echo esc_attr( $name ); ?>">
		<thead>
		<tr>
			<?php
			foreach ( $repeator_fields as $field_key => $field_value ) {
				?>
				<th>
					<?php echo esc_attr( $field_value['label'] ) ;?>
					<?php if( isset( $field_value['help'] ) &&  ! empty( $field_value['help'] ) ): ?>
						<!-- MCT Help Tip -->
						<div class="mct-help-tip-wrap">
							<span class="mct-help-tip-dec">
								<?php echo esc_attr( $field_value['help'] ); ?>
							</span>
						</div>
					<?php endif; ?>
				</th>
				<?php
			}
			?>
			<th><?php esc_attr_e( 'Action', 'mct-options' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if ( is_array( $value ) && ! empty( $value ) ) : ?>
			<?php foreach ( $value as $k => $row ) : ?>
				<tr data-repeater-item>
					<?php foreach ( $repeator_fields as $field_key => $field_value ) : ?>
						<td class="row-options " data-title="<?php echo esc_attr( $field_value['label'] ); ?>">
							<?php $mct_fields->print_field_repeator( $section, $name, $k, $field_key, $field_value, isset( $row[ $field_key ] ) ? $row[ $field_key ] : '' ); ?>
						</td>
					<?php endforeach; ?>
					<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
						<a data-repeater-delete href="#!"><span class="dashicons dashicons-dismiss"></span></a>
					</td>

				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr data-repeater-item>
				<?php foreach ( $repeator_fields as $field_key => $field_value ) : ?>
					<td class="row-options" data-title="<?php echo esc_attr( $field_value['label'] ); ?>">
						<?php $mct_fields->print_field_repeator( $section, $name, '', $field_key, $field_value ); ?>
					</td>
				<?php endforeach; ?>
				<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
					<a data-repeater-delete href="#!"><span class="dashicons dashicons-dismiss"></span></a>
				</td>

			</tr>
		<?php endif; ?>

		</tbody>
	</table>
	<button data-repeater-create type="button" class="button button-secondary">
		<?php echo esc_attr( $add_new_label ); ?>
	</button>
</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
