<?php
/**
 * Smart Wishlist Functions
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* === TESTER FUNCTIONS === */

if ( ! function_exists( 'wlfmc_is_wishlist' ) ) {
	/**
	 * Check if we're printing wishlist shortcode
	 *
	 * @return bool
	 */
	function wlfmc_is_wishlist() {
		global $wlfmc_is_wishlist;

		return $wlfmc_is_wishlist;
	}
}

if ( ! function_exists( 'wlfmc_is_wishlist_page' ) ) {
	/**
	 * Check if current page is wishlist
	 *
	 * @return bool
	 */
	function wlfmc_is_wishlist_page() {
		$wishlist_page_id = WLFMC()->get_wishlist_page_id();

		if ( ! $wishlist_page_id ) {
			return false;
		}

		return apply_filters( 'wlfmc_is_wishlist_page', is_page( $wishlist_page_id ) );
	}
}

if ( ! function_exists( 'wlfmc_is_single' ) ) {
	/**
	 * Returns true if it finds that you're printing a single product
	 * Should return false in any loop (including the ones inside single product page)
	 *
	 * @return bool Whether you're currently on single product template
	 */
	function wlfmc_is_single() {
		return apply_filters(
			'wlfmc_is_single',
			is_product() && ! in_array(
				wc_get_loop_prop( 'name' ),
				array(
					'related',
					'up-sells',
				)
			) && ! wc_get_loop_prop( 'is_shortcode' )
		);
	}
}

if ( ! function_exists( 'wlfmc_is_mobile' ) ) {
	/**
	 * Returns true if we're currently on mobile view
	 *
	 * @return bool Whether you're currently on mobile view
	 */
	function wlfmc_is_mobile() {
		global $wlfmc_is_mobile;

		return apply_filters( 'wlfmc_is_wishlist_responsive', true ) && ( wp_is_mobile() || $wlfmc_is_mobile );
	}
}


/* === TEMPLATE FUNCTIONS === */

if ( ! function_exists( 'wlfmc_locate_template' ) ) {
	/**
	 * Locate the templates and return the path of the file found
	 *
	 * @param string $path Path to locate.
	 * @param array  $var Unused.
	 *
	 * @return string
	 */
	function wlfmc_locate_template( $path, $var = null ) {
		$woocommerce_base = WC()->template_path();

		$template_woocommerce_path = $woocommerce_base . $path;
		$template_path             = '/' . $path;
		$plugin_path               = MC_WLFMC_DIR . 'templates/' . $path;

		$located = locate_template(
			array(
				$template_woocommerce_path, // Search in <theme>/woocommerce/.
				$template_path,             // Search in <theme>/.
			)
		);

		if ( ! $located && file_exists( $plugin_path ) ) {
			return apply_filters( 'wlfmc_locate_template', $plugin_path, $path );
		}

		return apply_filters( 'wlfmc_locate_template', $located, $path );
	}
}

if ( ! function_exists( 'wlfmc_get_template' ) ) {
	/**
	 * Retrieve a template file.
	 *
	 * @param string $path Path to get.
	 * @param mixed  $var Variables to send to template.
	 * @param bool   $return Whether to return or print the template.
	 *
	 * @return string|void
	 */
	function wlfmc_get_template( $path, $var = null, $return = false ) {
		$located = wlfmc_locate_template( $path, $var );

		if ( $var && is_array( $var ) ) {
			$atts = $var;
			extract( $var ); // @codingStandardsIgnoreLine.
		}

		if ( $return ) {
			ob_start();
		}

		// include file located.
		include $located;

		if ( $return ) {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'wlfmc_get_template_part' ) ) {
	/**
	 * Search and include a template part
	 *
	 * @param string $template Template to include.
	 * @param string $template_part Template part.
	 * @param string $template_layout Template variation.
	 * @param array  $var Array of variables to be passed to template.
	 * @param bool   $return Whether to return template or print it.
	 *
	 * @return string|void
	 */
	function wlfmc_get_template_part( $template = '', $template_part = '', $template_layout = '', $var = array(), $return = false ) {
		if ( ! empty( $template_part ) ) {
			$template_part = '-' . $template_part;
		}

		if ( ! empty( $template_layout ) ) {
			$template_layout = '-' . $template_layout;
		}

		$template_hierarchy = apply_filters(
			'wlfmc_template_part_hierarchy',
			array_merge(
				! wlfmc_is_mobile() ? array() : array(
					"wishlist-{$template}{$template_layout}{$template_part}-mobile.php",
					"wishlist-{$template}{$template_part}-mobile.php",
				),
				array(
					"wishlist-{$template}{$template_layout}{$template_part}.php",
					"wishlist-{$template}{$template_part}.php",
				)
			),
			$template,
			$template_part,
			$template_layout,
			$var
		);

		foreach ( $template_hierarchy as $filename ) {
			$located = wlfmc_locate_template( $filename );

			if ( $located ) {
				return wlfmc_get_template( $filename, $var, $return );
			}
		}
	}
}


/* === GET FUNCTIONS === */

if ( ! function_exists( 'wlfmc_get_hidden_products' ) ) {
	/**
	 * Retrieves a list of hidden products, whatever WC version is running
	 *
	 * WC switched from meta _visibility to product_visibility taxonomy since version 3.0.0,
	 * forcing a split handling (Thank you, WC!)
	 *
	 * @return array List of hidden product ids
	 */
	function wlfmc_get_hidden_products() {
		$hidden_products = get_transient( 'wlfmc_hidden_products' );

		if ( false === $hidden_products ) {
			if ( version_compare( WC()->version, '3.0.0', '<' ) ) {
				$hidden_products = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => - 1,
						'fields'         => 'ids',
						'meta_query'     => array(
							array(
								'key'   => '_visibility',
								'value' => 'visible',
							),
						),
					)
				);
			} else {
				$hidden_products = wc_get_products(
					array(
						'limit'      => - 1,
						'status'     => 'publish',
						'return'     => 'ids',
						'visibility' => 'hidden',
					)
				);
			}

			/**
			 * Array_filter was added to prevent errors when previous query returns for some reason just 0 index.
			 */
			$hidden_products = array_filter( $hidden_products );

			set_transient( 'wlfmc_hidden_products', $hidden_products, 30 * DAY_IN_SECONDS );
		}

		return apply_filters( 'wlfmc_hidden_products', $hidden_products );
	}
}

if ( ! function_exists( 'wlfmc_get_wishlist' ) ) {
	/**
	 * Retrieves wishlist by ID
	 *
	 * @param int|string $wishlist_id Wishlist ID or Wishlist Token.
	 *
	 * @return WLFMC_Wishlist|bool Wishlist object; false on error
	 */
	function wlfmc_get_wishlist( $wishlist_id ) {
		return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
	}
}

if ( ! function_exists( 'wlfmc_get_privacy_label' ) ) {
	/**
	 * Returns privacy label
	 *
	 * @param int  $privacy Privacy value.
	 * @param bool $extended Whether to show extended or simplified label.
	 *
	 * @return string Privacy label
	 */
	function wlfmc_get_privacy_label( $privacy, $extended = false ) {

		switch ( $privacy ) {
			case 1:
				$privacy_label = 'shared';
				$privacy_text  = __( 'Shared', 'wc-wlfmc-wishlist' );

				if ( $extended ) {
					$privacy_text  = '<b>' . $privacy_text . '</b> - ';
					$privacy_text .= __( 'Only people with a link to this list can see it', 'wc-wlfmc-wishlist' );
				}

				break;
			case 2:
				$privacy_label = 'private';
				$privacy_text  = __( 'Private', 'wc-wlfmc-wishlist' );

				if ( $extended ) {
					$privacy_text  = '<b>' . $privacy_text . '</b> - ';
					$privacy_text .= __( 'Only you can see this list', 'wc-wlfmc-wishlist' );
				}

				break;
			default:
				$privacy_label = 'public';
				$privacy_text  = __( 'Public', 'wc-wlfmc-wishlist' );

				if ( $extended ) {
					$privacy_text  = '<b>' . $privacy_text . '</b> - ';
					$privacy_text .= __( 'Anyone can search for and see this list', 'wc-wlfmc-wishlist' );
				}

				break;
		}

		return apply_filters( "wlfmc_{$privacy_label}_wishlist_visibility", $privacy_text, $extended, $privacy );
	}
}

if ( ! function_exists( 'wlfmc_get_privacy_value' ) ) {
	/**
	 * Returns privacy numeric value
	 *
	 * @param string $privacy_label Privacy label.
	 *
	 * @return int Privacy value
	 */
	function wlfmc_get_privacy_value( $privacy_label ) {

		switch ( $privacy_label ) {
			case 'shared':
				$privacy_value = 1;
				break;
			case 'private':
				$privacy_value = 2;
				break;
			default:
				$privacy_value = 0;
				break;
		}

		return apply_filters( 'wlfmc_privacy_value', $privacy_value, $privacy_label );
	}
}

if ( ! function_exists( 'wlfmc_get_option' ) ) {
	/**
	 * Return option value
	 *
	 * @param string $field  Field key.
	 * @param string $default Default value.
	 * @return mixed
	 */
	function wlfmc_get_option( $field, $default = '' ) {
		$all_options = get_option( 'wlfmc_options', array() );
		$value       = $default;

		if ( is_array( $all_options ) && ! empty( $all_options ) ) {
			foreach ( $all_options as $section ) {
				if ( isset( $section[ $field ] ) ) {
					$value = $section[ $field ];
					break;
				}
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'wlfmc_get_current_url' ) ) {
	/**
	 * Retrieves current url
	 *
	 * @return string Current url
	 */
	function wlfmc_get_current_url() {
		global $wp;

		/**
		 * Returns empty string by default, to avoid problems with unexpected redirects
		 * Added filter to change default behaviour, passing what we think is current page url
		 */
		return apply_filters( 'wlfmc_current_url', '', add_query_arg( $wp->query_vars, home_url( $wp->request ) ) );
	}
}

/* === UTILITY FUNCTIONS === */

if ( ! function_exists( 'wlfmc_object_id' ) ) {
	/**
	 * Retrieve translated object id, if a translation plugin is active
	 *
	 * @param int    $id Original object id.
	 * @param string $type Object type.
	 * @param bool   $return_original Whether to return original object if no translation is found.
	 * @param string $lang Language to use for translation ().
	 *
	 * @return int Translation id
	 */
	function wlfmc_object_id( $id, $type = 'page', $return_original = true, $lang = null ) {

		// process special value for $lang.
		if ( 'default' === $lang ) {
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // wpml default language.
				global $sitepress;
				$lang = $sitepress->get_default_language();
			} elseif ( function_exists( 'pll_default_language' ) ) { // polylang default language.
				$lang = pll_default_language( 'locale' );
			} else { // cannot determine default language.
				$lang = null;
			}
		}

		// Should work with WPML and PolyLang.
		$id = apply_filters( 'wpml_object_id', $id, $type, $return_original, $lang );

		// Space for additional translations.
		$id = apply_filters( 'wlfmc_object_id', $id, $type, $return_original, $lang );

		return $id;
	}
}

if ( ! function_exists( 'wlfmc_wpml_object_id' ) ) {
	/**
	 * Get id of post translation in current language
	 *
	 * @param int         $element_id The element ID.
	 * @param string      $element_type The element type.
	 * @param bool        $return_original_if_missing Return original if missing or not.
	 * @param null|string $language_code The language code.
	 *
	 * @return int the translation id
	 */
	function wlfmc_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null ) {
		if ( function_exists( 'wpml_object_id_filter' ) ) {
			return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $language_code );
		} elseif ( function_exists( 'icl_object_id' ) ) {
			return icl_object_id( $element_id, $element_type, $return_original_if_missing, $language_code );
		} else {
			return $element_id;
		}
	}
}

if ( ! function_exists( 'wlfmc_str_contains' ) ) {
	/**
	 * Determine if a string contains a given substring
	 *
	 * @param string $haystack String.
	 * @param string $needle substring.
	 *
	 * @return bool
	 */
	function wlfmc_str_contains( $haystack, $needle ) {
		return '' !== $needle  && mb_strpos( $haystack, $needle ) !== false;
	}
}
/**
 * ========================  Cookie Functions =================================
 */

if ( ! function_exists( 'wlfmc_get_cookie_expiration' ) ) {
	/**
	 * Returns default expiration for wishlist cookie
	 *
	 * @return int Number of seconds the cookie should last.
	 */
	function wlfmc_get_cookie_expiration() {
		return intval( apply_filters( 'wlfmc_cookie_expiration', 60 * 60 * 24 * 30 ) );
	}
}

if ( ! function_exists( 'wlfmc_setcookie' ) ) {
	/**
	 * Create a cookie.
	 *
	 * @param string $name Cookie name.
	 * @param mixed  $value Cookie value.
	 * @param int    $time Cookie expiration time.
	 * @param bool   $secure Whether cookie should be available to secured connection only.
	 * @param bool   $httponly Whether cookie should be available to HTTP request only (no js handling).
	 *
	 * @return bool
	 */
	function wlfmc_setcookie( $name, $value = array(), $time = null, $secure = false, $httponly = false ) {
		if ( ! apply_filters( 'wlfmc_set_cookie', true ) || empty( $name ) ) {
			return false;
		}

		$time = null !== $time ? $time : time() + wlfmc_get_cookie_expiration();

		$value      = wp_json_encode( stripslashes_deep( $value ) );
		$expiration = apply_filters( 'wlfmc_cookie_expiration_time', $time ); // Default 30 days.

		$_COOKIE[ $name ] = $value;
		wc_setcookie( $name, $value, $expiration, $secure, $httponly );

		return true;
	}
}

if ( ! function_exists( 'wlfmc_getcookie' ) ) {
	/**
	 * Retrieve the value of a cookie.
	 *
	 * @param string $name Cookie name.
	 *
	 * @return mixed
	 */
	function wlfmc_getcookie( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			return json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ), true );
		}

		return array();
	}
}

if ( ! function_exists( 'wlfmc_destroycookie' ) ) {
	/**
	 * Destroy a cookie.
	 *
	 * @param string $name Cookie name.
	 *
	 * @return void
	 */
	function wlfmc_destroycookie( $name ) {
		wlfmc_setcookie( $name, array(), time() - 3600 );
	}
}
