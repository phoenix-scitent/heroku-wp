<?php
/**
 * Ajax Functions for Scitent Propel
 *
 */

class propel_ajax {
  function __construct() {
    add_action( 'wp_ajax_aaas_member_number_update', array( $this, 'aaas_member_number_update' ) );

  }

  function aaas_member_number_update() {
    $new_aas_number = $_POST['new_aas_number'];
    $new_aas_number = preg_replace('/\D/', '', $new_aas_number); // sanitize!
    update_user_meta( get_current_user_id(), 'role', 'aaas_member' ); // make them a member
    $user = new WP_User( get_current_user_id() );
    $user->remove_cap( 'non_member' ); // remove non_member capabilities
    $user->add_cap( 'aaas_member' ); // give them aaas_member capabilities
    echo update_user_meta( get_current_user_id(), 'aaas_membership_number', $new_aas_number );  // give them a number
    wp_die();
  }
}

new propel_ajax();
