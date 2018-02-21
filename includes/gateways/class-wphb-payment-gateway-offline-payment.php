<?php

/**
 * WP Hotel Booking Offline payment class.
 *
 * @class       WPHB_Payment_Gateway_Offline_Payment
 * @version     2.0
 * @package     WP_Hotel_Booking/Classes
 * @category    Payment gateway Class
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPHB_Payment_Gateway_Offline_Payment' ) ) {

	/**
	 * Class WPHB_Payment_Gateway_Offline_Payment.
	 *
	 * @since 2.0
	 */
	class WPHB_Payment_Gateway_Offline_Payment extends WPHB_Abstract_Payment_Gateway {
		/**
		 * @var array
		 */
		protected $_settings = array();

		/**
		 * WPHB_Payment_Gateway_Offline_Payment constructor.
		 *
		 * @since 2.0
		 */
		public function __construct() {
			parent::__construct();
			$this->_slug        = 'offline-payment';
			$this->_title       = __( 'Offline Payment', 'wp-hotel-booking' );
			$this->_description = __( 'Pay on arrival', 'wp-hotel-booking' );
			$this->_settings    = WPHB_Settings::instance()->get( 'offline-payment' );
			$this->init();
		}

		/**
		 * Init hooks.
		 *
		 * @since 2.0
		 */
		public function init() {
			add_action( 'hb_payment_gateway_form_' . $this->slug, array( $this, 'form' ) );
			add_filter( 'hb_payment_method_title_offline-payment', array( $this, 'payment_method_title' ) );
		}

		/**
		 * Payment method title.
		 *
		 * @since 2.0
		 *
		 * @return mixed
		 */
		public function payment_method_title() {
			return $this->_description;
		}

		/**
		 * Print the text in total column.
		 *
		 * @since 2.0
		 *
		 * @param $booking_id
		 * @param $total
		 * @param $total_with_currency
		 */
		public function column_total_content( $booking_id, $total, $total_with_currency ) {
			$booking = WPHB_Booking::instance( $booking_id );
			if ( $booking->method === 'offline-payment' ) { ?>
                <small><?php echo __( 'Pay on arrival', 'wp-hotel-booking' ); ?></small>
				<?php
			}
		}

		/**
		 * Check to see if this payment is enable.
		 *
		 * @since 2.0
		 *
		 * @return bool
		 */
		public function is_enable() {
			return ! empty( $this->_settings['enable'] ) && $this->_settings['enable'] == 'on' || $this->_settings['enable'] == 1;
		}

		/**
		 * Process checkout booking.
		 *
		 * @since 2.0
		 *
		 * @param null $booking_id
		 *
		 * @return array
		 */
		public function process_checkout( $booking_id = null ) {
			$booking = WPHB_Booking::instance( $booking_id );
			if ( $booking ) {
				$booking->update_status( 'processing' );
			}

			return array(
				'result'   => 'success',
				'redirect' => hb_get_thank_you_url( $booking_id, $booking->booking_key )
			);

		}

		/**
		 * Display when selected Offline payment in checkout page.
		 *
		 * @since 2.0
		 */
		public function form() {
			echo __( ' Pay on Arrival', 'wp-hotel-booking' );
		}
	}

}