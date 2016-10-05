<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles');
function salient_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array('font-awesome'));
}

add_action( 'init', 'create_post_type' );
function create_post_type() {
  register_post_type( 'course_instructors',
    array(
      'labels' => array(
        'name' => __( 'Instructors' ),
        'singular_name' => __( 'Instructors' ),
        'menu_icon' => 'dashicons-welcome-write-blog'

      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}

add_action( 'woocommerce_before_checkout_form', 'scitent_redirect_checkout_if_not_logged_in', 10 );
function scitent_redirect_checkout_if_not_logged_in() {
  if ( !is_user_logged_in() ) {
    auth_redirect();
  }
}

function scitent_register_js_namespace() {
  wp_register_script( 'scitent-js', get_stylesheet_directory_uri() . '/js/scitent.js' );
  wp_enqueue_script('scitent-js');
  wp_localize_script('scitent-js','ajaxurl',admin_url( 'admin-ajax.php' ));
}
add_action( 'wp_enqueue_scripts', 'scitent_register_js_namespace' );


/****************************************
 * AAAS-Specific shortcodes
 */

/**
 * aaas_upgrade_membership()
 * creates a link for new AAAS members to enter their membership number
 */
function aaas_upgrade_membership( $atts) {
   $a = shortcode_atts( array(
      'action_page' => '/upgrade/',
      'helper_text' => 'Have an AAAS Member Number? Enter it here.',
      'placeholder_text' => 'AAAS Member Number',
      'congrats_text' => 'Congratulations.  Your status has been upgraded to AAAS Member.',
   ), $atts );

   ob_start();

   ?>
   <form action="<?php echo $a['action_page'] ?>">
   <p><?php echo $a['helper_text']; ?></p>
   <div class="userpro-input">
    <input id="aaas_upgrade_member_number" type="text" placeholder="<?php echo $a['placeholder_text'] ?>" />
    <div id="aaas_upgrade_member_number_warning" class="userpro-warning" style="top: 0px; opacity: 1; display:none;"><i class="userpro-icon-caret-up"></i><span></span></div>
   </div>
   <div id="aaas_upgrade_congrats" style="display:none;"><?php echo $a['congrats_text']; ?></div>
   <div class="marginTop40">
    <hr>
    <input id="aaas_submit" type="submit" class="darkBlueBnt" value="Submit" />
   </div> 
   <img id="aaas_submit_spinning" src="/wp-content/plugins/userpro/skins/elegant/img/loading.gif" alt="" class="userpro-loading">
   </form>

   <script type="text/javascript" >
     jQuery('#aaas_submit').click( scitent.aaas_number_validate_via_userpro );
   </script>

   <?php

   $out = ob_get_contents();
   ob_end_clean();

   return $out;
}
add_shortcode( 'propel-aaas-upgrade-membership', 'aaas_upgrade_membership' );


/****************************************
 * Other shortcodes 
 */

/**
 * conditional_button()
 * produced a button only if the user is logged in, and has membership type of "non_member"
 */
function conditional_button( $atts ) {
   // var_dump($atts);
   $a = shortcode_atts( array(
      'color' => 'accent-color',
      'hover_text_color_override' => '#fff',
      'size' => 'large',
      'url' => '/',
      'text' => 'Click Here',
      'color_override' => '',
   ), $atts );
   // var_dump(get_user_meta( get_current_user_id(), 'role', true ));
   if( 'non_member' === get_user_meta( get_current_user_id(), 'role', true ) ) {
     return do_shortcode('[button color="' . $a['color'] . '" hover_text_color_override="' . $a['hover_text_color_override'] . '" size="' . $a['size'] . '" url="' . $a['url'] . '" text="' . $a['text'] . '" color_override="' . $a['color_override'] . '"]');
   } else {
     return '';
   }
}
add_shortcode( 'propel-scitent-conditional-button' , 'conditional_button' );

function scitent_checkout_with_order_not_billing() {
    $original_shortcode_results = do_shortcode('[woocommerce_checkout]');
    $replaced = preg_replace('/Billing/','Order',$original_shortcode_results);
    if( $replaced ) {
        return $replaced;
    }
    return $original_shortcode_results;
}
add_shortcode( 'scitent_woocommerce_checkout', 'scitent_checkout_with_order_not_billing');


function after_registration_template_redirect()
{
    global $woocommerce;
    $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
    error_log($url_path);
    if( $url_path == 'after-registration' && is_user_logged_in() ) {
      if ( WC()->cart->get_cart_contents_count() != 0 ) {
        error_log("gotz cart");
        wp_safe_redirect( home_url( '/cart' ) );
        exit();
      } else {
        error_log("no cartz");
        wp_safe_redirect( home_url('/course-catalog') );
        exit();
      }
    } else {
      return false;
    }
}
add_action( 'template_redirect', 'after_registration_template_redirect' );

include_once "manyTeachers_shortcode.php"; 