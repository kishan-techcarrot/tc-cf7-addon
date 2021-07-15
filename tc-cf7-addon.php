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
	public static function instance() 
	{
		if ( is_null( self::$_instance ) ) 
		{
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
		
		include_once( TC_CF7_ADDON_PLUGIN_DIR . '/tc-cf7-addon-functions.php' );
		include_once( TC_CF7_ADDON_PLUGIN_DIR . '/includes/tc-cf7-addon-validation.php' );
		include_once( TC_CF7_ADDON_PLUGIN_DIR . '/includes/tc-cf7-addon-extra-features.php' );

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'activation' ) );
		register_deactivation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'deactivation' ) );
		add_action( 'admin_init', array( $this, 'updater' ) );

		// Actions
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 12 );
	}

	/**
	 * Called on plugin activation
	 */
	public function activation() 
	{
		global $wpdb;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$tc_cf7_addon_form_data = "CREATE TABLE ". $wpdb->prefix . "tc_cf7_addon_form_data (
				id bigint(20) unsigned NOT NULL auto_increment,
				cf7_id bigint(20) unsigned NOT NULL default '0',
				record_id varchar(255) default NULL,
				field_name varchar(255) default NULL,
				field_value longtext,
				PRIMARY KEY (id)
			) $collate;";

		$tc_cf7_addon_email_log = "CREATE TABLE ". $wpdb->prefix . "tc_cf7_addon_email_log (
				id bigint(20) unsigned NOT NULL auto_increment,
				cf7_id bigint(20) unsigned NOT NULL default '0',
				email_to varchar(255) default NULL,
				email_subject varchar(255) default NULL,
				email_message longtext,
				email_headers longtext,
				email_attachments varchar(1000) default NULL,
				ip_address varchar(20) default NULL,
				is_sent tinyint(1) DEFAULT NULL,
				error_message longtext,
				sent_date datetime NOT NULL,
				PRIMARY KEY (id)
			) $collate;";

		dbDelta( $tc_cf7_addon_form_data );
		dbDelta( $tc_cf7_addon_email_log );

		$upload_dir    = wp_upload_dir();
	    $cfdb7_dirname = $upload_dir['basedir'].'/tc_cf7_uploads';
	    if ( ! file_exists( $cfdb7_dirname ) ) 
	    {
	        wp_mkdir_p( $cfdb7_dirname );
	        $fp = fopen( $cfdb7_dirname.'/index.php', 'w');
	        fwrite($fp, "<?php \n\t // Silence is golden.");
	        fclose( $fp );
	    }

		update_option( 'tc_cf7_addon_version', TC_CF7_ADDON_VERSION );

		flush_rewrite_rules();
	}

	/**
	 * Called on plugin deactivation
	 */
	public function deactivation() 
	{
		global $wpdb;
		
		flush_rewrite_rules();
	}

	/**
	 * Handle Updates
	 */
	public function updater() 
	{
		if ( version_compare( TC_CF7_ADDON_VERSION, get_option( 'tc_cf7_addon_version' ), '>' ) ) {

			update_option( 'tc_cf7_addon_version', TC_CF7_ADDON_VERSION );

			flush_rewrite_rules();
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() 
	{
		$domain = 'tc-cf7-addon';       

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain( $domain, WP_LANG_DIR . "/tc-cf7-addon/".$domain."-" .$locale. ".mo" );

		load_plugin_textdomain($domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue scripts and css
	 */
	public function frontend_scripts() 
	{
		wp_register_script( 'tc-cf7-addon-front', TC_CF7_ADDON_PLUGIN_URL . '/assets/js/tc-cf7-addon-front.js', array( 'jquery' ), time(), true );

		wp_localize_script( 'tc-cf7-addon-front', 'tc_cf7_addon', array(
			'ajax_url' 	 => admin_url( 'admin-ajax.php' ),
			'tc_cf7_addon_security'  => wp_create_nonce( '_nonce_tc_cf7_addon_security' ),
		) );

		wp_enqueue_script( 'tc-cf7-addon-front' );
	}

}

$GLOBALS['tc_cf7_addon'] =  TC_CF7_Addon::instance();