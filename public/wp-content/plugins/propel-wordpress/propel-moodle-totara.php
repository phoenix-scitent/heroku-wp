<?php

class Propel_Moodle_Totara {

  private $edwiser_bridge;

  function __construct() {
    require_once EB_PLUGIN_DIR.'includes/class-eb.php';
    $this->edwiser_bridge = new EdwiserBridge();
    error_log("hello from propel moodle totara");
    // add_action( 'wp_ajax_activate_key',
      // array( $this, 'ajax_activate_key' ) );

  }

  /* ------------------------------------------- */
  /* ------------------------------------------- */
  /* ------------ WordPress Hooks -------------- */


  public function handleOrderComplete($order_id)
  {
      if (!empty($order_id)) {

          //global $wpdb;

          $is_processed = get_post_meta($order_id, '_is_processed', true);
          
          // Get Key KS 14/04/2016
          $latestkey = '';
          $_keys = get_post_meta($order_id, '_keys', true);
          if(!empty($_keys)){
            foreach($_keys as $finalkey){
              $latestkey = $finalkey['keys'][0];
            }
          }
          // ENd of Key Code KS 14/04/2016
          
          if (!empty($is_processed)) {
            $this->edwiser_bridge->logger()->add('user', 'Order id '.$order_id.' is already processed');
            return 0;
          }

          $order = wc_get_order($order_id); //Get Order details

          $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

          if (in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins)) :

            if (\WC_Subscriptions_Order::order_contains_subscription($order)) {
              $this->edwiser_bridge->logger()->add('user', 'Order id '.$order_id.' contains subscription product...');
              return 0;
            }

          endif;

          $user_id = get_post_meta($order_id, '_customer_user', true);

          $list_of_course_ids = self::_getMoodleCourseIdsForOrder($order);
          if (!empty($list_of_course_ids)) {
              $course_enrolled = self::_enrollUserInCourses($latestkey,$user_id, $list_of_course_ids);

              if (1 === $course_enrolled) {
                  update_post_meta($order_id, '_is_processed', true);
              }
          }
      }
  }

  /**
   * The ajax POST controller for activating keys on the OKM
   */
  function ajax_activate_key() {
    $key = $_POST['key'];
    error_log("Ajax_activate_key -> ". $key);
    $response = self::okm_activate_key( $key );

    if ( ! $response['success'] ){
      wp_send_json_error( $response );
    } else {
      wp_send_json_success( $response );
    }

  }

  /**
   * Enroll user in product's courses if selected during checkout
   */ 
  function auto_enroll_user_in_courses( $order_id ) {
    $user = wp_get_current_user();
    $propel_settings = get_option( 'propel_settings' );

    $enrollments = $_POST['enroll'];
    $order = new WC_Order( $order_id );

    $session['items'] = count( $order->get_items() );

    foreach ( $enrollments as $product => $enroll ) {
      $response = $this->okm_activate_key( $this->pluck_key( $order_id, $product ) );
      if ( ! $response['success'] )
        $session['course_id'] = $response['course_id'];

    }
    update_user_meta( get_current_user_id(), 'session', $session );
  }




  /* ------------------------------------------- */
  /* ------------------------------------------- */
  /* ------------ Internal Methods ------------- */

  function okm_activate_key( $key ) {
    global $wpdb;
    $user = wp_get_current_user();
    $propel_settings = get_option( 'propel_settings' );

    error_log("okm_activate_key -> ". $key);
    $post_data = array(
      'tenant_secret_key' => $propel_settings['okm_tenant_secret_key'], 
      'ext_user_id' => $user->ID,
      'first_name' => $user->user_firstname,
      'last_name' => $user->user_lastname,
      'email' => $user->user_email,
      'code' => $key  
    );

    $response = Propel_LMS::ping_api( $post_data, 'activate_key' );
    if ( array_key_exists( 'code', $response ) ){
      $response['success'] = true;
    } else {
      error_log(" ->  No Key matches: ". $key);
      $response['success'] = false;
      $respsonse['msg'] = 'This key does not exist or cannot be activated.';
    }

    if ( ! $response['success'] )
      return $response;

    $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";

    $product_id = $wpdb->get_var( 
            $wpdb->prepare( 
              $product_query,                   
              $response['product_sku'] 
            ) );

    if ( $product_id ) 
      $product = new WC_Product( $product_id );
    else {
      $respsonse['success'] = false;
      $respsonse['msg'] = 'No product with that sku';
      error_log(" ->  No product with that sku");
      return $response;
    }

    $courses_id = get_post_meta( $product_id, '_related_course', true );
    $course_id;

    if ( $courses_id && is_array( $courses_id ) ) {
      // TODO: Shouldn't we see if user exists already in the list?
      foreach ( $courses_id as $cid ) {
        $this->update_course_access( get_current_user_id(), $cid, $key );
        $course_id = $cid;
      }
    }
    $response['msg'] = 'Works great!';
    $response['course_id'] = $course_id;
    $response['url'] = '/my-courses';
    return $response;
  }

  /**
   * Attaches user to Course Post through  propel_enrollments table instead of post meta
   */
  static function update_course_access( $user_id, $course_id, $key, $remove = false ) {
    if ( empty( $user_id ) || empty( $course_id ) )
      return;

    // Set to propel_enrollments
    global $wpdb;

    $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;

    $exists = $wpdb->get_var( "
                      SELECT COUNT(*) 
                      FROM $propel_table 
                      WHERE user_id = $user_id
                        AND post_id = $course_id
                        AND expiration_date > NOW()
                    " );

    if ( ! $exists ) {
      $expiration = date_format(
                      date_add(
                        date_create( current_time( 'mysql' ) ),
                        date_interval_create_from_date_string( '365 days' )
                      ),
                      'Y-m-d H:i:s'
                    );
      $wpdb->insert( 
              $wpdb->prefix . Propel_DB::enrollments_table,
              array( 
                'post_id' => $course_id,
                'user_id' => $user_id,
                'activation_date' => current_time( 'mysql' ),
                'expiration_date' => $expiration,
                'activation_key'  => $key
              )
            );
    }
  }

  /**
   * Returns the first key for a product
   *   To be used only for auto-enrollment, when we know the first key is not used yet
   */
  function pluck_key( $order_id, $product_id ) {
    $keys = get_post_meta( $order_id, '_keys' );
    $sku = get_post_meta( $product_id, '_sku', true );
    // TODO: Save keys outside of embedded array
    foreach ( $keys[0] as $product )
      if ( $product['product_sku'] == $sku )
        return $product['keys'][0];
  }

}

new Propel_Activate_Key();