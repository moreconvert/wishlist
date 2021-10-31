<?php
/**
 * Field Class
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MCT_Fields' ) ) {

	/**
	 * Class MCT_Fields
	 */
	class MCT_Fields {
		/**
		 * Single instance of the class
		 *
		 * @var MCT_Fields
		 */
		protected static $instance;
		/**
		 * Sections
		 *
		 * @var mixed|string
		 */
		public $sections;
		/**
		 * Options
		 *
		 * @var mixed|string
		 */
		public $options;

		/**
		 * Option type
		 *
		 * @var mixed|string
		 */
		public $type;
		/**
		 * Title
		 *
		 * @var mixed|string
		 */
		public $title;
		/**
		 * Option id
		 *
		 * @var mixed|string
		 */
		public $id;
		/**
		 * Option description
		 *
		 * @var mixed|string
		 */
		public $desc;
		/**
		 * Saved options
		 *
		 * @var mixed|void
		 */
		public $saved_options;

		/**
		 * Returns single instance of the class
		 *
		 * @return MCT_Fields
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor of the class
		 *
		 * @param array $args array of options.
		 * @return void
		 */
		public function __construct( $args = array() ) {

			$this->sections      = isset( $args['sections'] ) ? $args['sections'] : '';
			$this->options       = isset( $args['options'] ) ? $args['options'] : '';
			$this->title         = isset( $args['title'] ) ? $args['title'] : '';
			$this->type          = isset( $args['type'] ) ? $args['type'] : '';
			$this->id            = isset( $args['id'] ) ? $args['id'] : '';
			$this->desc          = isset( $args['desc'] ) ? $args['desc'] : '';
			$this->saved_options = $this->get_option();
		}

		/**
		 * Print html output.
		 *
		 * @return void
		 * @version 1.1.0
		 */
		public function output() {
			do_action(
				'mct_output_panel_' . $this->type,
				array(
					'sections'      => $this->sections,
					'options'       => $this->options,
					'title'         => $this->title,
					'type'          => $this->type,
					'id'            => $this->id,
					'desc'          => $this->desc,
					'saved_options' => $this->saved_options,
				)
			);
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$active_section = isset( $_GET['section'] ) && '' !== $_GET['section'] ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
			$active_tab     = isset( $_GET['tab'] ) && '' !== $_GET['tab'] ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$type_class     = isset( $_GET['type'] ) && 'class' === $_GET['type'] ? true : false;
			// phpcs:enable
			?>
			<?php if ( ! has_action( 'mct_output_panel_' . $this->type ) ) : ?>
				<?php if ( 'simple-panel' === $this->type ) : ?>
					<?php if ( '' !== $this->sections && ! empty( $this->sections ) ): ?>
						<div
							class="mct-section-wrapper" <?php echo '' !== $active_section ? 'style="display: none"' : ''; ?>>
							<div class="table-title">
								<strong class=""><?php esc_html_e( 'Manage Settings', 'mct-options' ); ?></strong>
							</div>
							<table class="widefat striped  mct-sections">
								<tbody>
								<?php foreach ( $this->sections as $k => $section ) : ?>
									<tr class="">
										<td>
											<p class="d-flex space-between mct-fix-mar">
												<span>
												<?php echo esc_attr( $section ); ?>
												</span>
												<a href="#<?php echo esc_attr( $k ); ?>"
												   class="button center-align button-primary min-width-btn">
													<?php esc_html_e( 'Manage', 'mct-options' ); ?>
												</a>
											</p>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
					<?php foreach ( $this->options as $k => $option ) : ?>
						<?php $current_tab = ''; ?>
						<div id="<?php echo esc_attr( 'option_' . $k ); ?>" class="<?php echo ( '' !== $this->sections && ! empty( $this->sections ) ) ? 'postbox' : ''; ?>  mct-section-content" <?php echo '' === $this->sections || empty( $this->sections ) || $active_section === $k ? '' : 'style="display: none"'; ?>>
							<?php if (false === $type_class):?>
							<form method="post" action="">
							<?php endif;?>
								<div class="inside ">
									<?php if ( '' !== $this->sections && ! empty( $this->sections ) ): ?>
										<div class="d-flex space-between mct-fix-mar ">
											<strong
												class="wp-header-inline"><?php echo esc_attr( $this->sections[ $k ] ); ?></strong>
											<div class="">
												<?php wp_nonce_field( 'mct-' . $k, 'mct-' . $k . '-nonce' ); ?>
												<button
													class="button button-secondary min-width-btn mct-back-btn"><?php esc_html_e( 'Back', 'mct-options' ); ?></button>
												<button class="button button-primary min-width-btn" name="mct-action"
												        value="<?php echo esc_attr( $k ); ?>"
												        type="submit"><?php esc_html_e( 'Save Settings', 'mct-options' ); ?></button>
											</div>
										</div>
										<hr>
									<?php endif; ?>
									<?php if ( isset( $option['tabs'] ) && is_array( $option['tabs'] ) && ! empty( $option['tabs'] ) ) : ?>
										<nav class="nav-tab-wrapper mct-tabs">
											<?php foreach ( $option['tabs'] as $i => $tab ) : ?>
												<?php
												if ( '' === $current_tab ) {
													$current_tab = ( ( '' == $this->sections || empty( $this->sections ) ) && ( '' !== $active_tab ) ) || ( '' !== $active_section && $active_section === $k && '' !== $active_tab ) ? $active_tab : $i;
												}
												if(isset($option['fields'][$i]['type']) && 'class' === $option['fields'][$i]['type'] ){
													$url = add_query_arg(array('tab'=> $i,'type'=>'class') );
													?>
													<a class="nav-tab external-link <?php echo ( $current_tab === $i ) ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_attr( $tab ); ?></a>
													<?php
												} else {
													if(true === $type_class) {
														$url = remove_query_arg('type' );
														$url = add_query_arg('tab', $i,$url);
													}
													?>
													<a class="nav-tab <?php echo ( true === $type_class ) ? 'external-link' : ''; ?> <?php echo ( $current_tab === $i ) ? 'nav-tab-active' : ''; ?>"
													   href="<?php echo true === $type_class ? esc_url($url): '#'.esc_attr( $i ); ?>"><?php echo esc_attr( $tab ); ?></a>
													<?php
												}
												?>

											<?php endforeach; ?>
										</nav>
									<?php endif; ?>
									<?php if ( isset( $option['tabs'] ) && is_array( $option['tabs'] ) && ! empty( $option['tabs'] ) ) : ?>
										<?php foreach ( $option['fields'] as $tabkey => $fields ) : ?>
											<?php if(isset($option['fields'][$tabkey]['type']) && 'class' === $option['fields'][$tabkey]['type'] ){

												if (is_callable($option['fields'][$tabkey]['class'])) {

													call_user_func($option['fields'][$tabkey]['class']);

												}
												continue;
											}?>
											<div class="mct-tab-content"
											     id="<?php echo esc_attr( $tabkey ); ?>" <?php echo ( $current_tab === $tabkey ) ? '' : 'style="display: none"'; ?>>
												<?php
												// print table of fields.
												$this->print_table_fields( $k, $fields );
												?>
											</div>
										<?php endforeach; ?>
									<?php else : ?>
										<?php if ( isset( $option['fields'] ) && is_array( $option['fields'] ) && ! empty( $option['fields'] ) ) : ?>
											<div class="mct-tab-content">
												<?php
												// print table of fields.
												$this->print_table_fields( $k, $option['fields'] );
												?>
											</div>
										<?php endif; ?>

									<?php endif; ?>
									<?php if ( ('' === $this->sections || empty( $this->sections )) && false === $type_class ): ?>
										<div class="d-flex space-between mct-fix-mar ">
											<?php wp_nonce_field( 'mct-' . $k, 'mct-' . $k . '-nonce' ); ?>
											<button class="button button-primary min-width-btn" name="mct-action"
											        value="<?php echo esc_attr( $k ); ?>"
											        type="submit"><?php esc_html_e( 'Save Settings', 'mct-options' ); ?></button>
										</div>
									<?php endif; ?>
								</div>
							<?php if (false === $type_class):?>
							</form>
							<?php endif;?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php
		}

		/**
		 * Print table of group fields
		 *
		 * @param string $section_key Section key.
		 * @param array $fields Fields.
		 * @version 1.1.0
		 * @since 1.0.0
		 */
		public function print_table_fields( $section_key, $fields ) {
			?>
			<table class="form-table" role="presentation">
				<tbody>
				<?php foreach ( $fields as $fieldkey => $field ) : ?>

					<?php if ( ! in_array( $field['type'], array( 'end', 'start', 'manage' ) ) ): ?>

						<tr class="row-options row-<?php echo esc_attr( $fieldkey ); ?> <?php echo isset( $field['parent_class'] ) ? esc_attr( $field['parent_class'] ) : ''; ?>">
						<th scope="row">
							<?php echo esc_attr( $field['label'] ); ?>
							<?php if( isset( $field['help'] ) &&  ! empty( $field['help'] ) ): ?>
								<!-- MCT Help Tip -->
								<div class="mct-help-tip-wrap">
									<span class="mct-help-tip-dec">
										<?php echo esc_attr( $field['help'] ); ?>
									</span>
								</div>
							<?php endif; ?>
						</th>
						<td>

					<?php endif; ?>

					<?php $this->print_field( $section_key, $fieldkey, $field ); ?>

					<?php if ( ! in_array( $field['type'], array( 'end', 'start', 'manage' ) ) ): ?>

						</td>
						</tr>

					<?php endif; ?>

				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}

		/**
		 * Print fields
		 *
		 * @param string $section section key.
		 * @param string $name field key.
		 * @param array $field array of field args.
		 *
		 * @return bool|void
		 */
		public function print_field( $section, $name, $field ) {

			if ( empty( $field['type'] ) ) {
				return false;
			}

			$field_template = MCT_OPTION_PLUGIN_TEMPLATE_PATH . '/fields/' . sanitize_title( $field['type'] ) . '.php';

			$field_template = apply_filters( 'mct_get_field_template_path', $field_template, $field );

			if ( file_exists( $field_template ) ) {
				extract( $field ); // @codingStandardsIgnoreLine.
				$custom_attributes = array();
				$dependies         = '';
				if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
					foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				$custom_attributes = implode( ' ', $custom_attributes );
				$data              = isset( $field['data'] ) ? $this->html_data_to_string( $field['data'] ) : '';
				$value             = $this->get_field_option( $section, $name, $field['type'] );
				$value             = ( '' === $value && isset( $field['default'] ) ) ? $field['default'] : $value;
				$field_id          = isset( $field['id'] ) ? $field['id'] : $name;
				$class             = isset( $field['class'] ) ? $field['class'] : '';
				$links             = isset( $field['links'] ) ? $field['links'] : '';

				if ( isset( $field['dependies'] ) ) {
					if ( isset( $field['dependies']['id'] ) ) {
						$dependies .= " data-deps='" . wp_json_encode(
								array(
									'id'    => esc_attr( $field['dependies']['id'] ),
									'value' => esc_attr( $field['dependies']['value'] ),
								)
							) . "'";
					} else {
						$dependies .= " data-deps='" . wp_json_encode( $field['dependies'] ) . "'";
					}
				}

				include $field_template;
			}

		}

		/**
		 * Print repeator fields
		 *
		 * @param string $section section key.
		 * @param string $name repeator field name.
		 * @param int $index index.
		 * @param string $field_key field key.
		 * @param array $field array of field args.
		 * @param string|array $value value of field.
		 *
		 * @return bool|void
		 */
		public function print_field_repeator( $section, $name, $index, $field_key, $field, $value = '' ) {

			if ( empty( $field['type'] ) ) {
				return false;
			}

			$field_template = MCT_OPTION_PLUGIN_TEMPLATE_PATH . '/fields/' . sanitize_title( $field['type'] ) . '.php';

			$field_template = apply_filters( 'mct_get_field_template_path', $field_template, $field );

			if ( file_exists( $field_template ) ) {
				extract( $field ); // @codingStandardsIgnoreLine.
				$custom_attributes = array();
				$dependies         = '';
				if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
					foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				$custom_attributes = implode( ' ', $custom_attributes );
				$data              = isset( $field['data'] ) ? $this->html_data_to_string( $field['data'] ) : '';
				$value             = ( '' === $value && isset( $field['default'] ) ) ? $field['default'] : $value;
				$field_id          = $name . '[' . $index . '][' . $field_key . ']';
				$class             = isset( $field['class'] ) ? $field['class'] : '';
				$links             = isset( $field['links'] ) ? $field['links'] : '';
				$name              = $name . '[' . $index . '][' . $field_key . ']';
				if ( isset( $field['dependies'] ) ) {
					$dependies .= ' data-repdeps="' . esc_attr( $field['dependies']['id'] ) . '"';
					$dependies .= ' data-deps-value="' . esc_attr( $field['dependies']['value'] ) . '"';
				}

				include $field_template;
			}
		}

		/**
		 * Print repeator fields
		 *
		 * @param string $section section key.
		 * @param string $name repeator field name.
		 * @param int $index index.
		 * @param string $field_key field key.
		 * @param array $field array of field args.
		 * @param string|array $value value of field.
		 *
		 * @return bool|void
		 * @since 1.1.0
		 */
		public function print_field_manage( $section, $name, $index, $field_key, $field, $value = '' ) {

			if ( empty( $field['type'] ) ) {
				return false;
			}

			$field_template = MCT_OPTION_PLUGIN_TEMPLATE_PATH . '/fields/' . sanitize_title( $field['type'] ) . '.php';

			$field_template = apply_filters( 'mct_get_field_template_path', $field_template, $field );

			if ( file_exists( $field_template ) ) {
				extract( $field ); // @codingStandardsIgnoreLine.
				$custom_attributes = array();
				$dependies         = '';
				if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
					foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				$custom_attributes = implode( ' ', $custom_attributes );
				$data              = isset( $field['data'] ) ? $this->html_data_to_string( $field['data'] ) : '';
				$value             = ( '' === $value && isset( $field['default'] ) ) ? $field['default'] : $value;
				$field_id          = $name . '_' . $index . '_' . $field_key ;
				$class             = isset( $field['class'] ) ? $field['class'] : '';
				$links             = isset( $field['links'] ) ? $field['links'] : '';
				if ( isset( $field['dependies'] ) ) {
					$dependies .= ' data-mngdeps="' . esc_attr( $name. '_' . $index . '_' .$field['dependies']['id'] ) . '"';
					$dependies .= ' data-deps-value="' . esc_attr( $field['dependies']['value'] ) . '"';
				}
				$name              = $name . '[' . $index . '][' . $field_key . ']';


				include $field_template;
			}
		}

		/**
		 * Get single field option value.
		 *
		 * @param string $section Section.
		 * @param string $field field_key.
		 * @param string $field_type field_type.
		 *
		 * @return mixed|string
		 */
		public function get_field_option( $section, $field, $field_type ) {
			$options = $this->saved_options;
			// @codingStandardsIgnoreLine.
			return isset( $options[ $section ] ) && isset( $options[ $section ][ $field ] ) ? ( in_array( $field_type, array(
				'checkbox',
				'switch'
			) ) && '' === $options[ $section ][ $field ] ? '0' : $options[ $section ][ $field ] ) : '';
		}

		/**
		 * Get option.
		 *
		 * @return mixed|void
		 */
		public function get_option() {
			return get_option( $this->id, array() );
		}

		/**
		 * Convert Html data attribute to string.
		 *
		 * @param array $data Data attributes.
		 * @param bool $echo Echo or not.
		 *
		 * @return string|void
		 */
		private function html_data_to_string( $data = array(), $echo = false ) {
			$html_data = '';

			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data_attribute = "data-{$key}";
					$data_value     = ! is_array( $value ) ? $value : implode( ',', $value );

					$html_data .= ' ' . esc_attr( $data_attribute ) . '="' . esc_attr( $data_value ) . '"';
				}
				$html_data .= ' ';
			}

			if ( $echo ) {
				echo wp_kses_post( $html_data );
			} else {
				return $html_data;
			}
		}
	}
}
