<?php
/**
 * Plugin Name: Job Listings Location
 * Plugin URI:        https://nootheme.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.1.0
 * Author:            NooTheme
 * Author URI:        https://nootheme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       job-listings-location
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Job_Listings_Location {

	/**
	 * Job_Listings_Location constructor.
	 */
	public function __construct() {

		define( 'JLT_LOCATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'JLT_LOCATION_PLUGIN_TEMPLATE_DIR', JLT_LOCATION_PLUGIN_DIR . 'templates/' );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		$this->init();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'job-listings-location' );

		load_textdomain( 'job-listings-location', WP_LANG_DIR . "/job-listings-location/job-listings-location-$locale.mo" );
		load_plugin_textdomain( 'job-listings-location', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	public function init() {
		require JLT_LOCATION_PLUGIN_DIR . 'includes/functions.php';
		require JLT_LOCATION_PLUGIN_DIR . 'includes/admin-hooks.php';
		require JLT_LOCATION_PLUGIN_DIR . 'includes/admin-settings.php';
	}

	public function enqueue_scripts() {
		$jobboard_location = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
		wp_register_script( 'jlt-location', plugin_dir_url( __FILE__ ) . 'assets/scripts.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'jlt-location', 'JLT_Location', $jobboard_location );
		wp_enqueue_script( 'jlt-location' );
	}

	public function enqueue_style() {
		wp_enqueue_style( 'jlt-location', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), '1.0', 'all' );
	}

	public function admin_enqueue_scripts() {
		$jobboard_location = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
		wp_register_script( 'jlt-location-admin', plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'jlt-location-admin', 'JLT_Location', $jobboard_location );
		wp_enqueue_script( 'jlt-location-admin' );
	}
}

function run_job_listings_location() {
	new Job_Listings_Location();
}

add_action( 'job_listings_loaded', 'run_job_listings_location' );