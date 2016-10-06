<?php
/**
 * class-groups-notifications-admin.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package groups-notifications
 * @since groups-notifications 1.0.0
 */

/**
 * Admin section for Groups Notifications.
 */
class Groups_Notifications_Admin {

	const NONCE = 'groups-notifications-admin-nonce';

	/**
	 * Admin setup.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 100 );
	}

	/**
	 * Admin CSS.
	 */
	public static function admin_init() {
		wp_register_style( 'groups_notifications_admin', GROUPS_NOTIFICATIONS_PLUGIN_URL . 'css/groups_notifications_admin.css', array(), GROUPS_NOTIFICATIONS_CORE_VERSION );
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_submenu_page(
			'groups-admin',
			__( 'Notifications' ),
			__( 'Notifications' ),
			GROUPS_ADMINISTER_OPTIONS,
			'groups_notifications',
			array( __CLASS__, 'settings' )
		);
// 		add_action( 'admin_print_scripts-' . $admin_page, array( __CLASS__, 'admin_print_scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( __CLASS__, 'admin_print_styles' ) );
	}
	
	/**
	 * Admin styles.
	 */
	public static function admin_print_styles() {
		wp_enqueue_style( 'groups_admin' );
		wp_enqueue_style( 'groups_notifications_admin' );
	}
	
	/**
	 * Loads scripts.
	 */
	public static function admin_print_scripts() {
		wp_enqueue_script( 'groups-notifications', GROUPS_NOTIFICATIONS_PLUGIN_URL . 'js/groups-notifications.js', array(), GROUPS_NOTIFICATIONS_CORE_VERSION );
	}

	/**
	 * Settings admin section.
	 */
	public static function settings() {
		if ( !current_user_can( GROUPS_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		}
		require_once( GROUPS_NOTIFICATIONS_ADMIN_LIB . '/class-groups-notifications-admin-settings.php' );
		Groups_Notifications_Admin_Settings::view();
	}
}
Groups_Notifications_Admin::init();
