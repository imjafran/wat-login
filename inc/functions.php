<?php

namespace WAT;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class WAT {
 
    # member variables
    public $user = null;
    # Register Hooks 
    public function init() { 
        # custom construct method       
        add_action('rest_api_init',                         [$this, 'middleware_check'], 0);
        add_action('rest_api_init',                         [$this, 'register_wat_rests']);
        add_action('plugins_loaded',                        [$this, 'wat_loaded']);
        add_action('after_wat_registration',                [$this, 'update_user_metas']);
    }
    
    public function wat_loaded(){
        # if jwt installed, whitelisting wat
        add_filter( 'jwt_auth_whitelist', function($endpoints){
            array_push( $endpoints, '/wp-json/wat/*' );
            return $endpoints;
        });
    }


    function middleware_check(){

        $parsed_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $home_url = home_url();
        $requested_url = str_replace($home_url, '', $parsed_url);
        $default_middlewares = [
            '/wp-json/wat/v1/password/change'
        ];

        $middlewares = apply_filters( 'wat_apply_middleware',  $default_middlewares);        

        // check url match 
        $found = false;        
        foreach($middlewares as $middleware){
            $middleware = str_replace(['*'], ['.+'], $middleware);
            preg_match('~^' . $middleware . '+$~', $requested_url, $match);
            if($match[0]){
                $found = $match[0];
                break;
            }
        }

        if($found){
            $token = $this->getTokenFromHeader();
            if(!$token) {
                wp_send_json_error( 'INVALID_WEB_AUTH_TOKEN' );
                wp_die();
            }

            $user = get_users([
                'meta_key' => 'wa_token',
                'meta_value' => $token
            ]);

            if(empty($user) || !$user){
                wp_send_json_error( 'INVALID_WEB_AUTH_TOKEN' );
                wp_die();
            }

            $this->user =  $this->getUserFromObject($user[0]);
        }

        // wp_send_json( get_current_user_id() );

    }
    # Register REST 
    public function register_wat_rests($server){
        $routes_v1 = [            
            # [ROUTE, METHODS, CALLBACK],
            ['auth',                    ['POST'],       'authenticateUser'],
            ['verify',                  ['GET'],        'verifyToken'],
            ['logout',                  ['GET'],        'logoutUser'],
            ['register',                ['POST'],       'registerUser'],
            ['password/forgot',         ['POST'],        'send_password_reset_code'],
            ['password/change',         ['POST'],        'change_password'],
        ];

        foreach($routes_v1 as $route){
            register_rest_route( '/wat/v1', $route[0], [
                'methods'  => $route[1],
                'callback' => [$this, $route[2]]
            ]);
        }

    }

    public function createToken(){
        $wat_token_bytes = apply_filters( 'wat_token_bytes', 10 );
        return md5(time()) . bin2hex(random_bytes($wat_token_bytes));
    }

    public function getTokenFromHeader(){
        $headers = $_SERVER["HTTP_AUTHORIZATION"] ?? false;
        if($headers) {
            preg_match("/WAT.?\w+/im", $headers, $match);
            if($match && !empty($match) && count($match) > 0){
                $token = trim(str_replace('WAT', '', $match[0]));
                $this->token = $token;
            } else {
                $token = false;
            }
        }
        return $token;
    }

    public function verifyToken($request){
        $user = $this->getUser();
        if($user && isset($user->id)){
            wp_send_json_success( $user );
            wp_die();
        }
        wp_send_json_error( 'INVALID_WEB_AUTH_TOKEN' );
        wp_die();
    }
    
    public function authenticateUser($request){
        $email = $request['email'] ?? $request['username'] ?? null;
        $password = $request['password'] ?? null;

        # before wat login hook
        do_action('before_wat_login');
        
        # authenticate user 
        $authenticated = wp_authenticate($email, $password);
        
        if($authenticated->data != null){
            
            # logged in 
            $user = $this->getUserFromObject($authenticated);                        

            # delete former meta 
            delete_user_meta( $user->id, 'wa_token');
            # generate user meta 
            $token = $this->createToken();
            add_user_meta($user->id, 'wa_token', $token);
            $user->token = $token;


            # after wat auth hook 
            do_action( 'after_wat_login', $user->id );

            wp_send_json_success( $user );
            wp_die();
        }
        wp_send_json_error( $authenticated->errors ?? 'SERVER_ERROR' );
        wp_die();
    }

    public function logoutUser($request){
        $user = $this->getUser();
        delete_user_meta( $user->id, 'wa_token');
        wp_send_json_success('LOGOUT_SUCCESSFUL');
        wp_die();
    }

    public function getUserFromObject($data){
        $output = new \StdClass();
        $output->id = (int) $data->data->ID;
        $output->first_name = get_user_meta($data->data->ID, 'first_name', true);            
        $output->last_name = get_user_meta($data->data->ID, 'last_name', true);            
        $output->email = $data->data->user_email;        
        $output->username = $data->data->user_login;
        $output->token = get_user_meta($data->data->ID, 'wa_token', true);
        $output->role = $data->roles[0];
        
        $caps = [];
        foreach($data->allcaps as $cap => $value){
            if($value)
                $caps[] = $cap;
        }

        $output->capabilities = $caps;
        return $output;
    }

    public function getUserID($request = null){
        $user = $this->getUser();
        return $user->ID;
    }
    
    public function getUser($request = null){

        $token = $this->getTokenFromHeader() ?? ($request['token'] ?? null);
        if($token){
            $user = get_users([
                'meta_key' => 'wa_token',
                'meta_value' => $token
            ]);

            if(!empty($user) && $user){
                return $this->getUserFromObject($user[0]);
            }
            return false;        
        }
    }
  
    public function registerUser($request){

        $register_allow = get_option( 'users_can_register');
        if(!$register_allow) {
            wp_send_json_error( 'REGISTRATION_NOW_ALLOWED' );
            wp_die();
        }
        # before wat registration hook 
        do_action( 'before_wat_registration', $request );

        $user_login = $request['username'] ?? null;
        $user_pass = $request['password'] ?? null;
        $user_email = $request['email'] ?? null;

        if(!$user_email) {
            wp_send_json_error( 'INVALID_EMAIL' );
            wp_die();
        }

        # create password if not set 
        if(!$user_pass) {
            $user_pass = bin2hex(random_bytes(6));
        }

        # create unique username if not set 
        if($user_login  == null){
            $user_login = explode('@', $user_email)[0];
            $user_login = strtolower($user_login);
        }        

        # $user_id = wp_create_user( $user_login, $user_pass, $user_email );
        $error_code = null;
        
        while($user_id = wp_create_user( $user_login, $user_pass, $user_email )){
            if(!is_wp_error($user_id)){
                break;
            }
            $error_code = key($user_id->errors);
            if($error_code == 'existing_user_login' && !isset($request['username'])){
                $user_login .= rand(0,9);
                continue;
            } 
            wp_send_json_error( $user_id->errors);
            wp_die();
        }
       
        # delete former meta 
        delete_user_meta( $user_id, 'wa_token');
        # generate user meta 
        $token =  $this->createToken();
        add_user_meta($user_id, 'wa_token', $token);
        
        update_user_meta($user_id, 'first_name', $request['first_name'] ?? '');
        update_user_meta($user_id, 'last_name', $request['last_name'] ?? '');
        
        $user = new \StdClass();
        $user->id = $user_id;
        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->email = $user_email;
        $user->username = $user_login;
        $user->wa_token = $token;
        
        # after wat registration hook 
        do_action( 'after_wat_registration', $user_id );

        wp_send_json_success( $user );
        wp_die();
    }

    function update_user_metas($user_id){
        # update user meta

    }

    function send_password_reset_code($request){
        $email = $request['email'] ?? false;
        $username = $request['username'] ?? false;
        if(!$email && !$username){
            wp_send_json_error( 'INVALID_EMAIL_OR_USERNAME' );
            wp_die();            
        }

        $usisUserer = NULL;
        if($email){
            $isUser = get_user_by('email', $email );
        } else {
            $isUser = get_user_by('login', $username);
        }

        if(!$isUser){
            wp_send_json_error( 'USER_DOES_NOT_EXIST' );
            wp_die();
        }
        
        $user = new \WP_User( intval($isUser->ID) );
        $reset_key = get_password_reset_key( $user );
        $wc_emails = WC()->mailer()->get_emails();
        $sent = $wc_emails['WC_Email_Customer_Reset_Password']->trigger( $user->user_login, $reset_key );

        wp_send_json_success( 'SENT_RESET_LINK_TO_EMAIL' );
        wp_die();
        
    }

    function change_password($request){
        
        $user = $this->getUser();
        
        $old_pass = $request['old'] ?? false;

        if(!$old_pass){
            wp_send_json_error( 'INCORRECT_OLD_PASSWORD' );
            wp_die();
        }
       
        $userIntance = get_user_by_email( $user->email );

        if( !wp_check_password($old_pass, $userIntance->data->user_pass, $user->id )){
            wp_send_json_error( 'INCORRECT_OLD_PASSWORD' );
            wp_die();
        }

        $new_pass = $request['new'] ?? false;
        if(!$new_pass){
            wp_send_json_error( 'INVALID_PASSWORD' );
            wp_die();
        }

        wp_set_password($new_pass, $user->id);
        wp_send_json_success( 'PASSWORD_CHANGED' );
        wp_die();
    }
    
}

# additional features 
# declare a function that return logged in user 
if(!function_exists('wat_get_user')){
    function wat_get_user(){
        $functions = new \WAT\WAT();
        return $functions->getUser();
    }
}
