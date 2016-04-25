<?php
/*
    Plugin Name: TP Hotel Booking Importer
    Plugin URI: http://thimpress.com/
    Description: TP Hotel Booking Export, Import Rooms, Bookings, Pricings, Rooms Types, Rooms Capacities
    Author: ThimPress
    Version: 0.0.1
    Author URI: http://thimpress.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'HOTEL_BOOKING_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'HOTEL_BOOKING_IMPORTER_URI', plugin_dir_url( __FILE__ ) );
define( 'HOTEL_BOOKING_IMPORTER_VER', '0.0.1' );

final class Hotel_Booking_Importer {

	public $is_hotel_active = false;

	function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'is_hotel_active' ) );
	}

	/**
	 * is hotel booking activated
	 * @return boolean
	 */
	function is_hotel_active()
	{
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if( class_exists( 'TP_Hotel_Booking' ) && ( is_plugin_active( 'tp-hotel-booking/tp-hotel-booking.php' ) || is_plugin_active( 'wp-hotel-booking/wp-hotel-booking.php' ) ) )
		{
			$this->is_hotel_active = true;
		}

		if( ! $this->is_hotel_active ) {
			add_action( 'admin_notices', array( $this, 'add_notices' ) );
		}
		else
		{
			if( $this->is_hotel_active && is_admin() )
			{
				require_once HOTEL_BOOKING_IMPORTER_PATH . 'inc/functions.php';
				require_once HOTEL_BOOKING_IMPORTER_PATH . 'inc/class-hbip-admin-menu.php';
				require_once HOTEL_BOOKING_IMPORTER_PATH . 'inc/class-hbip-importer.php';
				require_once HOTEL_BOOKING_IMPORTER_PATH . 'inc/class-hbip-exporter.php';
			}
		}

		$this->load_text_domain();
	}

	function load_text_domain() {
		$default = WP_LANG_DIR . '/plugins/tp-hotel-booking-importer-' . get_locale() . '.mo';
		$plugin_file = HOTEL_BOOKING_IMPORTER_PATH . '/languages/tp-hotel-booking-importer-' . get_locale() . '.mo';
		$file = false;
		if ( file_exists( $default ) ) {
			$file = $default;
		} else {
			$file = $plugin_file;
		}
		if ( $file ) {
			load_textdomain( 'tp-hotel-booking-importer', $file );
		}
	}

	/**
	 * notices missing tp-hotel-booking plugin
	 */
	function add_notices()
	{
		?>
			<div class="error">
				<p><?php _e( 'The <strong>TP Hotel Booking</strong> is not installed and/or activated. Please install and/or activate before you can using <strong>TP Hotel Booking Importer</strong> add-on'); ?></p>
			</div>
		<?php
	}

}

new Hotel_Booking_Importer();