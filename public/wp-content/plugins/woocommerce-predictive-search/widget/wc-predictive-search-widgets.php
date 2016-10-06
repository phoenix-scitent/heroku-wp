<?php
/**
 * WooCommerce Predictive Search Widget
 *
 * Table Of Contents
 *
 * get_items_search()
 * __construct()
 * widget()
 * woops_results_search_form()
 * update()
 * form()
 */
class WC_Predictive_Search_Widgets extends WP_Widget 
{
	
	public static function get_items_search() {
		$items_search = array(
				'product'				=> array( 'number' => 6, 'name' => wc_ps_ict_t__( 'Product Name', __('Product Name', 'woops') ) ),
				'post'					=> array( 'number' => 0, 'name' => wc_ps_ict_t__( 'Posts', __('Posts', 'woops') ) ),
				'page'					=> array( 'number' => 0, 'name' => wc_ps_ict_t__( 'Pages', __('Pages', 'woops') ) )
			);
			
		return $items_search;
	}

	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_products_predictive_search',
			'description' => __( "User sees search results as they type in a dropdown - links through to 'All Search Results Page' that features endless scroll.", 'woops'),
			'customize_selective_refresh' => true,
		);
		parent::__construct('products_predictive_search', __('WooCommerce Predictive Search', 'woops'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$number_items = $instance['number_items'];
		if (!is_array($number_items) || count($number_items) < 1 ) $number_items = array();
		if(!isset($instance['text_lenght']) || $instance['text_lenght'] < 0) $text_lenght = 100; 
		else $text_lenght = $instance['text_lenght'];
		$show_catdropdown = 0;
		$show_price = empty($instance['show_price']) ? 0 : $instance['show_price'];
		$widget_template = 'sidebar';

		$show_image = empty($instance['show_image']) ? 0 : $instance['show_image'];
		$show_desc = empty($instance['show_desc']) ? 0 : $instance['show_desc'];

		if ( class_exists('SitePress') ) {
			$current_lang = ICL_LANGUAGE_CODE;
			$search_box_texts = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : array() );
			if ( !is_array($search_box_texts) ) $search_box_texts = get_option('woocommerce_search_box_text', array() );
			if ( is_array($search_box_texts) && isset($search_box_texts[$current_lang]) ) $search_box_text = esc_attr( stripslashes( trim( $search_box_texts[$current_lang] ) ) );
			else $search_box_text = '';
		} else {
			$search_box_text = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : '' );
			if ( is_array($search_box_text) || trim($search_box_text) == '' ) $search_box_text = get_option('woocommerce_search_box_text', '' );
			if ( is_array($search_box_text) ) $search_box_text = '';
		}

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $this->woops_results_search_form($widget_id, $number_items, $text_lenght, $search_box_text, $show_catdropdown, $show_image, $show_price, $show_desc, $widget_template );
		echo $after_widget;
	}
	
	public static function woops_results_search_form($widget_id, $number_items=array(), $text_lenght=100, $search_box_text = '', $show_catdropdown = 1, $show_image = 1, $show_price = 1, $show_desc = 1, $widget_template = 'sidebar' ) {
		
		global $woocommerce_search_page_id;
		
		$ps_id = str_replace('products_predictive_search-','',$widget_id);

		$row = 0;
		if (!is_array($number_items) || count($number_items) < 1 || array_sum($number_items) < 1) {
			$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
			$number_items_default = array();
			foreach ($items_search_default as $key => $data) {
				if ($data['number'] > 0) {
					$number_items_default[$key] = $data['number'];
				}
			}
			$number_items = $number_items_default;
		}

		$common = '';
		$search_list = array();
		foreach ($number_items as $key => $number) {
			if ($number > 0) {
				$row += $number;
				$row++;
				$search_list[] = $key;
			}
		}
		$search_in = json_encode($number_items);

		$ps_args = array(
			'search_box_text'  => $search_box_text,
			'row'              => $row,
			'text_lenght'      => $text_lenght,
			'show_catdropdown' => $show_catdropdown,
			'widget_template'  => $widget_template,
			'show_image'       => $show_image,
			'show_price'       => $show_price,
			'show_desc'        => $show_desc,
			'search_in'        => $search_in,
			'search_list'      => $search_list,
		);

		$search_form = wc_ps_search_form( $ps_id, $widget_template, $ps_args, false );

		return $search_form . '<div style="clear:both;"></div>';
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_items'] = $new_instance['number_items'];
		$instance['text_lenght'] = strip_tags($new_instance['text_lenght']);
		$instance['show_price'] = $new_instance['show_price'];
		$instance['search_box_text'] = $new_instance['search_box_text'];
		$instance['show_image'] = $new_instance['show_image'];
		$instance['show_desc'] = $new_instance['show_desc'];
		return $instance;
	}

	function form( $instance ) {
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );

		$global_search_box_text = get_option('woocommerce_search_box_text');
		$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
		$number_items_default = array();
		foreach ($items_search_default as $key => $data) {
			$number_items_default[$key] = $data['number'];
		}
		unset($key);
		unset($data);
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number_items' => $number_items_default, 'text_lenght' => 100, 'show_price' => 1, 'show_catdropdown' => 1, 'show_image' => 1, 'show_desc' => 1, 'widget_template' => 'sidebar', 'search_box_text' => $global_search_box_text ) );
		$title = strip_tags($instance['title']);
		$number_items = $instance['number_items'];
		if (!is_array($number_items) || count($number_items) < count($items_search_default) ) $number_items = $number_items_default;
		$text_lenght = strip_tags($instance['text_lenght']);
		$show_price = $instance['show_price'];
		$show_catdropdown = $instance['show_catdropdown'];
		$search_box_text = $instance['search_box_text'];
		$widget_template = $instance['widget_template'];

		$show_image = $instance['show_image'];
		$show_desc = $instance['show_desc'];
?>
		<style type="text/css">
		.item_heading{ width:130px; display:inline-block;}
		ul.predictive_search_item li{padding-left:15px; background:url(<?php echo WOOPS_IMAGES_URL; ?>/sortable.gif) no-repeat left center; cursor:pointer;}
		ul.predictive_search_item li.ui-sortable-placeholder{border:1px dotted #111; visibility:visible !important; background:none;}
		ul.predictive_search_item li.ui-sortable-helper{background-color:#DDD;}
		</style>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
		if ( class_exists('SitePress') ) {
			if ( !is_array($search_box_text) ) $search_box_text = array();
			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
				foreach ( $active_languages as $language ) {
		?>
        	<p><label for="<?php echo $this->get_field_id('search_box_text'); ?>_<?php echo $language['code']; ?>"><?php _e('Search box text message', 'woops'); ?> (<?php echo $language['display_name']; ?>)</label> <input class="widefat" id="<?php echo $this->get_field_id('search_box_text'); ?>_<?php echo $language['code']; ?>" name="<?php echo $this->get_field_name('search_box_text'); ?>[<?php echo $language['code']; ?>]" type="text" value="<?php if ( isset( $search_box_text[$language['code'] ] ) ) esc_attr_e( $search_box_text[$language['code']] ); ?>" /></p>
        <?php
				}
			}
		} else {
			if ( is_array($search_box_text) ) $search_box_text = '';
		?>
            <p><label for="<?php echo $this->get_field_id('search_box_text'); ?>"><?php _e('Search box text message:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('search_box_text'); ?>" name="<?php echo $this->get_field_name('search_box_text'); ?>" type="text" value="<?php echo esc_attr($search_box_text); ?>" /></p>
		<?php } ?>
            <p><?php _e("Activate search 'types' for this widget by entering the number of results to show in the widget dropdown. &lt;empty&gt; = not activated. Sort order by drag and drop", 'woops'); ?></p>
            <ul class="ui-sortable predictive_search_item">
            <?php foreach ($number_items as $key => $value) { ?>
            	<?php if ( isset( $items_search_default[$key] ) ) { ?>
            	<li><span class="item_heading"><label for="search_<?php echo $key; ?>"><?php echo $items_search_default[$key]['name']; ?></label></span> <input id="search_<?php echo $key; ?>" name="<?php echo $this->get_field_name('number_items'); ?>[<?php echo $key; ?>]" type="text" value="<?php echo esc_attr($value); ?>" style="width:50px;" /></li>
            	<?php } ?>
            <?php } ?>
            </ul>
            <p>
            	<label for="<?php echo $this->get_field_id('widget_template'); ?>"><?php _e('Select Template:', 'woops'); ?></label>
            	<select id="<?php echo $this->get_field_id('widget_template'); ?>" name="widget_template" disabled="disabled">
					<option value="sidebar" selected="selected" ><?php _e('Widget', 'woops'); ?></option>
					<option value="header"><?php _e('Header', 'woops'); ?></option>
            	</select> <span style="color: #f00; font-size: 11px;">* <?php _e('Premium Feature!', 'woops'); ?></span>
            </p>
            <p>
            	<label><input type="checkbox" name="show_catdropdown" value="1" disabled="disabled" /> <?php _e('Search in Product Category', 'woops'); ?></label>
            	<span style="color: #f00; font-size: 11px;">* <?php _e('Premium Feature!', 'woops'); ?></span>
            </p>
            <p>
            	<label><input type="checkbox" name="<?php echo $this->get_field_name('show_image'); ?>" value="1" <?php checked( $show_image, 1 ); ?>  /> <?php _e('Show Results Images', 'woops'); ?></label>
            </p>
            <p>
            	<label><input type="checkbox" name="<?php echo $this->get_field_name('show_price'); ?>" value="1" <?php checked( $show_price, 1 ); ?>  /> <?php _e('Show Results Prices', 'woops'); ?></label>
            </p>
            <p>
            	<label><input class="wc_ps_show_desc" type="checkbox" name="<?php echo $this->get_field_name('show_desc'); ?>" value="1" <?php checked( $show_desc, 1 ); ?>  /> <?php _e('Show Results Description', 'woops'); ?></label>
            </p>
            <p class="wc_ps_show_desc_container" style="<?php echo ( 0 == $show_desc ) ? 'display: none' : ''; ?>">
            	<label for="<?php echo $this->get_field_id('text_lenght'); ?>"><?php _e('Character Count:', 'woops'); ?></label> <input style="width:50px;" id="<?php echo $this->get_field_id('text_lenght'); ?>" name="<?php echo $this->get_field_name('text_lenght'); ?>" type="text" value="<?php echo esc_attr($text_lenght); ?>" />
            </p>
		<script>
		jQuery(document).ready(function() {
        	jQuery(".predictive_search_item").sortable();
        	jQuery(document).on( 'change', ".wc_ps_show_desc", function(){
        		if ( jQuery(this).is(':checked') ) {
        			jQuery(this).parent('label').parent('p').siblings('.wc_ps_show_desc_container').show();
        		} else {
        			jQuery(this).parent('label').parent('p').siblings('.wc_ps_show_desc_container').hide();
        		}
        	});
		});
        </script>
<?php
	}
}
?>
