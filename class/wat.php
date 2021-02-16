<?php

namespace WAT;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

if(!class_exists("\WAT\WAT")) {
    class WAT {
    
        /*
            WAT | Web Auth Token 
            Binary Authentication System for WordPress REST API
            Architect : Jafran Hasan
            Documentation: https://github.com/imjafran/wat-login
        */
        # member variables
        public $extended = '/';

        function __construct(){
            $this->extended = str_replace('/index.php', '', $_SERVER['PHP_SELF']);
        }
        
        # Register Hooks 
        function init() { 
            # custom construct method       
            add_action('plugins_loaded',                        [$this, 'after_wat_loaded']);
            add_action('rest_api_init',                         [$this, 'wat_rest_init'], 0);
            add_action('rest_api_init',                         [$this, 'register_wat_rests']);
            add_action('wat_after_registration',                [$this, 'wat_after_registration'], 0);
        }
        
        // core build 
        function getMessages($message_id = false){
            $messages = [
                'empty_username' => 'Empty Username',
                'not_registered' => 'User not Registered',
                'invalid_username' => 'Invalid Username',
                'invalid_email' => 'Invalid Email',
                'empty_password' => 'Empty Password',
                'incorrect_password' => 'Incorrect Password',
                'existing_user_email' => 'Email already registered',
                'user_does_not_exist' => 'User doesn\'t exist',
                'not_allowed' => 'Not Allowed',
                'reset_email_sent' => 'Password Recovery Email Sent',
                'user_registered' => 'User Registered Successfully',
                'valid_web_auth_token' => 'Valid Web Auth Token',
                'invalid_web_auth_token' => 'Invalid Web Auth Token',
                'logged_in' => 'Logged In',
                'password_changed' => 'Password changed successfully',
            ];

            $FilteredMessages = apply_filters( 'wat_response_messages', $messages );

            return (array_key_exists(strtolower($message_id), $FilteredMessages)) ? $FilteredMessages[strtolower($message_id)] : $message_id;
        }        

        function response($success = true, $data = [], $codeWithData = ''){
            $response = [
                'success' => $success ? true : false,
                'code' => $codeWithData,
                'message' => '',
                // 'data' =>  [],
            ];         
            if(is_object($data) || is_array($data)){
                $response['data'] = $data;
            } else {
                $response['code'] = strtoupper($data);
                $response['message'] = $this->getMessages($data);
            }

            wp_send_json( $response );
            wp_die();
        }

        function success($data = [], $codeWithData = ''){
            $this->response(true, $data);
        }

        function error($data = [], $codeWithData = ''){
            $this->response(false, $data);
        }
        

        // token methods 
        function createToken(){
            $wat_token_bytes = apply_filters( 'wat_token_bytes', 10 );
            return time() . '.' . bin2hex(random_bytes($wat_token_bytes));
        }        
        
        function getTokenFromHeader(){
            $headers = $_SERVER["HTTP_AUTHORIZATION"] ?? false;
            if($headers) {
                preg_match("/WAT\s+[^\s]+/im", $headers, $match);
                if($match && !empty($match) && count($match) > 0){
                    $token = trim(str_replace('WAT', '', $match[0]));
                    $this->token = $token;
                } else {
                    $token = false;
                }
            }
            return $token;
        }

        function getToken(){
            $token = $this->getTokenFromHeader();
            if(!$token) {
                    $token = $_REQUEST['wat'] ?? false;
            }
            if(!$token){
                $params = @file_get_contents('php://input');
                $token = json_decode($params)->wat ?? false;
            }
            return $token;
        }
        
        function verifyToken(){
            
            $user = $this->getUserFromObject(wp_get_current_user());
            if(is_user_logged_in()){
                unset($user->capabilities);            
                $user->code = 'valid_web_auth_token';
                $user->message = $this->getMessages($user->code);
                $this->success($user);
            }
            $this->error('INVALID_WEB_AUTH_TOKEN');
        }
        
        function updateToken($user_id){
            $token =  $this->createToken();
            $update = update_user_meta( $user_id, '_wat_token', $token);
            if(!$update) add_user_meta( $user_id, '_wat_token', $token);
            return $token;
        }
        
        // user methods  
                
        function getUserFromObject($data){
            
            if(!$data) return false;
            
            $output = new \StdClass();
            $output->id = (int) $data->data->ID;
            $output->first_name = get_user_meta($data->data->ID, 'first_name', true);            
            $output->last_name = get_user_meta($data->data->ID, 'last_name', true);            
            $output->email = $data->data->user_email;        
            $output->username = $data->data->user_login;
            $output->token = get_user_meta($data->data->ID, '_wat_token', true);
            $output->role = $data->roles[0];
            
            $caps = [];
            if($data->allcaps){

                foreach($data->allcaps as $cap => $value){
                    if($value)
                        $caps[] = $cap;
                }

            }
            $output->capabilities = $caps;
            return $output;
        }
        
        function getUser(){

            if(is_user_logged_in()){
                $user = wp_get_current_user();
                return $this->getUserFromObject($user);
            }
            return false;
        }
        
        function createUser($email = null, $password = null, $username = null){
            
            if(!$email) return false;
            
            if(!$password) {
                $password = bin2hex(random_bytes(6));
            }

            # create unique username if not set 
            if(!$username){
                $username = strtolower(explode('@', $email)[0]);
            }

            $user_id = false;
            while($user_id = wp_create_user( $username, $password, $email )){
                if(!is_wp_error($user_id)){
                    break;
                }
                $error_code = key($user_id->errors);
                if($error_code == 'existing_user_login'){
                    $username .= rand(0,9);
                    continue;
                } 
                return false;
            }
            
            
            # after wat registration hook 
            do_action( 'wat_after_registration', $user_id );
            return $user_id;
        }

        function loginUser($user){
            # logged in 
            // if(!$user) return false;

            $this->updateToken($user->data->ID);
            $user = $this->getUserFromObject($user);  
            unset($user->capabilities);                    
            
            # after wat auth hook 
            do_action( 'wat_after_login', $user->data->ID );
            return $user;
        }

        // automatic authenticate user 
        function wat_rest_init(){
            $token = $this->getToken();
            if($token){
                $user = get_users([
                    'meta_key' => '_wat_token',
                    'meta_value' => $token
                ]);

                if($user && !empty($user)){                
                    wp_set_current_user( $user[0]->data->ID, $user[0]->data->user_login);                    
                } 
            }

            // validate_middlewares
            $this->validate_middlewares();
        }

        function validate_middlewares(){

            $parsed_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $parsed_url = explode('?', $parsed_url)[0];
            $home_url = home_url();
            $requested_url = trim(str_replace($home_url, '', $parsed_url), '/');
            $default_middlewares = [
                '/wp-json/wat/v1/password/change',
                '/wp-json/wat/v1/auth/test',
            ];

            $middlewares = apply_filters( 'wat_apply_middleware',  $default_middlewares);        

            // check url match 
            $isAppplied = false;        
            foreach($middlewares as $middleware){
                $middleware = trim($middleware, '/');
                $middleware = str_replace(['*'], ['.+'], $middleware);
                preg_match('~^' . $middleware . '+$~', $requested_url, $match);
                if($match[0]){
                    $isAppplied = $match[0];
                    break;
                }
            }

            if($isAppplied && !is_user_logged_in()) {
                $this->error('INVALID_WEB_AUTH_TOKEN');
            }
            
        }
        
               
        # Registering Endpoints
        function register_wat_rests($server){
            
            $routes_v1 = [            
                # [ROUTE, METHODS, CALLBACK],
                ['auth',                    ['POST'],       'authenticateUser'],
                ['verify',                  ['GET'],        'verifyToken'],
                ['logout',                  ['GET'],        'logoutUser'],
                ['register',                ['POST'],       'registerUser'],
                ['password/forgot',         ['POST'],        'send_password_reset_code'],
                ['password/change',         ['POST'],        'change_password'],
                
                // socials 
                ['auth/facebook',           ['POST'],        'auth_facebook'],
                ['auth/google',             ['POST'],        'auth_google'],
                
                // development 
                ['auth/test',             ['GET', 'POST'],        'auth_test'],
                
            ];

            foreach($routes_v1 as $route){
                register_rest_route( '/wat/v1', $route[0], [
                    'methods'  => $route[1],
                    'callback' => [$this, $route[2]]                    
                ]);
            }
        }

        
        // endpoints 
        function authenticateUser($request){
            $email = $request['email'] ?? $request['username'] ?? null;
            $password = $request['password'] ?? null;

            # before wat login hook
            do_action('wat_before_login');
            
            # authenticate user 
            $authenticated = wp_authenticate($email, $password);
            
            if($authenticated->data != null){
                # logged in 
                
                $user = $this->loginUser( $authenticated );              
              
                # after wat auth hook 
                do_action( 'wat_after_login', $user->id );
                
                $this->success($user);
            }
            $errros = array_map(function($error){
                return $this->getMessages($error);
            }, array_keys($authenticated->errors));
            
            $error = array_keys($authenticated->errors)[0];
            $error = $error == 'invalid_email' ? 'not_registered' : $error;
            $this->error($error);
        }

        function logoutUser($request){
            $id = get_current_user_id();
            if($id)
                update_user_meta( $id, '_wat_token', '');
            wp_logout();
            $this->success('LOGGED_OUT');
        }

        function registerUser($request){

            $register_allow = get_option( 'users_can_register');
            if(!$register_allow) {
                $this->error('not_allowed');
            }
            
            # before wat registration hook 
            do_action( 'wat_before_registration', $request );

            $username = $request['username'] ?? null;
            $password = $request['password'] ?? null;
            $email = $request['email'] ?? null;

            if(!$email) {
                $this->error('invalid_email');
            }
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {            
                $this->error('invalid_email');
            }

            $user_id = $this->createUser($email, $password, $username);
            if(!$user_id){
                $this->error('existing_user_email');
            } 

            add_user_meta( $user_id, '_wat_passwordless', false );
            
            update_user_meta($user_id, 'first_name', $request['first_name'] ?? '');
            update_user_meta($user_id, 'last_name', $request['last_name'] ?? '');
                  
            $user = $this->loginUser(get_user_by( 'id', $user_id ));
            $this->success( $user );
        }

        function send_password_reset_code($request){
            $email = $request['email'] ?? false;
            $username = $request['username'] ?? false;
            if(!$email && !$username){            
                $this->error('empty_username');         
            }

            $usisUserer = NULL;
            if($email){
                $isUser = get_user_by('email', $email );
            } else {
                $isUser = get_user_by('login', $username);
            }

            if(!$isUser){            
                $this->error('user_does_not_exist');
            }
            
            $user = new \WP_User( intval($isUser->ID) );
            $reset_key = get_password_reset_key( $user );
            $wc_emails = WC()->mailer()->get_emails();
            $sent = $wc_emails['WC_Email_Customer_Reset_Password']->trigger( $user->user_login, $reset_key );

            $this->success('reset_email_sent');
            
        }

        function change_password($request){
            
            $user = $this->getUser();
            
            // check if the user is passwordless user 
            $passwordless = get_user_meta($user->id, '_wat_passwordless', true);
           
            if(!$passwordless){
                $userIntance = get_user_by_email( $user->email );
                
                $old_pass = $request['old'] ?? false;

                if(!$force && !$old_pass){            
                    $this->error('empty_old_password');
                }
                if( !wp_check_password($old_pass, $userIntance->data->user_pass, $user->id )){            
                    $this->error( 'incorrect_old_password' );
                }
            }
            
            $new_pass = $request['password'] ?? false;
            if(!$new_pass){            
                $this->error('empty_password');
            }

            wp_set_password($new_pass, $user->id);
            
            // turn off passwordless mode 
            update_user_meta($user->id, '_wat_passwordless', false);
            
            $this->success('password_changed');
        }


        // api response 
        function getResponse($url = '', $data = [], $post = false){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, $post ? 1 : 0);
            curl_setopt($curl, CURLOPT_URL, $url);  
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            if(!empty($data))
                curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
            
            $result = curl_exec($curl);
            $info = curl_getinfo($curl); 

            curl_close($curl);
            return (object) [
                'type' => $info['content_type'],
                'code' => $info['http_code'],
                'response' => $result
            ];
        }

        // social login 

        // social facebook 
        function auth_facebook($request){
            // required 
            $facebook_id = $request['facebook_id'] ?? false;
            $access_token = $request['access_token'] ?? false;
            
            if(!$facebook_id) $this->error('invalid_facebook_id');
            if(!$access_token) $this->error('invalid_access_token');
            
            // action 
            $action = $request['action'] ?? 'auth';


            $url = 'https://graph.facebook.com/' . $facebook_id . '?fields=id,first_name,last_name,email,picture&access_token=' . $access_token;
            
            $fb = $this->getResponse($url);
            $response = json_decode($fb->response); 

            if(isset($response->error)){
               $this->error($response->error->message);
            }

             // chech the email registered yet 
            $linkedUsers = get_users([
                'meta_key' => '_wat_facebook',
                'meta_value' => $response->email
            ]);

            $linkedUser = $linkedUsers[0] ?? false;

            // response is ok 

            switch($action){
                case 'link':
                     // link account
                    if(is_user_logged_in()){
                        if($linkedUser) {
                            if($linkedUser->ID == get_current_user_id()) {
                                $this->error('already_linked');
                            } else {
                                $this->error('linked_to_someone_else');
                            }
                        } else {
                            update_user_meta( get_current_user_id(), '_wat_facebook', $response->email );
                            $this->success('facebook_linked');
                        }
                    } else {
                        $this->error('invalid_web_auth_token');
                    }
                break;

                case 'unlink':
                    // unlink 
                    if(is_user_logged_in()){
                        if($linkedUser) {
                            if($linkedUser->ID == get_current_user_id()) {

                                // check accessibility
                                $passwordless = get_user_meta(get_current_user_id(), '_wat_passwordless', true);
                                $googleConnected = get_user_meta(get_current_user_id(), '_wat_google', true);                                
                                if($passwordless && !$googleConnected) $this->error('You can not disconnect Facebook. Either connect Google or set Password first.');

                                update_user_meta( get_current_user_id(), '_wat_facebook', '' );
                                $this->success('facebook_unlinked');
                            } else {
                                $this->error('permission_denied');
                            }
                        } else {
                           $this->error('not_linked');
                        }
                    } else {
                        $this->error('invalid_web_auth_token');
                    }
                break;
                
                default: 
                // auth account 
               
                    
                    // if user registered 
                    if($linkedUser){         

                        // login the user 
                        $data = $this->loginUser( $linkedUser );
                        $this->success($data);
                        
                    } else {
                        // user not registered 
                        // check the email registered 
                        $userByEmail = get_user_by_email( $response->email );
                        // wp_send_json( $userByEmail );
                        
                        if(is_a($userByEmail, '\WP_User')){
                            // not connected yet 
                            $this->error('facebook_not_connected');
                        } else {

                            // register new account 
                            $user_id = $this->createUser($response->email);
                            add_user_meta( $user_id, '_wat_passwordless', true );
                            update_user_meta($user_id, 'first_name', $response->first_name );
                            update_user_meta( $user_id, 'last_name', $response->last_name );                
                            update_user_meta( $user_id, '_wat_facebook', $response->email );  
                            $picture = get_user_meta($user_id, '_wat_picture', true);
                            if(empty($picture)) update_user_meta($user_id, '_wat_picture', $response->picture->data->url);

                            $data = $this->loginUser(get_user_by('id', $user_id ));                            
                            $this->response(true, $data, "SET_PASSWORD");
                        }
                    }
                break;
            }
                            
                
        }
        // social google 
        function auth_google($request){
            // required fields 
            
            $access_token = $request['access_token'] ?? false;                            
            if(!$access_token) $this->error('access_token');                    
            
            // action 
            $action = $request['action'] ?? 'auth';
            
            $url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $access_token;
            
            $google = $this->getResponse($url);
            $response = json_decode($google->response);


            if(isset($response->error_description) && !empty($response->error_description)){
                $this->error('invalid_access_token');
            }
            
            // response is ok 

            $linkedUsers = get_users([
                        'meta_key' => '_wat_google',
                        'meta_value' => $response->email
                    ]);
            $linkedUser = $linkedUsers[0] ?? false;

            switch($action){
                case 'link':
                    // link account
                    if(is_user_logged_in()){
                        if($linkedUser) {
                            if($linkedUser->ID == get_current_user_id()) {
                                $this->error('already_linked');
                            } else {
                                $this->error('linked_to_someone_else');
                            }
                        } else {
                            update_user_meta( get_current_user_id(), '_wat_google', $response->email );
                            $this->success('google_linked');
                        }
                    } else {
                        $this->error('invalid_web_auth_token');
                    }
                break;

                case 'unlink':
                    // unlink account 
                    if(is_user_logged_in()){
                        if($linkedUser) {
                            if($linkedUser->ID == get_current_user_id()) {
                                // check accessibility
                                $passwordless = get_user_meta(get_current_user_id(), '_wat_passwordless', true);
                                $facebookConnected = get_user_meta(get_current_user_id(), '_wat_facebook', true);                                
                                if($passwordless && !$facebookConnected) $this->error('You can not disconnect Google. Either connect Facebook or set Password first.');
                            
                                update_user_meta( get_current_user_id(), '_wat_google', '' );
                                $this->success('google_unlinked');
                            } else {
                                $this->error('permission_denied');
                            }
                        } else {
                           $this->error('not_linked');
                        }
                    } else {
                        $this->error('invalid_web_auth_token');
                    }
                break;
                
                default: 
                // auth account 
                // chech the email registered yet 
                    
                    
                    // if user registered 
                    if($linkedUser){

                        // login the user 
                        $data = $this->loginUser( $linkedUser );
                        $this->success($data);
                        
                    } else {
                        // user not registered, so create one 
                        $userByEmail = get_user_by_email( $response->email );
                           
                        if(is_a($userByEmail, '\WP_User')){
                            // not connected yet 
                            $this->error('not_linked');
                        } else {
                            // register new account 
                            $user_id = $this->createUser($response->email);
                            add_user_meta( $user_id, '_wat_passwordless', true );
                            update_user_meta($user_id, 'first_name', $response->given_name );
                            update_user_meta( $user_id, 'last_name', $response->family_name );                
                            update_user_meta( $user_id, '_wat_google', $response->email );  
                            $picture = get_user_meta($user_id, '_wat_picture', true);
                            if(empty($picture)) update_user_meta($user_id, '_wat_picture', $response->picture);

                            $data = $this->loginUser(get_user_by('id', $user_id ));
                            $this->response(true, $data, "SET_PASSWORD");
                        }
                    }
                break;
            }
            
                            
                
        }

        // additional    
        function wat_after_registration($user_id){
            # update user meta
            add_user_meta($user_id, '_wat_facebook', '');
            add_user_meta($user_id, '_wat_google', '');
            // add_user_meta($user_id, '_wat_twitter', '');
            // add_user_meta($user_id, '_wat_linkedin', '');
            // add_user_meta($user_id, '_wat_github', '');
            // add_user_meta($user_id, '_wat_pinterest', '');
            add_user_meta($user_id, '_wat_picture', '');
        }     
        
        function after_wat_loaded(){
            # if jwt installed, whitelisting wat
            add_filter( 'jwt_auth_whitelist', function($endpoints){
                array_push( $endpoints, $extended . '/wp-json/wat/*' );
                return $endpoints;
            });
        }

            
        // development 
        function auth_test(){
           $this->succes('INVALID_EMAIL');
        }

    }
}
