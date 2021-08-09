<?php
/**
 * Template for displaying the add-button field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $name                      string Field name
 * @var $links                     array  Button links
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
?>
	<div id="<?php echo esc_attr( $field_id ); ?>" class="mct-repeater <?php echo esc_attr( $class ); ?>"
		<?php echo wp_kses_post( $dependies ); ?>>
		<table class="mct-border-table" data-repeater-list="<?php echo esc_attr( $name ); ?>">
			<thead>
			<tr>
				<th><?php esc_attr_e( 'Text', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Background', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Background hover', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Text color', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Text hover color', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Button link', 'mct-options' ); ?></th>
				<th><?php esc_attr_e( 'Action', 'mct-options' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( is_array( $value ) && ! empty( $value ) ) : ?>
				<?php foreach ( $value as $k => $row ) : ?>
					<?php $row['link'] = isset( $row['link'] ) ? $row['link'] : 'back'; ?>
					<tr data-repeater-item>
						<td data-title="<?php esc_attr_e( 'Text', 'mct-options' ); ?>">
							<input class="" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][label]" value="<?php echo esc_attr( $row['label'] ); ?>" type="text"/>
						</td>
						<td data-title="<?php esc_attr_e( 'Background', 'mct-options' ); ?>">
							<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][background]" value="<?php echo esc_attr( $row['background'] ); ?>" type="text"/>
						</td>
						<td data-title="<?php esc_attr_e( 'Background hover', 'mct-options' ); ?>">
							<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][background-hover]" value="<?php echo esc_attr( $row['background-hover'] ); ?>" type="text"/>
						</td>
						<td data-title="<?php esc_attr_e( 'Text color', 'mct-options' ); ?>">
							<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][label-color]" value="<?php echo esc_attr( $row['label-color'] ); ?>" type="text"/>
						</td>
						<td data-title="<?php esc_attr_e( 'Text hover color', 'mct-options' ); ?>">
							<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][label-hover-color]" value="<?php echo esc_attr( $row['label-hover-color'] ); ?>" type="text"/>
						</td>
						<td data-title="<?php esc_attr_e( 'Button link', 'mct-options' ); ?>">
							<select class="btn-link-type" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][link]" onchange="wlfmc_deps_link(this);">
								<?php foreach ( $links as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $row['link'] ); ?>><?php echo esc_attr( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<input class="show_on_custom-link" style="margin-top: 10px ;display: <?php echo 'custom-link' === $row['link'] ? 'inline-block' : 'none'; ?>" placeholder="<?php esc_attr_e( 'Url', 'mct-options' ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $k ); ?>][custom-link]" value="<?php echo isset( $row['custom-link'] ) ? esc_url( $row['custom-link'] ) : ''; ?>" type="url"/>

						</td>
						<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
							<a data-repeater-delete href="#!"><span class="dashicons dashicons-dismiss"></span></a>
						</td>

					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr data-repeater-item>
					<td data-title="<?php esc_attr_e( 'Text', 'mct-options' ); ?>">
						<input class="" type="text" name="<?php echo esc_attr( $name ); ?>[][label]"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Background', 'mct-options' ); ?>">
						<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[][background]" type="text"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Background hover', 'mct-options' ); ?>">
						<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[][background-hover]" type="text"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Text color', 'mct-options' ); ?>">
						<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[][label-color]" type="text"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Text hover color', 'mct-options' ); ?>">
						<input class="mct-color-picker" data-alpha-enabled="true" name="<?php echo esc_attr( $name ); ?>[][label-hover-color]" type="text"/>
					</td>
					<td data-title="<?php esc_attr_e( 'Button link', 'mct-options' ); ?>">
						<select class="" name="<?php echo esc_attr( $name ); ?>[][link]" onchange="wlfmc_deps_link(this);">
							<?php foreach ( $links as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input class="show_on_custom-link" style="margin-top: 10px ;display: none" placeholder="<?php esc_attr_e( 'Url', 'mct-options' ); ?>" name="<?php echo esc_attr( $name ); ?>[][custom-link]" type="url"/>

					</td>
					<td data-title="<?php esc_attr_e( 'Action', 'mct-options' ); ?>">
						<a data-repeater-delete href="#!"><span class="dashicons dashicons-dismiss"></span></a>
					</td>

				</tr>
			<?php endif; ?>

			</tbody>
		</table>
		<script>
			function wlfmc_deps_link(elem) {
				var element = elem.nextElementSibling;
				if (elem.options[elem.selectedIndex].value === 'custom-link') {
					element.style.display = "inline-block"
				} else {
					element.style.display = "none"
				}
			}
		</script>
		<button data-repeater-create type="button" class="button button-secondary">
			<?php esc_attr_e( 'Add new button', 'mct-options' ); ?>
		</button>
	</div>
<?php if ( isset( $desc ) ) : ?>
	<p class="description"><?php echo wp_kses_post( $desc ); ?></p>
<?php endif; ?>
