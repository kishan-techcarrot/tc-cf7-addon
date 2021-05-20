<?php
/**
 * TC_CF7_Addon Admin
 *
 * @class    TC_CF7_Addon_Admin
 * @package  TC_CF7_Addon\Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TC_CF7_Addon_Admin class.
 */
class TC_CF7_Addon_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		add_action( 'wpcf7_editor_panels', [$this, 'tc_cf7_addon_cvm_panel'] );
		add_action( 'wpcf7_after_save', [$this, 'tc_cf7_addon_save_validation'] );
	}

	public function tc_cf7_addon_cvm_panel( $panels ) 
	{
		$panels['tc-custom-validation'] = array(
			'title'    => __( 'Custom Validation', 'tc-cf7-addon' ),
			'callback' => array( $this, 'tc_cf7_addon_cvm_panel_callback' ),
		);
		return $panels;
	}

	public function tc_cf7_addon_cvm_panel_callback( $post ) 
	{
		wp_nonce_field( 'tc_cf7_addon_custom_validation_security', 'tc_cf7_addon_custom_validation_nonce' );
		?>
		<h2><?php _e( 'Custom Validation', 'tc-cf7-addon' ); ?></h2>

		<fieldset>
			<?php
			$cf7_fields = array();
			$cf7_id     = $post->id;
			if( $cf7_id != null)
			{
				$ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
				$cf7_fields = $ContactForm->scan_form_tags();
			}
			else
			{
				$cf7_fields = $post->scan_form_tags();
			}

			$arr_values = get_post_meta( $cf7_id, '_tc_cf7_addon_custom_validation', true );

			$arr_values = isset( $arr_values ) ? (array) $arr_values : array();

			$arr_values = recursive_sanitize_text_field( $arr_values );
			?>

			<table class="form-table">
				<thead>
					<tr>
						<th scope="row" width="30%"><?php _e( 'Your field', 'tc-cf7-addon' ); ?></th>
						<td width="35%"><?php _e( 'Field validation message', 'tc-cf7-addon' ); ?></td>
						<td width="35%"><?php _e( '/^[A-Za-z. ]+$/', 'tc-cf7-addon' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php if(!empty($cf7_fields)) : ?>

						<?php foreach($cf7_fields as $cf7_field) : ?>

							<?php
							$validation_pattern = isset($arr_values[$cf7_field->name]['validation-pattern']) ? sanitize_text_field($arr_values[$cf7_field->name]['validation-pattern']) : '';

							$validation_message = isset($arr_values[$cf7_field->name]['validation-message']) ? sanitize_text_field($arr_values[$cf7_field->name]['validation-message']) : '';
							?>

							<?php if( in_array($cf7_field->basetype, ['submit', 'acceptance']) ) : 
								continue; ?>

							<?php elseif( $cf7_field->basetype === 'email' ) : ?>
								<tr>
									<th scope="row">
										<label for="field-<?php echo $cf7_field->name.'-validation'; ?>"><?php echo $cf7_field->name.' (Wrong Email)'; ?></label>
									</th>
									<td>
										<input type="text" id="field-<?php echo $cf7_field->name.'-validation-pattern'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-pattern]'; ?>" class="regular-text" size="70" value="<?php echo $validation_pattern; ?>">
									</td>
									<td>
										<input type="text" id="field-<?php echo $cf7_field->name.'-validation-message'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-message]'; ?>" class="regular-text" size="70" value="<?php echo $validation_message; ?>">
									</td>
								</tr>
							
							<?php else : ?>
								<tr>
									<th scope="row">
										<label for="field-<?php echo $cf7_field->name.'-validation'; ?>"><?php echo $cf7_field->name.''; ?></label>
									</th>
									<td>
										<input type="text" id="field-<?php echo $cf7_field->name.'-validation-message'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-message]'; ?>" class="regular-text" size="70" value="<?php echo $validation_message; ?>">
									</td>
									<td>
										<?php if( in_array($cf7_field->basetype, ['text', 'email', 'tel', 'number', 'range']) ) : ?>
											<input type="text" id="field-<?php echo $cf7_field->name.'-validation-pattern'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-pattern]'; ?>" class="regular-text" size="70" value="<?php echo $validation_pattern; ?>">
										<?php endif; ?>
									</td>
								</tr>

							<?php endif; ?>

						<?php endforeach; ?>

					<?php endif; ?>
				</tbody>
			</table>

		</fieldset>
		<?php
	}

	public function tc_cf7_addon_save_validation($cf7)
	{
		if( empty($_POST) )
			return;

		if( isset($_POST['page']) && $_POST['page'] !== 'wpcf7' )
			return;

		if (!wp_verify_nonce( sanitize_text_field($_POST['tc_cf7_addon_custom_validation_nonce']), 'tc_cf7_addon_custom_validation_security' ) )
			return;

		$cf7_id = $cf7->id();

		if(!empty($_POST['tc-cf7-addon-validation']))
		{
			$arr_values = recursive_sanitize_text_field($_POST['tc-cf7-addon-validation']);

			update_post_meta( $cf7_id, '_tc_cf7_addon_custom_validation', $arr_values );
		}
	}
}

new TC_CF7_Addon_Admin();