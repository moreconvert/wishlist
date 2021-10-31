<?php

/**
 * WishList Link Widget.
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

// Don't load directly.
if (!defined('ABSPATH')) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

class WLFMC_WidgetWishListlink extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve WishList Link widget name.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Name.
	 */
	public function get_name() {
		return 'wlfmc-wish-list-link';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve WishList Link widget title.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Title.
	 */
	public function get_title() {
		return esc_html__( 'WishList Link', 'wc-wlfmc-wishlist' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve WishList Link widget icon.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Icon.
	 */
	public function get_icon() {
		return 'eicon-button';
	}


	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the WishList Link widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return array Widget Categories.
	 */
	public function get_categories() {
		return [ 'WLFMC_WishList' ];
	}

	/**
	 * Get WishList Link Button sizes.
	 *
	 * Retrieve an array of Button sizes for the WishList Link widget.
	 *
	 * @since 1.0.1
	 * @access public
	 * @static
	 *
	 * @return array An array containing button sizes.
	 */
	public static function get_button_sizes() {
		return [
			'xs' => __( 'Extra Small', 'wc-wlfmc-wishlist' ),
			'sm' => __( 'Small', 'wc-wlfmc-wishlist' ),
			'md' => __( 'Medium', 'wc-wlfmc-wishlist' ),
			'lg' => __( 'Large', 'wc-wlfmc-wishlist' ),
			'xl' => __( 'Extra Large', 'wc-wlfmc-wishlist' ),
		];
	}

	/**
	 * Register WishList Link Widget Controls
	 *
	 * @since     1.0.1
	 *
	 * @return void.
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_button',
			[
				'label' => __( 'Button', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'text',
			[
				'label' => __( 'Text', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Wish List', 'wc-wlfmc-wishlist' ),
				'placeholder' => __( 'Wish List', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => __( 'Left', 'wc-wlfmc-wishlist' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'wc-wlfmc-wishlist' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'wc-wlfmc-wishlist' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'wc-wlfmc-wishlist' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->add_control(
			'size',
			[
				'label' => __( 'Size', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => self::get_button_sizes(),
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'target',
			[
				'label' => __( 'Size', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => '_blank',
				'options' => [
					'_blank'  => __('Blank', 'wc-wlfmc-wishlist'),
					'_self'   => __('Self', 'wc-wlfmc-wishlist'),
					'_parent' => __('Parent', 'wc-wlfmc-wishlist'),
					'_top'    => __('Top', 'wc-wlfmc-wishlist'),
				],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label' => __( 'Icon', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => __( 'Icon Position', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => __( 'Before', 'wc-wlfmc-wishlist' ),
					'right' => __( 'After', 'wc-wlfmc-wishlist' ),
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label' => __( 'Icon Spacing', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .mc-wishlist-button-wrapper a .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Button', 'wc-wlfmc-wishlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'button_icon_color',
			[
				'label' => __( 'Icon Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper i, {{WRAPPER}} .mc-wishlist-button-wrapper svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => Global_Colors::COLOR_ACCENT,
						],
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a:hover, {{WRAPPER}} .mc-wishlist-button-wrapper a:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_icon_color',
			[
				'label' => __( 'Icon Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a:hover i, {{WRAPPER}} .mc-wishlist-button-wrapper a:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a:hover, {{WRAPPER}} .mc-wishlist-button-wrapper a:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __( 'Border Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a:hover, {{WRAPPER}} .mc-wishlist-button-wrapper a:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .mc-wishlist-button-wrapper a',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => __( 'Padding', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render WishList Link Button text.
	 *
	 * Render WishList Link widget text.
	 *
	 * @since 1.0.1
	 * @access protected
	 */
	protected function render_text() {
		$settings = $this->get_settings_for_display();

		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$is_new = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

		if ( ! $is_new && empty( $settings['icon_align'] ) ) {
			$settings['icon_align'] = $this->get_settings( 'icon_align' );
		}

		$this->add_render_attribute( [
			'content-wrapper' => [
				'class' => 'elementor-button-content-wrapper',
			],
			'icon-align' => [
				'class' => [
					'mc-wishlist-button-icon elementor-button-icon',
					'elementor-align-icon-' . $settings['icon_align'],
				],
			],
			'text' => [
				'class' => 'mc-wishlist-button-text elementor-button-text ',
			],
		] );

		$this->add_inline_editing_attributes( 'text', 'none' );
		?>
		<span <?php echo $this->get_render_attribute_string( 'content-wrapper' ); ?>>
			<?php if ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon']['value'] ) ) : ?>
			<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
				<?php if ( $is_new || $migrated ) :
					Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
				else : ?>
					<i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
				<?php endif; ?>
			</span>
			<?php endif; ?>
			<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo $settings['text']; ?></span>
		</span>
		<?php
	}

	/**
	 * Render WishList Link widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.1
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();
		$this->add_render_attribute( 'wrapper', 'class', 'mc-wishlist-button-wrapper' );

		$this->add_render_attribute( 'button', 'class', 'elementor-button' );

		if ( ! empty( $settings['target'] ) ) {
			$this->add_render_attribute( 'button', 'target', $settings['target'] );
		} else {
			$this->add_render_attribute( 'button', 'target', '_blank' );
		}

		if ( ! empty( $settings['size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['size'] );
		}

		$wishlist_url = is_user_logged_in() ? wc_get_account_endpoint_url( 'wlfmc-wishlist' ) : WLFMC()->get_wishlist_url();
		?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<a
					href="<?php echo $wishlist_url; ?>"
					<?php echo $this->get_render_attribute_string( 'button' ); ?>>
					<?php $this->render_text(); ?>
				</a>
			</div>
		<?php
	}
}
