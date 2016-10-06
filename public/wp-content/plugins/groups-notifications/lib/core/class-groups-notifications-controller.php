<?php
/**
 * class-groups-notifications-controller.php
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
 * Plugin controller
 */
class Groups_Notifications_Controller {

	public static $admin_messages = array();

	/**
	 * Boot the plugin.
	 */
	public static function boot() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		require_once( GROUPS_NOTIFICATIONS_CORE_LIB . '/class-groups-notifications-update.php');
		if ( self::check_dependencies() ) { // can't force deactivation even though setup depends on Groups functions because on update it would deactivate the plugin (WP 3.4.2)
			register_activation_hook( GROUPS_NOTIFICATIONS_FILE, array( __CLASS__, 'activate' ) );
			register_deactivation_hook( GROUPS_NOTIFICATIONS_FILE, array( __CLASS__, 'deactivate' ) );
			add_action( 'init', array( __CLASS__, 'init' ) );
			add_action( 'wpmu_new_blog', array( __CLASS__, 'wpmu_new_blog' ), 10, 2 );
			add_action( 'delete_blog', array( __CLASS__, 'delete_blog' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 4 );
			require_once( GROUPS_NOTIFICATIONS_CORE_LIB . '/class-groups-notifications.php' );
			if ( is_admin() ) {
				require_once( GROUPS_NOTIFICATIONS_ADMIN_LIB . '/class-groups-notifications-admin.php');
			}
		}
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Verify dependency on Groups.
	 *
	 * @param boolean $disable If true, disables the plugin if dependencies are not met. Defaults to false.
	 * @return true if dependencies are met, otherwise false.
	 */
	public static function check_dependencies( $disable = false ) {
		$result = true;
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}
		$groups_is_active = in_array( 'groups/groups.php', $active_plugins );
		if ( !$groups_is_active ) {
			self::$admin_messages[] = "<div class='error'>" . __( 'The <strong>Groups</strong> plugin is missing.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . "</div>";
		}
		if ( !$groups_is_active ) {
			if ( $disable ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				deactivate_plugins( array( GROUPS_NOTIFICATIONS_FILE ) );
			}
			$result = false;
		}
		return $result;
	}
	
	/**
	 * Run activation for a newly created blog in a multisite environment.
	 * 
	 * @param int $blog_id
	 */
	public static function wpmu_new_blog( $blog_id, $user_id ) {
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			if ( key_exists( 'groups-notifications/groups-notifications.php', $active_sitewide_plugins ) ) {
				Groups_Controller::switch_to_blog( $blog_id );
				self::setup();
				Groups_Controller::restore_current_blog();
			}
		}
	}
	
	/**
	 * Run deactivation for a blog that is about to be deleted in a multisite
	 * environment.
	 * 
	 * @param int $blog_id
	 */
	public static function delete_blog( $blog_id, $drop = false ) {
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			if ( key_exists( 'groups-notifications/groups-notifications.php', $active_sitewide_plugins ) ) {
				Groups_Controller::switch_to_blog( $blog_id );
				self::cleanup( $drop );
				Groups_Controller::restore_current_blog();
			}
		}
	}
	
	/**
	 * Initialize.
	 * 
	 * - Loads the plugin's translations as part of Groups'.
	 * - Version check.
	 */
	public static function init() {
		load_plugin_textdomain( GROUPS_PLUGIN_DOMAIN, null, 'groups-notifications/languages' );
		self::version_check();
	}
	
	/**
	 * Plugin activation.
	 * @param boolean $network_wide
	 */
	public static function activate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$blog_ids = Groups_Utility::get_blogs();
			foreach ( $blog_ids as $blog_id ) {
				Groups_Controller::switch_to_blog( $blog_id );
				self::setup();
				Groups_Controller::restore_current_blog();
			}
		} else {
			self::setup();
		}
	}

	/**
	 * Plugin activation work.
	 */
	private static function setup() {
	}

	/**
	 * Checks current version and triggers update if needed.
	 */
	public static function version_check() {
		global $groups_admin_messages;
		$previous_version = get_option( 'groups_notifications_plugin_version', null );
		if ( strcmp( $previous_version, GROUPS_NOTIFICATIONS_CORE_VERSION ) < 0 ) {
			if ( self::update( $previous_version ) ) {
				update_option( 'groups_notifications_plugin_version', GROUPS_NOTIFICATIONS_CORE_VERSION );
			} else {
				$groups_admin_messages[] = '<div class="error">Updating Groups Notifications plugin core <em>failed</em>.</div>';
			}
		}
	}
	
	/**
	 * Update maintenance.
	 */
	public static function update( $previous_version ) {
		global $wpdb, $groups_admin_messages;
		$result = true;
		$queries = array();
		switch ( $previous_version ) {
			// nothing to do yet
		} // switch
		foreach ( $queries as $query ) {
			if ( $wpdb->query( $query ) === false ) {
				$result = false;
			}
		}
		return $result;
	}
	
	/**
	* Drop tables and clear data if the plugin is deactivated.
	* This will happen only if the user chooses to delete data upon deactivation in Groups.
	* @param boolean $network_wide
	*/
	public static function deactivate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			if ( class_exists( 'Groups_Options' ) ) {
				if ( Groups_Options::get_option( 'groups_network_delete_data', false ) ) {
					$blog_ids = Groups_Utility::get_blogs();
					foreach ( $blog_ids as $blog_id ) {
						Groups_Controller::switch_to_blog( $blog_id );
						self::cleanup( true );
						Groups_Controller::restore_current_blog();
					}
				}
			}
		} else {
			self::cleanup();
		}
	}

	/**
	 * Plugin deactivation cleanup.
	 * @param $drop overrides the groups_delete_data option, default is false
	 */
	private static function cleanup( $drop = false ) {
		global $wpdb;
		if ( class_exists( 'Groups_Options' ) ) {
			$delete_data = Groups_Options::get_option( 'groups_delete_data', false );
			if ( $delete_data || $drop ) {
				delete_option( 'groups_notifications_plugin_version' );
			}
		}
	}

	/**
	 * Not used. 
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @param string $status
	 */
	public static function after_plugin_row( $plugin_file, $plugin_data, $status) {
	}

	/**
	 * Show warning when data is deleted on deactivation.
	 * @param array $plugin_meta
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @param string $status
	 */
	public static function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $plugin_file == plugin_basename( GROUPS_NOTIFICATIONS_FILE ) ) {
			if ( class_exists( 'Groups_Options' ) ) {
				if ( is_multisite() ) {
					if ( Groups_Options::get_option( 'groups_network_delete_data', false ) ) {
						$plugin_meta[] =
						'<span style="background-color:#fff;color:#600;font-weight:bold;">' .
						__( 'WARNING : Groups is set to delete network data on deactivation. This plugin will DELETE all its data when deactivated network-wide.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) .
						'</span>';
					}
				} else {
					if ( Groups_Options::get_option( 'groups_delete_data', false ) ) {
						$plugin_meta[] =
						'<span style="background-color:#fff;color:#600;font-weight:bold;">' .
						__( 'WARNING : Groups is set to delete data on deactivation. This plugin will DELETE its data when deactivated.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) .
						'</span>';
					}
				}
			}
		}
		return $plugin_meta;
	}

	/**
	 * Returns the table name, needed to make things simple on activation/deactivation when
	 * Groups is NOT activated.
	 * @param string $name
	 * @return string
	 */
	public static function get_tablename( $name ) {
		if ( function_exists( '_groups_get_tablename' ) ) {
			return _groups_get_tablename( $name );
		} else {
			global $wpdb;
			return $wpdb->prefix . 'groups_' . $name; // pray GROUPS_TP never should change
		}
	}
}
Groups_Notifications_Controller::boot();
