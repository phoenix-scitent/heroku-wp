<?php
/**
 * Scitent Propel USERPRO Helper
 *  -> helps validate users for AAAS
 */

// add_action( 'wp_ajax_nopriv_userpro_side_validate', 'scitent_valid_aaas_number', 1 );

// function scitent_valid_aaas_number( ) {
//  global $userpro;
  
//  if ( $_POST['action'] != 'userpro_side_validate')
//    die();
    
//  $input_value = $_POST['input_value'];
//  $ajaxcheck = $_POST['ajaxcheck'];
//  $output['error'] = '';
//  switch($ajaxcheck) {
  
//    case 'scitent_valid_aaas_number':
//      if( 0 === preg_match( '/^[0-9]{8}$/', $input_value ) ) {
//        $output['error'] = __('That is not a valid code.','scitent');
//        $output=json_encode($output);
//        if(is_array($output)){ print_r($output); }else{ echo $output; } die;
//      }
//      break;
//  }

// }