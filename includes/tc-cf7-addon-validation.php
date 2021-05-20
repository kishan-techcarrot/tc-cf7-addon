<?php
/**
 * TC_CF7_Addon Validation
 *
 * @class    TC_CF7_Addon_Validation
 * @package  TC_CF7_Addon\Validation
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TC_CF7_Addon_Validation class.
 */
class TC_CF7_Addon_Validation extends WPCF7_Validation{

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_filter( 'wpcf7_validate_text*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_email*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_url*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_tel*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_number*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_range*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_date*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_textarea*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_select*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_checkbox*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_radio*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_acceptance*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_quiz*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );
		add_filter( 'wpcf7_validate_file*', [$this, 'tc_cf7_addon_required_message'], 9, 2 );

		add_filter( 'wpcf7_validate', [$this, 'tc_cf7_addon_validate_message'], 20, 2 );
	}

	public function tc_cf7_addon_required_message( $result, $tag ) 
	{
		$cf7_id = isset( $_POST['_wpcf7'] ) ? $_POST['_wpcf7'] : '';
		$value = isset( $_POST[$tag->name] )
		? trim( wp_unslash( strtr( (string) $_POST[$tag->name], "\n", " " ) ) )
		: '';

		if( $value === '' )
		{
			$arr_values = get_post_meta( $cf7_id, '_tc_cf7_addon_custom_validation', true );
			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
			$arr_values = recursive_sanitize_text_field( $arr_values );

			$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : 'The '. $tag->name .' field is required.';

			$result->invalidate( $tag->name, $message );
		}

		return $result;
	}

	public function tc_cf7_addon_validate_message( $result, $tags ) 
	{
		$cf7_id = isset( $_POST['_wpcf7'] ) ? $_POST['_wpcf7'] : '';
		$arr_values = get_post_meta( $cf7_id, '_tc_cf7_addon_custom_validation', true );
		$arr_values = isset( $arr_values ) ? (array) $arr_values : array();
		$arr_values = recursive_sanitize_text_field( $arr_values );

		foreach ($tags as $tag) 
		{
			$value = isset( $_POST[$tag->name] )
			? trim( wp_unslash( strtr( (string) $_POST[$tag->name], "\n", " " ) ) )
			: '';

			$message = isset($arr_values[$tag->name]['validation-message']) ? $arr_values[$tag->name]['validation-message'] : 'The '. $tag->name .' field is required.';
			$pattern = isset($arr_values[$tag->name]['validation-pattern']) ? $arr_values[$tag->name]['validation-pattern'] : '';

			echo '<pre>';
			print_r($pattern);
			echo '</pre>' . __FILE__ . ' ( Line Number ' . __LINE__ . ')';
			die;

			if( !empty($pattern) && !preg_match($pattern, $value) )
			{
				$result->invalidate( $tag, $message );
			}
		}

		return $result;
	}

}

return new TC_CF7_Addon_Validation();