<?php
define('BEROCKETAAPF', 'BeRocket_AAPF_Widget');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/* Widget */
function BeRocket_AAPF_load_widgets() {
    register_widget( 'BeRocket_AAPF_widget' );
}

require_once dirname( __FILE__ ).'/functions.php';
if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
    add_action( 'widgets_init', 'BeRocket_AAPF_load_widgets' );
    add_action( 'wp_ajax_nopriv_berocket_aapf_listener', array( 'BeRocket_AAPF_Widget', 'listener' ) );
    add_action( 'wp_ajax_berocket_aapf_listener', array( 'BeRocket_AAPF_Widget', 'listener' ) );
    add_action( 'wp_ajax_nopriv_berocket_aapf_listener_pc', array( 'BeRocket_AAPF_Widget', 'listener_product_count' ) );
    add_action( 'wp_ajax_berocket_aapf_listener_pc', array( 'BeRocket_AAPF_Widget', 'listener_product_count' ) );

    add_action( 'wp_ajax_berocket_aapf_color_listener', array( 'BeRocket_AAPF_Widget', 'color_listener' ) );
    add_action( 'wp_ajax_nopriv_berocket_aapf_color_listener', array( 'BeRocket_AAPF_Widget', 'color_listener' ) );
}
/**
 * BeRocket_AAPF_Widget - main filter widget. One filter for any needs
 */
class BeRocket_AAPF_Widget extends WP_Widget {

    public static $defaults = array(
        'br_wp_footer'                  => false,
        'widget_type'                   => 'filter',
        'title'                         => '',
        'filter_type'                   => 'attribute',
        'attribute'                     => 'price',
        'custom_taxonomy'               => 'product_cat',
        'type'                          => 'slider',
        'operator'                      => 'OR',
        'order_values_by'               => '',
        'text_before_price'             => '',
        'text_after_price'              => '',
        'parent_product_cat'            => '',
        'depth_count'                   => '0',
        'widget_is_hide'                => '0',
        'show_product_count_per_attr'   => '0',
        'hide_collapse_arrow'           => '0',
        'use_value_with_color'          => '0',
        'values_per_row'                => '1',
        'icon_before_title'             => '',
        'icon_after_title'              => '',
        'icon_before_value'             => '',
        'icon_after_value'              => '',
        'description'                   => '',
        'css_class'                     => '',
        'tag_cloud_height'              => '0',
        'tag_cloud_min_font'            => '12',
        'tag_cloud_max_font'            => '14',
        'tag_cloud_tags_count'          => '100',
        'use_min_price'                 => '0',
        'min_price'                     => '0',
        'use_max_price'                 => '0',
        'max_price'                     => '1',
        'height'                        => 'auto',
        'scroll_theme'                  => 'dark',
        'selected_area_show'            => '0',
        'hide_selected_arrow'           => '0',
        'selected_is_hide'              => '0',
        'is_hide_mobile'                => '0',
        'cat_propagation'               => '0',
        'product_cat'                   => '',
        'show_page'                     => array( 'shop', 'product_cat', 'product_tag' ),
        'cat_value_limit'               => '0',
        'child_parent'                  => '',
        'child_parent_depth'            => '1',
        'ranges'                        => array( 1, 10 ),
    );

    /**
     * Constructor
     */
    function BeRocket_AAPF_Widget() {
        global $wp_version;
        /* Widget settings. */
        $widget_ops  = array( 'classname' => 'widget_berocket_aapf', 'description' => __('Add Filters to Products page', BeRocket_AJAX_domain) );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'berocket_aapf_widget' );

        /* Create the widget. */
        if( strcmp( $wp_version, '4.3') < 0 ) {
            $this->WP_Widget( 'berocket_aapf_widget', __('AJAX Product Filters', BeRocket_AJAX_domain), $widget_ops, $control_ops );
        } else {
            $this->__construct( 'berocket_aapf_widget', __('AJAX Product Filters', BeRocket_AJAX_domain), $widget_ops, $control_ops );
        }

        add_filter( 'berocket_aapf_listener_wp_query_args', 'br_aapf_args_parser' );
    }

    /**
     * Show widget to user
     *
     * @param array $args
     * @param array $instance
     */
    function widget( $args, $instance ) {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );

        if( @ $br_options['user_func'] && is_array( $br_options['user_func'] ) ) {
            $user_func = array_merge( BeRocket_AAPF::$defaults['user_func'], $br_options['user_func'] );
        } else {
            $user_func = BeRocket_AAPF::$defaults['user_func'];
        }

        if( @ $br_options['filters_turn_off'] || is_product() ) return false;

        if ( @ $instance['show_page'] ) {
            $pageid = get_the_ID();
            $pagelimit = FALSE;

            foreach ( @ $instance['show_page'] as $page => $is_show ) {
                if( $is_show ) {
                    $pagelimit = TRUE;
                    break;
                }
            }
            if ( $pagelimit &&
                ( ( ! is_product_category() && ! is_shop() && ! is_product_tag() && ! @ in_array( $pageid, $instance['show_page'] ) ) || 
                ( is_shop() && ! @ in_array( 'shop', $instance['show_page'] ) ) || 
                ( is_product_category() && ! @ in_array( 'product_cat', $instance['show_page'] ) ) || 
                ( is_product_tag() && ! @ in_array( 'product_tag', $instance['show_page'] ) ) )
                ) {
                return false;
            }
        }

        global $wp_query, $wp, $sitepress, $br_wc_query;
        if ( isset ( $br_wc_query ) ) {
            $old_query = $wp_query;
            $wp_query = $br_wc_query;
        }

        if( BeRocket_AAPF::$debug_mode ) {
            if( ! isset( BeRocket_AAPF::$error_log['6_widgets'] ) )
            {
                BeRocket_AAPF::$error_log['6_widgets'] = array();
            } 
            $widget_error_log             = array();
            $widget_error_log['wp_query'] = $wp_query;
        }

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url( '../js/scrollbar/Scrollbar.concat.min.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );

        /* themer */
        wp_enqueue_script( 'berocket_aapf_widget-themer-script', plugins_url( '../js/styler/formstyler.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );

        /* main scripts */
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'berocket_aapf_widget-script', plugins_url( '../js/widget.min.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_enqueue_script( 'berocket_aapf_widget-hack-script', plugins_url( '../js/mobiles.min.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_enqueue_script( 'berocket_aapf_widget-tag_cloud', plugins_url( '../js/j.doe.cloud.min.js', __FILE__ ), array( 'jquery-ui-core' ), BeRocket_AJAX_filters_version );

        $wp_query_product_cat     = '-1';
        $wp_check_product_cat     = '1q1main_shop1q1';
        if ( @ $wp_query->query['product_cat'] ) {
            $wp_query_product_cat = explode( "/", $wp_query->query['product_cat'] );
            $wp_query_product_cat = $wp_query_product_cat[ count( $wp_query_product_cat ) - 1 ];
            $wp_check_product_cat = $wp_query_product_cat;
        }

        if ( ! $br_options['products_holder_id'] ) $br_options['products_holder_id'] = 'ul.products';

        $post_temrs = "[]";
        if ( @ $_POST['terms'] ) {
            $post_temrs = @ json_encode( $_POST['terms'] );
        }

        if ( method_exists($sitepress, 'get_current_language') ) {
            $current_language = $sitepress->get_current_language();
        } else {
            $current_language = '';
        }

        $option_permalink = get_option( 'berocket_permalink_option' );
        $permalink_values = explode( 'values', $option_permalink['value'] );

        $current_page_url = preg_replace( "~paged?/[0-9]+/?~", "", home_url( $wp->request ) );
        if( @ $br_options['nice_urls'] ) {
            $current_page_url = preg_replace( "~".$option_permalink['variable']."/.+~", "", $current_page_url );
        }

        $permalink_structure = get_option('permalink_structure');
        if ( $permalink_structure ) {
            $permalink_structure = substr($permalink_structure, -1);
            if ( $permalink_structure == '/' ) {
                $permalink_structure = true;
            } else {
                $permalink_structure = false;
            }
        } else {
            $permalink_structure = false;
        }

        wp_localize_script(
            'berocket_aapf_widget-script',
            'the_ajax_script',
            array(
                'nice_url_variable'                    => $option_permalink['variable'],
                'nice_url_value_1'                     => $permalink_values[0],
                'nice_url_value_2'                     => $permalink_values[1],
                'nice_url_split'                       => $option_permalink['split'],
                'version'                              => BeRocket_AJAX_filters_version,
                'number_style'                         => array( @ $br_options['number_style']['thousand_separate'], @ $br_options['number_style']['decimal_separate'], @ $br_options['number_style']['decimal_number'] ),
                'current_language'                     => $current_language,
                'current_page_url'                     => $current_page_url,
                'ajaxurl'                              => admin_url( 'admin-ajax.php' ),
                'product_cat'                          => $wp_query_product_cat,
                'products_holder_id'                   => @ $br_options['products_holder_id'],
                'result_count_class'                   => ( @ $br_options['woocommerce_result_count_class'] ? @ $br_options['woocommerce_result_count_class'] : BeRocket_AAPF::$defaults['woocommerce_result_count_class'] ),
                'ordering_class'                       => ( @ $br_options['woocommerce_ordering_class'] ? @ $br_options['woocommerce_ordering_class'] : BeRocket_AAPF::$defaults['woocommerce_ordering_class'] ),
                'pagination_class'                     => ( @ $br_options['woocommerce_pagination_class'] ? @ $br_options['woocommerce_pagination_class'] : BeRocket_AAPF::$defaults['woocommerce_pagination_class'] ),
                'control_sorting'                      => @ $br_options['control_sorting'],
                'seo_friendly_urls'                    => @ $br_options['seo_friendly_urls'],
                'slug_urls'                            => @ $br_options['slug_urls'],
                'nice_urls'                            => @ $br_options['nice_urls'],
                'ub_product_count'                     => @ $br_options['ub_product_count'],
                'ub_product_text'                      => @ $br_options['ub_product_text'],
                'ub_product_button_text'               => @ $br_options['ub_product_button_text'],
                'berocket_aapf_widget_product_filters' => $post_temrs,
                'user_func'                            => apply_filters( 'berocket_aapf_user_func', $user_func ),
                'default_sorting'                      => get_option('woocommerce_default_catalog_orderby'),
                'first_page'                           => @ $br_options['first_page_jump'],
                'scroll_shop_top'                      => @ $br_options['scroll_shop_top'],
                'ajax_request_load'                    => @ $br_options['ajax_request_load'],
                'ajax_request_load_style'              => ( @ $br_options['ajax_request_load_style'] ? @ $br_options['ajax_request_load_style'] : BeRocket_AAPF::$defaults['ajax_request_load_style'] ),
                'no_products'                          => ("<p class='no-products woocommerce-info" . ( ( $br_options['no_products_class'] ) ? ' '.$br_options['no_products_class'] : '' ) . "'>" . $br_options['no_products_message'] . "</p>"),
                'recount_products'                     => @ $br_options['recount_products'],
                'pos_relative'                         => @ $br_options['pos_relative'],
                'woocommerce_removes'                  => json_encode( array( 
                                                              'result_count' => @ $br_options['woocommerce_removes']['result_count'],
                                                              'ordering'     => @ $br_options['woocommerce_removes']['ordering'],
                                                              'pagination'   => @ $br_options['woocommerce_removes']['pagination'],
                                                          ) ),
                'description_show'                     => ( @ $br_options['description']['show'] ? @ $br_options['description']['show'] : 'click' ),
                'description_hide'                     => ( @ $br_options['description']['hide'] ? @ $br_options['description']['hide'] : 'click' ),
                'hide_sel_value'                       => @ $br_options['hide_value']['sel'],
                'hide_o_value'                         => @ $br_options['hide_value']['o'],
                'scroll_shop_top_px'                   => ( ( @ $br_options['scroll_shop_top_px'] ) ? $br_options['scroll_shop_top_px'] : BeRocket_AAPF::$defaults['scroll_shop_top_px'] ),
                'load_image'                           => '<div class="berocket_aapf_widget_loading"><div class="berocket_aapf_widget_loading_container">
                                                          <div class="berocket_aapf_widget_loading_top">' . ( ( @ $br_options['ajax_load_text']['top'] ) ? $br_options['ajax_load_text']['top'] : '' ) . '</div>
                                                          <div class="berocket_aapf_widget_loading_left">' . ( ( @ $br_options['ajax_load_text']['left'] ) ? $br_options['ajax_load_text']['left'] : '' ) . '</div>' .
                                                          ( ( @ $br_options['ajax_load_icon'] ) ? '<img alt="" src="'.$br_options['ajax_load_icon'].'">' : '<div class="berocket_aapf_widget_loading_image"></div>' ) .
                                                          '<div class="berocket_aapf_widget_loading_right">' . ( ( @ $br_options['ajax_load_text']['right'] ) ? $br_options['ajax_load_text']['right'] : '' ) . '</div>
                                                          <div class="berocket_aapf_widget_loading_bottom">' . ( ( @ $br_options['ajax_load_text']['bottom'] ) ? $br_options['ajax_load_text']['bottom'] : '' ) . '</div>
                                                          </div></div>',
                'translate'                            => array(
                                                            'show_value' => __('Show value(s)', BeRocket_AJAX_domain),
                                                            'hide_value' => __('Hide value(s)', BeRocket_AJAX_domain),
                ),
                'trailing_slash'                       => $permalink_structure,
            )
        );

        if( BeRocket_AAPF::$debug_mode ) {
            $widget_error_log['args']     = $args;
            $widget_error_log['instance'] = $instance;
        }

        if ( @ ! $instance['br_wp_footer'] ) {
            global $br_widget_ids;
            if ( ! isset( $br_widget_ids ) ) {
                $br_widget_ids = array();
            }
            $br_widget_ids[] = $instance;
        }

        extract( $args );
        extract( $instance );

        if ( ! @ $order_values_by ) {
            $order_values_by = 'Default';
        }

        if ( @ $filter_type == 'product_cat' || $filter_type == '_stock_status' ) {
            $attribute   = $filter_type;
            $filter_type = 'attribute';
        }

        $product_cat = @ json_decode( $product_cat );

        if ( $product_cat && is_product_category() ) {
            $hide_widget = true;

            $cur_cat = get_term_by( 'slug', $wp_query_product_cat, 'product_cat' );
            $cur_cat_ancestors = get_ancestors( $cur_cat->term_id, 'product_cat' );
            $cur_cat_ancestors[] = $cur_cat->term_id;

            if ( @ $cat_propagation ) {
                foreach ( $product_cat as $cat ) {
                    $cat = get_term_by( 'slug', $cat, 'product_cat' );

                    if ( @ in_array( $cat->term_id, $cur_cat_ancestors ) ) {
                        $hide_widget = false;
                        break;
                    }
                }
            } else {
                foreach ( $product_cat as $cat ) {
                    if ( $cat == $wp_query_product_cat ) {
                        $hide_widget = false;
                        break;
                    }
                }
            }


            if ( $hide_widget ) {
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['return'] = 'hide_widget';
                    BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                }
                if ( isset ( $br_wc_query ) ) {
                    $wp_query = $old_query;
                }
                return true;
            }
        }

        if ( @ $widget_type == 'update_button' ) {
            set_query_var( 'title', apply_filters( 'berocket_aapf_widget_title', $title ) );
            set_query_var( 'uo', br_aapf_converter_styles( @ $br_options['styles'] ) );
            set_query_var( 'is_hide_mobile', @ $is_hide_mobile );
            br_get_template_part( 'widget_update_button' );
            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['return'] = 'update_button';
                BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
            }
            if ( isset ( $br_wc_query ) ) {
                $wp_query = $old_query;
            }
            return '';
        }

        if ( @ $widget_type == 'selected_area' ) {
            if ( ! @ $scroll_theme ) {
                $scroll_theme = 'dark';
            }
            set_query_var( 'title', apply_filters( 'berocket_aapf_widget_title', $title ) );
            set_query_var( 'uo', br_aapf_converter_styles( @ $br_options['styles'] ) );
            set_query_var( 'selected_area_show', $selected_area_show );
            set_query_var( 'hide_selected_arrow', $hide_selected_arrow );
            set_query_var( 'selected_is_hide', $selected_is_hide );
            set_query_var( 'is_hide_mobile', @ $is_hide_mobile );
            br_get_template_part( 'widget_selected_area' );

            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['return'] = 'selected_area';
                BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
            }
            if ( isset ( $br_wc_query ) ) {
                $wp_query = $old_query;
            }
            return '';
        }

        $woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget::woocommerce_hide_out_of_stock_items();
        if( $woocommerce_hide_out_of_stock_items == 'yes' && $filter_type == 'attribute' && $attribute == '_stock_status' ) {
            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['return'] = 'stock_status';
                BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
            }
            if ( isset ( $br_wc_query ) ) {
                $wp_query = $old_query;
            }
            return true;
        }

        $terms = $sort_terms = $price_range = array();
        //debug( array( "filter_type" => $filter_type, "attribute" => $attribute ) );
        if ( $filter_type == 'attribute' ) {
            if ( $type == 'ranges' ) {
                if ( count( $ranges ) < 2 ) {
                    if( BeRocket_AAPF::$debug_mode ) {
                        $widget_error_log['ranges'] = $ranges;
                        $widget_error_log['return'] = 'ranges < 2';
                        BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                    }
                    if ( isset ( $br_wc_query ) ) {
                        $wp_query = $old_query;
                    }
                    return false;
                }
                $terms = array();
                $ranges[0]--;
                for ( $i = 1; $i < count( $ranges ); $i++ ) {
                    $t_id = ($ranges[$i - 1] + 1).'*'.$ranges[$i];
                    $t_name = ( ( @ $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ).$text_before_price.($ranges[$i - 1] + 1).$text_after_price.( ( @ $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' ).
                    ' - '.
                    ( ( @ $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_before_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_before_value.'" alt=""></i>' ) : '' ).$text_before_price.$ranges[$i].$text_after_price.( ( @ $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3) == 'fa-' ) ? '<i class="fa '.$icon_after_value.'"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="'.$icon_after_value.'" alt=""></i>' ) : '' );
                    $term = array( 'term_id' => $t_id, 'slug' => $t_id, 'name' => $t_name, 'count' => 1, 'taxonomy' => $attribute );
                    $terms[] = (object)$term;
                }
                set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = $terms;
                }
            } elseif ( $attribute == 'price' ) {
                if ( @ $price_values ) {
                    $price_range = @ explode( ",", $price_values );
                } else {
                    $price_range = br_get_cache( 'price_range', $wp_check_product_cat, $br_options['object_cache'] );
                    if ( $price_range === false ) {
                        $price_range = BeRocket_AAPF_Widget::get_price_range( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );
                        br_set_cache( 'price_range', $price_range, $wp_check_product_cat, BeRocket_AJAX_cache_expire, $br_options['object_cache'] );
                    }
                    if ( ! $price_range or count( $price_range ) < 2 ) {
                        if( BeRocket_AAPF::$debug_mode ) {
                            $widget_error_log['price_range'] = $price_range;
                            $widget_error_log['return'] = 'price_range < 2';
                            BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                        }
                        if ( isset ( $br_wc_query ) ) {
                            $wp_query = $old_query;
                        }
                        return false;
                    }
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['price_range'] = $price_range;
                }
            } elseif ( $attribute == '_stock_status' ) {
                if( $type != 'select' && $type != 'slider' ) {
                    array_push($terms, (object)array('term_id' => '0', 'name' => __('Any', BeRocket_AJAX_domain), 'slug' => '', 'taxonomy' => '_stock_status'));
                }
                array_push($terms, (object)array('term_id' => '1', 'name' => __('In stock', BeRocket_AJAX_domain), 'slug' => 'instock', 'taxonomy' => '_stock_status'));
                array_push($terms, (object)array('term_id' => '2', 'name' => __('Out of stock', BeRocket_AJAX_domain), 'slug' => 'outofstock', 'taxonomy' => '_stock_status'));
                set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = $terms;
                }
            } elseif ( $attribute == 'product_cat' ) {
                $parent_product_cat_cache = $parent_product_cat;
                $terms = br_get_cache ( $attribute . $order_values_by, $wp_check_product_cat . $parent_product_cat_cache . $depth_count, $br_options['object_cache'] );
                if ( br_is_filtered() || $terms === false ) {
                    $terms_unsort = self::get_product_categories( '', $parent_product_cat, array(), 0, $depth_count, true );

                    self::sort_terms( $terms_unsort, array(
                        "order_values_by" => $order_values_by,
                        "attribute"       => $attribute,
                    ) );

                    $terms_unsort = self::set_terms_on_same_level( $terms_unsort );
                    $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], $terms_unsort, @ $cat_value_limit );
                    if ( ! br_is_filtered() ) {
                        br_set_cache( $attribute.$order_values_by, $terms, $wp_check_product_cat.$parent_product_cat_cache.$depth_count, BeRocket_AJAX_cache_expire, $br_options['object_cache'] );
                    }
                }

                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = $terms;
                }
                set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );
                unset( $terms, $terms_unsort );
            } else {
                $sort_array  = array();
                $wc_order_by = wc_attribute_orderby( $attribute );

                $terms = br_get_cache ( $attribute, $wp_check_product_cat, $br_options['object_cache'] );
                if( br_is_filtered() || $terms === false || @ $child_parent == 'parent' || @ $child_parent == 'child' ) {
                    $current_terms = self::get_terms_child_parent ( @ $child_parent, $attribute, FALSE, @ $child_parent_depth );
                    $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], $current_terms, @ $cat_value_limit );
                    if( ! br_is_filtered() && @ $child_parent != 'parent' && @ $child_parent != 'child' ) {
                        br_set_cache ( $attribute, $terms, $wp_check_product_cat, BeRocket_AJAX_cache_expire, $br_options['object_cache'] );
                    }
                }

                if ( @ count( $terms ) < 1 ) {
                    if( BeRocket_AAPF::$debug_mode ) {
                        $widget_error_log['terms'] = @ $terms;
                        $widget_error_log['return'] = 'terms < 1';
                        BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                    }
                    if ( isset ( $br_wc_query ) ) {
                        $wp_query = $old_query;
                    }
                    return false;
                }

                if ( $wc_order_by == 'menu_order' and $order_values_by == 'Default' ) {
                    foreach ( $terms as $term ) {
                        $sort_array[] = get_woocommerce_term_meta( @ $term->term_id, 'order_' . $attribute );
                    }
                    array_multisort( $sort_array, $terms );
                } else {
                    self::sort_terms( $terms, array(
                        "wc_order_by"     => $wc_order_by,
                        "order_values_by" => $order_values_by,
                        "filter_type"     => $filter_type,
                    ) );
                }

                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = $terms;
                }
                set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );
            }

        } elseif ( $filter_type == 'tag' ) {
            $attribute = 'product_tag';
            $terms = br_get_cache ( $attribute.$order_values_by, $wp_check_product_cat, $br_options['object_cache'] );
            if( br_is_filtered() || $terms === false ) {
                $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], FALSE, @ $cat_value_limit );

                if ( $order_values_by != 'Default' ) {
                    self::sort_terms( $terms, array(
                        "order_values_by" => $order_values_by,
                        "attribute"       => $attribute,
                    ) );
                }
                if( ! br_is_filtered() ) {
                    br_set_cache ( $attribute.$order_values_by, $terms, $wp_check_product_cat, BeRocket_AJAX_cache_expire, $br_options['object_cache'] );
                }
            }

            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['terms'] = $terms;
            }
            set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );

            if ( @ count( $terms ) < 1 ) {
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = @ $terms;
                    $widget_error_log['return'] = 'terms < 1';
                    BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                }
                if ( isset ( $br_wc_query ) ) {
                    $wp_query = $old_query;
                }
                return false;
            }
        } elseif ( $filter_type == 'custom_taxonomy' ) {
            $terms = br_get_cache ( $custom_taxonomy.$order_values_by, $filter_type.$wp_check_product_cat, $br_options['object_cache'] );
            if( br_is_filtered() || $terms === false || @ $child_parent == 'parent' || @ $child_parent == 'child' ) {
                if ( $custom_taxonomy == 'product_cat' ) {
                    $terms_unsort = self::get_product_categories( '', 0, array(), 0, 50, true );
                    $terms_unsort = self::get_terms_child_parent ( @ $child_parent, $custom_taxonomy, $terms_unsort, @ $child_parent_depth );

                    if ( $order_values_by != 'Default' ) {
                        self::sort_terms( $terms_unsort, array(
                            "order_values_by" => $order_values_by,
                            "attribute"       => $attribute,
                        ) );
                    }

                    if ( @ $child_parent != 'parent' && @ $child_parent != 'child' ) {
                        $terms_unsort = self::set_terms_on_same_level( $terms_unsort );
                    }
                    $terms = BeRocket_AAPF_Widget::get_attribute_values( $custom_taxonomy, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], $terms_unsort, @ $cat_value_limit );
                } else {
                    $terms = self::get_terms_child_parent ( @ $child_parent, $custom_taxonomy, FALSE, @ $child_parent_depth );
                    $terms = BeRocket_AAPF_Widget::get_attribute_values( $custom_taxonomy, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], $terms, @ $cat_value_limit );

                    if ( $order_values_by != 'Default' ) {
                        self::sort_terms( $terms, array(
                            "order_values_by" => $order_values_by,
                        ) );
                        if ( ! br_is_filtered() && @ $child_parent != 'parent' && @ $child_parent != 'child' ) {
                            br_get_cache( $custom_taxonomy . $order_values_by, $terms, $filter_type . $wp_check_product_cat, BeRocket_AJAX_cache_expire, $br_options['object_cache'] );
                        }
                    }
                }
            }

            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['terms'] = $terms;
            }
            set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );

            if ( @ count( $terms ) < 1 ) {
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['terms'] = @ $terms;
                    $widget_error_log['return'] = 'terms < 1';
                    BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                }
                if ( isset ( $br_wc_query ) ) {
                    $wp_query = $old_query;
                }
                return false;
            }
        }

        $style = $class = '';
        if( @$height and $height != 'auto' ){
            $style = "style='max-height: {$height}px; overflow: hidden;'";
            $class = "berocket_aapf_widget_height_control";
        }

        if( !$scroll_theme ) $scroll_theme = 'dark';
        if( $filter_type == 'custom_taxonomy' )
            $attribute = $custom_taxonomy;

        set_query_var( 'operator', $operator );
        set_query_var( 'attribute', $attribute );
        set_query_var( 'type', $type );
        set_query_var( 'title', apply_filters( 'berocket_aapf_widget_title', $title ) );
        set_query_var( 'class', apply_filters( 'berocket_aapf_widget_class', $class ) );
        set_query_var( 'css_class', apply_filters( 'berocket_aapf_widget_css_class', @ $css_class ) );
        set_query_var( 'style', apply_filters( 'berocket_aapf_widget_style', $style ) );
        set_query_var( 'scroll_theme', $scroll_theme );
        set_query_var( 'x', time() );
        set_query_var( 'filter_type', $filter_type );
        set_query_var( 'uo', br_aapf_converter_styles( @ $br_options['styles'] ) );
        set_query_var( 'notuo', @ $br_options['styles'] );
        set_query_var( 'widget_is_hide', @ $widget_is_hide );
        set_query_var( 'is_hide_mobile', @ $is_hide_mobile );
        set_query_var( 'show_product_count_per_attr', @ $show_product_count_per_attr );
        set_query_var( 'cat_value_limit', @ $cat_value_limit );
        set_query_var( 'icon_before_title', @ $icon_before_title );
        set_query_var( 'icon_after_title', @ $icon_after_title );
        set_query_var( 'hide_o_value', @ $br_options['hide_value']['o'] );
        set_query_var( 'hide_sel_value', @ $br_options['hide_value']['sel'] );
        set_query_var( 'description', @ $description );
        set_query_var( 'hide_collapse_arrow', @ $hide_collapse_arrow );
        set_query_var( 'values_per_row', @ $values_per_row );
        set_query_var( 'child_parent', @ $child_parent );
        set_query_var( 'child_parent_depth', @ $child_parent_depth );
        set_query_var( 'product_count_style', @ $br_options['styles_input']['product_count'].'pcs '.@ $br_options['styles_input']['product_count_position'].'pcs' );

        // widget title and start tag ( <ul> ) can be found in templates/widget_start.php
        do_action('berocket_aapf_widget_before_start');
        br_get_template_part('widget_start');
        do_action('berocket_aapf_widget_after_start');

        $slider_with_string = false;
        $stringed_is_numeric = true;
        $slider_step = 1;

        if ( $type == 'slider' ) {
            $min = $max   = false;
            $main_class   = 'slider';
            $slider_class = 'berocket_filter_slider';

            if( $attribute == 'price' ){
                wp_localize_script(
                    'berocket_aapf_widget-script',
                    'br_price_text',
                    array(
                        'before'  => @ $text_before_price,
                        'after'   => @ $text_after_price,
                    )
                );
                if ( @ $price_values ) {
                    $all_terms_name = $price_range;
                    $stringed_is_numeric = true;
                    $min = 0;
                    $max = count( $all_terms_name ) - 1;
                    $slider_with_string = true;
                } else {
                    if( $price_range ) {
                        foreach ( $price_range as $price ) {
                            if ( $min === false or $min > (int) $price ) {
                                $min = $price;
                            }
                            if ( $max === false or $max < (int) $price ) {
                                $max = $price;
                            }
                        }
                    }
                    if( $use_min_price && $min_price <= $min ) {
                        $min = $min_price;
                    }
                    if ( $use_max_price && $max_price >= $max ) {
                        $max = $max_price;
                    }
                }
                $id = 'br_price';
                $slider_class .= ' berocket_filter_price_slider';
                $main_class .= ' price';

                $min = floor( $min );
                $max = ceil( $max );
            } else {
                if ( @ count( $terms ) < 1 ) {
                    if( BeRocket_AAPF::$debug_mode ) {
                        $widget_error_log['terms'] = @ $terms;
                        $widget_error_log['return'] = 'terms < 1';
                        BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
                    }
                    if ( isset ( $br_wc_query ) ) {
                        $wp_query = $old_query;
                    }
                    return false;
                }
                if( @ $terms ) {
                    $all_terms_name = array();
                    foreach ( $terms as $term ) {
                        if ( ! is_numeric( $term->name ) ) {
                            $slider_with_string = true;
                            if ( ! is_numeric( substr( $term->name, 0, 1 ) ) ) {
                                $stringed_is_numeric = false;
                            }
                        }
                        if ( $min === false or strcmp( $min, $term->name ) > 0 ) {
                            $min = $term->name;
                        }
                        if ( $max === false or strcmp( $max, $term->name ) < 0 ) {
                            $max = $term->name;
                        }
                        array_push( $all_terms_name, $term->name );
                    }

                    if ( ! $slider_with_string ) {
                        $min = false;
                        $max = false;
                        foreach ( $terms as $term ) {
                            if ( (float) $term->name != (int) (float) $term->name ) {
                                if ( round( (float) $term->name, 1 ) == (float) $term->name && $slider_step != 0.01 ) {
                                    $slider_step = 10;
                                } else {
                                    $slider_step = 100;
                                }
                            }
                            if ( $min === false or $min > (float) $term->name ) {
                                $min = round( (float) $term->name, 2 );
                                if ( $min > (float) $term->name ) {
                                    $max -= 0.01;
                                }
                            }
                            if ( $max === false or $max < (float) $term->name ) {
                                $max = round( (float) $term->name, 2 );
                                if ( $max < (float) $term->name ) {
                                    $max += 0.01;
                                }
                            }
                        }
                    }
                }

                $id = $term->taxonomy;
                if ( ! $slider_with_string ) {
                    $min *= $slider_step;
                    $max *= $slider_step;
                    $all_terms_name = null;
                } else {
                    if ( count( $all_terms_name ) == 1 ) {
                        array_push( $all_terms_name, $all_terms_name[0] );
                    }
                    $min = 0;
                    $max = count( $all_terms_name ) - 1;
                    if( $stringed_is_numeric ) {
                        sort( $all_terms_name, SORT_NUMERIC );
                    } else {
                        sort( $all_terms_name );
                    }
                }
            }

            $slider_value1 = $min;
            $slider_value2 = $max;

            if ( $attribute == 'price' and @ $_POST['price'] ) {
                if ( @ $price_values ) {
                    $slider_value1 = array_search( $_POST['price'][0], $all_terms_name );
                    $slider_value2 = array_search( $_POST['price'][1], $all_terms_name );
                } else {
                    $slider_value1 = $_POST['price'][0];
                    $slider_value2 = $_POST['price'][1];
                }
            }
            if ( $attribute != 'price' and @ $_POST['limits'] ) {
                foreach ( $_POST['limits'] as $p_limit ) {
                    if ( $p_limit[0] == $attribute ) {
                        $slider_value1 = $p_limit[1];
                        $slider_value2 = $p_limit[2];
                        if ( ! $slider_with_string ) {
                            $slider_value1 *= $slider_step;
                            $slider_value2 *= $slider_step;
                        } else {
                            $p_limit[1] = urldecode( $p_limit[1] );
                            $p_limit[2] = urldecode( $p_limit[2] );
                            $slider_value1 = array_search( $p_limit[1], $all_terms_name );
                            $slider_value2 = array_search( $p_limit[2], $all_terms_name );
                        }
                    }
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['value_1'] = $slider_value1;
                    $widget_error_log['value_2'] = $slider_value2;
                    $widget_error_log['step'] = $slider_step;
                }
            }

            set_query_var( 'slider_value1', $slider_value1 );
            set_query_var( 'slider_value2', $slider_value2 );
            set_query_var( 'filter_slider_id', $id );
            set_query_var( 'main_class', $main_class );
            set_query_var( 'slider_class', $slider_class );
            set_query_var( 'min', $min );
            set_query_var( 'max', $max );
            set_query_var( 'step', $slider_step );
            set_query_var( 'slider_with_string', $slider_with_string );
            set_query_var( 'all_terms_name', @ $all_terms_name );
            set_query_var( 'text_before_price', @ $text_before_price );
            set_query_var( 'text_after_price', @ $text_after_price );
        }
        set_query_var( 'first_page_jump', @ $first_page_jump );
        set_query_var( 'icon_before_value', @ $icon_before_value );
        set_query_var( 'icon_after_value', @ $icon_after_value );

        if ( $type == 'tag_cloud' ) {
            $tag_script_var = array(
                'height'        => $tag_cloud_height,
                'min_font_size' => $tag_cloud_min_font,
                'max_font_size' => $tag_cloud_max_font,
                'tags_count'    => $tag_cloud_tags_count
            );
            set_query_var( 'tag_script_var', $tag_script_var );
        } elseif ( $type == 'color' || $type == 'image' ) {
            set_query_var( 'use_value_with_color', @ $use_value_with_color );
        }

        br_get_template_part( $type );

        do_action('berocket_aapf_widget_before_end');
        br_get_template_part('widget_end');
        do_action('berocket_aapf_widget_after_end');
        if( BeRocket_AAPF::$debug_mode ) {
            $widget_error_log['return'] = 'OK';
            $widget_error_log['terms'] = @ $terms;
            BeRocket_AAPF::$error_log['6_widgets'][] = $widget_error_log;
        }
        if ( isset ( $br_wc_query ) ) {
            $wp_query = $old_query;
        }
    }

    public static function woocommerce_hide_out_of_stock_items(){
        $hide = get_option( 'woocommerce_hide_out_of_stock_items', null );

        if ( is_array( $hide ) ) {
            $hide = array_map( 'stripslashes', $hide );
        } elseif ( ! is_null( $hide ) ) {
            $hide = stripslashes( $hide );
        }

        return apply_filters( 'berocket_aapf_hide_out_of_stock_items', $hide );
    }

    public static function get_price_range( $wp_query_product_cat ){
        global $wpdb;
        $_POST['product_cat'] = $wp_query_product_cat;
        $extra_inner = $extra_where = '';

        if ( @ $_POST['product_cat'] and $_POST['product_cat'] != '-1' ) {
            $sub_categories = br_get_sub_categories( strip_tags( $_POST['product_cat'] ), 'slug', array( 'include_parent' => true ), 'term_taxonomy_id' );

            $extra_inner = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id) ";
            $extra_where = " AND ( {$wpdb->term_relationships}.term_taxonomy_id IN (" . $sub_categories . ") ) ";

            unset( $sub_categories );
        }
        $hide_out_of_stock = BeRocket_AAPF_Widget::woocommerce_hide_out_of_stock_items();
        $out_of_stock1 = '';
        $out_of_stock2 = '';
        if ( $hide_out_of_stock == 'yes' ) {
            $out_of_stock1 = "INNER JOIN {$wpdb->postmeta} AS pm2 ON ({$wpdb->posts}.ID = pm2.post_id)";
            $out_of_stock2 = "AND ( pm2.meta_key = '_stock_status' AND CAST(pm2.meta_value AS CHAR) = 'instock' )";
        }
        

        $query_string = "
                SELECT {$wpdb->postmeta}.meta_value
                FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)
                INNER JOIN {$wpdb->postmeta} AS pm1 ON ({$wpdb->posts}.ID = pm1.post_id)
                {$out_of_stock1}
                {$extra_inner}
                WHERE {$wpdb->posts}.post_type = 'product'
                AND {$wpdb->posts}.post_status = 'publish'
                {$extra_where}
                AND ( {$wpdb->postmeta}.meta_key = '_price' AND {$wpdb->postmeta}.meta_value > 0
                AND ( pm1.meta_key = '_visibility' AND CAST(pm1.meta_value AS CHAR) IN ('visible','catalog') )
                {$out_of_stock2} ) 
                GROUP BY {$wpdb->postmeta}.meta_value
                ORDER BY cast({$wpdb->postmeta}.meta_value as unsigned)";

        if( BeRocket_AAPF::$debug_mode ) {
            $wpdb->show_errors();
            BeRocket_AAPF::$error_log['101_price_range_SELECT'] = $query_string;
        }

        $query_string2 = $query_string . " ASC LIMIT 1";
        $prices = $wpdb->get_results($query_string2);

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['000_select_status_1'][] = @ $wpdb->last_error;
            BeRocket_AAPF::$error_log['000_select_status_1'][] = $prices;
        }

        $query_string2 = $query_string . " DESC LIMIT 1";
        if ( @ $prices && is_array( $prices ) ) {
            $prices = array_merge( $prices, $wpdb->get_results($query_string2) );
        }

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['000_select_status_2'][] = @ $wpdb->last_error;
            BeRocket_AAPF::$error_log['000_select_status_2'][] = $prices;
        }

        $price_range = array();

        if ( @ $prices and is_array( $prices ) ) {
            foreach ( $prices as $price ) {
                $price_range[] = $price->meta_value;
            }
            $price_range = array_unique( $price_range );
        }
        unset( $prices );

        if ( @ count( $price_range ) < 2 ) {
            $price_range = false;
        }
        return apply_filters( 'berocket_aapf_get_price_range', $price_range );
    }

    public static function get_attribute_values( $taxonomy = '', $order_by = 'id', $hide_empty = false, $count_filtering = true, $input_terms = FALSE, $product_cat = FALSE ) {
if( BeRocket_AAPF::$debug_mode ) {
    if( ! isset( BeRocket_AAPF::$error_log['6_term_recount'] ) )
    {
        BeRocket_AAPF::$error_log['6_term_recount'] = array();
    } 
    $term_recount_log = array();
}
        if ( ! $taxonomy || $taxonomy == 'price' ) return array();

        global $wp_query;

        if( $product_cat || $hide_empty) {
            $terms                 = array();
            $q_args                = $wp_query->query_vars;
            $q_args['nopaging']    = true;
            $q_args['post__in']    = '';
            $q_args['tax_query']   = '';
            $q_args['product_tag'] = '';
            $q_args['taxonomy']    = '';
            $q_args['term']        = '';
            $q_args['meta_query']  = '';
            $q_args['fields']      = 'ids';
            if ( $product_cat ) {
                $q_args['product_cat'] = $product_cat;
            }
            $the_query             = new WP_Query( $q_args );

            $term_count = array();
            foreach ( $the_query->posts as $post_id ) {
                $curent_terms = wp_get_object_terms( $post_id, $taxonomy );

                foreach ( $curent_terms as $t ) {
                    if ( ! in_array( $t->term_id, $terms ) ) {
                        $terms[] = $t->term_id;
                    }
                    if ( isset( $term_count[$t->term_id] ) ) {
                        $term_count[$t->term_id]++;
                    } else {
                        $term_count[$t->term_id] = 1;
                    }
                }
            }
        }
        if ( $hide_empty ) {

            unset( $curent_terms, $the_query );

            $args = array(
                'orderby'    => $order_by,
                'order'      => 'ASC',
                'hide_empty' => false,
            );

            if ( $input_terms === FALSE ) {
                $terms2 = get_terms( $taxonomy, $args );
            } else {
                $terms2 = $input_terms;
                unset( $input_terms );
            }

            $re = array();
            foreach ( $terms2 as $t ) {
                if ( in_array( $t->term_id, $terms ) ) {
                    @ $re[$t->term_id] = $t;
                    @ $re[$t->term_id]->count = $term_count[$t->term_id];
                }
            }

            unset( $term2 );
        } else {
            $args = array(
                'orderby'    => $order_by,
                'order'      => 'ASC',
                'hide_empty' => false,
            );
            if ( $input_terms === FALSE ) {
                $re = get_terms( $taxonomy, $args );
            } else {
                $re = $input_terms;
                unset( $input_terms );
            }
            if( $product_cat ) {
                foreach ( $re as $key => $t ) {
                    if( isset( $term_count[$re[$key]->term_id] ) ) {
                        $re[$key]->count = $term_count[$re[$key]->term_id];
                    } else {
                        $re[$key]->count = 0;
                    }
                }
            }
        }
if( BeRocket_AAPF::$debug_mode ) {
    $term_recount_log['before_recount'] = $re;
}
        if ( $count_filtering ) {
            $q_args = $wp_query->query_vars;
            $q_args['nopaging'] = true;
            if ( $product_cat ) {
                $q_args['product_cat'] = $product_cat;
            }
            if ( is_array( @ $q_args['tax_query'] ) ) {
                foreach( $q_args['tax_query'] as $key => $val ) {
                    if ( isset( $val['taxonomy'] ) && $val['taxonomy'] == $taxonomy ) {
                        if ( isset( $q_args['tax_query'][ $key ] ) ) unset( $q_args['tax_query'][ $key ] );
                    }
                }
            }

            $q_args['taxonomy']    = '';
            $q_args['term']        = '';
            $args                  = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
            $q_args['product_tag'] = @ $args['product_tag'];

            if ( $taxonomy == 'product_tag' ) {
                $q_args['product_tag'] = '';
            }
            $q_args['fields'] = 'ids';

            $the_query = new WP_Query( $q_args );
            $count_terms = array();

            //debug( "1---------------------" );
            //debug( microtime() );
            foreach ( $the_query->posts as $post ) {
                $curent_terms = br_wp_get_object_terms( $post, $taxonomy, array( "fields" => "ids" ) );
                foreach ( $curent_terms as $t ) {
                    if ( isset( $count_terms[$t] ) ) {
                        $count_terms[$t] += 1;
                    } else {
                        $count_terms[$t] = 1;
                    }
                }
            }
            //debug( "2---------------------" );
            //debug( microtime() );
if( BeRocket_AAPF::$debug_mode ) {
    $term_recount_log['count'] = $count_terms;
}
            unset( $the_query, $curent_terms, $q_args, $post );
            if ( @ is_array( $re ) ) {
                foreach ( $re as $i => $re_val ) {
                    @ $re[$i]->count = 0;
                    if ( isset( $count_terms[$re[$i]->term_id] ) ) {
                        $re[$i]->count = $count_terms[$re[$i]->term_id];
                    }

                    $children = get_term_children( $re[$i]->term_id, $taxonomy );
                    $children_count = 0;
                    if( is_array( $children ) ) {
                        foreach ( $children as $child ) {
                            $children_count += @ $count_terms[$child];
                        }
                    }
                    $re[$i]->count += ( $children_count );
                }
            }
            unset( $children_count, $children, $count_terms );
        }
        $re = array_values( $re );
if( BeRocket_AAPF::$debug_mode ) {
    $term_recount_log['after_recount'] = $re;
    BeRocket_AAPF::$error_log['6_term_recount'][] = $term_recount_log;
}
        return $re;
    }

    public static function sort_terms( &$terms, $sort_data ) {
        $sort_array = array();

        if ( @ count( $terms ) ) {
            if ( ( @ $sort_data['wc_order_by'] or @ $sort_data['order_values_by'] ) ) {
                if ( @ $sort_data['wc_order_by'] == 'name' and @ $sort_data['order_values_by'] == 'Default' or @ $sort_data['order_values_by'] == 'Alpha' ) {
                    foreach ( $terms as $term ) {
                        $sort_array[] = strtolower($term->name);
                        if ( @ $term->child ) {
                            self::sort_terms( $term->child, $sort_data );
                        }
                    }
                    array_multisort( $sort_array, $terms, SORT_ASC, SORT_STRING );
                } elseif ( @ $sort_data['wc_order_by'] == 'name_num' and @ $sort_data['order_values_by'] == 'Default' or @ $sort_data['order_values_by'] == 'Numeric' ) {
                    foreach ( $terms as $term ) {
                        $sort_array[] = (float) $term->name;
                        if ( @ $term->child ) {
                            self::sort_terms( $term->child, $sort_data );
                        }
                    }
                    array_multisort( $sort_array, $terms, SORT_ASC, SORT_NUMERIC );
                }
            }
        }
    }

    public static function set_terms_on_same_level( $terms, $return_array = array() ) {
        if ( @ count( $terms ) ) {
            foreach ( $terms as $term ) {
                if ( $term->depth > 0 ) {
                    for ( $i = 0; $i < $term->depth; $i++ ) {
                        $term->name = "&nbsp;&nbsp;" . $term->name;
                    }
                }
                $child = @$term->child;
                unset( $term->child );

                $return_array[] = $term;

                if ( @ $child ) {
                    $return_array = self::set_terms_on_same_level( $child, $return_array );
                }
            }
        } else {
            $return_array = @ $terms;
        }
        return $return_array;
    }

    public static function get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items, $use_filters = true ) {
        global $wp_query, $wp_rewrite;
        $_POST['product_cat'] = $wp_query_product_cat;

        $old_post_terms = @ $_POST['terms'];

        add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        $tags = @ $args['product_tag'];
        $meta_query = BeRocket_AAPF::remove_out_of_stock( array() , true, $woocommerce_hide_out_of_stock_items != 'yes' );
        $args['post__in'] = array();

        if( $woocommerce_hide_out_of_stock_items == 'yes' ) {
            $args['post__in'] = BeRocket_AAPF::remove_out_of_stock( $args['post__in'] );
        }
        if ( $use_filters ) {
            $args['post__in'] = BeRocket_AAPF::limits_filter( $args['post__in'] );
            $args['post__in'] = BeRocket_AAPF::price_filter( $args['post__in'] );
        } else {
            $args = array( 'posts_per_page' => -1 );
            if ( @$_POST['product_cat'] and $_POST['product_cat'] != '-1' ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => strip_tags( $_POST['product_cat'] ),
                    'operator' => 'IN'
                );
            }
        }

        $args['post_status'] = 'publish';
        $args['post_type'] = 'product';

        $wp_query = new WP_Query( $args );

        // here we get max products to know if current page is not too big
        if ( $wp_rewrite->using_permalinks() and preg_match( "~/page/([0-9]+)~", @ $_POST['location'], $mathces ) or preg_match( "~paged?=([0-9]+)~", @ $_POST['location'], $mathces ) ) {
            $args['paged'] = min( $mathces[1], $wp_query->max_num_pages );
            $wp_query = new WP_Query( $args );
        }
        if ( $wp_query->found_posts <= 1 ) {
            $args['paged'] = 0;
            $wp_query = new WP_Query( $args );
        }

        $products = array();
        if ( $wp_query->have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $products[] = get_the_ID();
            }
        }

        wp_reset_query();
        if( @ $meta_query && is_array( $meta_query ) && count( $meta_query ) > 0 ) {
            $q_vars = $wp_query->query_vars;
            foreach( $q_vars['meta_query'] as $key_meta => $val_meta ) {
                if( $key_meta != 'relation' && $val_meta['key'] == '_stock_status') {
                    unset( $q_vars['meta_query'][$key_meta] );
                }
            }
            $q_vars['meta_query'] = array_merge( $q_vars['meta_query'], $meta_query );
            $wp_query->set('meta_query', $q_vars['meta_query']);
        }
        if( @ $tags ) {
            $q_vars = $wp_query->query_vars;
            $q_vars['product_tag'] = $tags;
            unset($q_vars['s']);
            $wp_query = new WP_Query( $q_vars );
        }

        $_POST['terms'] = $old_post_terms;
        return $products;
    }

    /**
     * Validating and updating widget data
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array - new merged instance
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        /* Strip tags (if needed) and update the widget settings. */
        $instance['widget_type']         = strip_tags( $new_instance['widget_type'] );
        $instance['title']               = strip_tags( $new_instance['title'] );
        $instance['attribute']           = strip_tags( $new_instance['attribute'] );
        $instance['type']                = strip_tags( $new_instance['type'] );
        $instance['product_cat']         = ( @ $new_instance['product_cat'] ) ? json_encode( $new_instance['product_cat'] ) : '';
        $instance['scroll_theme']        = strip_tags( $new_instance['scroll_theme'] );
        $instance['cat_propagation']     = (int) $new_instance['cat_propagation'];
        $instance['css_class']           = strip_tags( $new_instance['css_class'] );
        $instance['text_before_price']   = strip_tags( $new_instance['text_before_price'] );
        $instance['text_after_price']    = strip_tags( $new_instance['text_after_price'] );
        $instance['filter_type']         = strip_tags( $new_instance['filter_type'] );
        $instance['custom_taxonomy']     = strip_tags( $new_instance['custom_taxonomy'] );
        $instance['filter_by']           = strip_tags( $new_instance['filter_by'] );
        $instance['widget_is_hide']      = (int) $new_instance['widget_is_hide'];
        $instance['is_hide_mobile']      = (int) $new_instance['is_hide_mobile'];
        $instance['description']         = strip_tags( $new_instance['description'] );
        $instance['parent_product_cat']  = strip_tags( $new_instance['parent_product_cat'] );
        $instance['order_values_by']     = strip_tags( $new_instance['order_values_by'] );
        $instance['hide_collapse_arrow'] = (int) $new_instance['hide_collapse_arrow'];
        $instance['selected_area_show']  = (int) $new_instance['selected_area_show'];
        $instance['selected_is_hide']    = (int) $new_instance['selected_is_hide'];
        $instance['hide_selected_arrow'] = (int) $new_instance['hide_selected_arrow'];
        $instance['show_page']           = $new_instance['show_page'];
        $instance['cat_value_limit']     = $new_instance['cat_value_limit'];
        $instance['child_parent']        = $new_instance['child_parent'];
        $instance['child_parent_depth']  = (int) $new_instance['child_parent_depth'];
        if ( ! $instance['child_parent_depth'] || $instance['child_parent_depth'] < 1 ) {
            $instance['child_parent_depth'] = 1;
        }
        if( $instance['type'] == 'slider' or $instance['type'] == 'tag_cloud' or ( $instance['filter_type'] == '_stock_status' and $instance['widget_type'] == 'filter' ) or ( $instance['filter_type'] == 'tag' and $instance['widget_type'] == 'filter' ) or ( $instance['filter_type'] == 'product_cat' and $instance['widget_type'] == 'filter' ) ) {
            $instance['child_parent'] = '';
        }

        if ( $instance['type'] == 'slider' or $instance['type'] == 'select' or $instance['type'] == 'tag_cloud' ) {
            $instance['values_per_row']  = 1;
        } else {
            $instance['values_per_row']  = $new_instance['values_per_row'];
        }

        if( $new_instance['height'] != 'auto' ) $new_instance['height'] = (float) $new_instance['height'];
        if( !$new_instance['height'] ) $new_instance['height'] = 'auto';
        $instance['height'] = $new_instance['height'];

        if( $new_instance['operator'] != 'OR' ) $new_instance['operator'] = 'AND';
        $instance['operator'] = $new_instance['operator'];

        if ( $instance['filter_type'] == 'tag' and $instance['type'] == 'tag_cloud' ) {
            $instance['tag_cloud_height']     = (int) $new_instance['tag_cloud_height'];
            $instance['tag_cloud_min_font']   = (int) $new_instance['tag_cloud_min_font'];
            $instance['tag_cloud_max_font']   = (int) $new_instance['tag_cloud_max_font'];
            $instance['tag_cloud_tags_count'] = (int) $new_instance['tag_cloud_tags_count'];
        }
        if ( $instance['type'] != 'slider' or $instance['type'] != 'tag_cloud' ) {
            $instance['show_product_count_per_attr'] = (int) $new_instance['show_product_count_per_attr'];
        }
        if ( $instance['filter_type'] == 'product_cat' ) {
            $instance['depth_count'] = (int) $new_instance['depth_count'];
            if( $instance['depth_count'] < 1 )
                $instance['depth_count'] = 1;
        }
        if ( $instance['filter_type'] == 'attribute' and $instance['attribute'] == 'price' ) {
            $instance['use_min_price'] = (int) $new_instance['use_min_price'];
            if((int) $new_instance['min_price'] >= 0) {
                $instance['min_price'] = (int) $new_instance['min_price'];
            } else {
                $instance['min_price'] = 0;
            }
            $instance['use_max_price'] = (int) $new_instance['use_max_price'];
            if((int) $new_instance['max_price'] >= 1) {
                $instance['max_price'] = (int) $new_instance['max_price'];
            } else {
                $instance['max_price'] = 1;
            }
        }

        if( ( $instance['filter_type'] == 'attribute' or $instance['filter_type'] == 'custom_taxonomy' ) and ( $instance['type'] == 'color' or $instance['type'] == 'image' ) ) {
            $instance['use_value_with_color'] = (int) $new_instance['use_value_with_color'];
            $_POST['tax_color_name']          = $instance['attribute'];
            $_POST['type']                    = $instance['type'];
            $_POST['tax_color_set']           = $_POST['br_widget_color'];
            BeRocket_AAPF_Widget::color_listener();
        }

        $instance['icon_after_title']  = $new_instance['icon_after_title'];
        $instance['icon_before_title'] = $new_instance['icon_before_title'];
        $instance['icon_after_value']  = $new_instance['icon_after_value'];
        $instance['icon_before_value'] = $new_instance['icon_before_value'];

        $instance['price_values'] = '';
        if ( $price_values = trim( $new_instance['price_values'] ) ) {
            $price_values = explode( ",", $price_values );

            foreach ( $price_values as $price_value ) {
                $instance['price_values'][] = (float) trim( $price_value );
            }

            $instance['price_values'] = array_unique( $instance['price_values'] );
            sort( $instance['price_values'], SORT_NUMERIC );

            $instance['price_values'] = implode( ",", $instance['price_values'] );
        }

        $instance['ranges'] = array();
        if ( isset( $new_instance['ranges'] ) && is_array( $new_instance['ranges'] ) ) {
            foreach ( $new_instance['ranges'] as $range ) {
                $range = (int) $range;
                if ( $range < 1 ) {
                    $range = 1;
                } 
                $instance['ranges'][] = $range;
            }
        }

        do_action( 'berocket_aapf_admin_update', $instance, $new_instance, $old_instance );

        return apply_filters( 'berocket_aapf_admin_update_instance', $instance );
    }

    /**
     * Output admin form
     *
     * @param array $instance
     *
     * @return string|void
     */
    function form( $instance ) {
        wp_enqueue_script( 'berocket_aapf_widget-admin-colorpicker', plugins_url( '../js/colpick.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_enqueue_script( 'berocket_aapf_widget-admin-script', plugins_url('../js/admin.js', __FILE__), array('jquery'), BeRocket_AJAX_filters_version );

        wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( '../css/colpick.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );

        wp_register_style( 'berocket_aapf_widget-style', plugins_url('../css/admin.css', __FILE__), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        $default = apply_filters( 'berocket_aapf_form_defaults', self::$defaults );

        $instance          = wp_parse_args( (array) $instance, $default );
        $attributes        = br_aapf_get_attributes();
        $categories        = self::get_product_categories( @ json_decode( $instance['product_cat'] ) );
        $categories        = self::set_terms_on_same_level( $categories );
        $tags              = get_terms( 'product_tag' );
        $custom_taxonomies = get_taxonomies( array( "_builtin" => false, "public" => true ) );

        include AAPF_TEMPLATE_PATH . "admin.php";
    }

    /**
     * Widget ajax listener
     */
    public static function listener(){
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );

        $wp_query = self::listener_wp_query();

        if( @ ! $br_options['ajax_request_load'] ) {
            ob_start();

            if ( $wp_query->have_posts() ) {

                woocommerce_product_loop_start();
                woocommerce_product_subcategories();

                while ( have_posts() ) {
                    the_post();
                    wc_get_template_part( 'content', 'product' );
                }

                woocommerce_product_loop_end();

                wp_reset_postdata();

                $_RESPONSE['products'] = ob_get_contents();
            } else {
                echo apply_filters( 'berocket_aapf_listener_no_products_message', "<div class='no-products" . ( ( $br_options['no_products_class'] ) ? ' '.$br_options['no_products_class'] : '' ) . "'>" . $br_options['no_products_message'] . "</div>" );

                $_RESPONSE['no_products'] = ob_get_contents();
            }
            ob_end_clean();
            if( ! @ $br_options['woocommerce_removes']['ordering'] ) {
                ob_start();
                woocommerce_catalog_ordering();
                $_RESPONSE['catalog_ordering'] = ob_get_contents();
                ob_end_clean();
            }
            if( ! @ $br_options['woocommerce_removes']['result_count'] ) {
                ob_start();
                woocommerce_result_count();
                $_RESPONSE['result_count'] = ob_get_contents();
                ob_end_clean();
            }
            if( ! @ $br_options['woocommerce_removes']['pagination'] ) {
                ob_start();
                woocommerce_pagination();
                $_RESPONSE['pagination'] = ob_get_contents();
                ob_end_clean();
            }
        }
        
        if( @ $br_options['recount_products']) {
            $_RESPONSE['attributesname'] = array();
            $_RESPONSE['attributes'] = array();
            if(is_array(@$_POST['attributes'])) {
                $attributes = array_combine ( $_POST['attributes'], $_POST['cat_limit'] );
                foreach( $attributes as $attribute => $cat_limit ) {
                    if($attribute != '_stock_status') {
                        $_RESPONSE['attributesname'][] = $attribute;
                        $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', ( ! @ $br_options['show_all_values'] ), TRUE, FALSE, $cat_limit );
                        $_RESPONSE['attributes'][] = $terms;
                    }
                }
            }
        }
        echo json_encode( $_RESPONSE );

        die();
    }

    /**
     * Widget ajax listener
     */
    public static function listener_product_count(){
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );

        $wp_query = self::listener_wp_query();

        $product_count = $wp_query->found_posts;
        
        echo json_encode( array( 'product_count' => $product_count ) );

        die();
    }

    public static function listener_wp_query() {
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );

        if ( @ $_POST['terms'] ) {
            if ( @ $br_options['slug_urls'] ) {
                foreach ( $_POST['terms'] as $post_key => $t ) {
                    if( $t[0] == 'price' ) {
                        if( preg_match( "~\*~", $t[1] ) ) {
                            if( ! isset( $_POST['price_ranges'] ) ) {
                                $_POST['price_ranges'] = array();
                            }
                            $_POST['price_ranges'][] = $t[1];
                            unset( $_POST['terms'][$post_key] );
                        }
                    } elseif( $t[0] == '_stock_status' ) {
                        $_stock_status = array( 'instock' => 1, 'outofstock' => 2);
                        $t[1] = @ $_stock_status[$t[1]];
                    } else {
                        $t[1] = get_term_by( 'slug', $t[3], $t[0] );
                        $t[1] = $t[1]->term_id;
                        $_POST['terms'][$post_key] = $t;
                    }
                }
            } else {
                foreach ( $_POST['terms'] as $post_key => $t ) {
                    if( $t[0] == 'price' ) {
                        if( preg_match( "~\*~", $t[1] ) ) {
                            if( ! isset( $_POST['price_ranges'] ) ) {
                                $_POST['price_ranges'] = array();
                            }
                            $_POST['price_ranges'][] = $t[1];
                            unset( $_POST['terms'][$post_key] );
                        }
                    }
                }
            }
        }

        add_filter( 'post_class', array( __CLASS__, 'add_product_class' ) );
        add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

        $woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget::woocommerce_hide_out_of_stock_items();

        $meta_query = BeRocket_AAPF::remove_out_of_stock( array() , true, $woocommerce_hide_out_of_stock_items != 'yes' );

        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        $args['post__in'] = array();

        if( $woocommerce_hide_out_of_stock_items == 'yes' ) {
            $args['post__in'] = BeRocket_AAPF::remove_out_of_stock( $args['post__in'] );
        }
        $args['post__in'] = BeRocket_AAPF::remove_hidden( $args['post__in'] );
        $args['meta_query'] = $meta_query;

        $args['post__in']       = BeRocket_AAPF::limits_filter( $args['post__in'] );
        $args['post__in']       = BeRocket_AAPF::price_filter( $args['post__in'] );
        $args['post_status']    = 'publish';
        $args['post_type']      = 'product';
        $default_posts_per_page = get_option( 'posts_per_page' );
        $args['posts_per_page'] = apply_filters( 'loop_shop_per_page', $default_posts_per_page );
        if ( @ $_POST['price_ranges'] ) {
            $price_range_query = array( 'relation' => 'OR' );
            foreach ( $_POST['price_ranges'] as $range ) {
                $range = explode( '*', $range );
                $price_range_query[] = array( 'key' => '_price', 'compare' => 'BETWEEN', 'type' => 'NUMERIC', 'value' => array( ($range[0] - 1), $range[1] ) );
            }
            $args['meta_query'][] = $price_range_query;
        }

        $wp_query = new WP_Query( $args );

        // here we get max products to know if current page is not too big
        if ( $wp_rewrite->using_permalinks() and preg_match( "~/page/([0-9]+)~", $_POST['location'], $mathces ) or preg_match( "~paged?=([0-9]+)~", $_POST['location'], $mathces ) ) {
            $args['paged'] = min( $mathces[1], $wp_query->max_num_pages );
            $wp_query      = new WP_Query( $args );
        }
        return $wp_query;
    }

    public static function rebuild() {
        add_action('woocommerce_before_shop_loop', array( __CLASS__, 'tags_restore' ), 999999);
    }

    public static function tags_restore() {
		global $wp_query;
        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );
        $tags = @ $args['product_tag'];
        if( @ $tags ) {
            $q_vars = $wp_query->query_vars;
            $q_vars['product_tag'] = $tags;
            $q_vars['taxonomy'] = '';
            $q_vars['term'] = '';
            unset( $q_vars['s'] );
            $wp_query = new WP_Query( $q_vars );
        }
    }

    public static function woocommerce_before_main_content() {
        ?>||EXPLODE||<?php
        self::tags_restore();
    }

    public static function woocommerce_after_main_content() {
        ?>||EXPLODE||<?php
    }

    public static function pre_get_posts() {
        add_action( 'woocommerce_before_shop_loop', array( __CLASS__, 'woocommerce_before_main_content' ), 999999 );
        add_action( 'woocommerce_after_shop_loop', array( __CLASS__, 'woocommerce_after_main_content' ), 1 );
    }

    public static function end_clean() {
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        if ( $br_options['ajax_request_load_style'] != 'js' ) {
            $_RESPONSE['products'] = explode('||EXPLODE||', ob_get_contents());
            $_RESPONSE['products'] = $_RESPONSE['products'][1];
            ob_end_clean();

            if ( $_RESPONSE['products'] == null ) {
	            unset( $_RESPONSE['products'] );
	            ob_start();
                echo apply_filters( 'berocket_aapf_listener_no_products_message', "<p class='no-products woocommerce-info" . ( ( $br_options['no_products_class'] ) ? ' '.$br_options['no_products_class'] : '' ) . "'>" . $br_options['no_products_message'] . "</p>" );
                $_RESPONSE['no_products'] = ob_get_contents();
                ob_end_clean();
            } else {
                $_RESPONSE['products'] = str_replace( 'explode=explode#038;', '', $_RESPONSE['products'] );
                $_RESPONSE['products'] = str_replace( '&#038;explode=explode', '', $_RESPONSE['products'] );
                $_RESPONSE['products'] = str_replace( '?explode=explode', '', $_RESPONSE['products'] );
            }
        }

        if ( @ $br_options['recount_products'] ) {
            $_RESPONSE['attributesname'] = array();
            $_RESPONSE['attributes']     = array();

            if ( is_array( @ $_POST['attributes'] ) ) {
                $attributes = array_combine ( $_POST['attributes'], $_POST['cat_limit'] );
                foreach ( $attributes as $attribute => $cat_limit ) {
                    if ( $attribute != '_stock_status' ) {
                        $_RESPONSE['attributesname'][] = $attribute;
                        $terms                         = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', ( ! @ $br_options['show_all_values'] ), TRUE, FALSE, $cat_limit );
                        $_RESPONSE['attributes'][]     = $terms;
                    }
                }
            }
        }
        if( ! @ $br_options['woocommerce_removes']['ordering'] ) {
            ob_start();
            woocommerce_catalog_ordering();
            $_RESPONSE['catalog_ordering'] = ob_get_contents();
            ob_end_clean();
        }
        if( ! @ $br_options['woocommerce_removes']['result_count'] ) {
            ob_start();
            woocommerce_result_count();
            $_RESPONSE['result_count'] = ob_get_contents();
            ob_end_clean();
        }
        if( ! @ $br_options['woocommerce_removes']['pagination'] ) {
            ob_start();
            woocommerce_pagination();
            $_RESPONSE['pagination'] = ob_get_contents();
            $_RESPONSE['pagination'] = str_replace( 'explode=explode#038;', '', @ $_RESPONSE['pagination'] );
            $_RESPONSE['pagination'] = str_replace( '&#038;explode=explode', '', @ $_RESPONSE['pagination'] );
            $_RESPONSE['pagination'] = str_replace( '?explode=explode', '', @ $_RESPONSE['pagination'] );
            ob_end_clean();
        }
        if ( $br_options['ajax_request_load_style'] == 'js' ) echo '||JSON||';
        echo json_encode( $_RESPONSE );
        if ( $br_options['ajax_request_load_style'] == 'js' ) echo '||JSON||';

        die();
    }

    public static function start_clean() {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', BeRocket_AAPF::get_aapf_option() );
        if ( $br_options['ajax_request_load_style'] != 'js' ) {
            ob_start();
        }
    }

    public static function color_listener() {
        $taxonomy_name = $_POST['tax_color_name'];
        $type = $_POST['type'];
        if( isset( $_POST ['tax_color_set'] ) ) {
            foreach( $_POST['tax_color_set'] as $key => $value ) {
                update_metadata( 'berocket_term', $key, $type, $value );
                unset( $_POST['tax_color_set'] );
            }
        } else {
            BeRocket_AAPF_Widget::color_list_view( $type, $taxonomy_name, true );
            wp_die();
        }
    }

    public static function color_list_view( $type, $taxonomy_name, $load_script = false ) {
        $terms = get_terms( $taxonomy_name, array( 'hide_empty' => false ) );
        set_query_var( 'terms', $terms );
        set_query_var( 'type', $type );
        set_query_var( 'load_script', $load_script );
        br_get_template_part( 'color_ajax' );
    }

    public static function get_product_categories( $current_product_cat = '', $parent = 0, $data = array(), $depth = 0, $max_count = 9, $follow_hierarchy = false ) {
        return br_get_sub_categories( $parent, 'id', array( 'return' => 'hierarchy_objects', 'max_depth' => $max_count ) );
    }

    public static function add_product_class( $classes ) {
        $classes[] = 'product';
        return apply_filters( 'berocket_aapf_add_product_class', $classes );
    }

    public static function pagination_args( $args = array() ) {
        $args['base'] = str_replace( 999999999, '%#%', self::get_pagenum_link( 999999999 ) );
        return $args;
    }

    // 99% copy of WordPress' get_pagenum_link.
    public static function get_pagenum_link( $pagenum = 1, $escape = true ) {
        global $wp_rewrite;

        $pagenum = (int) $pagenum;

        $request = remove_query_arg( 'paged', preg_replace( "~".home_url()."~", "", @$_POST['location'] ) );

        $home_root = parse_url( home_url() );
        $home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
        $home_root = preg_quote( $home_root, '|' );

        $request = preg_replace( '|^' . $home_root . '|i', '', $request );
        $request = preg_replace( '|^/+|', '', $request );

        if ( ! $wp_rewrite->using_permalinks() ) {
            $base = trailingslashit( get_bloginfo( 'url' ) );

            if ( $pagenum > 1 ) {
                $result = add_query_arg( 'paged', $pagenum, $base . $request );
            } else {
                $result = $base . $request;
            }
        } else {
            $qs_regex = '|\?.*?$|';
            preg_match( $qs_regex, $request, $qs_match );

            if ( ! empty( $qs_match[0] ) ) {
                $query_string = $qs_match[0];
                $request      = preg_replace( $qs_regex, '', $request );
            } else {
                $query_string = '';
            }

            $request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request );
            $request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request );
            $request = ltrim( $request, '/' );

            $base = trailingslashit( get_bloginfo( 'url' ) );

            if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
                $base .= $wp_rewrite->index . '/';

            if ( $pagenum > 1 ) {
                $request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
            }

            $result = $base . $request . $query_string;
        }

        /**
         * Filter the page number link for the current request.
         *
         * @since 2.5.0
         *
         * @param string $result The page number link.
         */
        $result = apply_filters( 'get_pagenum_link', $result );

        if ( $escape )
            return esc_url( $result );
        else
            return esc_url_raw( $result );
    }

    public static function get_terms_child_parent ( $child_parent, $attribute, $current_terms = FALSE, $child_parent_depth = 1 ) {
        if ( @ $child_parent == 'parent' ) {
            $args_terms = array(
                'orderby'    => 'id',
                'order'      => 'ASC',
                'hide_empty' => false,
                'parent'     => 0,
            );
            if( $attribute == 'product_cat' ) {
                $current_terms = self::get_product_categories( '', 0, array(), 0, 0, true );
            } else {
                $current_terms = get_terms( $attribute, $args_terms );
            }
        }
        if ( @ $child_parent == 'child' ) {
            $current_terms = array( (object) array( 'depth' => 0, 'child' => 0, 'term_id' => 'R__term_id__R', 'count' => 'R__count__R', 'slug' => 'R__slug__R', 'name' => 'R__name__R', 'taxonomy' => 'R__taxonomy__R' ) );
            $selected_terms = br_get_selected_term( $attribute );
            $selected_terms_id = array();
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $attribute );
                if( count( $ancestors ) >= ( @ $child_parent_depth - 1 ) ) {
                    if( count( $ancestors ) > ( @ $child_parent_depth - 1 ) ) {
                        $selected_term = $ancestors[count( $ancestors ) - ( @ $child_parent_depth )];
                    }
                    if ( ! in_array( $selected_term, $selected_terms_id ) ) {
                        $args_terms = array(
                            'orderby'    => 'id',
                            'order'      => 'ASC',
                            'hide_empty' => false,
                            'parent'     => $selected_term,
                        );
                        $selected_terms_id[] = $selected_term;
                        $additional_terms = get_terms( $attribute, $args_terms );
                        $current_terms = array_merge( $current_terms, $additional_terms );
                    }
                }
            }
        }
        return $current_terms;
    }
    public static function wc_shortcode_count_fix() {
        
    }
}