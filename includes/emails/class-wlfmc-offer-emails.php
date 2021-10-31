<?php
/**
 * Offer email class
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Offer_Emails' ) ) {
	/**
	 * WooCommerce Wishlist Offer Emails
	 *
	 */
	class WLFMC_Offer_Emails {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Offer_Emails
		 */
		protected static $instance;


		/**
		 * Constructor method, used to return object of the class to WC
		 */
		public function __construct() {

			add_action( 'wlfmc_added_to_wishlist', array( $this, 'add_emails' ), 10, 3 );
			add_action( 'wlfmc_send_offer_mail', array( $this, 'send_email' ), 10 );
			add_action( 'mc_panel_before_wlfmc_options_update', array( $this, 'check_for_delete_queue' ), 10 );

		}

		/**
		 * add queue email to DB
		 *
		 * @param $prod_id
		 * @param $wishlist_id
		 * @param $user_id
		 *
		 * @version 1.0.1
		 */
		public function add_emails( $prod_id, $wishlist_id, $user_id ) {

			if ( ! is_user_logged_in() ) {
				return;
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$options            = new MCT_Options( 'wlfmc_options' );
			$min_total          = $options->get_option( 'minimum-wishlist-total', 1 );
			$min_count          = $options->get_option( 'minimum-wishlist-count', 1 );
			$include_products   = $options->get_option( 'include-product' );
			$period_days        = (int) $options->get_option( 'period-days' );
			$offer_emails       = $options->get_option( 'offer_emails' );
			$current_time       = strtotime( current_time( 'mysql' ) );
			$last_time_added    = get_user_meta( $user_id, 'wlfmc_last_period_days', true );
			$exists             = true;
			$can_add_after_date = '' == $last_time_added ? $current_time : strtotime( '+' . $period_days . ' days', $last_time_added );
			$most_days          = 0;
			$need_coupon        = false;
			$coupon_amount      = $options->get_option( 'coupon-amount' );

			// check offer email exists and conditions is true or not
			if ( ! is_array( $offer_emails ) || empty( $offer_emails ) || $min_count > $wishlist->count_items() || $min_total > $wishlist->get_total() ) {
				return;
			}

			// Checked wishlist have one of the  product included or not
			if ( is_array( $include_products ) && ! empty( $include_products ) ) {
				$exists = false;
				foreach ( $include_products as $product_id ) {
					$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );

					/*if ( array_key_exists( $product_id, $wishlist->get_items() ) ) {
						$exists = true;
						break;
					}*/
					if ( $product_id === $prod_id ) {
						$exists = true;
						break;
					}

				}
			}

			if ( false === $exists || $can_add_after_date > $current_time ) {
				return;
			}


			foreach ( $offer_emails as $email_option ) {
				if ( ( '1' == $email_option['enable_email'] ) &&
				     ( intval( $email_option['send_after_days'] ) > 0 ) &&
				     ( ( 'plain' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) || ( 'html' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) )
				) {
					$most_days = intval( $email_option['send_after_days'] ) > $most_days ? intval( $email_option['send_after_days'] ) : $most_days;
				}
				if ( ( 'plain' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) || ( 'html' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) ) {
					$need_coupon = true;
				}
			}
			if ( ! $coupon_amount || ! floatval( $coupon_amount ) > 0 ) {
				$need_coupon = false;
			}

			$user               = get_userdata( $user_id );
			$current_user_email = $user->user_email;
			$expires_after_days = intval( $options->get_option( 'expiry-date' ) ) + $most_days;
			$date_expires       = strtotime( '+' . $expires_after_days . ' days', $current_time );
			$coupon_args        = array(
				'code'                 => $this->generate_coupon_code(),
				'discount_type'        => $options->get_option( 'discount-type', 'fixed_cart' ),
				'amount'               => $coupon_amount,
				'date_expires'         => $date_expires,
				'free_shipping'        => '1' == $options->get_option( 'free-shipping', '0' ) ? true : false,
				'email_restrictions'   => '1' == $options->get_option( 'user-restriction', '0' ) ? $current_user_email : '',
				'delete_after_expired' => '1' == $options->get_option( 'delete-after-expired', '0' ) ? 'yes' : 'no',
			);

			$coupon_id = $need_coupon ? $this->add_coupon( $coupon_args ) : null;

			foreach ( $offer_emails as $email_option ) {
				if ( '1' == $email_option['enable_email'] ) {

					$days       = intval( $email_option['send_after_days'] );
					$datesend   = $days > 0 ? strtotime( '+' . $days . ' days', $current_time ) : $current_time;
					$has_coupon = ( ( 'plain' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['text_content'], '{coupon_code}' ) ) || ( 'html' === $email_option['mail_type'] && wlfmc_str_contains( $email_option['html_content'], '{coupon_code}' ) ) ) ? 1 : 0;
					$this->insert_email( array(
						'user_id'       => $user_id,
						'wishlist_id'   => $wishlist_id,
						'has_coupon'    => $has_coupon,
						'coupon_id'     => $coupon_id,
						'product_id'    => $prod_id,
						'days'          => $days,
						'email_options' => array(
							'mail_type'    => $email_option['mail_type'],
							'mail_heading' => $email_option['mail_heading'],
							'mail_subject' => $email_option['mail_subject'],
							'mail_content' => ( 'plain' == $email_option['mail_type'] ) ? $email_option['text_content'] : $email_option['html_content'],

						),
						'datesend'      => date( 'Y-m-d H:i:s', $datesend ),
					) );
				}

			}

			update_user_meta( $user_id, 'wlfmc_last_period_days', $current_time );


		}

		/**
		 * send offer email
		 *
		 * @param $email_row
		 *
		 * @throws Exception
		 */
		public function send_email( $email_row ) {

			$mailer             = WC()->mailer();
			$user               = get_userdata( $email_row->user_id );
			$to                 = $user->user_email;
			$email_options      = unserialize( $email_row->email_options );
			$email_content      = $email_options['mail_content'];
			$coupon_code        = '';
			$coupon_amount      = '';
			$coupon_expiry_date = '';

			switch ( $email_options['mail_type'] ) {
				case 'html':
					$content_type = 'text/html';
					$template     = 'offer.php';

					break;
				case 'plain':
					$content_type = 'text/plain';
					$template     = 'plain/offer.php';
					break;
			}
			$headers = "Content-Type: {$content_type}\r\n";

			if ( 1 == $email_row->has_coupon ) {
				$coupon_object = new WC_Coupon( $email_row->coupon_id );
				$discounts     = new WC_Discounts();
				// check coupon valid
				if ( $discounts->is_coupon_valid( $coupon_object ) && wlfmc_str_contains( $email_content, '{coupon_code}' ) ) {
					$coupon_code        = $coupon_object->get_code();
					$coupon_amount      = $coupon_object->get_amount( 'view' );
					$coupon_expiry_date = $coupon_object->get_date_expires( 'view' );
				}
			}
			$wishlist     = WLFMC_Wishlist_Factory::get_wishlist( $email_row->wishlist_id );
			$wishlist_url = $wishlist->get_share_url();

			$shop_url     = get_permalink( wc_get_page_id( 'shop' ) );
			$checkout_url = add_query_arg( array(
				'add_all_to_cart' => 'true',
				'wishlist_id'     => $email_row->wishlist_id
			), $wishlist_url );

			$placeholders  = array(
				'{user_name}'        => $user->user_login,
				'{user_email}'       => $user->user_email,
				'{user_first_name}'  => $user->first_name,
				'{user_last_name}'   => $user->last_name,
				'{coupon_code}'      => $coupon_code,
				'{coupon_amount}'    => $coupon_amount,
				'{expiry_date}'      => $coupon_expiry_date,
				'{shop_url}'         => esc_url( $shop_url ),
				'{checkout_url}'     => esc_url( $checkout_url ),
				'{wishlist_url}'     => esc_url( $wishlist_url ),
				'{site_name}'        => get_bloginfo( 'name' ),
				'{site_description}' => get_bloginfo( 'description' ),
			);
			$email_content = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_content );

			unset( $placeholders['{shop_url}'] );
			unset( $placeholders['{checkout_url}'] );
			unset( $placeholders['{wishlist_url}'] );

			$email_heading = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_heading'] );
			$email_subject = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $email_options['mail_subject'] );


			$message = wlfmc_get_template( 'emails/' . $template, array(
				'email'         => $mailer,
				'email_heading' => apply_filters( 'wlfmc_offer_email_heading', $email_heading ),
				'email_content' => apply_filters( 'wlfmc_offer_email_content', $email_content )
			), true );


			add_filter( 'woocommerce_email_from_name', array( $this, 'get_from_name' ), 10 );
			add_filter( 'woocommerce_email_from_address', array( $this, 'get_from_address' ), 10 );

			$send_state = $mailer->send( $to, apply_filters( 'wlfmc_offer_email_subject', $email_subject ), $message, $headers, '' );

			remove_filter( 'woocommerce_email_from_name', array( $this, 'get_from_name' ), 10 );
			remove_filter( 'woocommerce_email_from_address', array( $this, 'get_from_address' ), 10 );

			if ( $send_state ) {
				$this->set_sent( $email_row->ID );
			} else {
				$this->set_notsent( $email_row->ID );
			}

		}

		/**
		 *  Get from name for email.
		 *
		 * @param $default
		 *
		 * @return string
		 *
		 * @since 1.0.1
		 */
		public function get_from_name( $default ) {

			$options = new MCT_Options( 'wlfmc_options' );

			return $options->get_option( 'email-from-name', $default );

		}

		/**
		 *  Get from address for email.
		 *
		 * @param $default
		 *
		 * @return string
		 *
		 * @since 1.0.1
		 */
		public function get_from_address( $default ) {

			$options = new MCT_Options( 'wlfmc_options' );

			return $options->get_option( 'email-from-address', $default );

		}

		/**
		 * insert Email to DB
		 *
		 * @param $args
		 *
		 * @return mixed
		 * @version 1.0.1
		 */
		public function insert_email( $args ) {
			global $wpdb;

			$wpdb->insert(
				$wpdb->wlfmc_offers,
				array(
					'user_id'       => $args['user_id'],
					'wishlist_id'   => $args['wishlist_id'],
					'has_coupon'    => $args['has_coupon'],
					'coupon_id'     => $args['coupon_id'],
					'product_id'    => $args['product_id'],
					'email_options' => serialize( $args['email_options'] ),
					'datesend'      => $args['datesend'],
					'days'          => $args['days'],
				),
				array(
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
				)
			);

			return $wpdb->insert_id;

		}

		/**
		 * Generate Random coupon code
		 * @return string
		 */
		public function generate_coupon_code() {
			global $wpdb;

			$sql = "SELECT COUNT(*) FROM `{$wpdb->posts}` WHERE `post_title` = %s AND `post_status` = 'publish' AND  `post_type` = 'shop_coupon'";

			do {
				$dictionary = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
				$nchars     = 8;
				$code       = "";

				for ( $i = 0; $i <= $nchars - 1; $i ++ ) {
					$code .= $dictionary[ mt_rand( 0, strlen( $dictionary ) - 1 ) ];
				}

				$count = $wpdb->get_var( $wpdb->prepare( $sql, $code ) );
			} while ( $count != 0 );

			return $code;
		}

		/**
		 * add new coupon code
		 *
		 * @param $args
		 *
		 * @return int
		 */
		public function add_coupon( $args ) {

			$args = wp_parse_args(
				$args,
				array(
					'code'                        => '',
					'discount_type'               => 'fixed_cart',
					'amount'                      => '',
					'date_expires'                => null,
					'free_shipping'               => false,
					'email_restrictions'          => '',
					'usage_limit'                 => 1,
					'usage_limit_per_user'        => 1,
					'delete_after_expired'        => 'no',
					'individual_use'              => false,
					'product_ids'                 => array(),
					'excluded_product_ids'        => array(),
					'product_categories'          => array(),
					'excluded_product_categories' => array(),
					'exclude_sale_items'          => false,
					'minimum_amount'              => '',
					'maximum_amount'              => '',
				)
			);
			$args = apply_filters( 'wlfmc_add_coupon_offer_args', $args );

			$coupon = new WC_Coupon();

			$coupon->set_code( $args['code'] );

			//the coupon discount type can be 'fixed_cart', 'percent' or 'fixed_product', defaults to 'fixed_cart'
			$coupon->set_discount_type( $args['discount_type'] );

			//the discount amount, defaults to zero
			$coupon->set_amount( $args['amount'] );

			//the coupon's expiration date defaults to null
			$coupon->set_date_expires( $args['date_expires'] );

			//determines if the coupon can only be used by an individual, defaults to false
			$coupon->set_individual_use( $args['individual_use'] );

			//the individual prodcuts that the disciunt will apply to, default to an empty array
			$coupon->set_product_ids( $args['product_ids'] );

			//the individual products that are excluded from the discount, default to an empty array
			$coupon->set_excluded_product_ids( $args['excluded_product_ids'] );

			//the times the coupon can be used, defaults to zero
			$coupon->set_usage_limit( $args['usage_limit'] );

			//the times the coupon can be used per user, defaults to zero
			$coupon->set_usage_limit_per_user( $args['usage_limit_per_user'] );

			//whether the coupon awards free shipping, defaults to false
			$coupon->set_free_shipping( $args['free_shipping'] );

			//the product categories included in the promotion, defaults to an empty array
			$coupon->set_product_categories( $args['product_categories'] );

			//the product categories excluded from the promotion, defaults to an empty array
			$coupon->set_excluded_product_categories( $args['excluded_product_categories'] );

			//whether sale items are excluded from the coupon, defaults to false
			$coupon->set_exclude_sale_items( $args['exclude_sale_items'] );

			//the minimum amount of spend required to make the coupon active, defaults to an empty string
			$coupon->set_minimum_amount( $args['minimum_amount'] );

			//the maximum amount of spend required to make the coupon active, defaults to an empty string
			$coupon->set_maximum_amount( $args['maximum_amount'] );

			//a list of email addresses, the coupon will only be applied if the customer is linked to one of the listed emails, defaults to an empty array
			$coupon->set_email_restrictions( $args['email_restrictions'] );

			//save the coupon
			$coupon->save();

			if ( 'yes' == $args['delete_after_expired'] ) {
				update_post_meta( $coupon->get_id(), 'delete_after_expired', 'yes' );
			}

			return $coupon->get_id();


		}

		/**
		 * Delete Expired coupons
		 */
		public function delete_expired_coupons() {
			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'date_expires',
						'value'   => current_time( 'timestamp' ),
						'compare' => '<='
					),
					array(
						'key'     => 'date_expires',
						'value'   => '',
						'compare' => '!='
					),
					array(
						'key'     => 'delete_after_expired',
						'value'   => 'yes',
						'compare' => '='
					),
				)
			);

			$coupons = get_posts( $args );

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					wp_delete_post( $coupon->ID, true );
				}
			}
		}

		/**
		 * Set email offer to Sent
		 *
		 * @param $id
		 */
		public function set_sent( $id ) {
			global $wpdb;

			$wpdb->update(
				$wpdb->wlfmc_offers,
				array( 'is_sent' => 1 ),
				array( 'ID' => $id ),
				array( '%d' )
			);
		}

		/**
		 * Set email offer to NotSent
		 *
		 * @param $id
		 */
		public function set_notsent( $id ) {
			global $wpdb;

			$wpdb->update(
				$wpdb->wlfmc_offers,
				array( 'is_sent' => 2 ),
				array( 'ID' => $id ),
				array( '%d' )
			);
		}

		/**
		 * Get count email offer in queue
		 * @return int
		 */
		public function get_count_send_queue() {
			global $wpdb;

			return (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->wlfmc_offers} WHERE is_sent = 0 " );
		}

		/**
		 * Get count email offer in queue by days
		 *
		 * @param int $days
		 *
		 * @return int
		 *
		 * @version 1.0.1
		 * @since 1.0.1
		 */
		public function get_count_send_queue_by_days( $days = 0 ) {
			global $wpdb;

			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->wlfmc_offers} WHERE days=%s AND is_sent = 0 ", $days ) );
		}


		/**
		 * get email queues
		 *
		 * @param $limit
		 *
		 * @return mixed
		 */
		public function get_email_queue( $limit ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->wlfmc_offers} WHERE is_sent = 0 AND  `datesend` < NOW() LIMIT %d", $limit ) );
		}

		/**
		 * Delete email queue that not sent
		 */
		public function delete_email_queue() {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->wlfmc_offers} WHERE is_sent = 0 " ) );
		}

		/**
		 * Check admin option that user want to delete email queue or not
		 */
		public function check_for_delete_queue() {
			if ( isset( $_POST['delete-queue-emails'] ) && $_POST['delete-queue-emails'] == '1' ) {
				$this->delete_email_queue();
				$_POST['delete-queue-emails'] = '0';
			}
		}

		/**
		 * Returns text with placeholders that can be used in this email
		 *
		 * @param string $email_type Email type.
		 * @param int $number
		 *
		 * @return string Placeholders
		 */
		public static function get_default_content( $email_type, $number = 0 ) {
			switch ( $number ) {
				case 1 :
					if ( 'plain' == $email_type ) {
						return __(
							'Hi {user_name},

 I\'ll keep this short to make the 19 seconds it takes to read this worth your time (yes, I timed it.)

If you remember, you were interested in some of our products, which are still on your Wishlist.
Would it be helpful if we send you the wishlist link?
{wishlist_url}

Thinking about The specific result achieved after the purchase?
So finalize your purchase?

Thanks so much for your attention
Regards,
{site_name}',
							'wc-wlfmc-wishlist'
						);
					} else {
						return __(
							'<p>Hi {user_name},</p>
<br>
<p>I\'ll keep this short to make the 19 seconds it takes to read this worth your time (yes, I timed it.)</p>
<br>
<p>If you remember, you were interested in some of our products, which are still on your Wishlist.
Would it be helpful if we send you the wishlist link? </p>
<p><a href="{wishlist_url}">Wishlist</a></p>
<br>
<p>Thinking about The specific result achieved after the purchase?</p>
<p>So finalize your purchase?</p>
<br>
<p>Thanks so much for your attention</p>
<p>Regards,</p>
<p>{site_name}</p>
',
							'wc-wlfmc-wishlist'
						);
					}
					break;
				case 2:
					if ( 'plain' == $email_type ) {
						return __(
							'Hey {user_name},
It\'s employee name on this side.

I’ve got good news for you: Now you can get your favorite product for {coupon_amount} off!  Simply use the code below to get it:
{coupon_code}
{wishlist_url}

Remember to use this discount code at the checkout and it\'s valid until {expiry_date}.

Please let us know if you need any assistance. Hope you like the deal!

Best,
employee name, {site_name}',
							'wc-wlfmc-wishlist'
						);
					} else {
						return __(
							'<p>Hey {user_name},</p>
<p>It\'s employee name on this side.</p>
<br>
<p>I’ve got good news for you: Now you can get your favorite product for {coupon_amount} off!  Simply use the code below to get it:</p>
<p>{coupon_code}</p>
<p><a href="{wishlist_url}">Wishlist</a></p>
<br>
<p>Remember to use this discount code at the checkout and it\'s valid until {expiry_date}.</p>
<br>
<p>Please let us know if you need any assistance. Hope you like the deal!</p>
<br>
<p>Best,</p>
<p>employee name, {site_name}</p>
',
							'wc-wlfmc-wishlist'
						);
					}
					break;
				case 3:
					if ( 'plain' == $email_type ) {
						return __(
							'Don’t hesitate !

Howdy,
employee name again!
The best deals are selling out fast!

You have an opportunity to buy. Your time isn\'t unlimited, so make sure you make your decision fast and get benefitted.
{coupon_code}
{checkout_url}

I\'n not going anywhere, so I’ll be here if you need my help.
employee name',
							'wc-wlfmc-wishlist'
						);
					} else {
						return __(
							'<p>Don’t hesitate !</p>
<br>
<p>Howdy,</p>
<p>employee name again!</p>
<p>The best deals are selling out fast!</p>
<br>
<p>You have an opportunity to buy. Your time isn\'t unlimited, so make sure you make your decision fast and get benefitted.</p>
<p>{coupon_code}</p>
<p><a href="{checkout_url}">Checkout Now!</a></p>
<br>
<p>I\'n not going anywhere, so I’ll be here if you need my help.</p>
<p>employee name</p>',
							'wc-wlfmc-wishlist'
						);
					}
					break;
				case 4:
					if ( 'plain' == $email_type ) {
						return __(
							'Time is running out!
You have limited time to buy your favorite product for the lowest price.

In order not to waste time:
I want to use  {coupon_amount} off: {checkout_url}
I want to miss it out: {shop_url}

1. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.
2. My number: --- (If you have any questions)
3. Website support number: --- (Customer Service)

Have a quick and good purchase,
employee name',
							'wc-wlfmc-wishlist'
						);
					} else {
						return __(
							'<p>Time is running out!</p>
<p>You have limited time to buy your favorite product for the lowest price</p>
<br>
<p>In order not to waste time:</p>
<p>I want to use  {coupon_amount} off: <a href="{checkout_url}">Checkout Now!</a></p>
<p>I want to miss it out:<a href="{shop_url}">Website</a></p>
<br>
<p>1. Use the code {coupon_code} at checkout. it\'s only valid until {expiry_date}.</p>
<p>2. My number: --- (If you have any questions)</p>
<p>3. Website support number: --- (Customer Service)</p>
<br>
<p>Have a quick and good purchase,</p>
<p>employee name</p>',
							'wc-wlfmc-wishlist'
						);
					}
					break;
				default:
					if ( 'plain' == $email_type ) {
						return __(
							'Hi {user_name}
A offer for you!

use this coupon code
{coupon_code}
to get an amazing discount!',
							'wc-wlfmc-wishlist'
						);
					} else {
						return __(
							'<p>Hi {user_name}</p>
<p>A offer for you!</p>
<p>Use this coupon code <b>{coupon_code}</b> to get an amazing discount!</p>',
							'wc-wlfmc-wishlist'
						);
					}
					break;
			}

		}

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Offer_Emails
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}
}
/**
 * Unique access to instance of WLFMC_Offer_Emails class
 *
 * @return WLFMC_Offer_Emails
 */
function WLFMC_Offer_Emails() {
	return WLFMC_Offer_Emails::get_instance();
}

