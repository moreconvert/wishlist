<?php
/**
 * Options Class
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MCT_Options' ) ) {
	/**
	 * Class MCT_Options
	 */
	class MCT_Options {
		/**
		 * Options
		 *
		 * @var mixed|void
		 */
		public $options;

		/**
		 * Constructor of the class
		 *
		 * @param string $option_id  Option_id.
		 */
		public function __construct( $option_id ) {

			$this->options = get_option( $option_id, array() );

		}

		/**
		 * Get value of field
		 *
		 * @param string $field field key.
		 * @param string $default default value.
		 *
		 * @return string
		 */
		public function get_option( $field, $default = '' ) {

			$value = $default;
			if ( is_array( $this->options ) && ! empty( $this->options ) ) {
				foreach ( $this->options as $section ) {
					if ( isset( $section[ $field ] ) ) {
						$value = $section[ $field ];
						break;
					}
				}
			}

			return $value;
		}

	}
}
