<?php

namespace Combosoft\WATLogin;
use \WP_REST_Response;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class Functions {
    /**
     * Hooks Register
     */
    public $blacklist = [
        '/wp-json/combopos/v1/app',
        '/wp-json/combopos/v1/products'
    ];
    
    function register_hooks() {
       
        add_action('rest_api_init', [$this, 'before_rest_init']);
        add_action('rest_api_init', [$this, 'register_wat_rests']);
    }
    
    function register_wat_rests($server){
        register_rest_route( '/wat', 'auth', [
            'methods'  => ['POST'],
            'callback' => [$this, 'authenticate_user']
        ]);

        register_rest_route( '/wat', 'profile', [
            'methods'  => ['GET'],
            'callback' => [$this, 'get_profile']
        ]);

        register_rest_route( '/wat', 'verify', [
            'methods'  => ['GET'],
            'callback' => [$this, 'verify_token']
        ]);

        register_rest_route( '/wat', 'profile', [
            'methods'  => ['POST'],
            'callback' => [$this, 'edit_profile']
        ]);
    }

    
    function authenticate_user(){
        $email = $_REQUEST['email'] ?? ( $_REQUEST['username'] ?? null);
        $password = $_REQUEST['password'] ?? null;

        $authenticated = wp_authenticate($email, $password);
        
        if($authenticated->data != null){
            
            // logged in 
            $data = $authenticated->data;
            $user = new \StdClass();
            $user->id = (int) $data->ID;
            $user->name = $data->display_name;            
            $user->email = $data->user_email;        
            $user->username = $data->user_login;
            $user->status = $data->user_status;
            

            // delete former meta 
            delete_user_meta( $data->ID, 'wa_token');
            // generate user meta 
            $randomUniqueKey =  time() . $user->id . bin2hex(random_bytes(15)) . substr( str_shuffle('ABCDEFGHIJKLMNOPQRST123456789'), 0, 5);
            add_user_meta($user->id, 'wa_token', $randomUniqueKey);
            $user->wa_token = $randomUniqueKey;

            wp_send_json_success( $user );
            wp_die();
        }
        wp_send_json_error( $authenticated->errors );
        wp_die();
    }

    function getToken(){
        $headers = $_SERVER["HTTP_AUTHORIZATION"] ?? false;
        if($headers) {
            preg_match("/WAT.?\w+/im", $headers, $match);
            if($match && !empty($match) && count($match) > 0){
                return trim(str_replace('WAT', '', $match[0]));
            } else {
                return false;
            }
        }
        return false;
    }

    function verify_token(){
        $token = $this->getToken() ?? ($_REQUEST['token'] ?? null);
        if(!$token) {
            wp_send_json_error( 'Invalid Web Auth Token' );
            wp_die();
        }

        $user = get_users([
            'meta_key' => 'wa_token',
            'meta_value' => $token
        ]);

        if(!$user){
           wp_send_json_error( 'Invalid Web Auth Token' );
            wp_die();
        } 

        return [
            'success' => true,
            'data' => 'Web Auth Token Verified'
        ];
    }

    function get_user_by_token($token){

        $user = get_users([
            'meta_key' => 'wa_token',
            'meta_value' => $token
        ]);

        if(!$user){
            return [
                'success' => false,
                'error' => 'Invalid Token'
            ];
        }

        // reauthenticate user 

        $data = $user[0]->data;
        $output = new \StdClass();
        $output->id = (int) $data->ID;
        $output->name = $data->display_name;            
        $output->email = $data->user_email;        
        $output->username = $data->user_login;
        $output->status = $data->user_status;
        $output->wa_token = get_user_meta($data->ID, 'wa_token', true);

        return $output;
    }
    

    function before_rest_init(){
        
    }

    function get_profile(){
        $user = wat_get_user();
        // $customer = 
        $customer = new \WC_Customer( $user->data->ID );
        if(!$customer){
            return [
                'success' => false,
                'data' => 'No customer found'
            ];
        }

        $userdata = [
            'name' => $customer->get_display_name(),
            'email' => $customer->get_display_name(),
            'username' => $customer->get_display_name(),
            'first_name' => $customer->get_display_name(),
            'last_name' => $customer->get_display_name(),
            'billing' => [
                'first_name' => $customer->get_billing_first_name(),
                'last_name' => $customer->get_billing_last_name(),
                'company' => $customer->get_billing_company(),
                'addres_1' => $customer->get_billing_address_1(),
                'addres_2' => $customer->get_billing_address_2(),
                'city' => $customer->get_billing_city(),
                'state' => $customer->get_billing_state(),
                'postcode' => $customer->get_billing_postcode(),
                'country' => $customer->get_billing_country(),
            ],
            'shipping' => [
                'first_name' => $customer->get_shipping_first_name(),
                'last_name' => $customer->get_shipping_last_name(),
                'company' => $customer->get_shipping_company(),
                'addres_1' => $customer->get_shipping_address_1(),
                'addres_2' => $customer->get_shipping_address_2(),
                'city' => $customer->get_shipping_city(),
                'state' => $customer->get_shipping_state(),
                'postcode' => $customer->get_shipping_postcode(),
                'country' => $customer->get_shipping_country(),
            ],
        ];
        

        // $customer_orders = get_posts([
        //     'numberposts' => -1,
        //     'meta_key'    => '_customer_user',
        //     'meta_value'  => $user->data->ID,
        //     'post_type'   => wc_get_order_types(),
        //     'post_status' => array_keys( wc_get_order_statuses() ),
        // ]);

        // $userdata['total_orders'] = count($customer_orders);

        return [
            'success' => true,
            'data' => $userdata
        ];
    }
    function edit_profile(){
        
    }
}






add_action( 'plugins_loaded', function(){
    add_filter(
		'jwt_auth_whitelist',
		function ( $endpoints ) {
			$whitelists = array(
				'/wp-json/wat/*',
			);

			foreach ( $whitelists as $whitelist ) {
				if ( ! in_array( $whitelist, $endpoints, true ) ) {
					array_push( $endpoints, $whitelist );
				}
			}

			return $endpoints;
		}
	);
} );


function wat_get_user(){
    $functions = new \Combosoft\WATLogin\Functions();
    $user = $functions->get_user();
    return get_user_by('id', $user->id) ?? false;
}
