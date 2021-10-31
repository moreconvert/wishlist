<?php
/**
 * Template for displaying the Manage field
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.1.0
 * @since 1.1.0
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
 * @var $fields                    array Array of all fields
 * @var $section                   string active Section
 * @var $field                     array Array of all field attributes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$mct_fields = new MCT_Fields();

?>
</tbody>
</table>

<div id="<?php echo esc_attr( $field_id ); ?>" class="postbox mct-article <?php echo 'article-' . $field_id; ?>"   <?php echo esc_attr( $class ); ?>"
	<?php echo wp_kses_post( $dependies ); ?>>
	<div class="article-title">
		<h2>
			<?php echo esc_attr( $field['title'] ); ?>
		</h2>
		<?php if ( isset( $help ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $help ); ?></p>
		<?php endif; ?>
	</div>
	<br>
	<br>
	<table class="widefat striped  mct-manages" style="border: none;margin: 0 -15px;width: calc(100% + 30px);">
		<thead>
		<tr>
			<?php
			foreach ( $field['table-fields'] as $k => $col ) {
				echo '<td>' ;
				echo  esc_attr( $col['label'] ) ;
				if( isset( $col['help'] ) &&  ! empty( $col['help'] ) ): ?>
					<!-- MCT Help Tip -->
					<div class="mct-help-tip-wrap">
						<span class="mct-help-tip-dec">
							<?php echo esc_attr( $col['help'] ); ?>
						</span>
					</div>
				<?php endif;

				echo  '</td>';
			}
			?>
			<td></td>
		</tr>
		</thead>
		<tbody>
		<?php
		for ( $i = 0; $i <= $field['count'] - 1; $i ++ ) { ?>

			<tr >
				<?php
				foreach ( $field['table-fields'] as $k => $col ) {
					echo '<td>';
					if (isset($field['default'][$i][$k]))
						$col['default'] = $field['default'][$i][$k];

					$val = isset(  $value[$i][$k] ) ?  $value[$i][$k] : '';

					if(isset($col['value_class']) && ! empty($col['value_class']) && is_callable($col['value_class']) && isset($col['value_depend']) && ''!== $col['value_depend']){
						$value_dep          = isset(  $value[$i][$col['value_depend']] ) ?  $value[$i][$col['value_depend']] : '';
						if($value_dep){
							$val = call_user_func($col['value_class'],$value_dep);
						}
					}

					$mct_fields->print_field_manage( $section, $name, $i, $k, $col, $val );
					echo '</td>';
				} ?>
				<td>
					<a href="#<?php echo esc_attr( $field_id ); ?>_<?php echo esc_attr($i);?>"
					   class="button center-align button-secondry min-width-btn show-manage-item">
						<?php esc_html_e( 'Manage', 'mct-options' ); ?>
					</a>
					<?php if(isset($field['table-action'])):?>
						<a href="#!" data-id="<?php echo esc_attr($i);?>" data-field="<?php echo esc_attr( $field_id ); ?>"
						   class="button center-align button-primary min-width-btn <?php echo esc_attr($field['table-action']['class']) ?>">
							<?php echo esc_attr($field['table-action']['title']) ?>
						</a>
					<?php endif;?>
				</td>
			</tr>
		<?php } ?>

		</tbody>
	</table>

</div>
<?php
for ( $i = 0; $i <= $field['count'] - 1; $i ++ ) :
	// print table of fields.
	?>
	<div class="mct-manage-item" id="<?php echo esc_attr( $field_id ); ?>_<?php echo esc_attr($i);?>" style="display: none">
		<h1>
			<?php echo $field['row-title'] ? esc_attr( str_replace( '%s', $i + 1, $field['row-title'] ) ) : ''; ?>
			<a class="back-manage-item" href="#"><?php _e('Back','mct-options' );?></a>
		</h1>
		<span><?php echo $field['row-desc'] ? esc_attr( $field['row-desc'] ) : ''; ?></span>
		<table class="form-table" role="presentation">
			<tbody>
			<?php foreach ( $field['fields'] as $k => $col ): ?>
				<?php
					if (isset($field['default'][$i][$k]))
						$col['default'] = $field['default'][$i][$k];
				?>
				<?php if ( ! in_array( $col['type'], array( 'end', 'start' ) ) ): ?>
					<tr class="row-options  row-<?php echo esc_attr( $field_id ); ?> <?php echo isset( $col['parent_class'] ) ? esc_attr( $col['parent_class'] ) : ''; ?>">
					<th scope="row">
						<?php echo esc_attr( $col['label'] ); ?>
						<?php if( isset( $col['help'] ) &&  ! empty( $col['help'] ) ): ?>
							<!-- MCT Help Tip -->
							<div class="mct-help-tip-wrap">
								<span class="mct-help-tip-dec">
									<?php echo esc_attr( $col['help'] ); ?>
								</span>
							</div>
						<?php endif; ?>
					</th>
					<td>
				<?php endif; ?>
				<?php $mct_fields->print_field_manage( $section, $name, $i, $k, $col, isset(  $value[$i][$k] ) ?  $value[$i][$k] : '' ); ?>
				<?php if ( ! in_array( $col['type'], array( 'end', 'start' ) ) ): ?>
					</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php
endfor;
?>
<table class="form-table" role="presentation">
	<tbody>
