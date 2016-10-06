<?php

class propel_shortcodes {

   function __construct(){
      add_shortcode( 'propel-key-widget', array( $this, 'key_widget' ) );
      add_shortcode( 'propel-key-activator', array( $this, 'key_activator' ) );
      add_shortcode( 'propel-key-submit', array( $this, 'key_submit' ) );

      add_shortcode( 'propel-tos', array( $this, 'terms_of_service' ) );
      add_shortcode( 'propel-terms-of-service', array( $this, 'terms_of_service' ) );

      add_shortcode( 'propel-okm', array( $this, 'okm' ) );


      add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
   }


   function key_widget( $atts ) {
      the_widget( 'Propel_LMS_Widget', $atts );
   }


   function key_activator( $atts ) {

      Propel_LMS::check_tenant_key( 'activate key' );

      wp_enqueue_script( 'key-activator' );
      wp_enqueue_style( 'key-activator' );
      wp_enqueue_style( 'dashicons' );

      $out = '';

      $out .= '
         <div class="okm-key-activation" >
            <label>Enter Key: <input type="text" id="okm-key" /></label>

            <ul class="confirmations">
               <li class="validation">
                  <span class="dashicons dashicons-yes" style="display: none;"></span>
                  <span class="dashicons dashicons-no" style="display: none;"></span>
                  <span class="message"></span>
               </li>
               <li class="activation">
                  <span class="dashicons dashicons-yes" style="display: none;"></span>
                  <span class="dashicons dashicons-no" style="display: none;"></span>
                  <span class="message"></span>
               </li>
            </ul>
            <img class="load" src="/wp-includes/js/thickbox/loadingAnimation.gif" />
         </div>
      ';

      return $out;
   }

   function key_submit( $atts ) {
      wp_enqueue_style( 'key-submit' );

      if ( isset( $atts ) && is_array( $atts ) ) extract( $atts );

      if ( ! isset( $button_text ) ) $button_text = 'Activate Key';
      if ( ! isset( $cancel_text ) ) $cancel_text = 'I Decline';
      if ( ! isset( $disabled ) )    $disabled = 'disabled';

      $out  = '<input type="button" id="activate_key" value="' . $button_text . '"' . $disabled . ' />';
      $out .= '<a href="' . get_bloginfo( 'url' ) . '">' . $cancel_text . '</a>';
      $out .= '<br />';

      return $out;
   }

   function terms_of_service( $atts ) {
      wp_enqueue_style( 'terms-of-service' );

      $tos = get_page_by_title( 'Terms of Use' );

      $out  = '<h3>Terms of Use</h3>';
      $out .= '<div class="terms-of-service">';
      $out .=   $tos->post_content;
      $out .= '</div>';

      return $out;
   }


   /**
    * Renders the [propel-okm] shortcode for the 'OKM' admin page
    *   Generally includes an iframe contacting the Scitent OKM server
    *   Shortcode attributes include:
    *     - $org_id
    *     - $width
    *     - $height
    *
    * @author  caseypatrickdriscoll
    *
    * @created 2015-01-19 16:05:05
    *
    * @edited  2015-04-07 11:41:51 - Refactors to require http/https in the URI setting
    *
    * @param   Array    $atts  The attributes sent through the shortcode
    *
    * @return  string   $out   The html output including iframe
    */
   function okm( $atts ) {
      global $current_user;
      get_currentuserinfo();

      function enqueue_porthole_js() {
        wp_enqueue_script('jQuery');  
        wp_enqueue_script( 'porthole.min.js',  plugins_url() . '/propel-wordpress/vendor/porthole/porthole.min.js', array(), null, true );
        wp_enqueue_script( 'okm_parent_porthole.js',  plugins_url() . '/propel-wordpress/js/okm_parent_porthole.js', array(), null, true );
      }
      enqueue_porthole_js();

      if ( isset( $atts ) && ! empty( $atts ) ) extract( $atts );

      if ( ! isset( $org_id ) ) $org_id = get_user_meta( $current_user->ID, 'propel_org_admin', 1 );
      if ( ! isset( $width ) ) $width = '100%';
      if ( ! isset( $height ) ) $height = '3000';

      $settings = get_option( 'propel_settings' );
      $tenant_key = $settings['okm_tenant_secret_key'];
      $a0UserId = $_COOKIE["a0userId"];

      if ( empty( $org_id ) ) {
         return 'No Organization ID set for this Org Admin.';
      }
      
      // If OKM is not setup with auth0, use the old auth and iframe
      if ( $settings['okm_sso_enabled'] == 'on' && $a0UserId) {
         error_log("Authenticate with OKM using auth0");
         $out = '<iframe src=" id="okm-frame" name="okm-frame"' . Propel_LMS::okm_server() . '/accounts/sso/' . $tenant_key . '" width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="auto"></iframe>';
      } else {
         error_log("Authenticate with OKM using auth tokens");
         $auth_array = array(
             'tenant_secret_key' => $tenant_key,
             'first_name'        => $current_user->user_firstname,
             'last_name'         => $current_user->user_lastname,
             'ext_user_id'       => $current_user->ID,
             'role'              => $current_user->roles[0],
             'email'             => $current_user->user_email,
             'org_id'            => $org_id
         );

         $propel_org_admin = get_user_meta( $current_user->ID, 'propel_org_admin', true );

         if ( ! empty( $propel_org_admin ) ) $auth_array['org_id'] = $propel_org_admin;

         $response = Propel_LMS::ping_api( $auth_array, 'authenticate' );
         $okm_token = $response['auth_token'];

         $out = '<iframe id="okm-frame" name="okm-frame" width="' . $width . '" height="' .       $height . '" frameborder="0" scrolling="auto"></iframe><script> jQuery(document).ready(function(){ console.log("dat okm"); setTimeout(function(){ jQuery("#okm-frame").attr("src", "'. Propel_LMS::okm_server() . '/accounts/' . $okm_token . '/sign_in' .'");}, 200); });</script>';
      }
      return $out;
   }




   function register_scripts_and_styles() {
      wp_register_script( 'key-activator',
         plugins_url( '/js/key-activator.js', __FILE__ ),  array( 'jquery' ) );

      wp_register_style( 'key-activator',
         plugins_url( '/css/key-activator.css', __FILE__ ) );
      wp_register_style( 'key-submit',
         plugins_url( '/css/key-submit.css', __FILE__ ) );
      wp_register_style( 'terms-of-service',
         plugins_url( '/css/terms-of-service.css', __FILE__ ) );
   }
}

new propel_shortcodes();