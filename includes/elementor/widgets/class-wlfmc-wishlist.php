<?php

/**
 * Wishlist Widget.
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

class WLFMC_WidgetWishlist extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve WishList widget name.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Name.
	 */
	public function get_name() {
		return 'wlfmc-wish-list';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve WishList widget title.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Title.
	 */
	public function get_title() {
		return esc_html__( 'Wish List', 'wc-wlfmc-wishlist' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve WishList widget icon.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @return string Widget Icon.
	 */
	public function get_icon() {
		return 'eicon-table';
	}


	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the WishList widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return array Widget Categories.
	 */
	public function get_categories() {
		return [ 'WLFMC_WishList' ];
	}

	/**
	 * Table Styling Controls
	 *
	 * @since     1.0.1
	 *
	 * @return void.
	 * @access private
	 */
	private function table_styling_controls () {
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Table', 'wc-wlfmc-wishlist' ),
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
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table',
			]
		);

		$this->start_controls_tabs( 'tabs_table_style' );

		$this->start_controls_tab(
			'tab_table_normal',
			[
				'label' => __( 'Normal', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'table_text_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'table_icon_color',
			[
				'label' => __( 'Icons Color', 'wc-wlfmc-wishlist' ),
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
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_table_hover',
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
					'{{WRAPPER}} .wlfmc_wishlist_table:hover, {{WRAPPER}} .wlfmc_wishlist_table:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_icon_color',
			[
				'label' => __( 'Icons Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .mc-wishlist-button-wrapper:hover i, {{WRAPPER}} .mc-wishlist-button-wrapper:hover svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'table_background_hover',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table:hover, {{WRAPPER}} .wlfmc_wishlist_table:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'table_hover_border_color',
			[
				'label' => __( 'Border Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table:hover, {{WRAPPER}} .wlfmc_wishlist_table:focus' => 'border-color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table',
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
					'{{WRAPPER}} .wlfmc_wishlist_table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'table_box_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => __( 'Padding', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Table Header Styling Controls
	 *
	 * @since     1.0.1
	 *
	 * @return void.
	 * @access private
	 */
	private function table_header_styling_controls () {
		$this->start_controls_section(
			'section_header_style',
			[
				'label' => __( 'Table Header', 'wc-wlfmc-wishlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'header_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead tr th',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'header_text_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead tr th',
			]
		);

		$this->start_controls_tabs( 'tabs_table_header_style' );

		$this->start_controls_tab(
			'tab_table_header_normal',
			[
				'label' => __( 'Normal', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'table_header_text_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table thead tr th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'header_background',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead tr th',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_table_header_hover',
			[
				'label' => __( 'Hover', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'header_hover_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table thead tr th:hover, {{WRAPPER}} .wlfmc_wishlist_table thead tr th:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'table_header_background_hover',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead tr th:hover, {{WRAPPER}} .wlfmc_wishlist_table thead tr th:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'table_header_hover_border_color',
			[
				'label' => __( 'Border Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table thead:hover, {{WRAPPER}} .wlfmc_wishlist_table thead:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'header_hover_animation',
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
				'name' => 'header_border',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'table_header_box_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table thead tr th',
			]
		);

		$this->add_responsive_control(
			'header_text_padding',
			[
				'label' => __( 'Padding', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table thead tr th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Table Header Styling Controls
	 *
	 * @since     1.0.1
	 *
	 * @return void.
	 * @access private
	 */
	private function table_body_styling_controls () {
		$this->start_controls_section(
			'section_body_style',
			[
				'label' => __( 'Items', 'wc-wlfmc-wishlist' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'body_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table tbody tr td',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'body_text_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table tbody tr td',
			]
		);

		$this->start_controls_tabs( 'tabs_table_body_style' );

		$this->start_controls_tab(
			'tab_table_body_normal',
			[
				'label' => __( 'Normal', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'table_body_text_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table tbody tr td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'body_background',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} table.wlfmc_wishlist_table tbody tr td',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_table_body_hover',
			[
				'label' => __( 'Hover', 'wc-wlfmc-wishlist' ),
			]
		);

		$this->add_control(
			'body_hover_color',
			[
				'label' => __( 'Text Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table tbody tr td:hover, {{WRAPPER}} .wlfmc_wishlist_table tbody tr td:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'table_body_background_hover',
				'label' => __( 'Background', 'wc-wlfmc-wishlist' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table tbody tr td:hover, {{WRAPPER}} .wlfmc_wishlist_table tbody tr td:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'table_body_hover_border_color',
			[
				'label' => __( 'Border Color', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table tbody tr:hover, {{WRAPPER}} .wlfmc_wishlist_table tbody tr:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'body_hover_animation',
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
				'name' => 'body_border',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table tbody tr',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'table_body_box_shadow',
				'selector' => '{{WRAPPER}} .wlfmc_wishlist_table tbody tr td',
			]
		);

		$this->add_responsive_control(
			'body_text_padding',
			[
				'label' => __( 'Padding', 'wc-wlfmc-wishlist' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wlfmc_wishlist_table tbody tr td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register WishList Widget Controls
	 *
	 * @since     1.0.1
	 *
	 * @return void.
	 * @access protected
	 */
	protected function register_controls() {
		// Table Styling Controls
		$this->table_styling_controls();

		// Table Header Styling Controls
		$this->table_header_styling_controls();

		// Table Body Styling Controls
		$this->table_body_styling_controls();
	}

	/**
	 * Render WishList widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.1
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute( 'wrapper', 'class', 'mc-wishlist-wrapper' );
		?>
			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
				<?php echo do_shortcode( '[wlfmc_wishlist]' ); ?>
			</div>
		<?php
	}
}
