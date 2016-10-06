<?php
/**
 * class-groups-notifications-mailer.php
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
 * Notification mailer - provides email notifications.
 */
class Groups_Notifications_Mailer {

	private static $mailer = null;

	/**
	 * Send email.
	 * Use <br/> not \r\n as line breaks in original message, the mailer
	 * generates a plain text version from the original message and
	 * sends a multipart/alternative including text/plain and text/html parts.
	 *
	 * @param string $email
	 * @param string $subject the email subject (do NOT pass it translated, it will be done here)
	 * @param string $message the email message (do NOT pass it translated, it will be done here) 
	 */
	public static function mail( $email, $subject, $message, $tokens = array(), $force_smtp = false ) {

		// see below (*)
// 		$boundary_id = md5( time() );
// 		$boundary    = sprintf( 'groups-notification-%s', $boundary_id );

// 		// email headers
// 		$headers  = 'MIME-Version: 1.0' . "\r\n";
// 		$headers .= 'Content-type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n";

		// translate
		$subject = __( $subject, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );
		$message = __( $message, GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN );

		// token substitution
		$site_title = wp_specialchars_decode( get_bloginfo( 'blogname' ), ENT_QUOTES );
		$site_url   = get_bloginfo( 'url' );

		$tokens = array_merge(
			$tokens,
			array(
				'site_title' => $site_title,
				'site_url'   => $site_url
			)
		);
		foreach ( $tokens as $key => $value ) {
			$substitute = self::filter( $value );
			$subject    = str_replace( "[" . $key . "]", $substitute, $subject );
			$message    = str_replace( "[" . $key . "]", $substitute, $message );
		}

		$subject = stripslashes( $subject );
		$message = stripslashes( $message );

		$html_message = $message;

		// 
		// (*) Issues:
		// - insufficient way of deriving the plaintext version, see below for a better way
		// - wp_mail doesn't handle multipart correctly, see http://core.trac.wordpress.org/ticket/15448
		//
// 		$plain_message = preg_replace( '/\\r\\n|\\r|\\n|<p>|<P>/', '', $message );
// 		$plain_message = preg_replace( '/<br>|<br\/>|<BR>|<BR\/>|<\/p>|<\/P>/', "\r\n", $plain_message );
// 		$plain_message = wp_filter_nohtml_kses( $plain_message );

// 		$message =
// 			"\r\n\r\n--" . $boundary . "\r\n" .
// 			'Content-type: text/plain; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n\r\n" .
// 			$plain_message . "\r\n" .
// 			"\r\n\r\n--" . $boundary . "\r\n" .
// 			'Content-type: text/html; charset="' . get_option( 'blog_charset' ) . '"' . "\r\n\r\n" .
// 			$message . "\r\n" .
// 			"\r\n\r\n--" . $boundary . "--\r\n\r\n" ;

// 		@wp_mail( $email, wp_filter_nohtml_kses( $subject ),  $message, $headers );

		//
		// A much more robust way to build the plaintext version:
		//
		$content_start = 0;
		$content_end = strlen( $message ) - 1;
		$body_start = stripos( $message, '<body' );
		if ( $body_start !== false ) {
			$content_start = stripos( $message, '>', $body_start ) + 1;
			$body_end = stripos( $message, '</body>' );
			if ( $body_end !== false ) {
				$content_end = $body_end - 1;
			}
		}
		$id = '#!#' . md5( time() + rand(0, time() ) ) . '#!#';
		$plain_message = substr( $message, $content_start, $content_end - $content_start + 1 );
		$plain_message = preg_replace( '/<a[^>]+href=\"(.+?)\"[^>]*>(.+?)<\/a>/ims', " $2 [$1] " , $plain_message );
		$plain_message = preg_replace( '/<h[1-6](.*?)>|<\/h[1-6]>/i', '$0' . $id, $plain_message );
		$plain_message = preg_replace( '/\\r\\n|\\r|\\n|<p>|<P>/', '', $plain_message );
		$plain_message = preg_replace( '/<br>|<br\/>|<BR>|<BR\/>|<\/p>|<\/P>/', "\r\n\r\n", $plain_message );
		$plain_message = wp_strip_all_tags( $plain_message );
		$plain_message = str_replace( $id, "\r\n\r\n", $plain_message );
		$plain_message = preg_replace_callback( "/(&#[0-9]+;)/", array( __CLASS__, 'decode' ), $plain_message );

		$sent = false;
		$use_smtp = $force_smtp || Groups_Options::get_option( Groups_Notifications::USE_SMTP, false );
		if ( $use_smtp ) {
			$sent = self::send( $email, stripslashes( wp_strip_all_tags( $subject ) ), $html_message, $plain_message );
		}
		if ( !$sent ) {
			@wp_mail( $email, stripslashes( wp_strip_all_tags( $subject ) ), $html_message, sprintf( 'Content-type: text/html; charset="%s"' . "\r\n", get_option( 'blog_charset' ) ) );
		}
	}
	
	/**
	 * Send using PHPMailer's AltBody
	 *
	 * @param string $email email recipient
	 * @param string $subject email subject
	 * @param string $message HTML message
	 * @param string $plain_message plain text message
	 */
	private static function send( $email, $subject, $message, $plain_message ) {
		$sent = false;
		$smtp_email             = Groups_Options::get_option( Groups_Notifications::SMTP_EMAIL, '' );
		$smtp_name              = Groups_Options::get_option( Groups_Notifications::SMTP_NAME, 'Notifications' );
		$smtp_set_return_path   = Groups_Options::get_option( Groups_Notifications::SMTP_SET_RETURN_PATH, false );
		$smtp_host              = Groups_Options::get_option( Groups_Notifications::SMTP_HOST, '' );
		$smtp_port              = Groups_Options::get_option( Groups_Notifications::SMTP_PORT, '' );
		$smtp_encryption        = Groups_Options::get_option( Groups_Notifications::SMTP_ENCRYPTION, null );
		$smtp_authentication    = Groups_Options::get_option( Groups_Notifications::SMTP_AUTHENTICATION, true );
		$smtp_user              = Groups_Options::get_option( Groups_Notifications::SMTP_USER, '' );
		$smtp_password          = Groups_Options::get_option( Groups_Notifications::SMTP_PASSWORD, '' );
		if ( !empty( $smtp_email ) && !empty( $smtp_host ) && !empty( $smtp_port ) )  {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			if ( self::$mailer === null ) {
				try {
					self::$mailer = new PHPMailer();
					self::$mailer->IsSMTP();
					self::$mailer->Host = $smtp_host;
					self::$mailer->Port = $smtp_port;
					if ( $smtp_authentication ) {
						self::$mailer->SMTPAuth = true;
						self::$mailer->Username = $smtp_user;
						self::$mailer->Password = $smtp_password;
					}
					switch( $smtp_encryption ) {
						case 'ssl' :
						case 'tls' :
							self::$mailer->SMTPSecure = $smtp_encryption;
							break;
					}
					self::$mailer->From = $smtp_email;
					self::$mailer->FromName = $smtp_name;
					if ( $smtp_set_return_path ) {
						self::$mailer->Sender = $smtp_email;
					}
				} catch( phpmailerException $e ) {
				}
			} else {
				try {
					self::$mailer->ClearAddresses();
					self::$mailer->ClearAllRecipients();
					self::$mailer->ClearAttachments();
					self::$mailer->ClearBCCs();
					self::$mailer->ClearCCs();
					self::$mailer->ClearCustomHeaders();
					self::$mailer->ClearReplyTos();
				} catch( phpmailerException $e ) {
				}
			}
			try {
				self::$mailer->AddAddress( $email );
				self::$mailer->Subject = $subject;
				self::$mailer->AltBody = $plain_message;
				self::$mailer->MsgHTML( $message );
				self::$mailer->CharSet = get_option( 'blog_charset' );
				self::$mailer->Send();
				$sent = true;
			} catch( phpmailerException $e ) {
			}
		}
		return $sent;
	}

	/**
	 * Numeric entities.
	 *
	 * @param array $s matches
	 */
	public static function decode( $s ) {
		return mb_convert_encoding( $s[1], get_bloginfo( 'charset' ), "HTML-ENTITIES" );
	}

	/**
	 * Send a test email.
	 * @param string $email recipient email address
	 * @return string empty on success or error info
	 */
	public static function test( $email ) {
		$result = '';
		self::mail(
				$email,
				__( 'Groups Notifications Test Email', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
				__( 'This is a test message.', GROUPS_NOTIFICATIONS_PLUGIN_DOMAIN ),
				array(),
				true
		);
		if ( self::$mailer !== null && self::$mailer->IsError() ) {
			$result = self::$mailer->ErrorInfo;
		}
		return $result;
	}

	/**
	 * Filters mail header injection, html, ...
	 * @param string $unfiltered_value
	 */
	public static function filter( $unfiltered_value ) {
		$filtered_value = preg_replace('/(%0A|%0D|content-type:|to:|cc:|bcc:)/i', '', $unfiltered_value );
		return stripslashes( wp_filter_nohtml_kses( self::filter_xss( trim( strip_tags( $filtered_value ) ) ) ) );
	}

	/**
	 * Filter xss
	 *
	 * @param string $string input
	 * @return filtered string
	 */
	public static function filter_xss( $string ) {
		// Remove NUL characters (ignored by some browsers)
		$string = str_replace( chr( 0 ), '', $string );
		// Remove Netscape 4 JS entities
		$string = preg_replace( '%&\s*\{[^}]*(\}\s*;?|$)%', '', $string );
		// Defuse all HTML entities
		$string = str_replace( '&', '&amp;', $string );
		// Change back only well-formed entities in our whitelist
		// Decimal numeric entities
		$string = preg_replace( '/&amp;#([0-9]+;)/', '&#\1', $string );
		// Hexadecimal numeric entities
		$string = preg_replace( '/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string );
		// Named entities
		$string = preg_replace( '/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string );
		return preg_replace( '%
				(
				<(?=[^a-zA-Z!/])  # a lone <
				|                 # or
				<[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
				|                 # or
				>                 # just a >
		)%x', '', $string );
	}
}
