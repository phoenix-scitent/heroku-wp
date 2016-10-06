<?php

add_action('wp_enqueue_scripts', 'CDNJS_scripts');
function CDNJS_scripts() 
{
  wp_enqueue_script('eqcss-polyfill','https://cdnjs.cloudflare.com/ajax/libs/eqcss/1.2.1/EQCSS-polyfills.min.js', array(), false, false );
  wp_script_add_data( 'eqcss-polyfill', 'conditional', 'lt IE 9' );

  wp_enqueue_script('eqcss', plugins_url( '/js/eqcss.min.js', __FILE__ ), array(), false, false);
} 