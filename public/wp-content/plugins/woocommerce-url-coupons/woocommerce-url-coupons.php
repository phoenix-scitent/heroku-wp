<?php
/**
 * Plugin Name: WooCommerce URL Coupons
 * Plugin URI: http://www.woothemes.com/products/url-coupons/
 * Description: Create a unique URL that applies a discount and optionally adds one or more products to the customer's cart.
 * Author: WooThemes / SkyVerge
 * Author URI: http://www.woothemes.com
 * Version: 2.2.1
 * Text Domain: woocommerce-url-coupons
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-URL-Coupons
 * @author      SkyVerge
 * @category    Marketing
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '9912621edf36273767b4166cda88fa54', '184613' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.2.0', __( 'WooCommerce URL Coupons', 'woocommerce-url-coupons' ), __FILE__, 'init_woocommerce_url_coupons', array( 'minimum_wc_version' => '2.3.6', 'backwards_compatible' => '4.2.0' ) );

function init_woocommerce_url_coupons() {

/**
 * Main Plugin Class
 *
 * @since 1.0
 */
class WC_URL_Coupons extends SV_WC_Plugin {

	
	/** plugin version number */
	const VERSION = '2.2.1';

	/** @var WC_URL_Coupons single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'url_coupons';

	/** plugin text domain, DEPRECATED as of 2.2.0 */
	const TEXT_DOMAIN = 'woocommerce-url-coupons';

	/** @var WC_URL_Coupons_Frontend instance */
	public $frontend;

	/** @var WC_URL_Coupons_Admin instance */
	public $admin;

	/** @var WC_URL_Coupons_Ajax instance */
	public $ajax;


	/**
	 * Bootstrap plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct( self::PLUGIN_ID, self::VERSION );

		$this->includes();
	}


	/**
	 * Include required files
	 *
	 * @since 2.0.0
	 */
	public function includes() {

		if ( is_admin() ) {

			// admin
			$this->admin = $this->load_class( '/includes/admin/class-wc-url-coupons-admin.php', 'WC_URL_Coupons_Admin' );

			if ( is_ajax() ) {
				$this->ajax = $this->load_class( '/includes/class-wc-url-coupons-ajax.php', 'WC_URL_Coupons_AJAX' );
			}

		} elseif ( ! is_ajax() ) {

			// frontend
			$this->frontend = $this->load_class( '/includes/class-wc-url-coupons-frontend.php', 'WC_URL_Coupons_Frontend' );
		}

	}


	/** Admin methods ******************************************************/


	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-url-coupons', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/** Helper methods ******************************************************/


	/**
	 * Main URL Coupons Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.3.0
	 * @see wc_url_coupons()
	 * @return WC_URL_Coupons
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce URL Coupons', 'woocommerce-url-coupons' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 2.1.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
	    return 'http://docs.woothemes.com/document/url-coupons/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 2.1.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Upgrade to the current version
	 *
	 * @since 1.0
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed_version
	 */
	public function upgrade( $installed_version ) {

		// upgrade to 1.0.2
		if ( version_compare( $installed_version, '1.0.2', '<' ) ) {

			// prior versions had a bug where any coupons trashed would not remove the associated unique URL from the active
			// list, resulting in "coupon does not exist" errors when the unique URLs were visited. This wasn't a very visible
			// problem with very unique URLs, but becomes a serious problem when someone uses "/checkout/" as the unique URL ಠ_ಠ

			// load active coupon list
			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// iterate through post IDs
			foreach ( $coupons as $coupon_id => $coupon_data ) {

				// if coupon doesn't exist or is not published, remove from active list
				if ( 'publish' !== get_post_status( $coupon_id ) ) {
					unset( $coupons[ $coupon_id ] );
				}
			}

			// update active list
			update_option( 'wc_url_coupons_active_urls', $coupons );

			// clear transient
			delete_transient( 'wc_url_coupons_active_urls' );
		}

		// upgrade to 2.0.0
		if ( version_compare( $installed_version, '2.0.0', '<' ) ) {

			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// 2 changes to the coupon data:
			// 1) "force apply" is now called "defer apply"
			// 2) prior versions didn't support the redirect page type and while we
			// can (and do) use `page` as the default, it's nicer to have the redirect page
			// type set properly
			foreach ( $coupons as $coupon_id => $data ) {

				// force => defer
				$coupons[ $coupon_id ]['defer'] = isset( $coupons[ $coupon_id ]['force'] ) ? $coupons[ $coupon_id ]['force'] : false;
				if ( $coupons[ $coupon_id ]['defer'] ) {
					update_post_meta( $coupon_id, '_wc_url_coupons_defer_apply', 'yes' );
				}

				// remove force
				unset( $coupons[ $coupon_id ]['force'] );
				delete_post_meta( $coupon_id, '_wc_url_coupons_force_apply' );


				// update redirect page type
				if ( empty( $data['redirect'] ) ) {
					continue;
				}

				$post_type = get_post_type( $data['redirect'] );

				// no existing redirects should be set to these post types, but just in case
				if ( ! $post_type ) {
					$post_type = 'page';
				} elseif ( 'product_variation' === $post_type ) {
					$post_type = 'product';
				}

				$coupons[ $coupon_id ]['redirect_page_type'] = $post_type;
			}

			// update active list
			update_option( 'wc_url_coupons_active_urls', $coupons );

			// clear transient
			delete_transient( 'wc_url_coupons_active_urls' );
		}

		// upgrade to 2.1.1
		if ( version_compare( $installed_version, '2.1.1', '<' ) ) {

			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// prior versions didn't update the redirect post type coupon meta
			foreach ( $coupons as $coupon_id => $data ) {

				// update redirect page type
				if ( empty( $data['redirect'] ) || get_post_meta( $coupon_id, '_wc_url_coupons_redirect_page_type', true ) ) {
					continue;
				}

				$post_type = get_post_type( $data['redirect'] );

				// no existing redirects should be set to these post types, but just in case
				if ( ! $post_type ) {
					$post_type = 'page';
				} elseif ( 'product_variation' === $post_type ) {
					$post_type = 'product';
				}

				update_post_meta( $coupon_id, '_wc_url_coupons_redirect_page_type', $post_type );
			}
		}

	}


} // end WC_URL_Coupons class


/**
 * Returns the One True Instance of URL Coupons
 *
 * @since 1.3.0
 * @return WC_URL_Coupons
 */
function wc_url_coupons() {
	return WC_URL_Coupons::instance();
}

// fire ze missiles!
wc_url_coupons();

} // init_woocommerce_url_coupons()
