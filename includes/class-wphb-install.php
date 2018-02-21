<?php

/**
 * Installation related functions and actions.
 *
 * @class       WPHB_Install
 * @version     2.0
 * @package     WP_Hotel_Booking/Classes
 * @category    Class
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WPHB_Install' ) ) {

	/**
	 * Class WPHB_Install.
	 *
	 * @since 2.0
	 */
	class WPHB_Install {

		/**
		 * @var array
		 */
		static $upgrade = array();

		/**
		 * Install processes.
		 *
		 * @since 2.0
		 */
		public static function install() {

			global $wpdb;
			if ( is_multisite() ) {
				// store the current blog id
				$current_blog = $wpdb->blogid;
				// Get all blogs in the network and activate plugin on each one
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blog_ids as $blog_id ) {
					// each blog
					switch_to_blog( $blog_id );

					self::do_install();

					// restore
					restore_current_blog();
				}
			} else {
				self::do_install();
			}
		}

		/**
		 * Uninstall processes.
		 *
		 * @since 2.0
		 */
		public static function uninstall() {
			if ( is_multisite() ) {
				delete_site_option( 'wphb_notice_remove_hotel_booking' );
			} else {
				delete_option( 'wphb_notice_remove_hotel_booking' );
			}
		}

		/**
		 * Do install process.
		 */
		public static function do_install() {
			// create pages
			self::create_pages();
			// create update options
			self::create_options();
			// create tables
			self::create_tables();
		}

		/**
		 * Create default options
		 */
		public static function create_options() {
			$settings_pages = WPHB_Admin_Settings::get_settings_pages();

			foreach ( $settings_pages as $setting ) {
				$options = $setting->get_settings();
				foreach ( $options as $option ) {
					if ( isset( $option['id'], $option['default'] ) ) {
						if ( ! get_option( $option['id'], false ) ) {
							update_option( $option['id'], $option['default'] );
						}
					}
				}
			}
		}

		/**
		 * Create default page.
		 */
		public static function create_pages() {
			if ( ! function_exists( 'hb_create_page ' ) ) {
				include_once( WPHB_INCLUDES . 'admin/wphb-admin-functions.php' );
				include_once( WPHB_INCLUDES . 'wphb-functions.php' );
			}

			$pages = array();
			if ( ! hb_get_page_id( 'cart' ) || ! get_post( hb_get_page_id( 'cart' ) ) ) {
				$pages['cart'] = array(
					'name'    => _x( 'hotel-cart', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Hotel Cart', 'Page Title', 'wp-hotel-booking' ),
					'content' => '[' . apply_filters( 'hotel_booking_cart_shortcode_tag', 'hotel_booking_cart' ) . ']'
				);
			}

			if ( ! hb_get_page_id( 'checkout' ) || ! get_post( hb_get_page_id( 'checkout' ) ) ) {
				$pages['checkout'] = array(
					'name'    => _x( 'hotel-checkout', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Hotel Checkout', 'Page Title', 'wp-hotel-booking' ),
					'content' => '[' . apply_filters( 'hotel_booking_checkout_shortcode_tag', 'hotel_booking_checkout' ) . ']'
				);
			}

			if ( ! hb_get_page_id( 'search' ) || ! get_post( hb_get_page_id( 'search' ) ) ) {
				$pages['search'] = array(
					'name'    => _x( 'hotel-search', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Hotel Booking Search', 'Page Title', 'wp-hotel-booking' ),
					'content' => '[' . apply_filters( 'hotel_booking_search_shortcode_tag', 'hotel_booking' ) . ']'
				);
			}

			if ( ! hb_get_page_id( 'account' ) || ! get_post( hb_get_page_id( 'account' ) ) ) {
				$pages['account'] = array(
					'name'    => _x( 'hotel-account', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Hotel Account', 'Page Title', 'wp-hotel-booking' ),
					'content' => '[' . apply_filters( 'hotel_booking_account_shortcode_tag', 'hotel_booking_account' ) . ']'
				);
			}

			if ( ! hb_get_page_id( 'terms' ) || ! get_post( hb_get_page_id( 'terms' ) ) ) {
				$pages['terms'] = array(
					'name'    => _x( 'hotel-term-condition', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Terms and Conditions ', 'Page Title', 'wp-hotel-booking' ),
					'content' => apply_filters( 'hotel_booking_terms_content', 'Something notices' )
				);
			}

			if ( ! hb_get_page_id( 'thankyou' ) || ! get_post( hb_get_page_id( 'thankyou' ) ) ) {
				$pages['thankyou'] = array(
					'name'    => _x( 'hotel-thank-you', 'Page Slug', 'wp-hotel-booking' ),
					'title'   => _x( 'Hotel Thank You', 'Page Title', 'wp-hotel-booking' ),
					'content' => '[' . apply_filters( 'hotel_booking_thankyou_shortcode_tag', 'hotel_booking_thankyou' ) . ']'
				);
			}

			if ( $pages ) {
				foreach ( $pages as $key => $page ) {
					$pageId = hb_create_page( esc_sql( $page['name'] ), 'hotel_booking_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? hb_get_page_id( $page['parent'] ) : '' );
					hb_settings()->set( $key . '_page_id', $pageId );
				}
			}
		}

		/**
		 * Create database tables.
		 */
		public static function create_tables() {
			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$charset_collate = $wpdb->get_charset_collate();

			$table = $wpdb->prefix . 'hotel_booking_order_items';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

				// order items
				$sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_order_items (
					order_item_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					order_item_name longtext NOT NULL,
					order_item_type varchar(255) NOT NULL,
					order_item_parent bigint(20) NULL,
					order_id bigint(20) unsigned NOT NULL,
					UNIQUE KEY order_item_id (order_item_id),
					PRIMARY KEY  (order_item_id)
				) $charset_collate;
			";
				dbDelta( $sql );
			}

			$table = $wpdb->prefix . 'hotel_booking_order_itemmeta';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

				// order item meta
				$sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_order_itemmeta (
					meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					hotel_booking_order_item_id bigint(20) unsigned NOT NULL,
					meta_key varchar(255) NULL,
					meta_value longtext NULL,
					UNIQUE KEY meta_id (meta_id),
					PRIMARY KEY  (meta_id),
					KEY hotel_booking_order_item_id(hotel_booking_order_item_id),
					KEY meta_key(meta_key)
				) $charset_collate;
			";
				dbDelta( $sql );
			}

			$table = $wpdb->prefix . 'hotel_booking_plans';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) != $table ) {

				// pricing tables
				$sql = "
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}hotel_booking_plans (
					plan_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					room_id bigint(20) unsigned NOT NULL,
					start_time timestamp NULL,
					end_time timestamp NULL,
					pricing longtext NULL,
					UNIQUE KEY plan_id (plan_id),
					PRIMARY KEY  (plan_id)
				) $charset_collate;
			";
				dbDelta( $sql );
			}
		}


		/**
		 * Add tables to delete when delete blog.
		 *
		 * @since 2.0
		 *
		 * @param $tables
		 *
		 * @return array
		 */
		static function delete_tables( $tables ) {
			global $wpdb;
			$tables[] = $wpdb->prefix . 'hotel_booking_order_items';
			$tables[] = $wpdb->prefix . 'hotel_booking_order_itemmeta';
			$tables[] = $wpdb->prefix . 'hotel_booking_plans';

			return $tables;
		}

		/**
		 * Create new tables when create new blog in multisite.
		 *
		 * @since 2.0
		 *
		 * @param $blog_id
		 */
		static function create_new_blog( $blog_id ) {
			$plugin = 'wp-hotel-booking/wp-hotel-booking.php';
			if ( is_plugin_active_for_network( $plugin ) ) {
				// switch to current blog
				switch_to_blog( $blog_id );
				self::create_tables();
				// restore
				restore_current_blog();
			}
		}
	}
}
