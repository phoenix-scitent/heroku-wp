<?php
/**
 * class-groups-notifications.php
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
 * Notification handler.
 */
class Groups_Notifications {

	const NOTIFY_REGISTERED_GROUP                = 'notify_registered_group';

	const NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP = 'notify_admin_groups_created_user_group';
	const NOTIFY_ADMIN_SUBJECT_CREATED           = 'notify_admin_subject_created';
	const NOTIFY_ADMIN_MESSAGE_CREATED           = 'notify_admin_message_created';

	const NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP = 'notify_admin_groups_deleted_user_group';
	const NOTIFY_ADMIN_SUBJECT_DELETED           = 'notify_admin_subject_deleted';
	const NOTIFY_ADMIN_MESSAGE_DELETED           = 'notify_admin_message_deleted';

	const NOTIFY_USER_GROUPS_CREATED_USER_GROUP = 'notify_user_groups_created_user_group';
	const NOTIFY_USER_SUBJECT_CREATED           = 'notify_user_subject_created';
	const NOTIFY_USER_MESSAGE_CREATED           = 'notify_user_message_created';

	const NOTIFY_USER_GROUPS_DELETED_USER_GROUP = 'notify_user_groups_deleted_user_group';
	const NOTIFY_USER_SUBJECT_DELETED           = 'notify_user_subject_deleted';
	const NOTIFY_USER_MESSAGE_DELETED           = 'notify_user_message_deleted';

	const USE_SMTP             = 'notifications_use_smtp';
	const SMTP_EMAIL           = 'notifications_smtp_email';
	const SMTP_NAME            = 'notifications_smtp_name';
	const SMTP_SET_RETURN_PATH = 'notifications_set_return_path';
	const SMTP_HOST            = 'notifications_smtp_host';
	const SMTP_PORT            = 'notifications_smtp_port';
	const SMTP_ENCRYPTION      = 'notifications_smtp_encryption';
	const SMTP_AUTHENTICATION  = 'notifications_smtp_authentication';
	const SMTP_USER            = 'notifications_smtp_user';
	const SMTP_PASSWORD        = 'notifications_smtp_password';

	/**
	 * Default subjects.
	 * @var array
	 */
	public static $subjects = null;

	/**
	 * Default messages.
	 * @var array
	 */
	public static $messages = null;

	/**
	 * 
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Adds notifications on hooks.
	 */
	public static function wp_init() {
		self::$subjects = array(
			'admin' => array(
				'created' => __( '[user_login] has joined the [group_name] group', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
				'deleted' => __( '[user_login] has left the [group_name] group', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN )
			),
			'user' => array(
				'created' => __( 'Welcome to the [group_name] group', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
				'deleted' => __( 'You left the [group_name] group', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
			)
		);
		self::$messages = array(
			'admin' => array(
				'created' => __(
					'Greetings,<br/>
[user_login] is now a member of the [group_name] group at <a href="[site_url]">[site_title]</a>.<br/>
'
					, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN
				),
				'deleted' => __(
					'Greetings,<br/>
[user_login] has left the [group_name] group at <a href="[site_url]">[site_title]</a>.<br/>
'
					, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
			),
			'user' => array(
				'created' => __(
					'Greetings [user_login],<br/>
You are now a member of the [group_name] group at <a href="[site_url]">[site_title]</a>.<br/>
'
					, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN
				),
				'deleted' => __(
					'Greetings [user_login],<br/>
You have left the [group_name] group at <a href="[site_url]">[site_title]</a>.<br/>
'
					, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN
				),
			)
		);
		if (
			Groups_Options::get_option( self::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP, false ) ||
			Groups_Options::get_option( self::NOTIFY_USER_GROUPS_CREATED_USER_GROUP, false )
		) {
			add_action( 'groups_created_user_group', array( __CLASS__, 'groups_created_user_group' ), 10, 2 );
		}
		if (
			Groups_Options::get_option( self::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP, false ) ||
			Groups_Options::get_option( self::NOTIFY_USER_GROUPS_DELETED_USER_GROUP, false )
		) {
			add_action( 'groups_deleted_user_group', array( __CLASS__, 'groups_deleted_user_group' ), 10, 2 );
		}
	}

	/**
	 * Notification when a user has joined a group.
	 * @param int $user_id
	 * @param int $group_id
	 */
	public static function groups_created_user_group( $user_id, $group_id ) {
		require_once( GROUPS_NOTIFICATIONS_CORE_LIB .'/class-groups-notifications-mailer.php' );

		if ( Groups_Group::read( $group_id ) ) {

			$group = new Groups_Group( $group_id );
			if ( $group->name === Groups_Registered::REGISTERED_GROUP_NAME ) {
				if ( !Groups_Options::get_option( Groups_Notifications::NOTIFY_REGISTERED_GROUP, false ) ) {
					return;
				}
			}

			$user       = new Groups_User( $user_id );
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$tokens = array(
				'group_name' => $group->name,
				'user_email' => $user_email,
				'user_login' => $user_login
			);

			if ( Groups_Options::get_option( self::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP, false ) ) {
				$admin_email = get_bloginfo( 'admin_email' );
				if ( !empty( $admin_email ) ) {
					$subject = Groups_Options::get_option( self::NOTIFY_ADMIN_SUBJECT_CREATED, self::$subjects['admin']['created'] );
					$message = Groups_Options::get_option( self::NOTIFY_ADMIN_MESSAGE_CREATED, self::$messages['admin']['created'] );
					Groups_Notifications_Mailer::mail(
						$admin_email,
						$subject,
						$message,
						$tokens
					);
				}
			}

			if ( Groups_Options::get_option( self::NOTIFY_USER_GROUPS_CREATED_USER_GROUP, false ) ) {
				if ( !empty( $user_email ) ) {
					$subject = Groups_Options::get_option( self::NOTIFY_USER_SUBJECT_CREATED, self::$subjects['user']['created'] );
					$message = Groups_Options::get_option( self::NOTIFY_USER_MESSAGE_CREATED, self::$messages['user']['created'] );
					Groups_Notifications_Mailer::mail(
						$user_email,
						$subject,
						$message,
						$tokens
					);
				}
			}

		}

	}

	/**
	 * Notification when a user has left a group.
	 * @param int $user_id
	 * @param int $group_id
	 */
	public static function groups_deleted_user_group( $user_id, $group_id ) {

		require_once( GROUPS_NOTIFICATIONS_CORE_LIB .'/class-groups-notifications-mailer.php' );

		if ( Groups_Group::read( $group_id ) ) {

			$group = new Groups_Group( $group_id );
			if ( $group->name === Groups_Registered::REGISTERED_GROUP_NAME ) {
				if ( !Groups_Options::get_option( Groups_Notifications::NOTIFY_REGISTERED_GROUP, false ) ) {
					return;
				}
			}

			// triggered by user deletion => abort
			if ( !get_user_by( 'id', $user_id ) ) {
				return;
			}

			$user       = new Groups_User( $user_id );
			$user_login = stripslashes( $user->user_login );
			$user_email = stripslashes( $user->user_email );

			$tokens = array(
				'group_name' => $group->name,
				'user_email' => $user_email,
				'user_login' => $user_login
			);

			if ( Groups_Options::get_option( self::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP, false ) ) {
				$admin_email = get_bloginfo( 'admin_email' );
				if ( !empty( $admin_email ) ) {
					$subject = Groups_Options::get_option( self::NOTIFY_ADMIN_SUBJECT_DELETED, self::$subjects['admin']['deleted'] );
					$message = Groups_Options::get_option( self::NOTIFY_ADMIN_MESSAGE_DELETED, self::$messages['admin']['deleted'] );
					Groups_Notifications_Mailer::mail(
						$admin_email,
						$subject,
						$message,
						$tokens
					);
				}
			}

			if ( Groups_Options::get_option( self::NOTIFY_USER_GROUPS_DELETED_USER_GROUP, false ) ) {
				if ( !empty( $user_email ) ) {
					$subject = Groups_Options::get_option( self::NOTIFY_USER_SUBJECT_DELETED, self::$subjects['user']['deleted'] );
					$message = Groups_Options::get_option( self::NOTIFY_USER_MESSAGE_DELETED, self::$messages['user']['deleted'] );
					Groups_Notifications_Mailer::mail(
						$user_email,
						$subject,
						$message,
						$tokens
					);
				}
			}

		}
	}
}
Groups_Notifications::init();
