<?php
/**
 * class-groups-notifications-admin-notifications.php
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
 * @since groups-notifications 1.3.0
 */

/**
 * Admin settings for Groups Notifications.
 */
class Groups_Notifications_Admin_Settings {

	const NONCE = 'groups-notifications-admin-nonce';

	/**
	 * Show notifications
	 */
	public static function view() {

		$output = '';

		if ( !current_user_can( GROUPS_ADMINISTER_GROUPS ) ) {
			wp_die( __( 'Access denied.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		}

		if ( isset( $_POST['submit'] ) && isset( $_POST[self::NONCE] ) ) {

			if ( !wp_verify_nonce( $_POST[self::NONCE], 'settings' ) ) {
				wp_die( __( 'I fart in your general direction.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
			}

			Groups_Options::update_option( Groups_Notifications::NOTIFY_REGISTERED_GROUP, !empty( $_POST[Groups_Notifications::NOTIFY_REGISTERED_GROUP] ) );

			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP, !empty( $_POST[Groups_Notifications::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP] ) );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_SUBJECT_CREATED, $_POST[Groups_Notifications::NOTIFY_ADMIN_SUBJECT_CREATED] );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_MESSAGE_CREATED, $_POST[Groups_Notifications::NOTIFY_ADMIN_MESSAGE_CREATED] );

			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP, !empty( $_POST[Groups_Notifications::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP] ) );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_SUBJECT_DELETED, $_POST[Groups_Notifications::NOTIFY_ADMIN_SUBJECT_DELETED] );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_ADMIN_MESSAGE_DELETED, $_POST[Groups_Notifications::NOTIFY_ADMIN_MESSAGE_DELETED] );

			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_GROUPS_CREATED_USER_GROUP, !empty( $_POST[Groups_Notifications::NOTIFY_USER_GROUPS_CREATED_USER_GROUP] ) );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_SUBJECT_CREATED, $_POST[Groups_Notifications::NOTIFY_USER_SUBJECT_CREATED] );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_MESSAGE_CREATED, $_POST[Groups_Notifications::NOTIFY_USER_MESSAGE_CREATED] );

			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_GROUPS_DELETED_USER_GROUP, !empty( $_POST[Groups_Notifications::NOTIFY_USER_GROUPS_DELETED_USER_GROUP] ) );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_SUBJECT_DELETED, $_POST[Groups_Notifications::NOTIFY_USER_SUBJECT_DELETED] );
			Groups_Options::update_option( Groups_Notifications::NOTIFY_USER_MESSAGE_DELETED, $_POST[Groups_Notifications::NOTIFY_USER_MESSAGE_DELETED] );

			Groups_Options::update_option( Groups_Notifications::USE_SMTP, !empty( $_POST[Groups_Notifications::USE_SMTP] ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_EMAIL, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_EMAIL] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_NAME, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_NAME] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_SET_RETURN_PATH, !empty( $_POST[Groups_Notifications::SMTP_SET_RETURN_PATH] ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_HOST, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_HOST] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_PORT, absint( trim( $_POST[Groups_Notifications::SMTP_PORT] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_ENCRYPTION, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_ENCRYPTION] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_AUTHENTICATION, !empty( $_POST[Groups_Notifications::SMTP_AUTHENTICATION] ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_USER, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_USER] ) ) );
			Groups_Options::update_option( Groups_Notifications::SMTP_PASSWORD, wp_strip_all_tags( trim( $_POST[Groups_Notifications::SMTP_PASSWORD] ) ) );

		}

		$notify_registered_group = Groups_Options::get_option( Groups_Notifications::NOTIFY_REGISTERED_GROUP, false );

		$notify_admin_created  = Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP, false );
		$subject_admin_created = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_SUBJECT_CREATED, Groups_Notifications::$subjects['admin']['created'] ) );
		$message_admin_created = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_MESSAGE_CREATED, Groups_Notifications::$messages['admin']['created'] ) );

		$notify_user_created  = Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_GROUPS_CREATED_USER_GROUP, false );
		$subject_user_created = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_SUBJECT_CREATED, Groups_Notifications::$subjects['user']['created'] ) );
		$message_user_created = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_MESSAGE_CREATED, Groups_Notifications::$messages['user']['created'] ) );

		$notify_admin_deleted  = Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP, false );
		$subject_admin_deleted = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_SUBJECT_DELETED, Groups_Notifications::$subjects['admin']['deleted'] ) );
		$message_admin_deleted = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_ADMIN_MESSAGE_DELETED, Groups_Notifications::$messages['admin']['deleted'] ) );

		$notify_user_deleted  = Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_GROUPS_DELETED_USER_GROUP, false );
		$subject_user_deleted = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_SUBJECT_DELETED, Groups_Notifications::$subjects['user']['deleted'] ) );
		$message_user_deleted = stripslashes( Groups_Options::get_option( Groups_Notifications::NOTIFY_USER_MESSAGE_DELETED, Groups_Notifications::$messages['user']['deleted'] ) );

		// SMTP
		$use_smtp               = Groups_Options::get_option( Groups_Notifications::USE_SMTP, false );
		$smtp_email             = Groups_Options::get_option( Groups_Notifications::SMTP_EMAIL, '' );
		$smtp_name              = Groups_Options::get_option( Groups_Notifications::SMTP_NAME, 'Notifications' );
		$smtp_set_return_path   = Groups_Options::get_option( Groups_Notifications::SMTP_SET_RETURN_PATH, false );
		$smtp_host              = Groups_Options::get_option( Groups_Notifications::SMTP_HOST, '' );
		$smtp_port              = Groups_Options::get_option( Groups_Notifications::SMTP_PORT, '' );
		$smtp_encryption        = Groups_Options::get_option( Groups_Notifications::SMTP_ENCRYPTION, null );
		$smtp_authentication    = Groups_Options::get_option( Groups_Notifications::SMTP_AUTHENTICATION, true );
		$smtp_user              = Groups_Options::get_option( Groups_Notifications::SMTP_USER, '' );
		$smtp_password          = Groups_Options::get_option( Groups_Notifications::SMTP_PASSWORD, '' );

		// options form
		$output .= '<h2>' . __( 'Notification Settings', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h2>';

		$output .= '<div class="groups-notifications-settings">';
		$output .= '<form action="" name="settings" method="post">';
		$output .= '<div>';

		$output .= '<div class="panel">';
		$output .= '<h3>' . __( 'Message format', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h3>';

		$output .= '<p class="description">' . __( 'The message format is HTML.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';
		$output .= '<p class="description">' . __( 'Line breaks must be inserted explicitly using <code>&lt;br/&gt;</code>.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';
		$output .= '<p class="description">' . __( 'A plain text version is generated automatically and included with the HTML version of messages sent.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';
		$output .= '<p class="description">' . __( 'These tokens can be used in the subject and message: [group_name] [user_email] [user_login] [site_title] [site_url].', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';
		$output .= '</div>'; // .panel

		$output .= '<div class="save">';
		$output .= '<input type="submit" class="button" name="submit" value="' . __( 'Save', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '"/>';
		$output .= '</div>'; // .save

		$output .= '<h3>';
		$output .= __( 'Registered group', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</h3>';

		$output .= '<div class="check">';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', esc_attr( Groups_Notifications::NOTIFY_REGISTERED_GROUP ), $notify_registered_group ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Send notifications for the <em>Registered</em> group.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</div>'; // .check

		//
		// Admin notifications
		//

		$output .= '<h3>';
		$output .= __( 'Administrator Notifications', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</h3>';

		$output .= '<p>' . __( 'When enabled, these notifications are sent to the site admin.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';

		//
		// Notify admin when a user has joined a group
		//

		$output .= '<div class="notification">';

		$output .= '<h4>' . __( 'User joined', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h4>';

		$output .= '<div class="check">';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', esc_attr( Groups_Notifications::NOTIFY_ADMIN_GROUPS_CREATED_USER_GROUP ), $notify_admin_created ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Notify when a user has joined a group.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</div>'; // .check

		$output .= '<div class="subject">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Subject', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::NOTIFY_ADMIN_SUBJECT_CREATED, esc_attr( $subject_admin_created ) );
		$output .= '</label>';
		$output .= '</div>'; // .subject

		$output .= '<div class="message">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Message', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<textarea name="%s">%s</textarea>', Groups_Notifications::NOTIFY_ADMIN_MESSAGE_CREATED, stripslashes( $message_admin_created ) );
		$output .= '</label>';
		$output .= '</div>'; // .message

		$output .= '<div class="defaults">';
		$output .= '<span class="title">';
		$output .= __( 'Defaults', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$subjects['admin']['created'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$messages['admin']['created'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '</div>'; // .defaults

		$output .= '</div>'; // .notification

		//
		// Notify admin when a user has left a group
		//

		$output .= '<div class="notification">';

		$output .= '<h4>' . __( 'User left', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h4>';

		$output .= '<div class="check">';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', esc_attr( Groups_Notifications::NOTIFY_ADMIN_GROUPS_DELETED_USER_GROUP ), $notify_admin_deleted ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Notify when a user has left a group.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</div>'; // .check

		$output .= '<div class="label">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Subject', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::NOTIFY_ADMIN_SUBJECT_DELETED, esc_attr( $subject_admin_deleted ) );
		$output .= '</label>';
		$output .= '</div>'; // .label

		$output .= '<div class="message">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Message', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<textarea name="%s">%s</textarea>', Groups_Notifications::NOTIFY_ADMIN_MESSAGE_DELETED, stripslashes( $message_admin_deleted ) );
		$output .= '</label>';
		$output .= '</div>'; // .message
		
		$output .= '<div class="defaults">';
		$output .= '<span class="title">';
		$output .= __( 'Defaults', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$subjects['admin']['deleted'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$messages['admin']['deleted'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '</div>'; // .defaults

		$output .= '</div>'; // .notification

		//
		// User notifications
		//

		$output .= '<h3>';
		$output .= __( 'User Notifications', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</h3>';
		
		$output .= '<p>' . __( 'When enabled, these notifications are sent to the user.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</p>';

		//
		// Notify user after joining a group
		//

		$output .= '<div class="notification">';

		$output .= '<h4>' . __( 'User joined', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h4>';

		$output .= '<div class="check">';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', esc_attr( Groups_Notifications::NOTIFY_USER_GROUPS_CREATED_USER_GROUP ), $notify_user_created ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Notify when the user has joined a group.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</div>'; // .check

		$output .= '<div class="subject">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Subject', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::NOTIFY_USER_SUBJECT_CREATED, esc_attr( $subject_user_created ) );
		$output .= '</label>';
		$output .= '</div>'; // .subject

		$output .= '<div class="message">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Message', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<textarea name="%s">%s</textarea>', Groups_Notifications::NOTIFY_USER_MESSAGE_CREATED, stripslashes( $message_user_created ) );
		$output .= '</label>';
		$output .= '</div>'; // .message
		
		$output .= '<div class="defaults">';
		$output .= '<span class="title">';
		$output .= __( 'Defaults', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$subjects['user']['created'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$messages['user']['created'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '</div>'; // .defaults

		$output .='</div>'; // .notification

		//
		// Notify user after leaving a group 
		//

		$output .= '<div class="notification">';

		$output .= '<h4>' . __( 'User left', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h4>';

		$output .= '<div class="check">';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', esc_attr( Groups_Notifications::NOTIFY_USER_GROUPS_DELETED_USER_GROUP ), $notify_user_deleted ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Notify when the user has left a group.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</div>'; // .check

		$output .= '<div class="subject">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Subject', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::NOTIFY_USER_SUBJECT_DELETED, esc_attr( $subject_user_deleted ) );
		$output .= '</label>';
		$output .= '</div>'; // .subject

		$output .= '<div class="message">';
		$output .= '<label>';
		$output .= '<span class="title">';
		$output .= __( 'Message', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= sprintf( '<textarea name="%s">%s</textarea>', Groups_Notifications::NOTIFY_USER_MESSAGE_DELETED, stripslashes( $message_user_deleted ) );
		$output .= '</label>';
		$output .= '</div>'; // .message
		
		$output .= '<div class="defaults">';
		$output .= '<span class="title">';
		$output .= __( 'Defaults', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</span>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$subjects['user']['deleted'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '<pre>';
		$output .= htmlentities( Groups_Notifications::$messages['user']['deleted'], ENT_COMPAT, get_bloginfo( 'charset' ) );
		$output .= '</pre>';
		$output .= '</div>'; // .defaults

		$output.= '</div>'; // .notification

		$output .= '<h3>';
		$output .= __( 'SMTP', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</h3>';

		$output .= '<div class="smtp">';

		$output .= '<p>';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', Groups_Notifications::USE_SMTP, $use_smtp ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Enable SMTP', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'Send notifications using these settings', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( '<em>From</em> Email Address', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_EMAIL, esc_attr( $smtp_email ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( '<em>From</em> Name', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_NAME, esc_attr( $smtp_name ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', Groups_Notifications::SMTP_SET_RETURN_PATH, $smtp_set_return_path ? ' checked="checked" ' : '' );
		$output .= ' ';
		$output .= __( 'Use the <em>From</em> Email Address as the return path.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</p>';

		$output .= '<p>';
		$output .= '<label>';
		$output .= __( 'SMTP Host', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_HOST, esc_attr( $smtp_host ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( 'SMTP Port', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_PORT, esc_attr( $smtp_port ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( 'Encryption', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<select name="%s">', Groups_Notifications::SMTP_ENCRYPTION );
		$output .= sprintf( '<option value="" %s>%s</option>', empty( $smtp_encryption ) ? ' selected="selected" ' : '', __( 'None', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		$output .= sprintf( '<option value="ssl" %s>%s</option>', $smtp_encryption == 'ssl' ? ' selected="selected" ' : '', __( 'SSL', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		$output .= sprintf( '<option value="tls" %s>%s</option>', $smtp_encryption == 'tls' ? ' selected="selected" ' : '', __( 'TLS', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		$output .= '</select>';
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= sprintf( '<input type="checkbox" name="%s" %s />', Groups_Notifications::SMTP_AUTHENTICATION, $smtp_authentication ? ' checked="checked" ' : '');
		$output .= ' ';
		$output .= __( 'SMTP Authentication', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</label>';
		$output .= '</p>';
		$output .= '<p class="description">';
		$output .= __( 'Do SMTP authentication with the <em>Username</em> and <em>Password</em> provided:', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( 'Username', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_USER, esc_attr( $smtp_user ) );
		$output .= '</label>';
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label>';
		$output .= __( 'Password', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" name="%s" value="%s" />', Groups_Notifications::SMTP_PASSWORD, esc_attr( $smtp_password ) );
		$output .= '</label>';
		$output .= '</p>';

		$output .= '</div>'; // .smtp

		$output .= '<div class="save">';
		$output .= wp_nonce_field( 'settings', self::NONCE, true, false );
		$output .= '<input type="submit" class="button" name="submit" value="' . __( 'Save', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '"/>';
		$output .= '</div>'; // .save

		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>'; // .groups-notifications-settings

		// test email
		$email = '';
		if ( isset( $_POST['test_email_action'] ) && ( $_POST['test_email_action'] == 'send' ) && wp_verify_nonce( $_POST['groups-notifications-test-email'], 'admin' ) ) {
			if ( !empty( $_POST['test_email'] ) ) {
				$email = wp_strip_all_tags( $_POST['test_email'] );
				if ( is_email( $email ) ) {
					require_once GROUPS_NOTIFICATIONS_CORE_LIB . '/class-groups-notifications-mailer.php';
					$result = Groups_Notifications_Mailer::test( $email );
					if ( empty( $result ) ) {
						$message = '<div style="background-color:#efe;padding:1em">' .
							sprintf( __( 'The test email has been sent to %s.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ), esc_html( $email ) ) .
							'</div>';
					} else {
						$message = '<div style="background-color:#fee;padding:1em">' .
							sprintf( __( 'Failed to send the test email to %s.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ), esc_html( $email ) ) .
							'<br/>' .
							'<pre>' .
							$result .
							'</pre>' .
							'</div>';
					}
				} else {
					$message = '<div style="background-color:#ffe;padding:1em">' .
						sprintf( __( '<em>%s</em> is not a valid email address.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ), esc_html( $email ) ) .
						'</div>';
				}
			}
		}
		$output .= '<div class="test_email">';
		$output .= '<h3>' . __( 'Test SMTP Settings', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) . '</h3>';
		if ( isset( $message ) ) {
			$output .= $message;
		}
		$output .= '<form name="test_email" action="" method="post">';
		$output .= '<div>';
		$output .= '<label>';
		$output .= __( 'Email address:', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= sprintf( '<input type="text" value="%s" name="test_email" />', esc_attr( $email ) );
		$output .= '</label>';
		$output .= ' ';
		$output .= sprintf( '<input class="button" type="submit" name="submit" value="%s" />', __( 'Send', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ) );
		$output .= '<input type="hidden" name="test_email_action" value="send" />';
		$output .= wp_nonce_field( 'admin', 'groups-notifications-test-email', true, false );
		$output .= '</div>';
		$output .= '</form>';
		$output .= '<p>';
		$output .= __( 'You can send a test email to the specified email address.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= ' ';
		$output .= __( 'You must <strong>enable</strong> SMTP and <strong>save</strong> the settings before sending, otherwise the test email will be sent using the default mailer.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$output .= '</p>';
		$output .= '</div>'; // .test_email

		echo $output;
		Groups_Help::footer();
	}

}
