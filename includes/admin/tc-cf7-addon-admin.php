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
		include( 'tc-cf7-addon-forms-list.php' );
		include( 'tc-cf7-addon-form-records-list.php' );

		include( 'tc-cf7-addon-email-log.php' );

		add_action('admin_menu', array($this, 'admin_menu'), 12);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'wpcf7_editor_panels', [$this, 'tc_cf7_addon_cvm_panel'] );
		add_action( 'wpcf7_after_save', [$this, 'tc_cf7_addon_save_validation'] );
	}

	public function admin_menu()
    {
        add_menu_page( __('tc CF7', 'tc-cf7-addon'), __('tc CF7', 'tc-cf7-addon'), 'manage_options', 'tc-cf7-addon-db', array($this, 'tc_cf7_addon_form_records_list'), TC_CF7_ADDON_PLUGIN_URL.'/assets/images/tc-icon.png', 30);

        add_submenu_page('tc-cf7-addon-db', __('Email Log', 'tc-cf7-addon'), __('Email Log', 'tc-cf7-addon'), 'manage_options', 'tc-cf7-addon-email-log', array($this, 'tc_cf7_addon_email_log'));
    }

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @return void
     */
    public function admin_enqueue_scripts() 
    {
    	wp_register_script( 'tc-cf7-addon-admin', TC_CF7_ADDON_PLUGIN_URL . '/assets/js/tc-cf7-addon-admin.js', array( 'jquery' ), time(), true );
        wp_enqueue_script( 'tc-cf7-addon-admin' );
    }

    public function tc_cf7_addon_form_records_list()
    {
    	$cf7_id = isset($_REQUEST['cf7_id']) ? $_REQUEST['cf7_id'] : '';

    	if(!empty($cf7_id))
    	{
    		$tc_cf7_addon_form_records_list = new TC_CF7_Addon_Form_Records_List();
	        $tc_cf7_addon_form_records_list->prepare_items();
	    	?>
	    	<div class="wrap">
	            <h2><?php _e( 'Contact Form Records List', 'tc-cf7-addon' ); ?></h2>

	            <form id="tc_cf7_addon_form_records_list" method="post">
	            <?php
	                $tc_cf7_addon_form_records_list->display();
	            ?>
	        	</form>
	        </div>
	        <?php
    	}
    	else
    	{
    		$cf7_forms_list = new TC_CF7_Addon_Forms_List();
	        $cf7_forms_list->prepare_items();
	    	?>
	    	<div class="wrap">
	            <h2><?php _e( 'Contact Forms List', 'tc-cf7-addon' ); ?></h2>

	            <?php
	                $cf7_forms_list->display();
	            ?>
	        </div>
	        <?php
    	}
    }

    public function tc_cf7_addon_email_log()
    {
    	$tc_cf7_addon_email_log = new TC_CF7_Addon_Email_Log();
        $tc_cf7_addon_email_log->prepare_items();
    	?>
    	<div class="wrap">
            <h2><?php _e( 'Email Log', 'tc-cf7-addon' ); ?></h2>

            <form id="tc_cf7_addon_email_log" method="post">
            <?php
                $tc_cf7_addon_email_log->display();
            ?>
        	</form>
        </div>
        <?php
    }

	public function tc_cf7_addon_cvm_panel( $panels ) 
	{
		$panels['tc-cf7-addon-custom-validation'] = array(
			'title'    => __( 'Custom Validation', 'tc-cf7-addon' ),
			'callback' => array( $this, 'tc_cf7_addon_cvm_panel_callback' ),
		);
		$panels['tc-cf7-addon-extra-features'] = array(
			'title'    => __( 'Extra Features', 'tc-cf7-addon' ),
			'callback' => array( $this, 'tc_cf7_addon_extra_features_panel_callback' ),
		);
		return $panels;
	}

	public function tc_cf7_addon_cvm_panel_callback($post) 
	{
		wp_nonce_field( 'tc_cf7_addon_save_data_security', 'tc_cf7_addon_save_data_nonce' );
		?>
		<h2><?php _e( 'Custom Validation', 'tc-cf7-addon' ); ?></h2>

		<fieldset>
			<?php
			$cf7_fields = array();
			$cf7_id     = $post->id();
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
										<input type="text" id="field-<?php echo $cf7_field->name.'-validation-message'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-message]'; ?>" class="regular-text" size="70" value="<?php echo $validation_message; ?>">
									</td>
									<td>
										<input type="text" id="field-<?php echo $cf7_field->name.'-validation-pattern'; ?>" name="tc-cf7-addon-validation[<?php echo $cf7_field->name.'][validation-pattern]'; ?>" class="regular-text" size="70" value="<?php echo $validation_pattern; ?>">
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

	public function tc_cf7_addon_extra_features_panel_callback($post)
	{
		wp_nonce_field( 'tc_cf7_addon_save_data_security', 'tc_cf7_addon_save_data_nonce' );

		$cf7_id     = $post->id();
		$skip_mail = get_post_meta( $cf7_id, '_tc_cf7_addon_skip_mail', true );
		$save_email_log = get_post_meta( $cf7_id, '_tc_cf7_addon_save_email_log', true );
		$save_form_data = get_post_meta( $cf7_id, '_tc_cf7_addon_save_form_data', true );
		?>
		<h2><?php _e( 'Extra Features', 'tc-cf7-addon' ); ?></h2>

		<fieldset>
			<table class="form-table">
				<tbody>
					<tr>
						<th width="30%"><?php _e( 'Skip Send Email', 'tc-cf7-addon' ); ?></th>
						<td><input type="checkbox" name="tc-cf7-addon-skip-mail" value="1" <?php echo checked($skip_mail, 1); ?> /> </br>
							<small><?php _e('If you are not send mail then checked.', 'tc-cf7-addon' ); ?></small></td>
					</tr>
					<tr>
						<th width="30%"><?php _e( 'Save Email Log', 'tc-cf7-addon' ); ?></th>
						<td><input type="checkbox" name="tc-cf7-addon-save-email-log" value="1" <?php echo checked($save_email_log, 1); ?> /></br>
							<small><?php _e('If you are save email log in database then checked.', 'tc-cf7-addon' ); ?></small></td>
						</td>
					</tr>
					<tr>
						<th width="30%"><?php _e( 'Save in Database', 'tc-cf7-addon' ); ?></th>
						<td><input type="checkbox" name="tc-cf7-addon-save-form_data" value="1" <?php echo checked($save_form_data, 1); ?> /></br>
							<small><?php _e('If you are save records in database then checked.', 'tc-cf7-addon' ); ?></small></td>
						</td>
					</tr>
					<tr>
						<th width="30%"><?php _e( 'Integration with 3party API', 'tc-cf7-addon' ); ?></th>
						<td>
							<code>
								add_action('tc_cf7_addon_thirdparty_api_{$cf7_id}', 'tc_cf7_addon_thirdparty_api_callback_{$cf7_id}', 20, 2);
								</br>
								</br>
								function callback_function_name($cf7, $form_data) </br>
								{
									</br>&nbsp;&nbsp;&nbsp; //your code </br>
								}
							</code>
						</td>
					</tr>
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

		if (!wp_verify_nonce( sanitize_text_field($_POST['tc_cf7_addon_save_data_nonce']), 'tc_cf7_addon_save_data_security' ) )
			return;

		$cf7_id = $cf7->id();

		if(!empty($_POST['tc-cf7-addon-validation']))
		{
			$arr_values = recursive_sanitize_text_field($_POST['tc-cf7-addon-validation']);

			update_post_meta( $cf7_id, '_tc_cf7_addon_custom_validation', $arr_values );
		}

		$skip_mail = isset($_POST['tc-cf7-addon-skip-mail']) ? $_POST['tc-cf7-addon-skip-mail'] : '';
		update_post_meta( $cf7_id, '_tc_cf7_addon_skip_mail', $skip_mail );

		$save_email_log = isset($_POST['tc-cf7-addon-save-email-log']) ? $_POST['tc-cf7-addon-save-email-log'] : '';
		update_post_meta( $cf7_id, '_tc_cf7_addon_save_email_log', $save_email_log );

		$save_form_data = isset($_POST['tc-cf7-addon-save-form_data']) ? $_POST['tc-cf7-addon-save-form_data'] : '';
		update_post_meta( $cf7_id, '_tc_cf7_addon_save_form_data', $save_form_data );
	}

}

new TC_CF7_Addon_Admin();