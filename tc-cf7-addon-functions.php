<?php
/**
 * Recursive sanitation for an array
 * @param $array
 * @return mixed
 */
function recursive_sanitize_text_field($array) {
	foreach ( $array as $key => &$value ) {
		if ( is_array( $value ) ) {
			$value = recursive_sanitize_text_field($value);
		}
		else {
			$value = sanitize_text_field( $value );
		}
	}

	return $array;
}