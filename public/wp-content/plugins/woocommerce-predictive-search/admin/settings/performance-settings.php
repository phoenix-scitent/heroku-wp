<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Predictive Search Performance Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class WC_Predictive_Search_Performance_Settings extends WC_Predictive_Search_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'performance-settings';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = '';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wc_predictive_search_performance_settings';
	
	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;
	
	/**
	 * @var array
	 */
	public $form_fields = array();
	
	/**
	 * @var array
	 */
	public $form_messages = array();
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		
		$this->init_form_fields();
		$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Performance Settings successfully saved.', 'woops' ),
				'error_message'		=> __( 'Error: Performance Settings can not save.', 'woops' ),
				'reset_message'		=> __( 'Performance Settings successfully reseted.', 'woops' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '_settings_' . 'predictive_search_synch_data' . '_start', array( $this, 'predictive_search_synch_data' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {
		
		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		global $wc_predictive_search_admin_interface;
		
		$wc_predictive_search_admin_interface->reset_settings( $this->form_fields, $this->option_name, false );
	}

	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		if ( isset( $_POST['predicitve-search-synch-wp-data'] ) ) {
			@set_time_limit(86400);
			@ini_set("memory_limit","1000M");

			global $wc_ps_synch;
			$wc_ps_synch->synch_full_database();

			echo '<div class="updated"><p>' . __( '<strong>SUCCESS</strong>! Your Predictive Search Database has been successfully updated.', 'woops' ) . '</p></div>';
		}
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		global $wc_predictive_search_admin_interface;
		
		$wc_predictive_search_admin_interface->get_settings( $this->form_fields, $this->option_name );
	}
	
	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array ( 
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {
		
		$subtab_data = array( 
			'name'				=> 'performance-settings',
			'label'				=> __( 'Performance', 'woops' ),
			'callback_function'	=> 'wc_predictive_search_performance_settings_form',
		);
		
		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {
	
		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();
		
		return $subtabs_array;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		global $wc_predictive_search_admin_interface;
		
		$output = '';
		$output .= $wc_predictive_search_admin_interface->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );
		
		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
		if ( isset( $_GET['page'] ) && 'woo-predictive-search' == $_GET['page'] && isset( $_GET['tab'] ) && $this->parent_tab == $_GET['tab'] ) {
			if ( ! isset( $_SESSION ) ) {
				@session_start();
			}
		}
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
			
			array(
            	'name' 		=> __( 'Manual Database Sync', 'woops' ),
            	'desc'		=> __( 'Predictive Search database is auto updated whenever a product or post is published or updated. Please run a Manual database sync if you upload products by csv or feel that Predictive Search results are showing old data.  Will sync the Predictive Search database with your current WooCommerce and WordPress databases', 'woops' ),
            	'id'		=> 'predictive_search_synch_data',
                'type' 		=> 'heading',
				'is_box'	=> true,
           	),

			array(
            	'name' 		=> __( 'Search Performance Settings', 'woops' ),
                'type' 		=> 'heading',
				'desc'		=> '<img class="rwd_image_maps" src="'.WOOPS_IMAGES_URL.'/premium-performance-settings.png" usemap="#performanceMap" style="width: auto; max-width: 100%;" border="0" />
<map name="performanceMap" id="performanceMap">
	<area shape="rect" coords="410,145,925,210" href="'.$this->pro_plugin_page_url.'" target="_blank" />
</map>',
				'id'		=> 'predictive_search_performance_settings',
				'is_box'	=> true,
           	),
        ));
	}

	public function predictive_search_synch_data() {
		global $wc_ps_posts_data;
	?>
		<tr valign="top" class="">
			<th class="titledesc" scope="row"><label><?php _e('Sync Search Data', 'woops');?></label></th>
			<td class="forminp">
				<input type="submit" class="button button-primary" name="predicitve-search-synch-wp-data" value="<?php _e('Sync Now', 'woops');?>" /><br />
				<p>
					<span class="a3-ps-synched-title"><?php _e('You have synced', 'woops');?>:</span>
					<span class="a3-ps-synched-products">
						<?php
						$total_products = $wc_ps_posts_data->get_total_items_synched('product');
						if ( $total_products > 0 ) {
							echo sprintf( _n( '%s Product', '%s Products', $total_products, 'woops' ), number_format( $total_products ) );
						} else {
							echo sprintf( __( '%s Product', 'woops' ), $total_products );
						}
						?>
					</span>-
					<span class="a3-ps-synched-posts">
						<?php
						$total_posts = $wc_ps_posts_data->get_total_items_synched('post');
						if ( $total_posts > 0 ) {
							echo sprintf( _n( '%s Post', '%s Posts', $total_posts, 'woops' ), number_format( $total_posts ) );
						} else {
							echo sprintf( __( '%s Post', 'woops' ), $total_posts );
						}
						?>
					</span>-
					<span class="a3-ps-synched-pages">
						<?php
						$total_pages = $wc_ps_posts_data->get_total_items_synched('page');
						if ( $total_pages > 0 ) {
							echo sprintf( _n( '%s Page', '%s Pages', $total_pages, 'woops' ), number_format( $total_pages ) );
						} else {
							echo sprintf( __( '%s Page', 'woops' ), $total_pages );
						}
						?>
					</span>
				</p>
			</td>
		</tr>
	<?php
	}

	public function include_script() {
	?>
	<style type="text/css">
		.a3-ps-synched-products {
			color: #96587d;
		}
		.a3-ps-synched-posts {
			color: #7ad03a;
		}
		.a3-ps-synched-pages {
			color: #0073aa;
		}
	</style>
    <?php
    	wp_enqueue_script( 'jquery-rwd-image-maps' );
	}
}

global $wc_predictive_search_performance_settings;
$wc_predictive_search_performance_settings = new WC_Predictive_Search_Performance_Settings();

/** 
 * wc_predictive_search_performance_settings_form()
 * Define the callback function to show subtab content
 */
function wc_predictive_search_performance_settings_form() {
	global $wc_predictive_search_performance_settings;
	$wc_predictive_search_performance_settings->settings_form();
}

?>
