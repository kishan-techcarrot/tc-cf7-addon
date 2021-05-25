<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * TC_CF7_Addon Email_Log
 *
 * @class    TC_CF7_Addon_Email_Log
 * @package  TC_CF7_Addon\Email_Log
 * @version  1.0.0
 */

/**
 * TC_CF7_Addon_Email_Log class.
 */
class TC_CF7_Addon_Email_Log extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() 
	{
		parent::__construct(array(
            'singular' => 'cf7_email_log', //Singular label
            'plural'   => 'cf7_email_logs', //plural label, also this well be one of the table css class
            'ajax'     => false //We won't support Ajax for this table
        ));
	}

	/**
     * column_default function.
     * 
     * @access public
     * @param mixed $post
     * @param mixed $column_name
     */
    public function column_default( $item, $column_name ) {
        global $wpdb;

        $view_url = admin_url("admin-ajax.php?height=600&width=1000&action=email_log_thickbox_model_view&log_id=" . $item->id);

        switch( $column_name ) {
            case 'is_sent' :
                return $item->is_sent ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-no"></span>';

            case 'actions' :
                return '<a class="thickbox" href="'.$view_url.'" title="Email Content"><span class="dashicons dashicons-visibility"></span></a>';

            default:
                return $item->$column_name;
        }
    }

    /**
     * column_cb function.
     * 
     * @access public
     * @param mixed $item
     */
    public function column_cb( $item ){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'id',
            $item->id
        );
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        return $columns = array(
            'cb'            => '<input type="checkbox" />',
            'sent_date'     => 'Sent Date',
            'is_sent'       => 'Sent Status',
            'email_to'      => 'To',
            'email_subject' => 'Subject',
            'error_message' => 'Error',
            'actions'       => 'Actions',
        );
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /** 
     * Process bulk actions
     */
    public function process_bulk_action() {
        global $wpdb;
        
        if ( ! isset( $_POST['id'] ) ) {
            return;
        }
        
        $items = array_map( 'sanitize_text_field', $_POST['id'] );

        if ( $items ) {
            switch ( $this->current_action() ) {
                case 'delete' :
                    $ids = implode( "','", $items );
                    $wpdb->query( "DELETE FROM {$wpdb->prefix}tc_cf7_addon_email_log WHERE id IN('$ids')" );
                    echo '<div class="updated"><p>' . sprintf( __( '%d email log deleted', 'tc-cf7-addon' ), sizeof( $items ) ) . '</p></div>';
                break;
            }
        }
    }

    /**
     * prepare_items function.
     * 
     * @access public
     */
    public function prepare_items() 
    {
        global $wpdb;
        
        $current_page   = $this->get_pagenum();
        $per_page       = 20;
        $orderby        = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id';
        $order          = ! empty( $_REQUEST['order'] ) &&  ( $_REQUEST['order'] === 'asc' ) ? 'ASC' : 'DESC';

        /**
         * Init column headers
         */
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        
        /**
         * Process bulk actions
         */
        $this->process_bulk_action();

        $where = array( 'WHERE 1=1' );

        $where = implode( ' ', $where );
        
        /**
         * Get items
         */
        $max = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}tc_cf7_addon_email_log $where ORDER BY {$orderby} {$order};" );
        
        $this->items = $wpdb->get_results( $wpdb->prepare( "
            SELECT * FROM {$wpdb->prefix}tc_cf7_addon_email_log
            $where 
            ORDER BY `{$orderby}` {$order} LIMIT %d, %d
        ", ( $current_page - 1 ) * $per_page, $per_page ) );

        /**
         * Pagination
         */
        $this->set_pagination_args( array(
            'total_items' => $max, 
            'per_page'    => $per_page,
            'total_pages' => ceil( $max / $per_page )
        ) );

        
        /*echo '<pre>';
        print_r($wpdb->last_query);
        print_r($wpdb->last_result);
        echo '</pre>' . __FILE__ . ' ( Line Number ' . __LINE__ . ')';*/
        
    }	

}

add_action('init', 'email_log_thickbox_model_init');
function email_log_thickbox_model_init()
{
    add_thickbox();
}

add_action('wp_ajax_email_log_thickbox_model_view', 'email_log_thickbox_model_view');
add_action('wp_ajax_nopriv_email_log_thickbox_model_view', 'email_log_thickbox_model_view');
function email_log_thickbox_model_view()
{
    global $wpdb;

    $log_id  = isset($_REQUEST['log_id']) ? $_REQUEST['log_id'] : '';

    ob_start();

    $log = get_cf7_email_log($log_id);

    if(!empty($log)) : ?>

        <div class="wrap">

            <table style="width: 100%;">
                <tr style="background: #eee;">
                    <td style="padding: 5px;"><?php _e( 'Sent at', 'tc-cf7-addon' ); ?>:</td>
                    <td style="padding: 5px;"><?php echo esc_html( $log->sent_date ); ?></td>
                </tr>
                <tr style="background: #eee;">
                    <td style="padding: 5px;"><?php _e( 'To', 'tc-cf7-addon' ); ?>:</td>
                    <td style="padding: 5px;"><?php echo esc_html( $log->email_to); ?></td>
                </tr>
                <tr style="background: #eee;">
                    <td style="padding: 5px;"><?php _e( 'Subject', 'tc-cf7-addon' ); ?>:</td>
                    <td style="padding: 5px;"><?php echo esc_html( $log->email_subject ); ?></td>
                </tr>
            </table>

            <h2 class="nav-tab-wrapper">
                <a href="#tabs_text" class="nav-tab nav-tab-active"><?php _e('Raw Email Content', 'tc-cf7-addon'); ?></a>
                <a href="#tabs_preview" class="nav-tab"><?php _e('Preview Content as HTML', 'tc-cf7-addon'); ?></a>           
            </h2>
            
            <div class="white-background">
                <div id="tabs_text" class="settings-panel">
                    <pre class="tabs_text-pre"><?php echo esc_textarea( $log->email_message ); ?></pre>
                </div>

                <div id="tabs_preview" class="settings-panel" style="display: none;">
                   <?php echo wp_kses( $log->email_message, el_kses_allowed_html( 'post' ) ); ?>
                </div>
            </div>
        </div>
    <?php endif;

    echo ob_get_clean();

    die;
}

function el_kses_allowed_html( $context = 'post' ) 
{
    $allowed_tags = wp_kses_allowed_html( $context );

    $allowed_tags['link'] = array(
        'rel'   => true,
        'href'  => true,
        'type'  => true,
        'media' => true,
    );

    return $allowed_tags;
}