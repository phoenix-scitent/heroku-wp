<?php
/**
 * groups-notifications.php
 *
 * Copyright (c) 2012 "kento" Karim Rahimpur www.itthinx.com
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
 *
 * Plugin Name: Groups Notifications
 * Plugin URI: http://www.itthinx.com/plugins/groups-notifications
 * Description: Notifications for Groups.
 * Version: 1.1.1
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * Donate-Link: http://www.itthinx.com
 * License: GPLv3
 */
define( 'GROUPS_NOTIFICATIONS_CORE_VERSION', '1.1.1' );

define( 'GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN', 'groups' );
define( 'GROUPS_NOTIFICATIONS_FILE', __FILE__ );

define( 'GROUPS_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url( GROUPS_NOTIFICATIONS_FILE ) );

if ( !defined( 'GROUPS_NOTIFICATIONS_CORE_URL' ) ) {
	define( 'GROUPS_NOTIFICATIONS_CORE_URL', WP_PLUGIN_URL . '/groups-notifications' );
}
if ( !defined( 'GROUPS_NOTIFICATIONS_CORE_DIR' ) ) {
	define( 'GROUPS_NOTIFICATIONS_CORE_DIR', WP_PLUGIN_DIR . '/groups-notifications' );
}
if ( !defined( 'GROUPS_NOTIFICATIONS_CORE_LIB' ) ) {
	define( 'GROUPS_NOTIFICATIONS_CORE_LIB', GROUPS_NOTIFICATIONS_CORE_DIR . '/lib/core' );
}
if ( !defined( 'GROUPS_NOTIFICATIONS_ADMIN_LIB' ) ) {
	define( 'GROUPS_NOTIFICATIONS_ADMIN_LIB', GROUPS_NOTIFICATIONS_CORE_DIR . '/lib/admin' );
}
require_once( GROUPS_NOTIFICATIONS_CORE_LIB . '/class-groups-notifications-controller.php');
