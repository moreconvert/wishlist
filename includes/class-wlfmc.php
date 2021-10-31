<?php
/**
 * Main Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WLFMC' ) ) {
	/**
	 * WooCommerce Wishlist
	 */
	class WLFMC {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '1.0.1';

		/**
		 * Plugin database version
		 *
		 * @var string
		 */
		public $db_version = '1.0.1';

		/**
		 * Store class WLFMC_Install.
		 *
		 * @var object
		 * @access private
		 */
		protected $wlfmc_install;

		/**
		 * Last operation token
		 *
		 * @var string
		 */
		public $last_operation_token;

		/**
		 * Query string parameter used to generate Wishlist urls
		 *
		 * @var string
		 */
		public $wishlist_param = 'wishlist-action';

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @version 1.0.1
		 */
		public function __construct() {

			// register data stores.
			add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ) );

			define( 'WLFMC_VERSION', $this->version );
			define( 'WLFMC_DB_VERSION', $this->db_version );
			// init install class.
			$this->wlfmc_install = WLFMC_Install();
			// init  offer emails.
			$this->wlfmc_offer_emails = WLFMC_Offer_Emails();
			// init frontend class.
			$this->wlfmc_frontend = WLFMC_Frontend();
			// init crons.
			$this->wlfmc_cron = WLFMC_Cron();
			// init session.
			$this->wlfmc_session = WLFMC_Session();

			if( class_exists('\Elementor\Plugin') ) {
				$this->wlfmc_elementor = WLFMC_Elementor();
			}

			// init admin handling.
			if ( is_admin()  ) {
				$this->wlfmc_admin = WLFMC_Admin();
			}
			// add rewrite rule.
			add_action( 'init', array( $this, 'add_rewrite_rules' ), 0 );
			add_filter( 'query_vars', array( $this, 'add_public_query_var' ) );

			// Polylang integration.
			add_filter( 'pll_translation_url', array( $this, 'get_pll_wishlist_url' ), 10, 1 );
		}

		/* === ITEMS METHODS === */

		/**
		 * Add a product in the wishlist.
		 *
		 * @param array $atts  Array of parameters; when not passed, params will be searched in $_REQUEST.
		 *
		 * @return void
		 * @throws Exception|WLFMC_Exception When an error occurs with Add to Wishlist operation.
		 */
		public function add( $atts = array() ) {
			$defaults = array(
				'add_to_wishlist'     => 0,
				'wishlist_id'         => 0,
				'quantity'            => 1,
				'user_id'             => false,
				'dateadded'           => '',
				'wishlist_name'       => '',
				'wishlist_visibility' => 0,
			);

			$atts = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts = ! empty( $atts ) ? $atts :  wp_unslash( $_REQUEST );
			$atts = wp_parse_args( $atts, $defaults );

			// filtering params.
			$prod_id     = apply_filters( 'wlfmc_adding_to_wishlist_prod_id', intval( $atts['add_to_wishlist'] ) );
			$wishlist_id = apply_filters( 'wlfmc_adding_to_wishlist_wishlist_id', $atts['wishlist_id'] );
			$quantity    = apply_filters( 'wlfmc_adding_to_wishlist_quantity', intval( $atts['quantity'] ) );
			$user_id     = apply_filters( 'wlfmc_adding_to_wishlist_user_id', intval( $atts['user_id'] ) );
			$dateadded   = apply_filters( 'wlfmc_adding_to_wishlist_dateadded', $atts['dateadded'] );

			do_action( 'wlfmc_adding_to_wishlist', $prod_id, $wishlist_id, $user_id );

			if ( ! $this->can_user_add_to_wishlist() ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_user_cannot_add_to_wishlist_message', __( 'The item cannot be added to this wishlist', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the wishlist.', 'wc-wlfmc-wishlist' ), 0 );
			}

			$wishlist = 'new' === $wishlist_id ? $this->add_wishlist( $atts ) : WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id, 'edit' );

			if ( ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'add_to_wishlist' ) ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the wishlist.', 'wc-wlfmc-wishlist' ), 0 );
			}

			$this->last_operation_token = $wishlist->get_token();

			if ( $wishlist->has_product( $prod_id ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_product_already_in_wishlist_message', wlfmc_get_option( 'already_in_wishlist_text' ) ), 1 );
			}

			$item = new WLFMC_Wishlist_Item();

			$item->set_product_id( $prod_id );
			$item->set_quantity( $quantity );
			$item->set_wishlist_id( $wishlist->get_id() );
			$item->set_user_id( $wishlist->get_user_id() );

			if ( $dateadded ) {
				$item->set_date_added( $dateadded );
			}

			$wishlist->add_item( $item );
			$wishlist->save();

			wp_cache_delete( 'wishlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wishlist-user-total-count-' . $user_id, 'wlfmc-wishlists' );
			}

			do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item->get_user_id() );
		}

		/**
		 * Remove an entry from the wishlist.
		 *
		 * @param array $atts  Array of parameters; when not passed, parameters will be retrieved from $_REQUEST.
		 *
		 * @return void
		 * @throws Exception|WLFMC_Exception When something was wrong with removal.
		 */
		public function remove( $atts = array() ) {
			$defaults = array(
				'remove_from_wishlist' => 0,
				'wishlist_id'          => 0,
				'user_id'              => false,
			);

			$atts = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );
			$atts = wp_parse_args( $atts, $defaults );

			$prod_id     = intval( $atts['remove_from_wishlist'] );
			$wishlist_id = intval( $atts['wishlist_id'] );
			$user_id     = intval( $atts['user_id'] );

			do_action( 'wlfmc_removing_from_wishlist', $prod_id, $wishlist_id, $user_id );

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_message', __( 'Error. Unable to remove the product from the wishlist.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist = apply_filters( 'wlfmc_get_wishlist_on_remove', WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id ), $atts );

			if ( apply_filters( 'wlfmc_allow_remove_after_add_to_cart', ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'remove_from_wishlist' ), $wishlist ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_message', __( 'Error. Unable to remove the product from the wishlist.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist->remove_product( $prod_id );
			$wishlist->save();

			wp_cache_delete( 'wishlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wishlist-user-total-count-' . $user_id );
			}

			do_action( 'wlfmc_removed_from_wishlist', $prod_id, $wishlist->get_id(), $wishlist->get_user_id() );
		}

		/**
		 * Check if the product exists in the wishlist.
		 *
		 * @param int      $product_id Product id to check.
		 * @param int|bool $wishlist_id Wishlist where to search (use false to search in default wishlist).
		 *
		 * @return bool
		 */
		public function is_product_in_wishlist( $product_id, $wishlist_id = false ) {
			$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return false;
			}

			return apply_filters( 'wlfmc_is_product_in_wishlist', $wishlist->has_product( $product_id ), $product_id, $wishlist_id );
		}

		/**
		 * Retrieve elements of the wishlist for a specific user
		 *
		 * @param mixed $args  Arguments array; it may contains any of the following:<br/>
		 * [<br/>
		 *     'user_id'             // Owner of the wishlist; default to current user logged in (if any), or false for cookie wishlist<br/>
		 *     'product_id'          // Product to search in the wishlist<br/>
		 *     'wishlist_id'         // wishlist_id for a specific wishlist, false for default, or all for any wishlist<br/>
		 *     'wishlist_token'      // wishlist token, or false as default<br/>
		 *     'wishlist_visibility' // all, visible, public, shared, private<br/>
		 *     'is_default' =>       // whether searched wishlist should be default one <br/>
		 *     'id' => false,        // only for table select<br/>
		 *     'limit' => false,     // pagination param; number of items per page. 0 to get all items<br/>
		 *     'offset' => 0         // pagination param; offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return WLFMC_Wishlist_Item[]|bool
		 */
		public function get_products( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlist_items( $args );
		}


		/**
		 * Retrieve details of a product in the wishlist.
		 *
		 * @param int      $product_id Product ID.
		 * @param int|bool $wishlist_id Wishlist ID.
		 *
		 * @return WLFMC_Wishlist_Item|bool
		 */
		public function get_product_details( $product_id, $wishlist_id = false ) {
			$product = $this->get_products(
				array(
					'prod_id'     => $product_id,
					'wishlist_id' => $wishlist_id,
				)
			);

			if ( empty( $product ) ) {
				return false;
			}

			return array_shift( $product );
		}

		/* === WISHLISTS METHODS === */

		/**
		 * Add a new wishlist for the user.
		 *
		 * @param array $atts  Array of params for wishlist creation.
		 *
		 * @return int Id of the wishlist created
		 */
		public function add_wishlist( $atts = array() ) {
			$defaults = array(
				'user_id' => false,
			);

			$atts = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );
			$atts = wp_parse_args( $atts, $defaults );

			$user_id = ( ! empty( $atts['user_id'] ) ) ? $atts['user_id'] : false;

			return $this->generate_default_wishlist( $user_id );
		}

		/**
		 * Update wishlist with arguments passed as second parameter
		 *
		 * @param int   $wishlist_id  Wishlist ID.
		 * @param array $args  Array of parameters to use in update process.
		 */
		public function update_wishlist( $wishlist_id, $args = array() ) {
			return;
		}

		/**
		 * Delete indicated wishlist
		 *
		 * @param int $wishlist_id  Wishlist ID.
		 */
		public function remove_wishlist( $wishlist_id ) {
			return;
		}

		/**
		 * Retrieve all the wishlist matching specified arguments
		 *
		 * @param mixed $args  Array of valid arguments<br/>
		 * [<br/>
		 *     'id'                  // Wishlist id to search, if any<br/>
		 *     'user_id'             // User owner<br/>
		 *     'wishlist_slug'       // Slug of the wishlist to search<br/>
		 *     'wishlist_name'       // Name of the wishlist to search<br/>
		 *     'wishlist_token'      // Token of the wishlist to search<br/>
		 *     'wishlist_visibility' // Wishlist visibility: all, visible, public, shared, private<br/>
		 *     'user_search'         // String to match against first name / last name or email of the wishlist owner<br/>
		 *     'is_default'          // Whether wishlist should be default or not<br/>
		 *     'orderby'             // Column used to sort final result (could be any wishlist lists column)<br/>
		 *     'order'               // Sorting order<br/>
		 *     'limit'               // Pagination param: maximum number of elements in the set. 0 to retrieve all elements<br/>
		 *     'offset'              // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 *     'show_empty'          // Whether to show empty lists os not<br/>
		 * ].
		 *
		 * @return WLFMC_Wishlist[]
		 */
		public function get_wishlists( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlists( $args );
		}

		/**
		 * Wrapper for \WLFMC::get_wishlists, will return wishlists for current user
		 *
		 * @return WLFMC_Wishlist[]
		 */
		public function get_current_user_wishlists() {
			$id = is_user_logged_in() ? get_current_user_id() : WLFMC_Session()->maybe_get_session_id();

			if ( ! $id ) {
				return array();
			}
			$lists = wp_cache_get( 'user-wishlists-' . $id, 'wlfmc-wishlists' );

			if ( ! $lists ) {
				$lists = WLFMC_Wishlist_Factory::get_wishlists(
					array(
						'orderby' => 'dateadded',
						'order'   => 'ASC',
					)
				);

				wp_cache_set( 'user-wishlists-' . $id, $lists, 'wlfmc-wishlists' );
			}

			return $lists;
		}

		/**
		 * Returns details of a wishlist, searching it by wishlist id
		 *
		 * @param int $wishlist_id Wishlist ID.
		 *
		 * @return WLFMC_Wishlist
		 */
		public function get_wishlist_detail( $wishlist_id ) {
			return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
		}

		/**
		 * Returns details of a wishlist, searching it by wishlist token
		 *
		 * @param string $wishlist_token  Wishlist token.
		 *
		 * @return WLFMC_Wishlist
		 */
		public function get_wishlist_detail_by_token( $wishlist_token ) {
			return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_token );
		}

		/**
		 * Generate default wishlist for current user or session
		 *
		 * @param string|int|bool $id string|int|bool Customer or session id; false if you want to use current customer or session.
		 *
		 * @return int Default wishlist id
		 */
		public function generate_default_wishlist( $id = false ) {
			$wishlist = WLFMC_Wishlist_Factory::generate_default_wishlist( $id );

			if ( $wishlist ) {
				return $wishlist->get_id();
			}

			return false;
		}

		/**
		 * Generate a token to visit wishlist
		 *
		 * @return string token
		 */
		public function generate_wishlist_token() {
			return WLFMC_Wishlist_Factory::generate_wishlist_token();
		}

		/**
		 * Returns an array of users that created and populated a public wishlist
		 *
		 * @param mixed $args  Array of valid arguments<br/>
		 * [<br/>
		 *     'search' // String to match against first name / last name / user login or user email of wishlist owner<br/>
		 *     'limit'  // Pagination param: number of items to show in one page. 0 to show all items<br/>
		 *     'offset' // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return array
		 */
		public function get_users_with_wishlist( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlist_users( $args );
		}

		/**
		 * Count users that have public wishlists
		 *
		 * @param string $search  String to match against first name / last name / user login or user email of wishlist owner.
		 *
		 * @return int
		 */
		public function count_users_with_wishlists( $search ) {
			return count( $this->get_users_with_wishlist( array( 'search' => $search ) ) );
		}

		/* === GENERAL METHODS === */

		/**
		 * Checks whether current user can add to the wishlist
		 *
		 * @param int|bool $user_id User id to test; false to use current user id.
		 *
		 * @return bool Whether current user can add to wishlist
		 */
		public function can_user_add_to_wishlist( $user_id = false ) {
			$user_id = $user_id ? $user_id : get_current_user_id();
			$options = new MCT_Options( 'wlfmc_options' );
			$return  = true;

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			$force_user_to_login          = $options->get_option( 'force_user_to_login', false );

			if ( ( 'users' === $who_can_see_wishlist_options && ! $user_id ) || ( true == $force_user_to_login && 'all' === $who_can_see_wishlist_options && ! $user_id ) ) {
				$return = false;
			}


			return apply_filters( 'wlfmc_can_user_add_to_wishlist', $return, $user_id );
		}

		/**
		 * Register custom plugin Data Stores classes
		 *
		 * @param array $data_stores  Array of registered data stores.
		 *
		 * @return array Array of filtered data store
		 */
		public function register_data_stores( $data_stores ) {
			$data_stores['wlfmc-wishlist']      = 'WLFMC_Wishlist_Data_Store';
			$data_stores['wlfmc-wishlist-item'] = 'WLFMC_Wishlist_Item_Data_Store';

			return $data_stores;
		}

		/**
		 * Add rewrite rules for wishlist
		 *
		 * @return void
		 */
		public function add_rewrite_rules() {

			// filter wishlist param.
			$this->wishlist_param = apply_filters( 'wlfmc_wishlist_param', $this->wishlist_param );

			$wishlist_page_id = isset( $_POST['wlfmc_wishlist_page_id'] ) ? intval( $_POST['wlfmc_wishlist_page_id'] ) : get_option( 'wlfmc_wishlist_page_id' );
			$wishlist_page_id = wlfmc_object_id( $wishlist_page_id, 'page', true, 'default' );

			if ( empty( $wishlist_page_id ) ) {
				return;
			}

			$wishlist_page      = get_post( $wishlist_page_id );
			$wishlist_page_slug = $wishlist_page ? $wishlist_page->post_name : false;

			if ( empty( $wishlist_page_slug ) ) {
				return;
			}

			if ( defined( 'POLYLANG_VERSION' ) || defined( 'ICL_PLUGIN_PATH' ) ) {
				return;
			}

			$regex_paged  = '(([^/]+/)*' . urldecode( $wishlist_page_slug ) . ')(/(.*))?/page/([0-9]{1,})/?$';
			$regex_simple = '(([^/]+/)*' . urldecode( $wishlist_page_slug ) . ')(/(.*))?/?$';

			add_rewrite_rule( $regex_paged, 'index.php?pagename=$matches[1]&' . $this->wishlist_param . '=$matches[4]&paged=$matches[5]', 'top' );
			add_rewrite_rule( $regex_simple, 'index.php?pagename=$matches[1]&' . $this->wishlist_param . '=$matches[4]', 'top' );

			$rewrite_rules = get_option( 'rewrite_rules' );

			if ( ! is_array( $rewrite_rules ) || ! array_key_exists( $regex_paged, $rewrite_rules ) || ! array_key_exists( $regex_simple, $rewrite_rules ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Adds public query var for wishlist
		 *
		 * @param array $public_var Variables.
		 *
		 * @return array
		 */
		public function add_public_query_var( $public_var ) {
			$public_var[] = $this->wishlist_param;
			$public_var[] = 'wishlist_id';

			return $public_var;
		}

		/**
		 * Return wishlist page id, if any
		 *
		 * @return int Wishlist page id.
		 */
		public function get_wishlist_page_id() {
			$wishlist_page_id = get_option( 'wlfmc_wishlist_page_id' );
			$wishlist_page_id = wlfmc_object_id( $wishlist_page_id );

			return apply_filters( 'wlfmc_wishlist_page_id', $wishlist_page_id );
		}

		/**
		 * Build wishlist page URL.
		 *
		 * @param string $action Action params.
		 *
		 * @return string
		 */
		public function get_wishlist_url( $action = '' ) {
			global $sitepress;
			$wishlist_page_id   = $this->get_wishlist_page_id();
			$wishlist_permalink = get_the_permalink( $wishlist_page_id );

			$action_params = explode( '/', $action );
			$view          = $action_params[0];
			$data          = isset( $action_params[1] ) ? $action_params[1] : '';

			if ( 'view' === $action && empty( $data ) ) {
				return $wishlist_permalink;
			}

			if ( get_option( 'permalink_structure' ) && ! defined( 'ICL_PLUGIN_PATH' ) && ! defined( 'POLYLANG_VERSION' ) ) {
				$wishlist_permalink = trailingslashit( $wishlist_permalink );
				$base_url           = trailingslashit( $wishlist_permalink . $action );
			} else {
				$base_url = $wishlist_permalink;
				$params   = array();

				if ( ! empty( $data ) ) {
					$params[ $this->wishlist_param ] = $view;

					if ( 'view' === $view ) {
						$params['wishlist_id'] = $data;
					} elseif ( 'user' === $view ) {
						$params['user_id'] = $data;
					}
				} else {
					$params[ $this->wishlist_param ] = $view;
				}

				$base_url = add_query_arg( $params, $base_url );
			}

			if ( defined( 'ICL_PLUGIN_PATH' ) && $sitepress->get_current_language() != $sitepress->get_default_language() ) {
				$base_url = add_query_arg( 'lang', $sitepress->get_current_language(), $base_url );
			}

			return apply_filters( 'wlfmc_wishlist_page_url', esc_url_raw( $base_url ), $action );
		}

		/**
		 * Retrieve url for the wishlist that was affected by last operation
		 *
		 * @return string Url to view last operation wishlist
		 */
		public function get_last_operation_url() {
			$action = 'view';

			if ( ! empty( $this->last_operation_token ) ) {
				$action .= "/{$this->last_operation_token}";
			}

			return $this->get_wishlist_url( $action );
		}

		/**
		 * Generates Add to Wishlist url, to use when customer do not have js enabled
		 *
		 * @param int   $product_id  Product id to add to wishlist.
		 * @param array $args  Any of the following parameters
		 * [
		 *     'base_url' => ''
		 *     'wishlist_id' => 0,
		 *     'quantity' => 1,
		 *     'user_id' => false,
		 *     'dateadded' => '',
		 *     'wishlist_name' => '',
		 *     'wishlist_visibility' => 0
		 * ].
		 *
		 * @return string Add to wishlist url
		 */
		public function get_add_to_wishlist_url( $product_id, $args = array() ) {
			$args = array_merge(
				array(
					'add_to_wishlist' => $product_id,
				),
				$args
			);

			if ( isset( $args['base_url'] ) ) {
				$base_url = $args['base_url'];
				unset( $args['base_url'] );

				$url = add_query_arg( $args, $base_url );
			} else {
				$url = add_query_arg( $args );
			}

			return apply_filters( 'wlfmc_add_to_wishlist_url', esc_url_raw( $url ), $product_id, $args );
		}

		/**
		 * Build the URL used to remove an item from the wishlist.
		 *
		 * @param int $item_id Item ID.
		 *
		 * @return string
		 */
		public function get_remove_url( $item_id ) {
			return esc_url( add_query_arg( 'remove_from_wishlist', $item_id ) );
		}

		/**
		 * Returns available views for wishlist page
		 *
		 * @return string[]
		 */
		public function get_available_views() {
			return apply_filters( 'wlfmc_available_wishlist_views', array( 'view', 'user' ) );
		}

		/**
		 * Checks whether multi-wishlist feature is enabled for current user
		 *
		 * @return bool Whether feature is enabled or not
		 */
		public function is_multi_wishlist_enabled() {
			return false;
		}

		/* === POLYLANG INTEGRATION === */

		/**
		 * Filters translation url for the wishlist page, when PolyLang is enabled
		 *
		 * @param string $url Translation url.
		 *
		 * @return string Filtered translation url for current page/post.
		 */
		public function get_pll_wishlist_url( $url ) {
			if ( wlfmc_is_wishlist_page() && isset( $_GET[ $this->wishlist_param ] ) ) {
				$wishlist_action = sanitize_text_field( wp_unslash( $_GET[ $this->wishlist_param ] ) );
				$user_id         = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : '';
				$wishlist_id     = isset( $_GET['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wishlist_id'] ) ) : '';

				$params = array_filter(
					array(
						$this->wishlist_param => $wishlist_action,
						'user_id'             => $user_id,
						'wishlist_id'         => $wishlist_id,
					)
				);

				$url = add_query_arg( $params, $url );
			}

			return $url;
		}
	}
}

/**
 * Unique access to instance of WLFMC class
 *
 * @return WLFMC
 */
function WLFMC() {
	return WLFMC::get_instance();
}
