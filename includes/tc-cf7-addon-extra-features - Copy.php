<?php
/**
 * TC_CF7_Addon Extra_Features
 *
 * @class    TC_CF7_Addon_Extra_Features
 * @package  TC_CF7_Addon\Extra_Features
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TC_CF7_Addon_Extra_Features class.
 */
class TC_CF7_Addon_Extra_Features {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_filter('wpcf7_skip_mail', [$this, 'tc_cf7_addon_skip_mail'], 10, 2);

		add_action('wpcf7_before_send_mail', [$this, 'tc_cf7_addon_before_send_mail'], 20);

		add_action('wpcf7_mail_components', [$this, 'tc_cf7_addon_save_email_log'], 10, 3);

		add_action( 'wp_ajax_tc_cf7_addon_update_mail_status', array( $this, 'tc_cf7_addon_update_mail_status' ) );
		add_action( 'wp_ajax_nopriv_tc_cf7_addon_update_mail_status', array( $this, 'tc_cf7_addon_update_mail_status' ) );
	}

	public function tc_cf7_addon_skip_mail($skip_mail, $cf7)
	{
		$is_skip_mail = get_post_meta( $cf7->id, '_tc_cf7_addon_skip_mail', true );

		if($is_skip_mail)
		{
			$skip_mail = true;
		}
		
		return $skip_mail;
	}

	public function tc_cf7_addon_before_send_mail($cf7)
	{
		/*
		* Create action for 3party API integration.
		*/
		do_action( 'tc_cf7_addon_thirdparty_api_'.$cf7->id );
		

		$save_form_data = get_post_meta( $cf7->id, '_tc_cf7_addon_save_form_data', true );
		if($save_form_data)
		{
			$this->tc_cf7_addon_save_form_data($cf7);
		}
	}

	public function tc_cf7_addon_save_form_data($cf7)
	{
		global $wpdb;

		$table_name    = $wpdb->prefix.'tc_cf7_addon_form_data';
    	$upload_dir    = wp_upload_dir();
    	$tc_cf7_dirname = $upload_dir['basedir'].'/tc_cf7_uploads';
    	$tc_cf7_dirurl = $upload_dir['baseurl'].'/tc_cf7_uploads';
    	$time_now      = time();

    	$form = WPCF7_Submission::get_instance();

    	if ( $form ) 
    	{
    		$data 		= $form->get_posted_data();
	        $arrFiles   = $form->uploaded_files();
	        
	        foreach ($arrFiles as $file_key => $files) 
	        {
	        	unset($data[$file_key]);

	        	if(is_array($files))
	        	{
	        		foreach ($files as $file) 
	        		{
	        			copy($file, $tc_cf7_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($file));

	        			$data[$file_key][] = $tc_cf7_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($file);
	        		}
	        	}
	        	else
	        	{
	        		copy($file, $tc_cf7_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($files));

	        		$data[$file_key][] = $tc_cf7_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($files);
	        	}
	        }

	        $params = [];

	        foreach ($data as $key => $value) 
	        {
        		$tmpValue = $value;

                if ( ! is_array($value) )
                {
                    $bl   = array('\"',"\'",'/','\\','"',"'");
                    $wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');

                    $tmpValue = str_replace($bl, $wl, $tmpValue );
                }

                $params[$key] = $tmpValue;
	        }

	        if(!empty($params))
	        {
	        	foreach ($params as $key => $value) 
	        	{
	        		$logs = [];
	        		$logs['cf7_id'] = $cf7->id;
	        		$logs['field_name'] = $key;
	        		$logs['field_value'] = is_array($value) ? json_encode($value) : $value;

	        		$wpdb->insert($table_name, $logs);
	        	}
	        }
    	}
	}

	public function tc_cf7_addon_save_email_log($components, $cf7, $instance)
	{
		global $wpdb;

		$table_name    = $wpdb->prefix.'tc_cf7_addon_email_log';

		$logs = [];
		$logs['cf7_id'] = $cf7->id;
		$logs['email_to'] = $components['sender'];
		$logs['email_subject'] = $components['subject'];
		$logs['email_message'] = $components['body'];
		$logs['email_headers'] = $components['additional_headers'];
		$logs['email_attachments'] = is_array($components['attachments']) ? json_encode($components['attachments']) : $components['attachments'];
		$logs['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$logs['is_sent'] = 1;
		$logs['sent_date'] = current_time('Y-m-d H:i:s');

		$wpdb->insert($table_name, $logs);
	}

	public function tc_cf7_addon_update_mail_status()
	{
		echo '<pre>';
		print_r($_REQUEST);
		echo '</pre>' . __FILE__ . ' ( Line Number ' . __LINE__ . ')';
		die;
	}

}

return new TC_CF7_Addon_Extra_Features();