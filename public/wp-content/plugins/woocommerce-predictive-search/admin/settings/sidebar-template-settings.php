<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Predictive Search Input Box Settings

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

class WC_Predictive_Search_Sidebar_Template_Settings extends WC_Predictive_Search_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'sidebar-template-settings';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wc_predictive_search_sidebar_template_settings';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wc_predictive_search_sidebar_template_settings';
	
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
				'success_message'	=> __( 'Widget Template Settings successfully saved.', 'woops' ),
				'error_message'		=> __( 'Error: Widget Template Settings can not save.', 'woops' ),
				'reset_message'		=> __( 'Widget Template Settings successfully reseted.', 'woops' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
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
			'name'				=> 'sidebar-template-settings',
			'label'				=> __( 'Widget Template', 'woops' ),
			'callback_function'	=> 'wc_predictive_search_sidebar_template_settings_form',
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

  		// Define settings
     	$this->form_fields = apply_filters( $this->form_key . '_settings_fields', array(
     		array(
            	'name' 		=> __( 'Search Box Container', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_container_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Search Box Alignment', 'woops' ),
				'id' 		=> 'sidebar_search_box_align',
				'desc'		=> __( 'Alignment within the widget area container', 'woops' ),
				'css' 		=> 'width:80px;',
				'type' 		=> 'select',
				'default'	=> 'none',
				'options'	=> array(
						'none'			=> __( 'None', 'woops' ) ,
						'left'			=> __( 'Left', 'woops' ) ,
						'center'		=> __( 'Center', 'woops' ) ,
						'right'			=> __( 'Right', 'woops' ) ,
					),
			),
			array(
				'name' 		=> __( 'Search Box Width', 'woops' ),
				'desc'		=> '% ' . __( 'of width of widget area container', 'woops' ) ,
				'id' 		=> 'sidebar_search_box_wide',
				'type' 		=> 'slider',
				'default'	=> 100,
				'min'		=> 30,
				'max'		=> 100,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Search Box Height', 'woops' ),
				'desc'		=> 'px',
				'id' 		=> 'sidebar_search_box_height',
				'type' 		=> 'text',
				'css'		=> 'width:40px;',
				'default'	=> 35,
			),
			array(
				'name' 		=> __( 'Search Box Margin', 'woops' ),
				'id' 		=> 'sidebar_search_box_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_search_box_margin_top',
	 										'name' 		=> __( 'Top', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_left',
	 										'name' 		=> __( 'Left', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_right',
	 										'name' 		=> __( 'Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 							)
			),
			array(
				'name' 		=> __( 'Search Box Margin (Mobiles)', 'woops' ),
				'id' 		=> 'sidebar_search_box_mobile_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_top',
	 										'name' 		=> __( 'Top', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_left',
	 										'name' 		=> __( 'Left', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_right',
	 										'name' 		=> __( 'Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Search Box Border', 'woops' ),
				'id' 		=> 'sidebar_search_box_border',
				'type' 		=> 'border',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#cdcdcd', 'corner' => 'rounded' , 'top_left_corner' => 4 , 'top_right_corner' => 4 , 'bottom_left_corner' => 4 , 'bottom_right_corner' => 4 ),
			),
			array(
				'name' 		=> __( 'Search Box Border Focus', 'woops' ),
				'id' 		=> 'sidebar_search_box_border_color_focus',
				'type' 		=> 'color',
				'default'	=> '#febd69',
			),
			array(
				'name' 		=> __( 'Border Shadow', 'woops' ),
				'id' 		=> 'sidebar_search_box_shadow',
				'type' 		=> 'box_shadow',
				'default'	=> array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' )
			),

			array(
            	'name' 		=> __( 'Search in Category Dropdown', 'woops' ),
                'type' 		=> 'heading',
                'desc'		=> '<img class="rwd_image_maps" src="'.WOOPS_IMAGES_URL.'/premium-search-in-category.png" usemap="#searchInCategoryMap" style="width: auto; max-width: 100%;" border="0" />
<map name="searchInCategoryMap" id="searchInCategoryMap">
	<area shape="rect" coords="445,120,890,180" href="'.$this->pro_plugin_page_url.'" target="_blank" />
</map>',
                'id'		=> 'predictive_search_category_dropdown_box',
                'is_box'	=> true,
           	),

           	array(
            	'name' 		=> '',
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_category_dropdown_pro_box',
                'class'		=> 'pro_feature_hidden',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Alignment', 'woops' ),
				'desc'		=> __( 'If set LEFT then Predictive Search Icon on RIGHT ', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_align',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'left',
				'checked_value'		=> 'left',
				'unchecked_value'	=> 'right',
				'checked_label'		=> __( 'LEFT', 'woops' ),
				'unchecked_label' 	=> __( 'RIGHT', 'woops' ),
			),
			array(
				'name' 		=> __( 'Maximum Width', 'woops' ),
				'desc'		=> __( '% width of Search Box', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_max_wide',
				'type' 		=> 'slider',
				'default'	=> 30,
				'min'		=> 10,
				'max'		=> 50,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Category Font', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#777777' )
			),
			array(
				'name' 		=> __( 'Down Icon Size', 'woops' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_category_dropdown_icon_size',
				'type' 		=> 'slider',
				'default'	=> 12,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Down Icon Colour', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),
			array(
				'name' 		=> __( 'Background Colour', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f3f3f3'
			),
			array(
				'name' 		=> __( 'Vertical Side Border', 'woops' ),
				'id' 		=> 'sidebar_category_dropdown_side_border',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#cdcdcd' ),
			),

			array(
            	'name' 		=> __( 'Search Icon', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_button_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Search Icon Size', 'woops' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_search_icon_size',
				'type' 		=> 'slider',
				'default'	=> 16,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Search Icon Colour', 'woops' ),
				'id' 		=> 'sidebar_search_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),
			array(
				'name' 		=> __( 'Search Icon Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_search_icon_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff'
			),
			array(
				'name' 		=> __( 'Background Colour', 'woops' ),
				'id' 		=> 'sidebar_search_icon_bg_color',
				'type' 		=> 'color',
				'default'	=> '#febd69'
			),
			array(
				'name' 		=> __( 'Background Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_search_icon_bg_hover_color',
				'type' 		=> 'color',
				'default'	=> '#f3a847'
			),
			array(
				'name' 		=> __( 'Vertical Side Border', 'woops' ),
				'id' 		=> 'sidebar_search_icon_side_border',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#febd69' ),
			),

			array(
            	'name' 		=> __( 'Search Input Box', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_input_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Input Font', 'woops' ),
				'id' 		=> 'sidebar_input_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#111111' )
			),
			array(
				'name' 		=> __( 'Input Padding', 'woops' ),
				'id' 		=> 'sidebar_input_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_input_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '6' ),
	 								array(  'id' 		=> 'sidebar_input_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Input Background Colour', 'woops' ),
				'id' 		=> 'sidebar_input_bg_color',
				'type' 		=> 'bg_color',
				'default'	=> array( 'enable' => 1, 'color' => '#ffffff' )
			),
			array(
				'name' 		=> __( 'Loading Icon Size', 'woops' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_loading_icon_size',
				'type' 		=> 'slider',
				'default'	=> 16,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Loading Icon Colour', 'woops' ),
				'id' 		=> 'sidebar_loading_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),

			array(
            	'name' 		=> __( 'Results Dropdown Container', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Dropdown Wide', 'woops' ),
				'id' 		=> 'popup_wide',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'input_wide',
				'checked_value'		=> 'input_wide',
				'unchecked_value'	=> 'full_wide',
				'checked_label'		=> __( 'Input Wide', 'woops' ),
				'unchecked_label' 	=> __( 'Full Wide', 'woops' ),
			),
           	array(
            	'name' 		=> __( 'Container Border', 'woops' ),
                'id' 		=> 'sidebar_popup_border',
				'type' 		=> 'border',
                'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#c2c2c2', 'corner' => 'square' , 'top_left_corner' => 0 , 'top_right_corner' => 0 , 'bottom_left_corner' => 0 , 'bottom_right_corner' => 0 ),
           	),

           	array(
            	'name' 		=> __( 'Results Dropdown Section Titles', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_title_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Title Font', 'woops' ),
				'id' 		=> 'sidebar_popup_heading_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Title Padding', 'woops' ),
				'id' 		=> 'sidebar_popup_heading_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_heading_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '3' ),
	 								array(  'id' 		=> 'sidebar_popup_heading_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
            	'name' 		=> __( 'Container Border Bottom', 'woops' ),
                'id' 		=> 'sidebar_popup_heading_border',
				'type' 		=> 'border_styles',
                'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#c2c2c2' ),
           	),
			array(
				'name' 		=> __( 'Container Background', 'woops' ),
				'id' 		=> 'sidebar_popup_heading_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f2f2f2',
			),

			array(
            	'name' 		=> __( 'Results Dropdown Items', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_items_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Item Padding', 'woops' ),
				'id' 		=> 'sidebar_popup_item_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_item_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '6' ),
	 								array(  'id' 		=> 'sidebar_popup_item_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
            	'name' 		=> __( 'Item Border Bottom', 'woops' ),
                'id' 		=> 'sidebar_popup_item_border',
				'type' 		=> 'border_styles',
                'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#c2c2c2' ),
           	),
           	array(
            	'name' 		=> __( 'Item Border Bottom Hover Colour', 'woops' ),
                'id' 		=> 'sidebar_popup_item_border_hover_color',
				'type' 		=> 'color',
				'default'	=> '#6d84b4',
           	),
			array(
				'name' 		=> __( 'Item Background Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_item_bg_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Item Background Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_item_bg_hover_color',
				'type' 		=> 'color',
				'default'	=> '#6d84b4',
			),
			array(
				'name' 		=> __( 'Item Image Size', 'woops' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_popup_item_image_size',
				'type' 		=> 'slider',
				'default'	=> 64,
				'min'		=> 32,
				'max'		=> 96,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Product Name Font', 'woops' ),
				'id' 		=> 'sidebar_popup_product_name_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#3b5998' ),
			),
			array(
				'name' 		=> __( 'Product Name Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_product_name_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Product Price Font', 'woops' ),
				'id' 		=> 'sidebar_popup_product_price_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Product Price Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_product_price_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Product Description Font', 'woops' ),
				'id' 		=> 'sidebar_popup_product_desc_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Product Description Hover Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_product_desc_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),

			array(
            	'name' 		=> __( 'Results Dropdown Footer', 'woops' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_footer_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Footer Padding', 'woops' ),
				'id' 		=> 'sidebar_popup_footer_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_footer_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '3' ),
	 								array(  'id' 		=> 'sidebar_popup_footer_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'woops' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Footer Background Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_footer_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f2f2f2',
			),
           	array(
				'name' 		=> __( 'See More Text', 'woops' ),
				'id' 		=> 'sidebar_popup_seemore_text',
				'type' 		=> 'text',
				'default'	=> __( "See more search results for '%s' in:", 'woops' ),
			),
			array(
				'name' 		=> __( 'See More Font', 'woops' ),
				'id' 		=> 'sidebar_popup_seemore_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '10px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#999999' ),
			),
			array(
				'name' 		=> __( 'More Link Font', 'woops' ),
				'id' 		=> 'sidebar_popup_more_link_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#3b5998' ),
			),
			array(
				'name' 		=> __( 'More Icon Size', 'woops' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_popup_more_icon_size',
				'type' 		=> 'slider',
				'default'	=> 12,
				'min'		=> 8,
				'max'		=> 24,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'More Icon Colour', 'woops' ),
				'id' 		=> 'sidebar_popup_more_icon_color',
				'type' 		=> 'color',
				'default'	=> '#3b5998'
			),

        ));
	}

	public function include_script() {
		wp_enqueue_script( 'jquery-rwd-image-maps' );
	?>
<script>
(function($) {

	$(document).ready(function() {

	});

})(jQuery);
</script>
    <?php
	}
}

global $wc_predictive_search_sidebar_template_settings_panel;
$wc_predictive_search_sidebar_template_settings_panel = new WC_Predictive_Search_Sidebar_Template_Settings();

/** 
 * wc_predictive_search_sidebar_template_settings_form()
 * Define the callback function to show subtab content
 */
function wc_predictive_search_sidebar_template_settings_form() {
	global $wc_predictive_search_sidebar_template_settings_panel;
	$wc_predictive_search_sidebar_template_settings_panel->settings_form();
}

?>
