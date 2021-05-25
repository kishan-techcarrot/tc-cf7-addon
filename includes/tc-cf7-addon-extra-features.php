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

	private $cf7_id = '';
	private $last_inserted_id = '';
	private $cf7_attachments = [];

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_filter('wpcf7_skip_mail', [$this, 'tc_cf7_addon_skip_mail'], 10, 2);

		add_action('wpcf7_before_send_mail', [$this, 'tc_cf7_addon_before_send_mail'], 20);

		//add_filter('pre_wp_mail', [$this, 'tc_cf7_addon_save_email_log'], 10, 2);
		add_filter('wp_mail', [$this, 'tc_cf7_addon_save_email_log'], 10);

		add_action( 'wp_mail_failed', array( $this, 'tc_cf7_addon_update_mail_status' ) );
	}

	public function tc_cf7_addon_skip_mail($skip_mail, $cf7)
	{
		$cf7_id = $cf7->id();

		$is_skip_mail = get_post_meta( $cf7_id, '_tc_cf7_addon_skip_mail', true );

		if($is_skip_mail)
		{
			$skip_mail = true;
		}
		
		return $skip_mail;
	}

	public function tc_cf7_addon_before_send_mail($cf7)
	{
		$cf7_id = $cf7->id();

		/*
		* Create action for 3party API integration.
		*/
		do_action( 'tc_cf7_addon_thirdparty_api_'.$cf7_id, $cf7, $_POST);


		$save_form_data = get_post_meta( $cf7_id, '_tc_cf7_addon_save_form_data', true );
		if($save_form_data)
		{
			$this->tc_cf7_addon_save_form_data($cf7);
		}
	}

	public function tc_cf7_addon_save_form_data($cf7)
	{
		global $wpdb;

		$cf7_id 		= $cf7->id();
		$table_name    	= $wpdb->prefix.'tc_cf7_addon_form_data';
    	$upload_dir    	= wp_upload_dir();
    	$tc_cf7_dirname = $upload_dir['basedir'].'/tc_cf7_uploads';
    	$tc_cf7_dirurl 	= $upload_dir['baseurl'].'/tc_cf7_uploads';
    	$time_now      	= time();

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
	        			$this->cf7_attachments[$file_key][] = $tc_cf7_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($file);
	        		}
	        	}
	        	else
	        	{
	        		copy($file, $tc_cf7_dirname.'/'.$time_now.'-'.$file_key.'-'.basename($files));

	        		$data[$file_key][] = $tc_cf7_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($files);
	        		$this->cf7_attachments[$file_key][] = $tc_cf7_dirurl.'/'.$time_now.'-'.$file_key.'-'.basename($files);
	        	}
	        }

	        $params = [];

	        foreach ($data as $key => $value) 
	        {
        		if( is_array($value) && !empty($value) )
                {
                	$value = implode(',', $value);
                }

                $params[$key] = $value;
	        }

	        if(!empty($params))
	        {
	        	$record_id = uniqid();

	        	foreach ($params as $key => $value) 
	        	{
	        		$logs = [];
	        		$logs['cf7_id'] = $cf7_id;
	        		$logs['record_id'] = $record_id;
	        		$logs['field_name'] = $key;
	        		$logs['field_value'] = is_array($value) ? json_encode($value) : $value;

	        		$wpdb->insert($table_name, $logs);
	        	}

	        	$logs = [];
        		$logs['cf7_id'] = $cf7_id;
        		$logs['record_id'] = $record_id;
        		$logs['field_name'] = 'created_date';
        		$logs['field_value'] = current_time('Y-m-d H:i:s');

        		$wpdb->insert($table_name, $logs);
	        }
    	}
	}

	//public function tc_cf7_addon_save_email_log($return, $atts)
	public function tc_cf7_addon_save_email_log($atts)
	{
		global $wpdb;

		$submission = WPCF7_Submission::get_instance();
		$contact_form = $submission->get_contact_form();
		$cf7_id = $contact_form->id();
		$this->cf7_id = $cf7_id;

		$table_name    = $wpdb->prefix.'tc_cf7_addon_email_log';

		$save_email_log = get_post_meta( $cf7_id, '_tc_cf7_addon_save_email_log', true );
		if($save_email_log)
		{
			$logs = [];
			$logs['cf7_id'] = $cf7_id;
			$logs['email_to'] = $atts['to'];
			$logs['email_subject'] = $atts['subject'];
			$logs['email_message'] = $atts['message'];
			$logs['email_headers'] = $atts['headers'];
			$logs['email_attachments'] = is_array($this->cf7_attachments) && !empty($this->cf7_attachments) ? json_encode($this->cf7_attachments) : '';
			$logs['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$logs['is_sent'] = 1;
			$logs['sent_date'] = current_time('Y-m-d H:i:s');

			$wpdb->insert($table_name, $logs);

			$this->last_inserted_id = $wpdb->insert_id;
		}

		return $atts;
	}

	public function tc_cf7_addon_update_mail_status($wp_errors)
	{
		global $wpdb;

		$save_email_log = get_post_meta( $this->cf7_id, '_tc_cf7_addon_save_email_log', true );
		if( $save_email_log && !empty($this->last_inserted_id) && !empty($this->cf7_id) )
		{
			$errors = $wp_errors->get_error_messages();

			$table_name    = $wpdb->prefix.'tc_cf7_addon_email_log';

			$logs = [];
			$logs['is_sent'] = 0;
			$logs['error_message'] = is_array($errors) ? implode(', ', $errors) : $errors;

			$wpdb->update($table_name, $logs, ['id' => $this->last_inserted_id, 'cf7_id' => $this->cf7_id]);
		}
	}

}

return new TC_CF7_Addon_Extra_Features();