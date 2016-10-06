<?php
/**
 * Plugin Name: WooCommerce AJAX Products Filter
 * Plugin URI: http://berocket.com/product/woocommerce-ajax-products-filter
 * Description: Unlimited AJAX products filters to make your shop perfect
 * Version: 2.0.6.1
 * Author: BeRocket
 * Requires at least: 4.0
 * Author URI: http://berocket.com
 */
define( "BeRocket_AJAX_filters_version", '2.0.6.1' );
define( "BeRocket_AJAX_domain", 'BRaapf'); 
define( "BeRocket_AJAX_cache_expire", '21600'); 

define( "AAPF_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );

load_plugin_textdomain( BeRocket_AJAX_domain, false, WP_PLUGIN_DIR . '/languages' );
require_once dirname( __FILE__ ) . '/includes/widget.php';
require_once dirname( __FILE__ ) . '/includes/functions.php';
require_once dirname( __FILE__ ) . '/includes/updater.php';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Class BeRocket_AAPF
 */

$br_aapf_debugs = array();
class BeRocket_AAPF {
    public static $debug_mode = false;
    public static $error_log = array();
    public static $info = array( 
        'id'        => 1,
        'version'   => BeRocket_AJAX_filters_version,
        'plugin'    => '',
        'slug'      => '',
        'key'       => ''
    );

    public static $defaults = array(
        'plugin_key'                      => '',
        'no_products_message'             => 'There are no products meeting your criteria',
        'pos_relative'                    => '1',
        'no_products_class'               => '',
        'products_holder_id'              => 'ul.products',
        'woocommerce_result_count_class'  => '.woocommerce-result-count',
        'woocommerce_ordering_class'      => 'form.woocommerce-ordering',
        'woocommerce_pagination_class'    => '.woocommerce-pagination',
        'woocommerce_removes'             => array(
            'result_count'                => '',
            'ordering'                    => '',
            'pagination'                  => '',
        ),
        'products_per_page'               => '',
        'control_sorting'                 => '',
        'seo_friendly_urls'               => '',
        'slug_urls'                       => '',
        'nice_urls'                       => '',
        'filters_turn_off'                => '',
        'show_all_values'                 => '',
        'hide_value'                      => array(
            'o'                           => '',
            'sel'                         => '',
        ),
        'first_page_jump'                 => '',
        'scroll_shop_top'                 => '',
        'scroll_shop_top_px'              => '-180',
        'recount_products'                => '',
        'selected_area_show'              => '',
        'object_cache'                    => 'wordpress',
        'ub_product_count'                => '1',
        'ub_product_text'                 => 'products',
        'ub_product_button_text'          => 'Show',
        'ajax_request_load'               => '1',
        'ajax_request_load_style'         => 'jquery',
        'product_per_row'                 => '4',
        
        'styles_input'                    => array(
            'checkbox'               => array( 'bcolor' => '', 'bwidth' => '', 'bradius' => '', 'fcolor' => '', 'backcolor' => '', 'icon' => '', 'fontsize' => '', 'theme' => '' ),
            'radio'                  => array( 'bcolor' => '', 'bwidth' => '', 'bradius' => '', 'fcolor' => '', 'backcolor' => '', 'icon' => '', 'fontsize' => '', 'theme' => '' ),
            'slider'                 => array( 'line_color' => '', 'line_height' => '', 'line_border_color' => '', 'line_border_width' => '', 'button_size' => '', 
                                               'button_color' => '', 'button_border_color' => '', 'button_border_width' => '', 'button_border_radius' => '' ),
            'pc_ub'                  => array( 'back_color' => '', 'border_color' => '', 'font_size' => '', 'font_color' => '', 'show_font_size' => '', 'close_size' => '', 
                                               'show_font_color' => '', 'show_font_color_hover' => '', 'close_font_color' => '', 'close_font_color_hover' => '' ),
            'product_count'          => 'round',
            'product_count_position' => '',
        ),
        'ajax_load_icon'                  => '',
        'ajax_load_text'                  => array(
            'top'                         => '',
            'bottom'                      => '',
            'left'                        => '',
            'right'                       => '',
        ),
        'description'                     => array(
            'show'                        => 'click',
            'hide'                        => 'click',
        ),
        'user_func'                       => array(
            'before_update'               => '',
            'on_update'                   => '',
            'after_update'                => '',
        ),
        'user_custom_css'                 => '',
        'br_opened_tab'                   => 'general',
        'number_style'                    => array(
            'thousand_separate' => '',
            'decimal_separate'  => '.',
            'decimal_number'    => '2',
        ),
        'debug_mode'                      => '',
    );
    public static $default_permalink = array (
        'variable' => 'filters',
        'value'    => '/values',
        'split'    => '/',
    );

    function __construct() {
        $error_log['000_select_status'] = array();
        register_activation_hook( __FILE__, array( __CLASS__, 'br_add_defaults' ) );
        register_uninstall_hook( __FILE__, array( __CLASS__, 'br_delete_plugin_options' ) );
        add_action( 'wpmu_new_blog', array( __CLASS__, 'new_blog' ), 10, 6 );
        add_filter( 'BeRocket_updater_add_plugin', array( __CLASS__, 'updater_info' ) );

        if ( ! @ is_network_admin() ) {

            if ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && br_get_woocommerce_version() >= 2.1 ) {
                $last_version = get_option('br_filters_version');

                if ( @ $last_version < BeRocket_AJAX_filters_version ) {
                    self::update_from_older ( @ $last_version );
                }
                $option = self::get_aapf_option();
                self::$debug_mode = @ $option['debug_mode'];

                if ( BeRocket_AAPF::$debug_mode ) {
                    add_filter( 'BeRocket_updater_error_log', array( __CLASS__, 'add_error_log' ) );
                    BeRocket_AAPF::$error_log['1_settings'] = $option;
                }

                if ( defined('DOING_AJAX') && DOING_AJAX ) {
                    add_action( 'setup_theme', array( __CLASS__, 'WPML_fix' ) );
                    add_filter('loop_shop_columns', array( __CLASS__, 'loop_columns' ), 9999 );
                }

                add_action( 'admin_menu', array( __CLASS__, 'br_add_options_page' ) );
                add_action( 'admin_init', array( __CLASS__, 'register_br_options' ) );
                add_action( 'current_screen', array( __CLASS__, 'register_permalink_option' ) );
                add_action( 'wp_head', array( __CLASS__, 'br_custom_user_css' ) );
                add_action( 'admin_init', array( __CLASS__, 'load_jquery_ui' ) );
                add_action( "wp_ajax_br_aapf_get_child", array ( __CLASS__, 'br_aapf_get_child' ) );
                add_action( "wp_ajax_nopriv_br_aapf_get_child", array ( __CLASS__, 'br_aapf_get_child' ) );

                add_shortcode( 'br_filters', array( __CLASS__, 'shortcode' ) );

                if ( ! @ defined( 'DOING_AJAX' ) ) {
                    add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_filters' ) );
                    add_filter( 'woocommerce_shortcode_products_query', array( __CLASS__, 'woocommerce_shortcode_products_query' ) );
                }
            
                add_action( 'init', array( __CLASS__, 'create_metadata_table' ), 999999999 );
                if ( @ $option['nice_urls'] ) {
                    add_action( 'init', array( __CLASS__, 'init' ) );
                    add_filter( 'rewrite_rules_array', array( __CLASS__, 'add_rewrite_rules' ), 999999999 );
                    add_filter( 'query_vars', array( __CLASS__, 'add_queryvars' ) );
                }

                if ( @ $_GET['explode'] == 'explode') {
                    add_action( 'woocommerce_before_template_part', array( 'BeRocket_AAPF_Widget', 'pre_get_posts'), 999999 );
                    add_action( 'wp_footer', array( 'BeRocket_AAPF_Widget', 'end_clean'), 999999 );
                    add_action( 'init', array( 'BeRocket_AAPF_Widget', 'start_clean'), 1 );
                } else {
                    add_action( 'woocommerce_before_template_part', array( 'BeRocket_AAPF_Widget', 'rebuild'), 999999 );
                }

                if ( @ $option['selected_area_show'] ) {
                    add_action ( 'woocommerce_archive_description', array(__CLASS__, 'selected_area'), 1 );
                }
                if ( @ $option['products_per_page'] && ! br_is_plugin_active( 'list-grid' ) && ! br_is_plugin_active( 'more-products' ) ) {
                    add_filter( 'loop_shop_per_page', create_function( '$cols', 'return '.$option['products_per_page'].';' ), 9999 );
                }
            } else {
                if( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
                    add_action( 'admin_notices', array( __CLASS__, 'update_woocommerce	' ) );
                } else {
                    add_action( 'admin_notices', array( __CLASS__, 'no_woocommerce' ) );
                }
            }
        }
    }

    public static function add_rewrite_rules ( $rules ) {
        $option_permalink = get_option( 'berocket_permalink_option' );
        $values_split = $option_permalink['value'];
        $values_split = explode( 'values', $values_split );
        $newrules = array();
        $shop_slug = get_post(wc_get_page_id('shop'));
        $newrules[$option_permalink['variable'].'/(.*)/?'] = 'index.php?post_type=product&'.$option_permalink['variable'].'=$matches[1]';
        $newrules[$shop_slug->post_name.'/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?pagename='.$shop_slug->post_name.'&'.$option_permalink['variable'].'=$matches[1]';
        $category_base = get_option( 'woocommerce_permalinks' );
        $tag_base = $category_base['tag_base'];
        $category_base = $category_base['category_base'];

        if ( ! @ $category_base ) {
            $category_base = 'product-category';
        }
        $newrules[$category_base.'/(.+?)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_cat=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';

        if ( ! @ $tag_base ) {
            $tag_base = 'product-tag';
        }
        $newrules[$tag_base.'/([^/]+)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_tag=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';

        return $newrules + $rules;
    }

    public static function init () {
        $option_permalink = get_option( 'berocket_permalink_option' );
        add_rewrite_endpoint($option_permalink['variable'], EP_PERMALINK|EP_SEARCH|EP_CATEGORIES|EP_TAGS|EP_PAGES);
        flush_rewrite_rules();
    }

    public static function add_queryvars( $query_vars ) {
        $option_permalink = get_option( 'berocket_permalink_option' );
        $query_vars[] = $option_permalink['variable'];
        return $query_vars;
    }

    public static function updater_info ( $plugins ) {
        $option = self::get_aapf_option();
        self::$info['key'] = @ $option['plugin_key'];
        self::$info['slug'] = basename( __DIR__ );
        self::$info['plugin'] = plugin_basename( __FILE__ );
        self::$info = apply_filters( 'berocket_aapf_update_info', self::$info );
        $plugins[] = self::$info;
        return $plugins;
    }

    public static function add_error_log( $error_log ) {
        $error_log[plugin_basename( __FILE__ )] =  self::$error_log;
        return $error_log;
    }

    public static function update_from_older( $version ) {
        $option = self::get_aapf_option();
        if ( @$version < '2.0.4' ) {
            $version_index = 1;
        } elseif ( @$version < '2.0.5' ) {
            $version_index = 2;
        } else {
            $version_index = 3;
        }

        $option = array_replace_recursive(self::$defaults, $option);

        switch ( $version_index ) {
            case 1:
            case 2:
                update_option( 'berocket_permalink_option', BeRocket_AAPF::$default_permalink );
                break;
        }

        update_option( 'br_filters_options', $option );
        update_option( 'br_filters_version', BeRocket_AJAX_filters_version );
    }
    
    public static function load_jquery_ui() {
        wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ) );
        wp_register_style( 'berocket_aapf_widget-admin-style', plugins_url( 'css/admin.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        wp_enqueue_style( 'berocket_aapf_widget-admin-style' );
    }

    public static function no_woocommerce() {
        echo '
        <div class="error">
            <p>' . __( 'Activate WooCommerce plugin before', BeRocket_AJAX_domain ) . '</p>
        </div>';
    }

    public static function update_woocommerce() {
        echo '
        <div class="error">
            <p>' . __( 'Update WooCommerce plugin', BeRocket_AJAX_domain ) . '</p>
        </div>';
    }

    public static function br_add_options_page() {
        add_submenu_page( 'woocommerce', __( 'Product Filters Settings', BeRocket_AJAX_domain ), __( 'Product Filters', BeRocket_AJAX_domain ), 'manage_options', 'br-product-filters', array(
            __CLASS__,
            'br_render_form'
        ) );
    }

    public static function shortcode( $atts = array() ) {
        if( BeRocket_AAPF::$debug_mode ) {
            if( ! isset( BeRocket_AAPF::$error_log['2_shortcodes'] ) )
            {
                BeRocket_AAPF::$error_log['2_shortcodes'] = array();
            } 
            BeRocket_AAPF::$error_log['2_shortcodes'][] = $atts;
        }
        $default = array(
            'type'      => 'checkbox',
        );
        $default = array_replace_recursive(BeRocket_AAPF_Widget::$defaults, $default);
        $a = shortcode_atts( $default, $atts );
        if ( @ $atts['product_cat'] ) {
            $a['product_cat'] = @ json_encode( explode( "|", $a['product_cat'] ) );
        }
        if ( @ $atts['show_page'] ) {
            $a['show_page'] = @ explode( "|", $a['show_page'] );
        }

        $a = apply_filters( 'berocket_aapf_shortcode_options', $a );

        $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget();
        $BeRocket_AAPF_Widget->widget( array(), $a );
    }

    public static function br_render_form() {
        wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ) );
        wp_register_script( 'berocket_aapf_widget-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version, false );
        wp_enqueue_script( 'berocket_aapf_widget-admin' );

        include AAPF_TEMPLATE_PATH . "admin-settings.php";
    }

    public static function woocommerce_shortcode_products_query( $query_vars ) {
        $query = new WP_Query($query_vars);
        $query = self::apply_user_filters( $query, true );
        $query_vars = $query->query_vars;
        return $query_vars;
    }

    public static function apply_user_filters( $query, $is_shortcode = FALSE ) {
        if( BeRocket_AAPF::$debug_mode ) {
            if ( ! @ is_array( BeRocket_AAPF::$error_log['8_1_query_in'] ) ) {
                BeRocket_AAPF::$error_log['8_1_query_in'] = array();
            }
            BeRocket_AAPF::$error_log['8_1_query_in'][] = $query;
            BeRocket_AAPF::$error_log['PERMALINK'] = get_option('permalink_structure');
        }
        $option_permalink = get_option( 'berocket_permalink_option' );
        if ( ( ( ! is_admin() && $query->is_main_query() ) || $is_shortcode ) && ( @ $_GET['filters'] || $query->get( $option_permalink['variable'], '' ) ) ) {
            if( BeRocket_AAPF::$debug_mode ) {
                BeRocket_AAPF::$error_log['8_query_in'] = $query;
            }
            br_aapf_args_converter( $query );

            $old_post_terms                      = @ $_POST['terms'];
            $woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget::woocommerce_hide_out_of_stock_items();
            $meta_query                          = BeRocket_AAPF::remove_out_of_stock( array(), true, $woocommerce_hide_out_of_stock_items != 'yes' );

            $args = br_aapf_args_parser();
            if ( isset( $args['meta_query'] ) ) {
                $args['meta_query'] += $meta_query;
            } else {
                $args['meta_query'] = $meta_query;
            }
            $_POST['terms'] = $old_post_terms;

            if ( @ $_POST['price'] ) {
                list( $_GET['min_price'], $_GET['max_price'] ) = $_POST['price'];
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'price_filter' ) );
            } else {
                if ( @ $_POST['price_ranges'] ) {
                    if ( ! isset( $args['meta_query'] ) ) {
                        $args['meta_query'] = array();
                    }
                    $price_range_query = array( 'relation' => 'OR' );
                    foreach ( $_POST['price_ranges'] as $range ) {
                        $range = explode( '*', $range );
                        $price_range_query[] = array( 'key' => '_price', 'compare' => 'BETWEEN', 'type' => 'NUMERIC', 'value' => array( ($range[0] - 1), $range[1] ) );
                    }
                    $args['meta_query'][] = $price_range_query;
                }
            }
            $args['post__in'] = array( 26, 119 );

            if ( @ $_POST['limits'] ) {
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'limits_filter' ) );
            }

            $args = apply_filters( 'berocket_aapf_filters_on_page_load', $args );
            if( BeRocket_AAPF::$debug_mode ) {
                BeRocket_AAPF::$error_log['3_user_filters'] = $args;
            }

            $args_fields = array( 'meta_key', 'tax_query', 'fields', 'where', 'join', 'meta_query', 'post__in' );
            foreach ( $args_fields as $args_field ) {
                if ( @ $args[ $args_field ] ) {
                    $query->set( $args_field, $args[ $args_field ] );
                }
            }
            if( BeRocket_AAPF::$debug_mode ) {
                BeRocket_AAPF::$error_log['8_query_out'] = $query;
            }
        }

        if ( ( ! is_admin() && $query->is_main_query() ) || $is_shortcode ) {
            global $br_wc_query;
            $br_wc_query = $query;
        }
        if ( $is_shortcode ) {
            add_action( 'wp_footer', array( __CLASS__, 'wp_footer_widget'), 99999 );
        }

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['8_2_query_out'] = $query;
        }
        return $query;
    }
    
    public static function remove_out_of_stock( $filtered_posts, $use_post_terms = false, $show_out_of_stock = false ) {
        global $wpdb;
        if ( $use_post_terms ) {
            $meta_query = array();
            if( @ $_POST['terms']) {
                foreach($_POST['terms'] as $term) {
                    if( $term[0] == '_stock_status' ) {
                        array_push($meta_query , array( 'key' => $term[0], 'value' => $term[3], 'compare' => '=' ) );
                    }
                }
                for ( $i = count( $_POST['terms'] ) - 1; $i >= 0; $i-- ) {
                    if ( $_POST['terms'][$i][0] ==  '_stock_status' ) {
                        unset( $_POST['terms'][$i] );
                    }
                }
            }

            if ( $show_out_of_stock ) {
                return $meta_query;
            } else {
                return array();
            }
        }

        $query_string = "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_stock_status' AND meta_value != 'outofstock'";

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['104_remove_out_of_stock_SELECT'] = $query_string;
            $wpdb->show_errors();
        }

        // TODO: split this into 2 queries(product and product_variation) this way we will not be using all data at the same time
        $matched_products_query = $wpdb->get_results( $query_string, OBJECT_K );
        unset( $query_string );
        $matched_products = array( 0 );

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['000_select_status'][] = @ $wpdb->last_error;
        }

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            // TODO: check if we really need this in_array. We have array_unique after foreach. Only one should be left
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }
        $matched_products = @ array_unique( $matched_products );

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            // TODO: array_intersect will create count($filtered_posts) * count($matched_products) loops.
            // TODO: this should be handled above, in foreach
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }

        return (array) $filtered_posts;
    }

    public static function remove_hidden( $filtered_posts ){
        global $wpdb;

        $query_string = "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_visibility' AND meta_value NOT IN ('hidden', 'search')";

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['105_remove_hidden_SELECT'] = $query_string;
            $wpdb->show_errors();
        }

        $matched_products_query = $wpdb->get_results( $query_string, OBJECT_K );
        unset( $query_string );
        $matched_products = array( 0 );

        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['000_select_status'][] = @ $wpdb->last_error;
        }

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }
        $matched_products = @ array_unique( $matched_products );

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }
        return (array) $filtered_posts;
    }

    public static function limits_filter( $filtered_posts ) {
        global $wpdb;

        if ( @ $_POST['limits'] ) {
            $matched_products = false;

            foreach ( $_POST['limits'] as $v ) {
                $v[1] = urldecode( $v[1] );
                $v[2] = urldecode( $v[2] );
                $all_terms_name = array();
                $all_terms_name = get_terms( $v[0], array( 'fields' => 'names') );
                $is_numeric = true;
                $is_with_string = false;
                if( is_wp_error ( $all_terms_name ) ) {
                    BeRocket_updater::$error_log[] = $all_terms_name->errors;
                }
                foreach ( $all_terms_name as $term ) {
                    if( ! is_numeric( substr( $term[0], 0, 1 ) ) ) {
                        $is_numeric = false;
                    }
                    if( ! is_numeric( $term ) ) {
                        $is_with_string = true;
                    }
                }
                if( $is_with_string ) {
                    if( $is_numeric ) {
                        sort( $all_terms_name, SORT_NUMERIC );
                    } else {
                        sort( $all_terms_name );
                    }
                    $start_terms    = array_search( $v[1], $all_terms_name );
                    $end_terms      = array_search( $v[2], $all_terms_name );
                    $all_terms_name = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
                    $all_terms_name_text = implode( "','", $all_terms_name );
                    $all_terms_name_text = str_replace( '%', '%%', $all_terms_name_text );
                    $CAST           = "IN ('" . $all_terms_name_text . "')";
                } else {
                    $CAST = "BETWEEN %f AND %f";
                }

                $query_string = "
                    SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                    INNER JOIN $wpdb->term_relationships as tr ON ID = tr.object_id
                    INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN $wpdb->terms as t ON t.term_id = tt.term_id
                    WHERE post_type = 'product' AND post_status = 'publish'
                    AND tt.taxonomy = %s AND t.name " . $CAST . "
                ";

                if( BeRocket_AAPF::$debug_mode ) {
                    BeRocket_AAPF::$error_log['106_'.$v[0].'limits_filter_SELECT'] = $wpdb->prepare( $query_string, $v[0], $v[1], $v[2] );
                    $wpdb->show_errors();
                }

                $matched_products_query = $wpdb->get_results( $wpdb->prepare( $query_string, $v[0], $v[1], $v[2] ), OBJECT_K );
                unset( $query_string );

                if( BeRocket_AAPF::$debug_mode ) {
                    BeRocket_AAPF::$error_log['000_select_status'][] = @ $wpdb->last_error;
                }

                if ( $matched_products_query ) {
                    if ( $matched_products === false ) {
                        $matched_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            $matched_products[] = $product->ID;
                            // TODO: probably this is not needed as this is for product_variation, not sure
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) ) {
                                $matched_products[] = $product->post_parent;
                            }
                        }
                    } else {
                        $new_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            if ( in_array( $product->ID, $matched_products ) ) {
                                $new_products[] = $product->ID;
                                // TODO: probably this is not needed as this is for product_variation, not sure
                                if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $new_products ) ) {
                                    $new_products[] = $product->post_parent;
                                }
                            }
                        }
                        $matched_products = $new_products;
                        unset( $new_products );
                    }
                    unset( $matched_products_query );
                }

                $matched_product_variations_query = $wpdb->get_results( $wpdb->prepare( "
                    SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                    INNER JOIN $wpdb->term_relationships as tr ON ID = tr.object_id
                    INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    INNER JOIN $wpdb->terms as t ON t.term_id = tt.term_id
                    WHERE post_type = 'product_variation' AND post_status = 'publish'
                    AND tt.taxonomy = %s AND t.name " . $CAST . "
                ", $v[0], $v[1], $v[2] ), OBJECT_K );

                if ( $matched_product_variations_query ) {
                    if ( $matched_products === false ) {
                        $matched_products = array( 0 );
                        foreach ( $matched_product_variations_query as $product ) {
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) ) {
                                $matched_products[] = $product->post_parent;
                            }
                        }
                    } else {
                        $new_products = array( 0 );
                        foreach ( $matched_product_variations_query as $product ) {
                            if ( in_array( $product->ID, $matched_products ) ) {
                                if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $new_products ) ) {
                                    $new_products[] = $product->post_parent;
                                }
                            }
                        }
                        $matched_products = $new_products;
                        unset( $new_products );
                    }
                    unset( $matched_product_variations_query );
                }
            }

            if ( $matched_products === false ) {
                $matched_products = array( 0 );
            } else {
                // TODO: need to remove array_unique and check if unique in the loop
                $matched_products = @ array_unique( $matched_products );
            }

            // Filter the id's
            if ( sizeof( $filtered_posts ) == 0 ) {
                $filtered_posts = $matched_products;
            } else {
                // TODO: need to remove array_intersect and check if intersect in the loop
                $filtered_posts = array_intersect( $filtered_posts, $matched_products );
            }
        }

        return (array) $filtered_posts;
    }

    public static function price_filter( $filtered_posts ) {
        global $wpdb;

        if ( @ $_POST['price'] || @ $_POST['price_ranges'] ) {
            $matched_products = array( 0 );
            if ( @ $_POST['price'] ) {
                $min              = floatval( $_POST['price'][0] );
                $max              = floatval( $_POST['price'][1] );

                if( BeRocket_AAPF::$debug_mode ) {
                    if( ! isset( BeRocket_AAPF::$error_log['5_price'] ) )
                    {
                        BeRocket_AAPF::$error_log['5_price'] = array();
                    } 
                    BeRocket_AAPF::$error_log['5_price']['select'] = 'from '.$min.' to '.$max;
                    $wpdb->show_errors();
                }

                $matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
                    SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta ON ID = post_id
                    WHERE post_type = 'product' AND post_status = 'publish' AND meta_key = %s AND meta_value BETWEEN %d AND %d
                ", '_price', $min, $max ), OBJECT_K ), $min, $max );
            } else {
                $values = $_POST['price_ranges'];
                $between = '';
                foreach ( $values as $value ) {
                    if ( $between ) {
                        $between .= ' OR ';
                    }
                    $between .= '( ';
                    $value = explode( '*', $value );
                    $between .= 'meta_value BETWEEN '.($value[0] - 1).' AND '.$value[1];
                    $between .= ' )';
                }

                $matched_products_query = apply_filters( 'woocommerce_price_ranges_filter_results', $wpdb->get_results( $wpdb->prepare( "
                    SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta ON ID = post_id
                    WHERE post_type = 'product' AND post_status = 'publish' AND meta_key = %s AND ( $between )
                ", '_price' ), OBJECT_K ), $values );
            }

            if( BeRocket_AAPF::$debug_mode ) {
                BeRocket_AAPF::$error_log['000_select_status'][] = @ $wpdb->last_error;
                BeRocket_AAPF::$error_log['0099_price'][] = $wpdb->prepare( "
                    SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta ON ID = post_id
                    WHERE post_type = 'product' AND post_status = 'publish' AND meta_key = %s AND ( $between )
                ", '_price' );
            }

            if ( $matched_products_query ) {
                foreach ( $matched_products_query as $product ) {
                    $matched_products[] = $product->ID;
                    // TODO: check if this is needed here. probably this is for product_variation
                    if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) ) {
                        $matched_products[] = $product->post_parent;
                    }
                }
                unset( $matched_products_query );
            }

            $matched_product_variations_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare( "
                SELECT DISTINCT ID, post_parent FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta ON ID = post_id
                WHERE post_type = 'product_variation' AND post_status = 'publish' AND meta_key = %s AND meta_value BETWEEN %d AND %d
            ", '_price', $min, $max ), OBJECT_K ), $min, $max );

            if ( $matched_product_variations_query ) {
                foreach ( $matched_product_variations_query as $product ) {
                    if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) ) {
                        $matched_products[] = $product->post_parent;
                    }
                }
                unset( $matched_product_variations_query );
            }

            // Filter the id's
            if ( sizeof( $filtered_posts ) == 0 ) {
                $filtered_posts = $matched_products;
            } else {
                // TODO: remove array_intersect from here and check for intersect in foreach
                $filtered_posts = array_intersect( $filtered_posts, $matched_products );
            }

        }

        return (array) $filtered_posts;
    }

    /**
     * Get template part (for templates like the slider).
     *
     * @access public
     *
     * @param string $name (default: '')
     *
     * @return void
     */
    public static function br_get_template_part( $name = '' ) {
        $template = '';

        // Look in your_child_theme/woocommerce-filters/name.php
        if ( $name ) {
            $template = locate_template( "woocommerce-filters/{$name}.php" );
        }

        // Get default slug-name.php
        if ( ! $template && $name && file_exists( AAPF_TEMPLATE_PATH . "{$name}.php" ) ) {
            $template = AAPF_TEMPLATE_PATH . "{$name}.php";
        }

        // Allow 3rd party plugin filter template file from their plugin
        $template = apply_filters( 'br_get_template_part', $template, $name );


        if ( $template ) {
            load_template( $template, false );
        }
    }

    public static function register_br_options() {
        register_setting( 'br_filters_plugin_options', 'br_filters_options', array( __CLASS__, 'sanitize_aapf_option' ) );
    }
    public static function register_permalink_option() {
        $screen = get_current_screen();
        $default_values = self::$default_permalink;
        if($screen->id == 'options-permalink') {
            self::save_permalink_option($default_values);
            self::_register_permalink_option($default_values);
        }
        if($screen->id == 'widgets' || $screen->id == 'woocommerce_page_br-product-filters') {
            if ( function_exists( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            } else {
                wp_enqueue_style( 'thickbox' );
                wp_enqueue_script( 'media-upload' );
                wp_enqueue_script( 'thickbox' );
            }
            /*wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-ui-widget ' );
            wp_enqueue_script( 'jquery-ui-selectmenu' );
            wp_enqueue_script( 'jquery-ui-accordion' );
            wp_enqueue_script( 'jquery-ui-button' );

            wp_register_style( 'jquery-ui', plugins_url( 'css/jquery-ui.min.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
            wp_enqueue_style( 'jquery-ui' );*/
            
            wp_enqueue_script( 'brjsf-ui', plugins_url( 'js/brjsf.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
            wp_register_style( 'brjsf-ui', plugins_url( 'css/brjsf.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
            wp_enqueue_style( 'brjsf-ui' );
        }
        if( BeRocket_AAPF::$debug_mode ) {
            BeRocket_AAPF::$error_log['21_current_screen'] = $screen;
        }
    }
    public static function _register_permalink_option($default_values) {
        $permalink_option = 'berocket_permalink_option';
        $option_values = get_option( $permalink_option );
        $data = shortcode_atts( $default_values, $option_values );
        update_option($permalink_option, $data);
        
        add_settings_section(
            'berocket_permalinks',
            'BeRocket AJAX Filters',
            'br_permalink_input_section_echo',
            'permalink'
        );
    }

    public static function save_permalink_option( $default_values ) {
        if ( isset( $_POST['berocket_permalink_option'] ) ) {
            $option_values    = $_POST['berocket_permalink_option'];
            $data             = shortcode_atts( $default_values, $option_values );
            $data['variable'] = urlencode( $data['variable'] );

            update_option( 'berocket_permalink_option', $data );
        }
    }
    public static function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        global $wpdb;
        if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
            $old_blog = $wpdb->blogid;
            switch_to_blog($blog_id);
            self::_br_add_defaults();
            switch_to_blog($old_blog);
        }
    }

    public static function br_add_defaults( $networkwide ) {
        global $wpdb;
        if ( function_exists('is_multisite') && is_multisite() ) {
            if ( $networkwide) {
                $old_blog = $wpdb->blogid;
                $blogids  = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

                foreach ( $blogids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::_br_add_defaults();
                }

                switch_to_blog( $old_blog );
                return;
            }
        } 
        self::_br_add_defaults();
    }

    public static function _br_add_defaults() {
        $tmp = self::get_aapf_option();
        $tmp2 = get_option( 'berocket_permalink_option' );
        $version = get_option( 'br_filters_version' );
        if ( @$tmp['chk_default_options_db'] == '1' or ! @is_array( $tmp ) ) {
            delete_option( 'br_filters_options' );
            update_option( 'br_filters_options', BeRocket_AAPF::$defaults );
        }
        if ( @$tmp['chk_default_options_db'] == '1' or ! @is_array( $tmp2 ) ) {
            delete_option( 'berocket_permalink_option' );
            update_option( 'berocket_permalink_option', BeRocket_AAPF::$default_permalink );
        }
    }

    public static function br_delete_plugin_options($networkwide) {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite()) {
            if ($networkwide) {
                $old_blog = $wpdb->blogid;
                $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    self::_br_delete_plugin_options();
                }
                switch_to_blog($old_blog);
                return;
            }
        }
        self::_br_delete_plugin_options();
    }

    public static function _br_delete_plugin_options() {
        delete_option( 'br_filters_options' );
        delete_option( 'berocket_permalink_option' );
    }

    public static function br_custom_user_css() {
        $options     = self::get_aapf_option();
        $replace_css = array(
            '#widget#'       => '.berocket_aapf_widget',
            '#widget-title#' => '.berocket_aapf_widget-title'
        );
        $result_css  = @ $options['user_custom_css'];
        foreach ( $replace_css as $key => $value ) {
            $result_css = str_replace( $key, $value, $result_css );
        }
        $uo = br_aapf_converter_styles( @$options['styles'] );
        echo '<style type="text/css">' . $result_css;
        echo ' div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a, div.berocket_aapf_selected_area_block a{'.@ $uo['style']['selected_area'].'}';
        echo ' div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a.br_hover *, div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a.br_hover, div.berocket_aapf_selected_area_block a.br_hover{'.@ $uo['style']['selected_area_hover'].'}';
        if ( @ $options['styles_input']['checkbox']['icon'] ) {
            echo 'ul.berocket_aapf_widget > li > span > input[type="checkbox"] + .berocket_label_widgets:before {display:inline-block;}';
            echo '.berocket_aapf_widget input[type="checkbox"] {display: none;}';
        }
        echo ' ul.berocket_aapf_widget > li > span > input[type="checkbox"] + .berocket_label_widgets:before {';
        if ( @ $options['styles_input']['checkbox']['bcolor'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['checkbox']['bcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['checkbox']['bcolor'].';';
        }
        if ( @ $options['styles_input']['checkbox']['bwidth'] || @ $options['styles_input']['checkbox']['bwidth'] === '0' )
            echo 'border-width: '.$options['styles_input']['checkbox']['bwidth'].'px;';
        if ( @ $options['styles_input']['checkbox']['bradius'] || @ $options['styles_input']['checkbox']['bradius'] === '0' )
            echo 'border-radius: '.$options['styles_input']['checkbox']['bradius'].'px;';
        if ( @ $options['styles_input']['checkbox']['fontsize'] )
            echo 'font-size: '.$options['styles_input']['checkbox']['fontsize'].'px;';
        if ( @ $options['styles_input']['checkbox']['fcolor'] ) {
            echo 'color: ';
            if ( $options['styles_input']['checkbox']['fcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['checkbox']['fcolor'].';';
        }
        if ( @ $options['styles_input']['checkbox']['backcolor'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['checkbox']['backcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['checkbox']['backcolor'].';';
        }
        echo '}';
        echo ' ul.berocket_aapf_widget > li > span > input[type="checkbox"]:checked + .berocket_label_widgets:before {';
        if ( @ $options['styles_input']['checkbox']['icon'] )
            echo 'content: "\\'.$options['styles_input']['checkbox']['icon'].'";';
        echo '}';
        if ( @ $options['styles_input']['radio']['icon'] ) {
            echo 'ul.berocket_aapf_widget > li > span > input[type="radio"] + .berocket_label_widgets:before {display:inline-block;}';
            echo '.berocket_aapf_widget input[type="radio"] {display: none;}';
        }
        echo ' ul.berocket_aapf_widget > li > span > input[type="radio"] + .berocket_label_widgets:before {';
        if ( @ $options['styles_input']['radio']['bcolor'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['radio']['bcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['radio']['bcolor'].';';
        }
        if ( @ $options['styles_input']['radio']['bwidth'] || @ $options['styles_input']['radio']['bwidth'] === '0' )
            echo 'border-width: '.$options['styles_input']['radio']['bwidth'].'px;';
        if ( @ $options['styles_input']['radio']['bradius'] || @ $options['styles_input']['radio']['bradius'] === '0' )
            echo 'border-radius: '.$options['styles_input']['radio']['bradius'].'px;';
        if ( @ $options['styles_input']['radio']['fontsize'] )
            echo 'font-size: '.$options['styles_input']['radio']['fontsize'].'px;';
        if ( @ $options['styles_input']['radio']['fcolor'] ) {
            echo 'color: ';
            if ( $options['styles_input']['radio']['fcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['radio']['fcolor'].';';
        }
        if ( @ $options['styles_input']['radio']['backcolor'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['radio']['backcolor'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['radio']['backcolor'].';';
        }
        echo '}';
        echo ' ul.berocket_aapf_widget > li > span > input[type="radio"]:checked + .berocket_label_widgets:before {';
        if ( @ $options['styles_input']['radio']['icon'] )
            echo 'content: "\\'.$options['styles_input']['radio']['icon'].'";';
        echo '}';
        echo '.berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content .ui-slider-range, .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content .ui-slider-range{';
        if ( @ $options['styles_input']['slider']['line_color'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['slider']['line_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['slider']['line_color'].';';
        }
        echo '}';
        echo '.berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content, .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content{';
        if ( @ $options['styles_input']['slider']['line_height'] )
            echo 'height: '.$options['styles_input']['slider']['line_height'].'px;';
        if ( @ $options['styles_input']['slider']['line_border_color'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['slider']['line_border_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['slider']['line_border_color'].';';
        }
        if ( @ $options['styles_input']['slider']['back_line_color'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['slider']['back_line_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['slider']['back_line_color'].';';
        }
        if ( @ $options['styles_input']['slider']['line_border_width'] || @ $options['styles_input']['slider']['line_border_width'] === '0' )
            echo 'border-width: '.$options['styles_input']['slider']['line_border_width'].'px;';
        echo '}';
        echo '.berocket_aapf_widget .slide .berocket_filter_slider .ui-state-default, 
            .berocket_aapf_widget .slide .berocket_filter_price_slider .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_slider.ui-widget-content .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_price_slider.ui-widget-content .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_slider .ui-widget-header .ui-state-default,
            .berocket_aapf_widget .slide .berocket_filter_price_slider .ui-widget-header .ui-state-default
            .berocket_aapf_widget .berocket_filter_slider.ui-widget-content .ui-slider-handle,
            .berocket_aapf_widget .berocket_filter_price_slider.ui-widget-content .ui-slider-handle{';
        if ( @ $options['styles_input']['slider']['button_size'] || @ $options['styles_input']['slider']['button_size'] === '0' )
            echo 'font-size: '.$options['styles_input']['slider']['button_size'].'px;';
        if ( @ $options['styles_input']['slider']['button_color'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['slider']['button_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['slider']['button_color'].';';
        }
        if ( @ $options['styles_input']['slider']['button_border_color'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['slider']['button_border_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['slider']['button_border_color'].';';
        }
        if ( @ $options['styles_input']['slider']['button_border_width'] || @ $options['styles_input']['slider']['button_border_width'] === '0' )
            echo 'border-width: '.$options['styles_input']['slider']['button_border_width'].'px;';
        if ( @ $options['styles_input']['slider']['button_border_radius'] || @ $options['styles_input']['slider']['button_border_radius'] === '0' )
            echo 'border-radius: '.$options['styles_input']['slider']['button_border_radius'].'px;';
        echo '}';
        echo ' .berocket_aapf_selected_area_hook div.berocket_aapf_widget_selected_area .berocket_aapf_widget_selected_filter a{'.( ( @ $uo['style']['selected_area_block'] ) ? 'background-'.@ $uo['style']['selected_area_block'] : '' ).( ( @ $uo['style']['selected_area_border'] ) ? ' border-'.@ $uo['style']['selected_area_border'] : '' ).'}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc {';
        if ( @ $options['styles_input']['pc_ub']['back_color'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['pc_ub']['back_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['back_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['border_color'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['pc_ub']['border_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['border_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['font_color'] ) {
            echo 'color: ';
            if ( $options['styles_input']['pc_ub']['font_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['font_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['font_size'] ) {
            echo 'font-size: '.$options['styles_input']['pc_ub']['font_size'].'px;';
        }
        echo '}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc > span {';
        if ( @ $options['styles_input']['pc_ub']['back_color'] ) {
            echo 'background-color: ';
            if ( $options['styles_input']['pc_ub']['back_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['back_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['border_color'] ) {
            echo 'border-color: ';
            if ( $options['styles_input']['pc_ub']['border_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['border_color'].';';
        }
        echo '}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_widget_update_button {';
        if ( @ $options['styles_input']['pc_ub']['show_font_color'] ) {
            echo 'color: ';
            if ( $options['styles_input']['pc_ub']['show_font_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['show_font_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['show_font_size'] ) {
            echo 'font-size: '.$options['styles_input']['pc_ub']['show_font_size'].'px;';
        }
        echo '}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_widget_update_button:hover {';
        if ( @ $options['styles_input']['pc_ub']['show_font_color_hover'] ) {
            echo 'color: ';
            if ( $options['styles_input']['pc_ub']['show_font_color_hover'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['show_font_color_hover'].';';
        }
        echo '}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_close_pc {';
        if ( @ $options['styles_input']['pc_ub']['close_font_color'] ) {
            echo 'color: ';
            if ( $options['styles_input']['pc_ub']['close_font_color'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['close_font_color'].';';
        }
        if ( @ $options['styles_input']['pc_ub']['close_size'] ) {
            echo 'font-size: '.$options['styles_input']['pc_ub']['close_size'].'px;';
        }
        echo '}';
        echo '.berocket_aapf_widget div.berocket_aapf_product_count_desc .berocket_aapf_close_pc:hover {';
        if ( @ $options['styles_input']['pc_ub']['close_font_color_hover'] ) {
            echo 'color: ';
            if ( $options['styles_input']['pc_ub']['close_font_color_hover'][0] != '#' ) {
                echo '#';
            }
            echo $options['styles_input']['pc_ub']['close_font_color_hover'].';';
        }
        echo '}';
        echo '</style>';
    }

    public static function create_metadata_table() {
        load_plugin_textdomain(BeRocket_AJAX_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
        wp_register_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
        wp_enqueue_style( 'font-awesome' );

        wp_register_style( 'berocket_aapf_widget-style', plugins_url( 'css/widget.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-style' );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url( 'css/scrollbar/Scrollbar.min.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );
        wp_register_style( 'berocket_aapf_widget-themer-style', plugins_url( 'css/styler/formstyler.css', __FILE__ ), "", BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-themer-style' );

        global $wpdb;
        $is_database = get_option( 'br_filters_color_database' );
        $type        = 'berocket_term';
        $table_name  = $wpdb->prefix . $type . 'meta';
        if ( ! $is_database ) {
            if ( ! empty ( $wpdb->charset ) ) {
                $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            }
            if ( ! empty ( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }

            $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
                meta_id bigint(20) NOT NULL AUTO_INCREMENT,
                {$type}_id bigint(20) NOT NULL default 0,
             
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext DEFAULT NULL,
                         
                UNIQUE KEY meta_id (meta_id)
            ) {$charset_collate};";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            update_option( 'br_filters_color_database', true );
        }
        $variable_name        = $type . 'meta';
        $wpdb->$variable_name = $table_name;
    }

    public static function selected_area() {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', self::get_aapf_option() );
        set_query_var( 'title', apply_filters( 'berocket_aapf_widget_title', @ $title ) );
        set_query_var( 'uo', br_aapf_converter_styles( @ $br_options['styles'] ) );
        set_query_var( 'selected_area_show', true );
        set_query_var( 'hide_selected_arrow', false );
        set_query_var( 'selected_is_hide', false );
        set_query_var( 'is_hooked', true );
        set_query_var( 'is_hide_mobile', false );
        br_get_template_part( 'widget_selected_area' );
        set_query_var( 'is_hooked', false );
    }

    public static function br_aapf_get_child() {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', self::get_aapf_option() );
        $taxonomy = $_POST['taxonomy'];
        $type = $_POST['type'];
        $term_id = $_POST['term_id'];
        $term_id = str_replace( '\\', '', $term_id );
        $term_id = json_decode($term_id);
        if ( $type == 'slider' ) {
            $all_terms_name = array();
            $terms_1        = get_terms( $taxonomy );
            $is_numeric = true;
            $terms = array();
            foreach ( $terms_1 as $term_ar ) {
                array_push( $all_terms_name, $term_ar->name );
                if( ! is_numeric( substr( $term_ar->name[0], 0, 1 ) ) ) {
                    $is_numeric = false;
                }
            }
            if( $is_numeric ) {
                sort( $all_terms_name, SORT_NUMERIC );
            } else {
                sort( $all_terms_name );
            }
            $start_terms    = array_search( $term_id[0], $all_terms_name );
            $end_terms      = array_search( $term_id[1], $all_terms_name );
            $all_terms_name = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
            foreach ( $all_terms_name as $term_name ) {
                $term_id = get_term_by ( 'name', $term_name, $taxonomy );
                $args_terms = array(
                    'orderby'    => 'id',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                    'parent'     => $term_id->term_id,
                );
                $current_terms = get_terms( $taxonomy, $args_terms );
                foreach ( $current_terms as $current_term ) {
                    $terms[] = $current_term;
                }
            }
            echo json_encode($terms);
        } else {
            if( is_array($term_id) && count($term_id) > 0 ) {
                $terms = array();
                foreach ( $term_id as $parent ) {
                    $args_terms = array(
                        'orderby'    => 'id',
                        'order'      => 'ASC',
                        'hide_empty' => false,
                        'parent'     => $parent,
                    );
                    if( $taxonomy == 'product_cat' ) {
                        $current_terms = BeRocket_AAPF_Widget::get_product_categories( '', $parent, array(), 0, 0, true );
                    } else {
                        $current_terms = get_terms( $taxonomy, $args_terms );
                    }
                    $new_terms = BeRocket_AAPF_Widget::get_attribute_values( $taxonomy, 'id', ( ! @ $br_options['show_all_values'] ), @ $br_options['recount_products'], $current_terms );
                    if ( is_array( $new_terms ) ) {
                        foreach ( $new_terms as $key => $term_val ) {
                            $new_terms[$key]->color = get_metadata( 'berocket_term', $term_val->term_id, 'color' );
                            $new_terms[$key]->r_class = '';
                            if( @ $br_options['hide_value']['o'] && isset($term_val->count) && $term_val->count == 0 ) {
                                $new_terms[$key]->r_class += 'berocket_hide_o_value ';
                            }
                        }
                    }
                    $terms = array_merge( $terms, $new_terms );
                }
                echo json_encode($terms);
            } else {
                echo json_encode($term_id);
            }
        }
        wp_die();
    }

    public static function WPML_fix() {
        global $sitepress;
        if ( method_exists( $sitepress, 'switch_lang' )
             && isset( $_POST['current_language'] )
             && $_POST['current_language'] !== $sitepress->get_default_language()
        ) {
            $sitepress->switch_lang( $_POST['current_language'], true );
        }
    }

    public static function loop_columns() {
        $options = self::get_aapf_option();
        $per_row = ( ( ! (int) @ $options['product_per_row'] || (int) @ $options['product_per_row'] < 1 ) ? 1 : (int) @ $options['product_per_row'] );
        return $per_row;
    }

    public static function order_by_popularity_post_clauses( $args ) {
        global $wpdb;
        $args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
        return $args;
    }

    public static function order_by_rating_post_clauses( $args ) {
        global $wpdb;
        $args['fields'] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
        $args['where'] .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
        $args['join'] .= "
            LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
            LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
            ";
        $args['orderby'] = "average_rating DESC, $wpdb->posts.post_date DESC";
        $args['groupby'] = "$wpdb->posts.ID";
        return $args;
    }
    public static function sanitize_aapf_option( $input ) {
        $default = BeRocket_AAPF::$defaults;
        $result = self::recursive_array_set( $default, $input );
        return $result;
    }
    public static function recursive_array_set( $default, $options ) {
        foreach( $default as $key => $value ) {
            if( array_key_exists( $key, $options ) ) {
                if( is_array( $value ) ) {
                    if( is_array( $options[$key] ) ) {
                        $result[$key] = self::recursive_array_set( $value, $options[$key] );
                    } else {
                        $result[$key] = self::recursive_array_set( $value, array() );
                    }
                } else {
                    $result[$key] = $options[$key];
                }
            } else {
                if( is_array( $value ) ) {
                    $result[$key] = self::recursive_array_set( $value, array() );
                } else {
                    $result[$key] = '';
                }
            }
        }
        foreach( $options as $key => $value ) {
            if( ! array_key_exists( $key, $result ) ) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    public static function get_aapf_option() {
        $options = get_option( 'br_filters_options' );
        if ( @ $options && is_array ( $options ) ) {
            $options = array_merge( BeRocket_AAPF::$defaults, $options );
        } else {
            $options = BeRocket_AAPF::$defaults;
        }
        return $options;
    }
    public static function wp_footer_widget() {
        global $br_widget_ids;
        echo '<div class="berocket_wc_shortcode_fix" style="display: none;">';
        foreach ( $br_widget_ids as $widget ) {
            $widget['br_wp_footer'] = true;
            $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget();
            $BeRocket_AAPF_Widget->widget( array(), $widget );
        }
        echo '</div>';
    }
}

new BeRocket_AAPF;