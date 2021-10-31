<?php
/**
 * Options Class
 *
 * @author MoreConvert
 * @package MoreConvert Options plugin
 * @version 1.1.0
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
		 * option id
		 * @var string
		 */
		public $option_id;
		/**
		 * Constructor of the class
		 *
		 * @param string $option_id  Option_id.
		 */
		public function __construct( $option_id ) {

			$this->option_id = $option_id;
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

		/**
		 * Update value of field
		 *
		 * @param string $field field key.
		 * @param string $value  value.
		 *
		 * @return string
		 * @since 1.1.0
		 */
		public function update_option( $field, $value ) {

			$new_option = 	$this->options;

			if ( is_array( $this->options ) && ! empty( $this->options ) ) {
				foreach ( $this->options as $k =>  $section ) {
					if ( isset( $section[ $field ] ) ) {
						$new_option[$k][ $field ] = $value ;
					}
				}
			}

			update_option($this->option_id ,$new_option );

			return true;
		}
	}
}
