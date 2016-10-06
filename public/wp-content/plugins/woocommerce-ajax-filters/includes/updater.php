<?php
if( ! class_exists( 'BeRocket_updater' ) ) {
    define( "BeRocket_update_path", 'http://berocket.com/' );
    define( "BeRocket_updater_log", TRUE );
    class BeRocket_updater {
        public static $plugin_info = array();
        public static $slugs = array();
        public static $key = '';
        public static $error_log = array();
        public static function run () {
            $options = get_option('BeRocket_account_option');
            self::$key = @ $options['account_key'];
            add_action( 'admin_head', array( __CLASS__, 'scripts') );
            add_action( 'admin_menu', array( __CLASS__, 'account_page') );
            add_action( 'network_admin_menu', array( __CLASS__, 'network_account_page'));
            add_action( 'admin_init', array( __CLASS__, 'account_option_register') );
            add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'update_check_set') );
            add_action( 'install_plugins_pre_plugin-information', array( __CLASS__, 'plugin_info'), 1);
            add_action( "wp_ajax_br_test_key", array ( __CLASS__, 'test_key' ) );
            add_filter( 'http_request_host_is_external', array ( __CLASS__, 'allow_berocket_host' ), 10, 3 );
            if ( BeRocket_updater_log ) {
                add_action('admin_footer', array( __CLASS__, 'error_log'));
                add_action('wp_footer', array( __CLASS__, 'error_log'));
            }
            $plugin = array();
            $plugin = apply_filters( 'BeRocket_updater_add_plugin', $plugin );
            self::$plugin_info = $plugin;
            foreach ( $plugin as $plug ) {
                self::$slugs[$plug['id']] = $plug['slug'];
            }
        }
        public static function error_log () {
            self::$error_log = apply_filters( 'BeRocket_updater_error_log', self::$error_log );
            ?>
            <script>
                console.log(<?php echo json_encode( self::$error_log ); ?>);
            </script>
            <?php
        }
        public static function allow_berocket_host( $allow, $host, $url ) {
            if ( $host == 'berocket.com' )
                $allow = true;
            return $allow;
        }
        public static function test_key () {
            if( $curl = curl_init() ) {
                curl_setopt($curl, CURLOPT_URL, BeRocket_update_path.'main/account_updater');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_POST, true);
                $postdata = 'key='.$_POST['key'].'&id='.$_POST['id'];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                $out = curl_exec($curl);
                echo $out;
                curl_close($curl);
            } else {
                $data = array('key_exist' => 0, 'status' => 'Failed', 'error' => 'cURL init failed. Please enable cURL for update.');
            }
            wp_die();
        }
        public static function scripts () {
            ?>
            <script>
                function BeRocket_key_check ( key, show_correct, product_id ) {
                    if ( typeof( product_id ) == 'undefined' || product_id == null ) {
                        product_id = 0;
                    }
                    data = {action: 'br_test_key',key: key, id: product_id};
                    is_submit = false;
                    jQuery.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        success: function (data) {
                                jQuery('.berocket_test_result').html(data);
                                if ( data.key_exist == 1 ) {
                                    if ( show_correct ) {
                                        html = '<h3>'+data.status+'</h3>';
                                        html +='<p><b>UserName: </b>'+data.username+'</p>';
                                        html +='<p><b>E-Mail: </b>'+data.email+'</p>';
                                        html +=data.plugin_table;
                                        jQuery('.berocket_test_result').html(html);
                                    }
                                    is_submit = true;
                                } else {
                                    html = '<h3>'+data.status+'</h3>';
                                    html +='<p><b>Error message:</b>'+data.error+'</p>';
                                    jQuery('.berocket_test_result').html(html);
                                }
                            },
                        dataType: 'json',
                        async: false
                    });
                    return is_submit;
                }
                jQuery(document).on( 'click', '.berocket_test_account_product', function ( event ) {
                    event.preventDefault();
                    key = jQuery('#berocket_product_key').val();
                    BeRocket_key_check ( key, true, jQuery(this).data('id') );
                });
            </script>
            <?php
        }
        public static function network_account_page () {
            add_menu_page( 
                'BeRocket Account Settings',
                'BeRocket Account',
                'manage_options', 
                'berocket_account', 
                array( __CLASS__, 'account_form')
            );
        }
        public static function account_page () {
            add_submenu_page( 
                'options-general.php',
                'BeRocket Account Settings',
                'BeRocket Account',
                'manage_options', 
                'berocket_account', 
                array( __CLASS__, 'account_form')
            );
        }
        public static function account_option_register () {
            register_setting( 'BeRocket_account_option_settings', 'BeRocket_account_option' );
        }
        public static function account_form () {
            ?>
            <div class="wrap">
                <form method="post" action="options.php" class="account_key_send">
                    <?php
                    settings_fields('BeRocket_account_option_settings');
                    $options = get_option('BeRocket_account_option');
                    ?>
                    <h2>BeRocket Account Settings</h2>
                    <div>
                        <label>Account key</label><input type="text" id="berocket_account_key" name="BeRocket_account_option[account_key]" value="<?php echo @$options['account_key'] ?>">
                        <input class="berocket_test_account button-secondary" type="button" value="Test">
                        <div class="berocket_test_result"></div>
                    </div>
                    <input type="submit" class="button-primary" value="Save Changes" />
                    <script>
                        jQuery('.berocket_test_account').click( function ( event ) {
                            event.preventDefault();
                            key = jQuery('#berocket_account_key').val();
                            BeRocket_key_check ( key, true );
                        });
                        jQuery(document).on( 'submit', '.account_key_send', function ( event ) {
                            key = jQuery('#berocket_account_key').val();
                            result = BeRocket_key_check ( key, false );
                            if ( ! result ) {
                                event.preventDefault();
                            }
                        });
                    </script>
                </form>
            </div>
            <?php
        }
        public static function update_check_set ( $value ) {
            foreach ( self::$plugin_info as $plugin ) {
                $key = @ self::$key;
                if( @ $plugin['key'] && count_chars( @ $plugin['key'] ) == 40 )
                    $key = @ $plugin['key'];
                $version = FALSE;
                if( @ $key && $curl = curl_init() ) {
                    $url = BeRocket_update_path.'main/get_plugin_version/'.$plugin['id'].'/'.$key;
                    curl_setopt( $curl, CURLOPT_URL, $url );
                    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
                    $version = curl_exec( $curl );
                    curl_close( $curl );
                    $version = json_decode( @ $version );
                    if ( @ $version->status == 'success' ) {
                        $version = $version->version;
                    } else {
                        $version = FALSE;
                    }
                }
                if ( $version !== FALSE ) {
                    $current_arr = explode( '.', $plugin['version'] );
                    $new_arr = explode( '.', $version );
                    while ( count( $current_arr ) > count( $new_arr ) ) {
                        array_pop ( $current_arr );	
                    }
                    if ( $current_arr < $new_arr ) {
                        $value->checked[$plugin['plugin']] = $version;
                        $val = (object)array(
                            'id'          => 'br_'.$plugin['id'], 
                            'new_version' => $version, 
                            'package'     => BeRocket_update_path.'main/update_product/'.$plugin['id'].'/'.$key,
                            'url'         => BeRocket_update_path.'product/'.$plugin['id'],
                            'plugin'      => $plugin['plugin'],
                            'slug'        => $plugin['slug']
                        );
                        $value->response[$plugin['plugin']] = $val;
                    }
                }
            }
            if ( isset( $value->no_update ) && is_array( $value->no_update ) ) {
                foreach ( $value->no_update as $key => $val ) {
                    if ( in_array( $val->slug, self::$slugs ) ) {
                        unset($value->no_update[$key]);
                    }
                }
            }
            return $value;
        }
        public static function plugin_info() {
            $plugin = wp_unslash( $_REQUEST['plugin'] );
            if ( in_array( $plugin, self::$slugs ) ) {
                remove_action('install_plugins_pre_plugin-information', 'install_plugin_information');
                $plugin_id = array_search( $plugin, self::$slugs );
                if( $curl = curl_init() ) {
                    $url = BeRocket_update_path.'main/update_info/'.$plugin_id;
                    curl_setopt( $curl, CURLOPT_URL, $url );
                    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
                    $plugin_info = curl_exec( $curl );
                    curl_close( $curl );
                    echo $plugin_info;
                    die;
                }
            }
        }
    }
    add_action( 'plugins_loaded', array( 'BeRocket_updater', 'run' ), 999 );
}
?>