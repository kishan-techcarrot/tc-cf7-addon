<?php
/**
Plugin Name: Contact Form 7 Addon
Plugin URI: https://www.techcarrot.ae/
Description: Contact Form 7 Addon - Techcarrot
Author: techcarrot FZ LLC
Author URI: https://www.techcarrot.ae/
Text Domain: tc-cf7-addon
Domain Path: /languages
Version: 1.0.0
Since: 1.0.0
Requires WordPress Version at least: 5.6
Copyright: 2021 techcarrot FZ LLC
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {	
	exit;
}

add_action( 'admin_notices', 'pre_check_before_installing_tc_cf7_addon' );
include_once(ABSPATH.'wp-admin/includes/plugin.php');
function pre_check_before_installing_tc_cf7_addon() 
{	
	/*
	* Check weather Contact Form 7 is installed or not. If Contact Form 7 is not installed or active then it will give notification to admin panel
	*/
	if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php') ) 
	{
        global $pagenow;
    	if( $pagenow == 'plugins.php' )
    	{
           echo '<div id="error" class="error notice is-dismissible"><p>';
           echo __( 'Contact Form 7 is require to use Contact Form 7 Addon' , 'wp-event-manager-zoom');
           echo '</p></div>';	
    	}
    	return true;
	}
}

/**
 * TC_CF7_Addon class.
 */
class TC_CF7_Addon {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * Main TC_CF7_Addon Instance.
	 *
	 * Ensures only one instance of TC_CF7_Addon is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see TC_CF7_Addon()
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor - get the plugin hooked in and ready
	 */
	public function __construct() 
	{
		//if wp event manager not active return from the plugin
		if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php') )
			return;

		// Define constants
		define( 'TC_CF7_ADDON_VERSION', '1.0.0' );
		define( 'TC_CF7_ADDON_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'TC_CF7_ADDON_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'TC_CF7_ADDON_BASENAME', plugin_basename( __FILE__ ) );

		if ( is_admin() ) {
			include_once TC_CF7_ADDON_PLUGIN_DIR . '/includes/admin/tc-cf7-addon-admin.php';
		}
		
		include_once( 'tc-cf7-addon-functions.php' );
		include_once( TC_CF7_ADDON_PLUGIN_DIR . '/includes/tc-cf7-addon-validation.php' );
		include_once( TC_CF7_ADDON_PLUGIN_DIR . '/includes/tc-cf7-addon-extra-features.php' );

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'activation' ) );
		register_deactivation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'deactivation' ) );
		add_action( 'admin_init', array( $this, 'updater' ) );

		// Actions
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 11 );
	}

	/**
	 * Called on plugin activation
	 */
	public function activation() {

		global $wpdb;

		flush_rewrite_rules();
	}

	/**
	 * Called on plugin deactivation
	 */
	public function deactivation() {

		global $wpdb;

		flush_rewrite_rules();
	}

	/**
	 * Handle Updates
	 */
	public function updater() {
		if ( version_compare( TC_CF7_ADDON_VERSION, get_option( 'tc_cf7_addon_version' ), '>' ) ) {

			update_option( 'tc_cf7_addon_version', TC_CF7_ADDON_VERSION );

			flush_rewrite_rules();
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {

		$domain = 'tc-cf7-addon';       

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain( $domain, WP_LANG_DIR . "/tc-cf7-addon/".$domain."-" .$locale. ".mo" );

		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue scripts and css
	 */
	public function frontend_scripts() {

	}

}

$GLOBALS['tc_cf7_addon'] =  TC_CF7_Addon::instance();