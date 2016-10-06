<?php 

/**
 * Override WordPress core emails
 */
function scitent_password_change_filter( $pass_change_email, $user, $userdata ) {
  $raw_pass_change_text = "Dear " . $user['first_name'] . " " . $user['last_name'] . ", <br><br> Your password was changed on the ###SITENAME### website.  If you did not change your password, please contact the Site Administrator here: ###ADMIN_EMAIL###. <br>  This email was sent to ###EMAIL### <br><br> Regards, <br> Administrator at ###SITENAME### <br> ###SITEURL###";

  $pass_change_text = __( $raw_pass_change_text );
  
  
  $pass_change_email = array(
          'to'      => $user['user_email'],
          'subject' => __( '[%s] Notice of Password Change' ),
          'message' => $pass_change_text,
          'headers' => '',
  );

  return $pass_change_email;
  
}
add_filter( 'password_change_email', 'scitent_password_change_filter', 10, 3);