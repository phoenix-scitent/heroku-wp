<p>
    <label class="br_admin_center"><?php _e('Widget Type', BeRocket_AJAX_domain) ?></label>
    <select id="<?php echo @ $this->get_field_id( 'widget_type' ); ?>" name="<?php echo @ $this->get_field_name( 'widget_type' ); ?>" class="berocket_aapf_widget_admin_widget_type_select br_select_menu_left">
        <option <?php if ( $instance['widget_type'] == 'filter' or ! $instance['widget_type'] ) echo 'selected'; ?> value="filter"><?php _e('Filter', BeRocket_AJAX_domain) ?></option>
        <option <?php if ( $instance['widget_type'] == 'update_button' ) echo 'selected'; ?> value="update_button"><?php _e('Update Products button', BeRocket_AJAX_domain) ?></option>
        <option <?php if ( $instance['widget_type'] == 'selected_area' ) echo 'selected'; ?> value="selected_area"><?php _e('Selected Filters area', BeRocket_AJAX_domain) ?></option>
    </select>
</p>

<hr />

<p>
    <label class="br_admin_center" for="<?php echo @ $this->get_field_id( 'title' ); ?>"><?php _e('Title', BeRocket_AJAX_domain) ?> </label>
    <input class="br_admin_full_size" id="<?php echo @ $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'title' ); ?>" value="<?php echo @ $instance['title']; ?>"/>
</p>

<div class="berocket_aapf_admin_filter_widget_content" <?php if ( @ $instance['widget_type'] == 'update_button' or @ $instance['widget_type'] == 'selected_area' ) echo 'style="display: none;"'; ?>>
    <p class="br_admin_half_size_left">
        <label class="br_admin_center"><?php _e('Filter By', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'filter_type' ); ?>" name="<?php echo @ $this->get_field_name( 'filter_type' ); ?>" class="berocket_aapf_widget_admin_filter_type_select br_select_menu_left">
            <option <?php if ( @ $instance['filter_type'] == 'attribute' ) echo 'selected'; ?> value="attribute"><?php _e('Attribute', BeRocket_AJAX_domain) ?></option>
            <option <?php if ( @ $instance['filter_type'] == '_stock_status' ) echo 'selected'; ?> value="_stock_status"><?php _e('Stock status', BeRocket_AJAX_domain) ?></option>
            <option <?php if ( @ $instance['filter_type'] == 'product_cat' ) echo 'selected'; ?> value="product_cat"><?php _e('Product sub-categories', BeRocket_AJAX_domain) ?></option>
            <option <?php if ( @ $instance['filter_type'] == 'tag' ) echo 'selected'; ?> value="tag"><?php _e('Tag', BeRocket_AJAX_domain) ?></option>
            <option <?php if ( @ $instance['filter_type'] == 'custom_taxonomy' ) echo 'selected'; ?> value="custom_taxonomy"><?php _e('Custom Taxonomy', BeRocket_AJAX_domain) ?></option>
        </select>
    </p>
    <p class="br_admin_half_size_right berocket_aapf_widget_admin_filter_type_ berocket_aapf_widget_admin_filter_type_attribute" <?php if ( @ $instance['filter_type'] and @ $instance['filter_type'] != 'attribute') echo 'style="display: none;"'; ?>>
        <label class="br_admin_center"><?php _e('Attribute', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'attribute' ); ?>" name="<?php echo @ $this->get_field_name( 'attribute' ); ?>" class="berocket_aapf_widget_admin_filter_type_attribute_select br_select_menu_right">
            <option <?php if ( @ $instance['attribute'] == 'price' ) echo 'selected'; ?> value="price"><?php _e('Price', BeRocket_AJAX_domain) ?></option>
            <?php foreach ( @ $attributes as $k => $v ) { ?>
                <option <?php if ( @ $instance['attribute'] == $k ) echo 'selected'; ?> value="<?php echo @ $k ?>"><?php echo @ $v ?></option>
            <?php } ?>
        </select>
    </p>
    <p class="br_admin_half_size_right berocket_aapf_widget_admin_filter_type_ berocket_aapf_widget_admin_filter_type_custom_taxonomy" <?php if ( $instance['filter_type'] != 'custom_taxonomy') echo 'style="display: none;"'; ?>>
        <label class="br_admin_center"><?php _e('Custom Taxonomies', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'custom_taxonomy' ); ?>" name="<?php echo @ $this->get_field_name( 'custom_taxonomy' ); ?>" class="berocket_aapf_widget_admin_filter_type_custom_taxonomy_select br_select_menu_right">
            <?php foreach( $custom_taxonomies as $k => $v ){ ?>
                <option <?php if ( @ $instance['custom_taxonomy'] == @ $k ) echo 'selected'; ?> value="<?php echo @ $k ?>"><?php echo @ $v ?></option>
            <?php } ?>
        </select>
    </p>
    <div class="br_clearfix"></div>
    <p class="br_admin_three_size_left">
        <label class="br_admin_center"><?php _e('Type', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'type' ); ?>" name="<?php echo @ $this->get_field_name( 'type' ); ?>" class="berocket_aapf_widget_admin_type_select br_select_menu_left">
            <?php if ( @ $instance['filter_type'] and @ $instance['filter_type'] != 'attribute' or @ $instance['attribute'] != 'price' ) { ?>
                <option <?php if ( @ $instance['type'] == 'checkbox' ) echo 'selected'; ?> value="checkbox">Checkbox</option>
                <option <?php if ( @ $instance['type'] == 'radio' ) echo 'selected'; ?> value="radio">Radio</option>
                <option <?php if ( @ $instance['type'] == 'select' ) echo 'selected'; ?> value="select">Select</option>
                <?php if ( $instance['filter_type'] != 'tag' && $instance['filter_type'] != 'product_cat' && $instance['filter_type'] != '_stock_status' && ( $instance['filter_type'] != 'custom_taxonomy' || @ $instance['custom_taxonomy'] != 'product_tag' ) ) { ?>
                    <option <?php if ( @ $instance['type'] == 'color' ) echo 'selected'; ?> value="color">Color</option>
                    <option <?php if ( @ $instance['type'] == 'image' ) echo 'selected'; ?> value="image">Image</option>
                <?php } ?>
            <?php } ?>
            <?php if ( @ $instance['filter_type'] and $instance['filter_type'] != 'tag' and ( $instance['filter_type'] != 'custom_taxonomy' or @ $instance['custom_taxonomy'] != 'product_tag' ) ) {?>
                <option <?php if ( @ $instance['type'] == 'slider') echo 'selected'; ?> value="slider">Slider</option>
            <?php }
            if ( @ $instance['filter_type'] and @ $instance['filter_type'] == 'attribute' and @ $instance['attribute'] == 'price' ) {?>
                <option <?php if ( @ $instance['type'] == 'ranges') echo 'selected'; ?> value="ranges">Ranges</option>
            <?php }
            if ( @ $instance['filter_type'] and $instance['filter_type'] == 'tag' ) { ?>
                <option <?php if ( @ $instance['type'] == 'tag_cloud' ) echo 'selected'; ?> value="tag_cloud">Tag cloud</option>
            <?php } ?>
        </select>
    </p>
    <p class="br_admin_three_size_left" <?php if ( ( ! @ $instance['filter_type'] or $instance['filter_type'] == 'attribute' ) and  @ $instance['attribute'] == 'price' or @ $instance['type'] == 'slider' ) echo " style='display: none;'"; ?> >
        <label class="br_admin_center"><?php _e('Operator', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'operator' ); ?>" name="<?php echo @ $this->get_field_name( 'operator' ); ?>" class="berocket_aapf_widget_admin_operator_select br_select_menu_left">
            <option <?php if ( @ $instance['operator'] == 'AND' ) echo 'selected'; ?> value="AND">AND</option>
            <option <?php if ( @ $instance['operator'] == 'OR' ) echo 'selected'; ?> value="OR">OR</option>
        </select>
    </p>
    <p class="berocket_aapf_order_values_by br_admin_three_size_left" <?php if ( ! @ $instance['filter_type'] or @ $instance['filter_type'] == '_stock_status') echo 'style="display: none;"'; ?>>
        <label class="br_admin_center"><?php _e('Values Order', BeRocket_AJAX_domain) ?></label>
        <select id="<?php echo @ $this->get_field_id( 'order_values_by' ); ?>" name="<?php echo @ $this->get_field_name( 'order_values_by' ); ?>" class="berocket_aapf_order_values_by_select br_select_menu_left">
            <option value=""><?php _e('Default', BeRocket_AJAX_domain) ?></option>
            <?php foreach ( array( 'Alpha', 'Numeric' ) as $v ) { ?>
                <option <?php if ( $instance['order_values_by'] == $v ) echo 'selected'; ?> value="<?php _e( $v, BeRocket_AJAX_domain ) ?>"><?php _e( $v, BeRocket_AJAX_domain ) ?></option>
            <?php } ?>
        </select>
    </p>
    <div class="br_clearfix"></div>
    <div class="berocket_widget_color_pick">
        <?php if ( @ $instance['type'] == 'color' || @ $instance['type'] == 'image' ) {
            if ( @ $instance['filter_type'] == 'attribute' ) {
                $attribute_color_view = @ $instance['attribute'];
            } elseif ( @ $instance['filter_type'] == 'product_cat' ) {
                $attribute_color_view = 'product_cat';
            } elseif ( @ $instance['filter_type'] == 'custom_taxonomy' ) {
                $attribute_color_view = @ $instance['custom_taxonomy'];
            }
            BeRocket_AAPF_Widget::color_list_view( @ $instance['type'], @ $attribute_color_view, true );
        } ?>
    </div>
    <div class="berocket_ranges_block"<?php if ( @ ! $instance['filter_type'] or @ $instance['filter_type'] != 'attribute' or @ $instance['attribute'] != 'price' or @ $instance['type'] != 'ranges' ) echo ' style="display: none;"'; ?>>
    <?php 
        if ( isset( $instance['ranges'] ) && is_array( $instance['ranges'] ) && count( $instance['ranges'] ) > 0 ) {
            foreach ( $instance['ranges'] as $range ) {
                ?><p class="berocket_ranges">
                    <input type="number" min="1" id="<?php echo @ $this->get_field_id( 'ranges' ); ?>" name="<?php echo @ $this->get_field_name( 'ranges' ); ?>[]" value="<?php echo $range; ?>">
                    <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
                </p><?php
            }
        } else {
            ?><p class="berocket_ranges">
                <input type="number" min="1" id="<?php echo @ $this->get_field_id( 'ranges' ); ?>" name="<?php echo @ $this->get_field_name( 'ranges' ); ?>[]" value="1">
                <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
            </p>
            <p class="berocket_ranges">
                <input type="number" min="1" id="<?php echo @ $this->get_field_id( 'ranges' ); ?>" name="<?php echo @ $this->get_field_name( 'ranges' ); ?>[]" value="50">
                <a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>
            </p> <?php
        }
        ?><p><a href="#add" class="berocket_add_ranges" data-html='<p class="berocket_ranges"><input type="number" min="1" id="<?php echo @ $this->get_field_id( 'ranges' ); ?>" name="<?php echo @ $this->get_field_name( 'ranges' ); ?>[]" value="1"><a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a></p>'><i class="fa fa-plus"></i></a></p>
    </div>
    <p <?php if ( @ $instance['filter_type'] != 'attribute' || @ $instance['attribute'] != 'price' ) echo " style='display: none;'"; ?> class="berocket_aapf_widget_admin_price_attribute" >
        <label class="br_admin_center" for="<?php echo @ $this->get_field_id( 'text_before_price' ); ?>"><?php _e('Text before price:', BeRocket_AJAX_domain) ?> </label>
        <input class="br_admin_full_size"  id="<?php echo @ $this->get_field_id( 'text_before_price' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'text_before_price' ); ?>" value="<?php echo @ $instance['text_before_price']; ?>"/>
        <label class="br_admin_center" for="<?php echo @ $this->get_field_id( 'text_after_price' ); ?>"><?php _e('after:', BeRocket_AJAX_domain) ?> </label>
        <input class="br_admin_full_size"  id="<?php echo @ $this->get_field_id( 'text_after_price' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'text_after_price' ); ?>" value="<?php echo @ $instance['text_after_price']; ?>" />
    </p>
    <p <?php if ( @ $instance['filter_type'] != 'attribute' || @ $instance['attribute'] != 'price' ) echo " style='display: none;'"; ?> class="berocket_aapf_widget_admin_price_attribute" >
        <label for="<?php echo @ $this->get_field_id( 'price_values' ); ?>"><?php _e('Use custom values(comma separated):', BeRocket_AJAX_domain) ?> </label>
        <input class="br_admin_full_size" id="<?php echo @ $this->get_field_id( 'price_values' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'price_values' ); ?>" value="<?php echo @ $instance['price_values']; ?>"/>
        <small><?php _e('* use numeric values only, strings will not work as expected', BeRocket_AJAX_domain) ?></small>
    </p>
    <div class="br_clearfix"></div>
    <div class="berocket_aapf_product_sub_cat_div" <?php if( $instance['filter_type'] != 'product_cat' ) echo 'style="display:none;"'; ?>>
            <label><?php _e('Product Category:', BeRocket_AJAX_domain) ?></label>
            <ul class="berocket_aapf_advanced_settings_categories_list">
                    <li>
                        <?php 
                        echo '<input type="radio" name="' . ( @ $this->get_field_name( 'parent_product_cat' ) ) . '" ' .
                             ( ( ! @ $instance['parent_product_cat'] ) ? 'checked' : '' ) . ' value="" ' .
                             'class="berocket_aapf_widget_admin_height_input" />';
                        ?>
                        <?php _e('None', BeRocket_AJAX_domain) ?>
                    </li>
            <?php
            $selected_category = false;
            foreach ( $categories as $category ) {
                if ( @ (int) $instance['parent_product_cat'] == @ (int) $category->term_id ) {
                    $selected_category = true;
                }
                echo '<li>';
                if ( @ (int) $category->depth ) {
                    for ( $depth_i = 0; $depth_i < $category->depth; $depth_i ++ ) {
                        echo "&nbsp;&nbsp;&nbsp;";
                    }
                }
                echo '<input type="radio" name="' . ( @ $this->get_field_name( 'parent_product_cat' ) ) . '" ' .
                     ( ( $selected_category ) ? 'checked' : '' ) . ' value="' . ( @ $category->term_id ).'" ' .
                     'class="berocket_aapf_widget_admin_height_input" />' . ( @ $category->name );
                echo '</li>';
                $selected_category = false;
            }
            ?>
            </ul>
        <p>
            <label for="<?php echo @ $this->get_field_id( 'depth_count' ); ?>"><?php _e('Deep level:', BeRocket_AJAX_domain) ?></label>
            <input id="<?php echo @ $this->get_field_id( 'depth_count' ); ?>" type="number" min=1 name="<?php echo @ $this->get_field_name( 'depth_count' ); ?>" value="<?php echo @ $instance['depth_count']; ?>" />
        </p>
    </div>
    <br />
    <div class="br_clearfix"></div>
    <div class="br_accordion">
        <h3><?php _e('Advanced Settings', BeRocket_AJAX_domain) ?></h3>
        <div class='berocket_aapf_advanced_settings'>
            <p>
                <input id="<?php echo @ $this->get_field_id( 'widget_is_hide' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'widget_is_hide' ); ?>" <?php if ( @ $instance['widget_is_hide'] ) echo 'checked'; ?> value="1" />
                <label for="<?php echo @ $this->get_field_id( 'widget_is_hide' ); ?>"><?php _e('Hide this widget on load?', BeRocket_AJAX_domain) ?></label>
            </p>
            <p class="berocket_aapf_widget_admin_non_price_tag_cloud" <?php if ( @ $instance['type'] == 'tag_cloud' || @ $instance['type'] == 'slider' ) echo 'style="display:none;"' ?>>
                <input id="<?php echo @ $this->get_field_id( 'show_product_count_per_attr' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'show_product_count_per_attr' ); ?>" <?php if ( @ $instance['show_product_count_per_attr'] ) echo 'checked'; ?> value="1" />
                <label for="<?php echo @ $this->get_field_id( 'show_product_count_per_attr' ); ?>"><?php _e('Show product count per attributes?', BeRocket_AJAX_domain) ?></label>
            </p>
            <p>
                <input id="<?php echo @ $this->get_field_id( 'hide_collapse_arrow' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'hide_collapse_arrow' ); ?>" <?php if ( @ $instance['hide_collapse_arrow'] ) echo 'checked'; ?> value="1" />
                <label for="<?php echo @ $this->get_field_id( 'hide_collapse_arrow' ); ?>"><?php _e('Hide collapse arrow?', BeRocket_AJAX_domain) ?></label>
            </p>
            <p class="berocket_aapf_advanced_color_pick_settings"<?php if ( $instance['type'] != 'color' && $instance['type'] != 'image' ) echo " style='display: none;'"; ?>>
                <input id="<?php echo @ $this->get_field_id( 'use_value_with_color' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'use_value_with_color' ); ?>" <?php if ( @ $instance['use_value_with_color'] ) echo 'checked'; ?> value="1" />
                <label for="<?php echo @ $this->get_field_id( 'use_value_with_color' ); ?>"><?php _e('Display value with color/image box?', BeRocket_AJAX_domain) ?></label>
            </p>
            <p class="br_admin_full_size" <?php if ( ( ! @ $instance['filter_type'] or $instance['filter_type'] == 'attribute' ) and  @ $instance['attribute'] == 'price' or @ $instance['filter_type'] == 'product_cat' or @ $instance['type'] == 'slider' or @ $instance['type'] == 'select' or @ $instance['type'] == 'tag_cloud' or ( @ $instance['filter_type'] == 'custom_taxonomy' and @ $instance['custom_taxonomy'] == 'product_cat' ) ) echo " style='display: none;'"; ?> >
                <label class="br_admin_center"><?php _e('Values per row', BeRocket_AJAX_domain) ?></label>
                <select id="<?php echo @ $this->get_field_id( 'values_per_row' ); ?>" name="<?php echo @ $this->get_field_name( 'values_per_row' ); ?>" class="berocket_aapf_widget_admin_values_per_row br_select_menu_left">
                    <option <?php if ( @ $instance['values_per_row'] == '1' || ! @ $instance['operator'] ) echo 'selected'; ?> value="1">Default</option>
                    <option <?php if ( @ $instance['values_per_row'] == '2' ) echo 'selected'; ?> value="2">2</option>
                    <option <?php if ( @ $instance['values_per_row'] == '3' ) echo 'selected'; ?> value="3">3</option>
                    <option <?php if ( @ $instance['values_per_row'] == '4' ) echo 'selected'; ?> value="4">4</option>
                </select>
            </p>
            <div class="br_accordion br_icons">
                <h3><?php _e('Icons', BeRocket_AJAX_domain) ?></h3>
                <div>
                    <label class="br_admin_center"><?php _e('Title Icons', BeRocket_AJAX_domain) ?></label>
                    <div class="br_clearfix"></div>
                    <div class="br_admin_half_size_left"><?php echo berocket_font_select_upload(__('Before', BeRocket_AJAX_domain), $this->get_field_id( 'icon_before_title' ), $this->get_field_name( 'icon_before_title' ), @ $instance['icon_before_title'] ); ?></div>
                    <div class="br_admin_half_size_right"><?php echo berocket_font_select_upload(__('After', BeRocket_AJAX_domain), $this->get_field_id( 'icon_after_title' ) , $this->get_field_name( 'icon_after_title' ) , @ $instance['icon_after_title'] ); ?></div>
                    <div class="br_clearfix"></div>
                    <div class="berocket_aapf_icons_select_block" <?php if ($instance['type'] == 'select') echo 'style="display:none;"' ?>>
                        <label class="br_admin_center"><?php _e('Value Icons', BeRocket_AJAX_domain) ?></label>
                        <div class="br_clearfix"></div>
                        <div class="br_admin_half_size_left"><?php echo berocket_font_select_upload(__('Before', BeRocket_AJAX_domain), $this->get_field_id( 'icon_before_value' ), $this->get_field_name( 'icon_before_value' ), @ $instance['icon_before_value'] ); ?></div>
                        <div class="br_admin_half_size_right"><?php echo berocket_font_select_upload(__('After', BeRocket_AJAX_domain) , $this->get_field_id( 'icon_after_value' ) , $this->get_field_name( 'icon_after_value' ) , @ $instance['icon_after_value'] ); ?></div>
                        <div class="br_clearfix"></div>
                    </div>
                </div>
            </div>
            <p>
                <label class="br_admin_center" style="text-align: left;" for="<?php echo @ $this->get_field_id( 'description' ); ?>"><?php _e('Description', BeRocket_AJAX_domain) ?></label>
                <textarea style="resize: none; width: 100%;" id="<?php echo @ $this->get_field_id( 'description' ); ?>" name="<?php echo @ $this->get_field_name( 'description' ); ?>"><?php echo @ $instance['description']; ?></textarea>
            </p>
            <p>
                <label class="br_admin_center" style="text-align: left;" for="<?php echo @ $this->get_field_id( 'css_class' ); ?>"><?php _e('CSS Class', BeRocket_AJAX_domain) ?> </label>
                <input id="<?php echo @ $this->get_field_id( 'css_class' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'css_class' ); ?>" value="<?php echo @ $instance['css_class']; ?>" class="berocket_aapf_widget_admin_css_class_input br_admin_full_size" />
                <small class="br_admin_center" style="font-size: 1em;"><?php _e('(use white space for multiple classes)', BeRocket_AJAX_domain) ?></small>
            </p>
            <?php echo @ $instance['filter_type_attribute']; ?>
            <div class="berocket_aapf_widget_admin_tag_cloud_block" <?php if ($instance['type'] != 'tag_cloud') echo 'style="display:none;"' ?>>
                <p>
                    <label for="<?php echo @ $this->get_field_id( 'tag_cloud_height' ); ?>"><?php _e('Tags Cloud Height:', BeRocket_AJAX_domain) ?> </label>
                    <input id="<?php echo @ $this->get_field_id( 'tag_cloud_height' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'tag_cloud_height' ); ?>" value="<?php echo @ $instance['tag_cloud_height']; ?>" class="berocket_aapf_widget_admin_height_input" />px
                </p>
                <p>
                    <label for="<?php echo @ $this->get_field_id( 'tag_cloud_min_font' ); ?>"><?php _e('Min Font Size:', BeRocket_AJAX_domain) ?> </label>
                    <input id="<?php echo @ $this->get_field_id( 'tag_cloud_min_font' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'tag_cloud_min_font' ); ?>" value="<?php echo @ $instance['tag_cloud_min_font']; ?>" class="berocket_aapf_widget_admin_height_input" />px
                </p>
                <p>
                    <label for="<?php echo @ $this->get_field_id( 'tag_cloud_max_font' ); ?>"><?php _e('Max Font Size:', BeRocket_AJAX_domain) ?> </label>
                    <input id="<?php echo @ $this->get_field_id( 'tag_cloud_max_font' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'tag_cloud_max_font' ); ?>" value="<?php echo @ $instance['tag_cloud_max_font']; ?>" class="berocket_aapf_widget_admin_height_input" />px
                </p>
                <p>
                    <label for="<?php echo @ $this->get_field_id( 'tag_cloud_tags_count' ); ?>"><?php _e('Max Tags Count:', BeRocket_AJAX_domain) ?> </label>
                    <input id="<?php echo @ $this->get_field_id( 'tag_cloud_tags_count' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'tag_cloud_tags_count' ); ?>" value="<?php echo @ $instance['tag_cloud_tags_count']; ?>" class="berocket_aapf_widget_admin_height_input" />
                </p>
            </div>
            <div class="berocket_aapf_widget_admin_price_attribute" <?php if ( ! ( @ $instance['attribute'] == 'price' && @ $instance['type'] == 'slider' ) ) echo " style='display: none;'"; ?> >
                <div class="br_admin_half_size_left">
                    <p class="berocket_aapf_checked_show_next">
                        <input id="<?php echo @ $this->get_field_id( 'use_min_price' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'use_min_price' ); ?>" <?php if ( @ $instance['use_min_price'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_input_price_is"/>
                        <label class="br_admin_full_size" for="<?php echo @ $this->get_field_id( 'use_min_price' ); ?>"><?php _e('Use min price', BeRocket_AJAX_domain) ?></label>
                    </p>
                    <p <?php if ( !$instance['use_min_price'] ) echo 'style="display:none"'; ?>>
                        <input type=number min=0 id="<?php echo @ $this->get_field_id( 'min_price' ); ?>" name="<?php echo @ $this->get_field_name( 'min_price' ); ?>" value="<?php echo ( ( @ $instance['min_price'] ) ? @ $instance['min_price'] : '0' ); ?>" class="br_admin_full_size berocket_aapf_widget_admin_input_price">
                    </p>
                </div>
                <div class="br_admin_half_size_right">
                    <p class="berocket_aapf_checked_show_next">
                        <input id="<?php echo @ $this->get_field_id( 'use_max_price' ); ?>" type="checkbox" name="<?php echo @ $this->get_field_name( 'use_max_price' ); ?>" <?php if ( @ $instance['use_max_price'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_input_price_is"/>
                        <label class="br_admin_full_size" for="<?php echo @ $this->get_field_id( 'use_max_price' ); ?>"><?php _e('Use max price', BeRocket_AJAX_domain) ?></label>
                    </p>
                    <p <?php if ( !$instance['use_max_price'] ) echo 'style="display:none"'; ?>>
                        <input type=number min=1 id="<?php echo @ $this->get_field_id( 'max_price' ); ?>" name="<?php echo @ $this->get_field_name( 'max_price' ); ?>" value="<?php echo ( ( @ $instance['max_price'] ) ? @ $instance['max_price'] : '0' ); ?>" class="br_admin_full_size berocket_aapf_widget_admin_input_price">
                    </p>
                </div>
                <div class="br_clearfix"></div>
            </div>
            <p>
                <label for="<?php echo @ $this->get_field_id( 'height' ); ?>"><?php _e('Filter Box Height:', BeRocket_AJAX_domain) ?> </label>
                <input id="<?php echo @ $this->get_field_id( 'height' ); ?>" type="text" name="<?php echo @ $this->get_field_name( 'height' ); ?>" value="<?php echo @ $instance['height']; ?>" class="berocket_aapf_widget_admin_height_input" />px
            </p>
            <p>
                <label for="<?php echo @ $this->get_field_id( 'scroll_theme' ); ?>"><?php _e('Scroll Theme:', BeRocket_AJAX_domain) ?> </label>
                <select id="<?php echo @ $this->get_field_id( 'scroll_theme' ); ?>" name="<?php echo @ $this->get_field_name( 'scroll_theme' ); ?>" class="berocket_aapf_widget_admin_scroll_theme_select br_select_menu_left">
                    <?php
                    $scroll_themes = array("light", "dark", "minimal", "minimal-dark", "light-2", "dark-2", "light-3", "dark-3", "light-thick", "dark-thick", "light-thin",
                        "dark-thin", "inset", "inset-dark", "inset-2", "inset-2-dark", "inset-3", "inset-3-dark", "rounded", "rounded-dark", "rounded-dots",
                        "rounded-dots-dark", "3d", "3d-dark", "3d-thick", "3d-thick-dark");
                    foreach( $scroll_themes as $theme ): ?>
                        <option <?php if ( @ $instance['scroll_theme'] == @ $theme ) echo 'selected'; ?>><?php echo @ $theme; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <div class="br_aapf_child_parent_selector" <?php if ( $instance['filter_type'] == 'attribute' and ( @ $instance['attribute'] == 'price'  or @ $instance['filter_type'] == 'product_cat' ) or @ $instance['type'] == 'slider' ) echo " style='display: none;'"; ?>>
                <p>
                    <label class="br_admin_center"><?php _e('Child/Parent Limitation', BeRocket_AJAX_domain) ?></label>
                    <select name="<?php echo @ $this->get_field_name( 'child_parent' ); ?>" class="br_select_menu_left berocket_aapf_widget_child_parent_select">
                        <option value="" <?php if ( ! @ $instance['child_parent'] ) echo 'selected' ?>><?php _e('Default', BeRocket_AJAX_domain) ?></option>
                        <option value="parent" <?php if ( @ $instance['child_parent'] == 'parent' ) echo 'selected' ?>><?php _e('Parent', BeRocket_AJAX_domain) ?></option>
                        <option value="child" <?php if ( @ $instance['child_parent'] == 'child' ) echo 'selected' ?>><?php _e('Child', BeRocket_AJAX_domain) ?></option>
                    </select>
                </p>
                <p class="berocket_aapf_widget_child_parent_depth_block" <?php if( @ $instance['child_parent'] != 'child' ) echo 'style="display: none;"'; ?>>
                    <label for="<?php echo @ $this->get_field_id( 'child_parent_depth' ); ?>" class="br_admin_full_size"><?php _e('Child depth', BeRocket_AJAX_domain) ?></label>
                    <input name="<?php echo @ $this->get_field_name( 'child_parent_depth' ); ?>" id="<?php echo @ $this->get_field_id( 'child_parent_depth' ); ?>" type="number" min="1" value="<?php echo @ $instance['child_parent_depth']; ?>">
                </p>
            </div>
        </div>
    </div>
</div>
<div class="berocket_aapf_admin_widget_selected_area" <?php if ( @ $instance['widget_type'] != 'selected_area' ) echo 'style="display: none;"'; ?>>
    <p>
        <label>
            <input type="checkbox" name="<?php echo @ $this->get_field_name( 'selected_area_show' ); ?>" <?php if ( @ $instance['selected_area_show'] ) echo 'checked'; ?> value="1" />
            <?php _e('Show if nothing is selected', BeRocket_AJAX_domain) ?>
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="<?php echo @ $this->get_field_name( 'hide_selected_arrow' ); ?>" <?php if ( @ $instance['hide_selected_arrow'] ) echo 'checked'; ?> value="1" />
            <?php _e('Hide collapse arrow?', BeRocket_AJAX_domain) ?>
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="<?php echo @ $this->get_field_name( 'selected_is_hide' ); ?>" <?php if ( @ $instance['selected_is_hide'] ) echo 'checked'; ?> value="1" />
            <?php _e('Hide this widget on load?', BeRocket_AJAX_domain) ?>
        </label>
    </p>
</div>
<div class="br_accordion">
    <h3><?php _e('Widget Output Limitations', BeRocket_AJAX_domain) ?></h3>
    <div>
        <p>
            <label>
                <input type="checkbox" name="<?php echo @ $this->get_field_name( 'is_hide_mobile' ); ?>" <?php if ( @ $instance['is_hide_mobile'] ) echo 'checked'; ?> value="1" />
                <?php _e('Hide this widget on mobile?', BeRocket_AJAX_domain) ?>
            </label>
        </p>
        <p>
            <label><?php _e('Product Category:', BeRocket_AJAX_domain) ?>
                <label class="berocket_aapf_advanced_settings_subcategory">
                    <input type="checkbox" name="<?php echo @ $this->get_field_name( 'cat_propagation' ); ?>" <?php if ( @ $instance['cat_propagation'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_height_input" />
                    <?php _e('include subcats?', BeRocket_AJAX_domain) ?>
                </label>
            </label>
            <ul class="berocket_aapf_advanced_settings_categories_list">
                <?php
                $p_cat = @json_decode( $instance['product_cat'] );

                foreach( $categories as $category ){
                    $selected_category = false;

                    if( @ $p_cat )
                        foreach( $p_cat as $cat ){
                            if( $cat == @ $category->slug )
                                $selected_category = true;
                        }
                    ?>
                    <li>
                        <?php
                        if ( @ (int)$category->depth ) for ( $depth_i = 0; $depth_i < $category->depth*3; $depth_i++ ) echo "&nbsp;";
                        ?>
                        <input type="checkbox" name="<?php echo @ $this->get_field_name( 'product_cat' ); ?>[]" <?php if ( @ $selected_category ) echo 'checked'; ?> value="<?php echo @ $category->slug ?>" class="berocket_aapf_widget_admin_height_input" />
                        <?php echo @ $category->name ?>
                    </li>
                <?php } ?>
            </ul>
        </p>
        <div class="br_accordion">
            <h3><?php _e('Dispay widget pages', BeRocket_AJAX_domain) ?></h3>
            <div  style="display: none;">
                <ul class="br_admin_150_height">
                    <li><label>
                        <input type="checkbox" name="<?php echo @ $this->get_field_name( 'show_page' ); ?>[]" <?php if ( @ in_array( 'shop', $instance['show_page'] ) ) echo 'checked'; ?> value="shop" />
                        <?php _e('shop', BeRocket_AJAX_domain) ?>
                    </label></li>
                    <li><label>
                        <input type="checkbox" name="<?php echo @ $this->get_field_name( 'show_page' ); ?>[]" <?php if ( @ in_array( 'product_cat', $instance['show_page'] ) ) echo 'checked'; ?> value="product_cat" />
                        <?php _e('product category', BeRocket_AJAX_domain) ?>
                    </label></li>
                    <li><label>
                        <input type="checkbox" name="<?php echo @ $this->get_field_name( 'show_page' ); ?>[]" <?php if ( @ in_array( 'product_tag', $instance['show_page'] ) ) echo 'checked'; ?> value="product_tag" />
                        <?php _e('product tags', BeRocket_AJAX_domain) ?>
                    </label></li>
                    <?php
                    $pages = get_pages();
                    foreach ( $pages as $page ) {
                        ?>
                        <li><label>
                            <input type="checkbox" name="<?php echo @ $this->get_field_name( 'show_page' ); ?>[]" <?php if ( @ in_array( $page->ID, $instance['show_page'] ) ) echo 'checked'; ?> value="<?php echo @ $page->ID ?>" />
                            <?php echo @ $page->post_title; ?>
                        </label></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="br_accordion">
            <h3><?php _e('Product Category Value Limitation', BeRocket_AJAX_domain) ?></h3>
            <div>
                <ul class="br_admin_150_height">
                    <li>
                        <input type="radio" name="<?php echo @ $this->get_field_name( 'cat_value_limit' ); ?>" <?php if ( ! @ $instance['cat_value_limit'] ) echo 'checked'; ?> value="0"/>
                        <?php _e('Disable', BeRocket_AJAX_domain) ?>
                    </li>
                <?php
                foreach( $categories as $category ){
                    $selected_category = false;
                    if( @ $instance['cat_value_limit'] == @ $category->slug )
                        $selected_category = true;
                    ?>
                    <li>
                        <?php
                        if ( @ (int)$category->depth ) for ( $depth_i = 0; $depth_i < $category->depth*3; $depth_i++ ) echo "&nbsp;";
                        ?>
                        <input type="radio" name="<?php echo @ $this->get_field_name( 'cat_value_limit' ); ?>" <?php if ( $selected_category ) echo 'checked'; ?> value="<?php echo @ $category->slug ?>"/>
                        <?php echo @ $category->name ?>
                    </li>
                <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    if( typeof(br_widget_set) == 'function' )
        br_widget_set();
</script>
