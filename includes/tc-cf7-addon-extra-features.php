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

		add_action('wpcf7_before_send_mail', [$this, 'tc_cf7_addon_before_send_mail']);
	}

	public function tc_cf7_addon_skip_mail($skip_mail, $cf7)
	{
		return $skip_mail;
	}

	public function tc_cf7_addon_before_send_mail($cf7)
	{

	}

}

return new TC_CF7_Addon_Extra_Features();