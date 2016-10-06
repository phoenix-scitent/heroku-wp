<?php
/**
 * Register Activation Hook
 */
update_option('wc_predictive_search_plugin', 'woo_predictive_search');
function wc_predictive_install(){
	global $wpdb;
	$woocommerce_search_page_id = WC_Predictive_Search_Functions::create_page( _x('woocommerce-search', 'page_slug', 'woops'), 'woocommerce_search_page_id', __('Woocommerce Predictive Search', 'woops'), '[woocommerce_search]' );
	WC_Predictive_Search_Functions::auto_create_page_for_wpml( $woocommerce_search_page_id, _x('woocommerce-search', 'page_slug', 'woops'), __('Woocommerce Predictive Search', 'woops'), '[woocommerce_search]' );

	// Set Settings Default from Admin Init
	global $wc_predictive_search_admin_init;
	$wc_predictive_search_admin_init->set_default_settings();

	global $wc_predictive_search;
	$wc_predictive_search->install_databases();

	update_option('wc_predictive_search_lite_version', '3.6.0');

	// Build sass
	global $wc_predictive_search_less;
	$wc_predictive_search_less->plugin_build_sass();

	delete_metadata( 'user', 0, $wc_predictive_search_admin_init->plugin_name . '-' . 'plugin_framework_global_box' . '-' . 'opened', '', true );

	flush_rewrite_rules();

	update_option('wc_predictive_search_just_installed', true);
}

function woops_init() {
	if ( get_option('wc_predictive_search_just_installed') ) {
		delete_option('wc_predictive_search_just_installed');

		@set_time_limit(86400);
		@ini_set("memory_limit","1000M");

		global $wc_ps_synch;
		$wc_ps_synch->synch_full_database();

		update_option('wc_predictive_search_just_confirm', 1);
		wp_redirect( admin_url( 'admin.php?page=woo-predictive-search', 'relative' ) );
		exit;
	}
	load_plugin_textdomain( 'woops', false, WOOPS_FOLDER.'/languages' );
}

// Add language
add_action('init', 'woops_init');

// Add custom style to dashboard
add_action( 'admin_enqueue_scripts', array( 'WC_Predictive_Search_Hook_Filter', 'a3_wp_admin' ) );

add_action( 'plugins_loaded', array( 'WC_Predictive_Search_Hook_Filter', 'plugins_loaded' ), 8 );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WC_Predictive_Search_Hook_Filter', 'plugin_extra_links'), 10, 2 );

function register_widget_woops_predictive_search() {
	register_widget('WC_Predictive_Search_Widgets');
}

// Need to call Admin Init to show Admin UI
global $wc_predictive_search_admin_init;
$wc_predictive_search_admin_init->init();

// Add upgrade notice to Dashboard pages
add_filter( $wc_predictive_search_admin_init->plugin_name . '_plugin_extension_boxes', array( 'WC_Predictive_Search_Hook_Filter', 'plugin_extension_box' ) );

// Custom Rewrite Rules
add_filter( 'query_vars', array( 'WC_Predictive_Search_Functions', 'add_query_vars' ) );
add_filter( 'rewrite_rules_array', array( 'WC_Predictive_Search_Functions', 'add_rewrite_rules' ) );

// Registry widget
add_action('widgets_init', 'register_widget_woops_predictive_search');

// Add shortcode [woocommerce_search]
add_shortcode('woocommerce_search', array('WC_Predictive_Search_Shortcodes', 'parse_shortcode_search_result'));


// Check upgrade functions
add_action( 'init', 'woo_ps_lite_upgrade_plugin' );
function woo_ps_lite_upgrade_plugin() {
	global $wc_predictive_search_admin_init;

    // Upgrade to 2.0
    if (version_compare(get_option('wc_predictive_search_lite_version'), '2.0') === -1) {
        update_option('wc_predictive_search_lite_version', '2.0');

        include( WOOPS_DIR. '/includes/updates/update-2.0.php' );
    }

    // Upgrade to 3.0
    if(version_compare(get_option('wc_predictive_search_lite_version'), '3.0.0') === -1){
        update_option('wc_predictive_search_lite_version', '3.0.0');

        include( WOOPS_DIR. '/includes/updates/update-3.0.php' );
    }

    // Upgrade to 3.2.0
	if(version_compare(get_option('wc_predictive_search_lite_version'), '3.2.0') === -1){
		update_option('wc_predictive_search_lite_version', '3.2.0');

		include( WOOPS_DIR. '/includes/updates/update-3.2.0.php' );
	}

	// Upgrade to 3.6.0
	if( version_compare(get_option('wc_predictive_search_lite_version'), '3.6.0') === -1 ){
		update_option('wc_predictive_search_lite_version', '3.6.0');

		// Set Settings Default from Admin Init
		$wc_predictive_search_admin_init->set_default_settings();

		// Build sass
		global $wc_predictive_search_less;
		$wc_predictive_search_less->plugin_build_sass();
	}

    update_option('wc_predictive_search_lite_version', '3.6.0');
}
?>
