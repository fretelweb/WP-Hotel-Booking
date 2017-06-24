<?php

/*
    Plugin Name: WP Hotel Booking Coupon
    Plugin URI: http://thimpress.com/
    Description: WP Hotel Booking Coupon
    Author: ThimPress
    Version: 2.0
    Author URI: http://thimpress.com
*/

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WP_Hotel_Booking_Coupon' ) ) {

	/**
	 * Main WP Hotel Booking Coupon Class.
	 *
	 * @version    2.0
	 */
	final class WP_Hotel_Booking_Coupon {

		/**
		 * WP Hotel Booking Coupon version.
		 *
		 * @var string
		 */
		public $_version = '2.0';

		/**
		 * WP_Hotel_Booking_Coupon constructor.
		 *
		 * @since 2.0
		 */
		public function __construct() {
			if ( self::wphb_is_active() ) {
				$this->define_constants();
				$this->includes();
				$this->init_hooks();
			} else {
				add_action( 'admin_notices', array( $this, 'add_notices' ) );
			}
		}

		/**
		 * Check WP Hotel Booking plugin active.
		 *
		 * @since 2.0
		 *
		 * @return bool
		 */
		public static function wphb_is_active() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			return is_plugin_active( 'wp-hotel-booking/wp-hotel-booking.php' );
		}

		/**
		 * Define WP Hotel Booking Coupon constants.
		 *
		 * @since 2.0
		 */
		private function define_constants() {
			define( 'WPHB_COUPON_ABSPATH', dirname( __FILE__ ) . '/' );
			define( 'WPHB_COUPON_VER', $this->_version );
		}

		/**
		 * Include required core files.
		 *
		 * @since 2.0
		 */
		public function includes() {
			require_once WPHB_COUPON_ABSPATH . '/includes/class-wphb-coupon.php';
			require_once WPHB_COUPON_ABSPATH . '/includes/class-wphb-coupon-post-types.php';
		}

		/**
		 * Main hooks.
		 *
		 * @since 2.0
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'load_text_domain' ) );
			add_action( 'hb_admin_settings_tab_after', array( $this, 'admin_settings' ) );
			add_action( 'hotel_booking_before_cart_total', array( $this, 'add_form' ) );
		}

		/**
		 * Load text domain.
		 *
		 * @since 2.0
		 */
		public function load_text_domain() {
			$default     = WP_LANG_DIR . '/plugins/wp-hotel-booking-coupon-' . get_locale() . '.mo';
			$plugin_file = WPHB_COUPON_ABSPATH . '/languages/wp-hotel-booking-coupon-' . get_locale() . '.mo';
			if ( file_exists( $default ) ) {
				$file = $default;
			} else {
				$file = $plugin_file;
			}
			if ( $file ) {
				load_textdomain( 'wphb-coupon', $file );
			}
		}

		/**
		 * Admin notice when WP Hotel Booking not active.
		 *
		 * @since 2.0
		 */
		public function add_notices() { ?>
            <div class="error">
                <p><?php _e( 'The <strong>WP Hotel Booking</strong> is not installed and/or activated. Please install and/or activate before you can using <strong>WP Hotel Booking Coupon</strong> add-on.' ); ?></p>
            </div>
			<?php
		}

		/**
		 * Add enable coupon setting in General tab.
		 *
		 * @since 2.0
		 *
		 * @param $settings
		 */
		public function admin_settings( $settings ) {
			if ( $settings !== 'general' ) {
				return;
			}
			$settings = hb_settings();
			?>
            <table class="form-table">
                <tr>
                    <th><?php _e( 'Enable Coupon', 'wphb-coupon' ); ?></th>
                    <td>
						<?php $field_name = $settings->get_field_name( 'enable_coupon' ); ?>
                        <input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="0"/>
                        <input type="checkbox"
                               name="<?php echo esc_attr( $field_name ); ?>" <?php checked( $settings->get( 'enable_coupon' ) ? 1 : 0, 1 ); ?>
                               value="1"/>
                    </td>
                </tr>
            </table>
			<?php
		}

		/**
		 * Add coupon form in cart and checkout page.
		 *
		 * @since 2.0
		 */
		public function add_form() {

			$settings = hb_settings();

			if ( $settings->get( 'enable_coupon' ) ) {
				$cart = WPHB_Cart::instance();
				if ( $coupon = $cart->coupon ) {
					$coupon = WPHB_Coupon::instance( $coupon );
					?>
                    <tr class="hb_coupon">
                        <td class="hb_coupon_remove" colspan="8">
                            <p class="hb-remove-coupon" align="right">
                                <a href="" id="hb-remove-coupon"><i class="fa fa-times"></i></a>
                            </p>
                            <span class="hb-remove-coupon_code"><?php printf( __( 'Coupon applied: %s', 'wphb-coupon' ), $coupon->coupon_code ); ?></span>
                            <span class="hb-align-right">-<?php echo hb_format_price( $coupon->discount_value ); ?></span>
                        </td>
                    </tr>
				<?php } else { ?>
                    <tr class="hb_coupon">
                        <td colspan="8" class="hb-align-center">
                            <input type="text" name="hb-coupon-code" value=""
                                   placeholder="<?php _e( 'Coupon', 'wphb-coupon' ); ?>"/>
                            <button type="button"
                                    id="hb-apply-coupon"><?php _e( 'Apply Coupon', 'wphb-coupon' ); ?></button>
                        </td>
                    </tr>
				<?php }

			}
		}

	}

}

new WP_Hotel_Booking_Coupon();
