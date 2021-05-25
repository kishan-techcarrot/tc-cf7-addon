<?php
/**
 * Recursive sanitation for an array
 * @param $array
 * @return mixed
 */
function recursive_sanitize_text_field($array) 
{
	foreach ( $array as $key => &$value ) 
	{
		if ( is_array( $value ) ) 
		{
			$value = recursive_sanitize_text_field($value);
		}
		else 
		{
			$value = sanitize_text_field( $value );
		}
	}

	return $array;
}

function get_cf7_records_count($cf7_id = '') 
{
	global $wpdb;

	$query = "SELECT COUNT(*) AS `totals` FROM %1s 
			WHERE cf7_id = %s 
			GROUP BY `record_id` 
			ORDER BY `record_id`
			";

	$records = $wpdb->get_col( $wpdb->prepare( 
                        $query, 
                        $wpdb->prefix . 'tc_cf7_addon_form_data',
                        $cf7_id
                ) );

	if( isset($records) && !empty($records) )
    {
    	return count($records);
    }
    else
    {
    	return false;
    }
}

function get_cf7_fields($cf7_id = '') 
{
	global $wpdb;

	$query = "SELECT `field_name` FROM %1s 
			WHERE cf7_id = %s 
			GROUP BY `field_name` 
			ORDER BY `id`
			";

	$fields = $wpdb->get_col( $wpdb->prepare( 
                        $query, 
                        $wpdb->prefix . 'tc_cf7_addon_form_data',
                        $cf7_id
                ) );

	if( isset($fields) && !empty($fields) )
    {
    	$columns = [];
        $columns['cb'] = '<input type="checkbox" />';
    	foreach ($fields as $field) 
    	{
    		$lable = str_replace('-', ' ', $field);
    		$lable = str_replace('_', ' ', $lable);
    		$columns[$field] = ucwords($lable);
    	}

    	return $columns;
    }
    else
    {
    	return false;
    }
}

function get_cf7_record($field_name = '', $record_id = '') 
{
    global $wpdb;

    $query = "SELECT * FROM %1s 
            WHERE field_name = %s AND record_id = %s 
            ORDER BY `id`
            ";

    $record = $wpdb->get_row( $wpdb->prepare( 
                        $query, 
                        $wpdb->prefix . 'tc_cf7_addon_form_data',
                        $field_name,
                        $record_id
                ) );

    if( isset($record) && !empty($record) )
    {
        return $record;
    }
    else
    {
        return false;
    }
}

function get_cf7_email_log($id = '') 
{
    global $wpdb;

    $where = 'WHERE 1=1';
    if ( isset($id) && !empty($id) ) {

        $where .= ' AND id IN ('. $id .')';
    }

    $query = "SELECT * FROM %1s 
            $where
            ORDER BY `id`
            ";

    if ( isset($id) && !empty($id) ) 
    {
        $logs = $wpdb->get_row( $wpdb->prepare( 
                        $query, 
                        $wpdb->prefix . 'tc_cf7_addon_email_log'
                ) );
    }
    else
    {
        $logs = $wpdb->get_results( $wpdb->prepare( 
                        $query, 
                        $wpdb->prefix . 'tc_cf7_addon_email_log'
                ) );
    }

    if( isset($logs) && !empty($logs) )
    {
        return $logs;
    }
    else
    {
        return false;
    }
}