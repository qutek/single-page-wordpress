<?php
/**
 * Plugin Name: WP Single Page
 * Description: Turn WordPress site into single page app
 * Author: Lafif Astahdziq
 * Author URI: https://lafif.me
 * Author Email: hello@lafif.me
 * Version: 0.0.1
 * Text Domain: wpsp
 * Domain Path: /languages/ 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_Single_Page' ) ) :

/**
 * Main WP_Single_Page Class
 *
 * @class WP_Single_Page
 * @version	1.0.0
 */
final class WP_Single_Page {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	public $capability = 'manage_options';

	/**
	 * @var WP_Single_Page The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WP_Single_Page Instance
	 *
	 * Ensures only one instance of WP_Single_Page is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return WP_Single_Page - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WP_Single_Page Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'wptb_loaded' );
	}

	/**
	 * Hook into actions and filters
	 * @since  1.0.0
	 */
	private function init_hooks() {

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

		register_uninstall_hook( __FILE__, 'uninstall' );
	}

	/**
	 * All install stuff
	 * @return [type] [description]
	 */
	public function install() {
		
		// we did something on install
		do_action( 'on_wptb_install' );
	}

	/**
	 * All uninstall stuff
	 * @return [type] [description]
	 */
	public function uninstall() {

		// we remove what we did 
		do_action( 'on_wptb_uninstall' );
	}

	/**
	 * Init WP_Single_Page when WordPress Initialises.
	 */
	public function init() {

		// register all scripts
		$this->register_scripts();
	}

	/**
	 * Register all scripts to used on our pages
	 * @return [type] [description]
	 */
	private function register_scripts(){

		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'wpsp', plugins_url( '/dist/wpsp'. $suffix . '.js', __FILE__ ), array('jquery'), $this->version, false );
 	}

	/**
	 * Define WP_Single_Page Constants
	 */
	private function define_constants() {

		$this->define( 'WPSP_PLUGIN_FILE', __FILE__ );
		$this->define( 'WPSP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'WPSP_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		  
	  	if ( $this->is_request( 'frontend' ) ) {
	  		include_once( 'includes/class-wpsp-frontend.php' );
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

}

endif;

/**
 * Returns the main instance of WP_Single_Page to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WP_Single_Page
 */
function WPSP() {
	return WP_Single_Page::instance();
}

WPSP();
