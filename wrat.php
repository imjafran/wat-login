<?php

/**
 *
 * @package wrat
 *
 * Plugin Name: WRAT | WordPress REST Auth Token
 * Plugin URI: https://github.com/imjafran/wrat
 * Description: oAuth2 Implementation for WordPress REST API, Specially Mobile Application
 * Version: 2.2.0
 * Author: Jafran Hasan
 * Author URI: https://github.com/iamjafran
 * License: GPLv3 or latter
 * Text Domain: wrat
 */

namespace WRAT;

/**
 * Restrict the direct script
 */

defined('ABSPATH') or die('Direct Script not Allowed');

/**
 * WP_REST_Response is REST response class of WordPress
 */
use \WP_REST_Response;

/**
 * Checking class existance
 */
if (!class_exists( __NAMESPACE__ . "\WRAT")) {
    
    final class WRAT
    {

        /**
         * plugin_file
         * Plugin Handler File
         * Default is __FILE__
         */
        private $plugin_file = __FILE__;


        /**
         * extended
         * @extended path recognize if WordPress installed in a sub directory of a domain / subdomain
         * Example: https://returnxero.com/another/wordpress/
         */
        private $extended = '/';


        /**
         * Access Token Validity
         * Default is +1month
         * Can be used as human readable time format like +1minute or +10days or +2years
         * Can be used as whole date like 2023-12-10 12:00:00 
         */
        
        private $validity = '+1month';





        
        private $table = 'wrat_tokens';

        public function __construct()
        {
            /**
             * Parsing the extended path
             */
            $this->extended = str_replace('/index.php', '', $_SERVER['PHP_SELF']);
        }

        public static function init($plugin_file = null)
        {
            /**
             * Instanciate the class
             */
            $instance = new self;

            /**
             * If plugin file found
             */
            if($plugin_file){
                $instance->plugin_file = $plugin_file;
            }
            
            /**
             * Resgiter the Hooks
             */
            $instance->register_hooks();

            
            /**
             * Returning the instance 
             */
            return $instance;
        }


        /**
         * Register Hooks 
         */
        private function register_hooks()
        {
            /**
             * Activation Hook
             */
            register_activation_hook($this->plugin_file, [$this, 'wrat_activate_plugin']);

            /**
             * Deactivation Hook
             */
            register_deactivation_hook($this->plugin_file, [$this, 'wrat_deactivate_plugin']);

          
            /**
             * Before registering RESTs
             */
            add_action('rest_api_init',                         [$this, 'wrat_rest_init'], 0);

            
            /**
             * Register RESTs routes
             */
            add_action('rest_api_init',                         [$this, 'register_wrat_rests'], 0);
        }

        /**
         * Activation hook callback
         **/ 
        private function wrat_activate_plugin()
        {
           
        }


        // function response($success = true, $code = false, $data = [])
        // {
        //     $response = ['success' => $success ? true : false];

        //     if ($code) $response['code'] = strtoupper($code);
        //     if (!empty($data)) {
        //         if (is_object($data) || is_array($data))
        //             $response['data'] = $data;
        //         else
        //             $response['code'] = strtoupper($data);
        //     }

        //     return $response;
        // }

        // function success($code = false, $data = [])
        // {
        //     return $this->response(true, $code, $data);
        // }

        // function error($code = false, $data = [])
        // {
        //     return $this->response(false, $code, $data);
        // }


        /**
         * 
         * Set custom validity for access token
         */
        public function validity($validity = '+1month')
        {

            /**
             * If the value is string, need to convert into unix timestamp
             */
            if( is_integer( $validity ) ) {
                $this->validity = strtotime($validity);
            } else {
                $this->validity = $validity;
            }
        }

        /**
         * Generate new access token
         */
        private function newTokenFor($user_id = null, $validity = null)
        {
            /**
             * Get User ID, unless get current user ID
             */

            $user_id = $user_id ?? get_current_user_id();

            $token = md5(bin2hex(random_bytes(16)));

            update_user_meta();
            return apply_filters( 'wrat_new_token', $token );
        }

        function getWRAT()
        {
            $token = false;
            $headers = $_SERVER["HTTP_AUTHORIZATION"] ?? false;
            if ($headers) {
                preg_match("/WRAT.[^\s]+/im", $headers, $match);
                if ($match && !empty($match) && count($match) > 0) {
                    $token = trim(str_replace('WRAT ', '', $match[0]));
                }
            }
            if (!$token) {
                $params = @file_get_contents('php://input');
                $token = json_decode($params)->wrat ?? false;
            }

            if (!$token) { 
                $token = $_REQUEST['wrat'] ?? false;
            }

            return $token;
        }

        function verifyWRAT()
        {
            if (is_user_logged_in()) {
                $user = $this->wrat_getUserFromObject(wp_get_current_user());
                return new WP_REST_Response($this->success('valid_wrat', $user));
            }
            return new WP_REST_Response($this->error('invalid_wrat'));
        }

        function deleteWRAT($token = false)
        {
            if (!$token) $token = $this->getWRAT();
            if ($token) {
                global $wpdb;
                $table_name = $wpdb->prefix . $this->table;
                $delete = $wpdb->query("DELETE FROM $table_name WHERE `token` = '{$token}';");
                return $delete;
            }
            return false;
        }

        function updateWRAT($user_id = false)
        {
            if (!$user_id) $user_id = get_current_user_id();
            if (!$user_id) return false;

            global $wpdb;
            $table_name = $wpdb->prefix . $this->table;

            $this->deleteWRAT();

            // create new token for the user 
            $token =  $this->create_wrat_token();
            $validity = apply_filters('wrat_validity', '1year');
            $wpdb->insert(
                $table_name,
                [
                    'user_id' => $user_id,
                    'token' => $token,
                    'validity' => date('Y-m-d H:i:s', strtotime('+' . $validity)),
                ]
            );

            return $token;
        }


        // user methods  

        function wrat_getUserFromObject($data)
        {
            if (!$data) return false;
            $output = new \StdClass();
            $output->id = (int) $data->data->ID;
            $output->first_name = get_user_meta($data->data->ID, 'first_name', true);
            $output->last_name = get_user_meta($data->data->ID, 'last_name', true);
            $output->email = $data->data->user_email;
            $output->role = $data->roles[0];
            $output->picture = get_user_meta($data->data->ID, '_wrat_picture', true);
            return $output;
        }

        function wrat_getUser()
        {
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                return $this->wrat_getUserFromObject($user);
            }
            return false;
        }

        function wrat_createUser($email = null, $password = null, $username = null)
        {

            if (!$email) return false;

            if (!$password) {
                $password = bin2hex(random_bytes(6));
            }

            # create unique username if not set 
            if (!$username) {
                $username = strtolower(explode('@', $email)[0]);
            }

            $user_id = false;
            while ($user_id = wp_create_user($username, $password, $email)) {
                if (!is_wp_error($user_id)) {
                    break;
                }
                $error_code = key($user_id->errors);
                if ($error_code == 'existing_user_login') {
                    $username .= rand(0, 9);
                    continue;
                }
                return false;
            }


            # after wat registration hook 
            do_action('wrat_after_registration', $user_id);
            return $user_id;
        }

        function wrat_loginUser($user)
        {
            $token = $this->updateWRAT($user->data->ID);
            $user = $this->wrat_getUserFromObject($user);
            $user->token = $token;

            # after wat auth hook 
            do_action('wrat_after_login', $user->data->ID);
            return $user;
        }

        // automatic authenticate user 
        function wrat_rest_init()
        {
            $token = $this->getWRAT();
            if ($token) {
                global $wpdb;
                $table_name = $wpdb->prefix . $this->table;
                $tokenFromDB = $wpdb->get_results($wpdb->prepare("SELECT user_id, validity FROM $table_name WHERE token = '$token' LIMIT 1"));

                if ($tokenFromDB && !empty($tokenFromDB)) {
                    $tokenFromDB = $tokenFromDB[0];
                    if (strtotime($tokenFromDB->validity) > time()) {
                        wp_set_current_user($tokenFromDB->user_id);
                    } else {
                        $this->deleteWRAT($token);
                    }
                }
            }

            // validate_wrat_endpoints
            if ($this->isWRATRestrictedRoute()) wp_send_json($this->error('invalid_wrat'));
        }

        function isWRATRestrictedRoute()
        {

            $parsed_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $parsed_url = explode('?', $parsed_url)[0];
            $requested_url = trim(str_replace(home_url(), '', $parsed_url), '/');
            $wrat_default_endpoints = [
                '/wp-json/wrat/refresh',
                '/wp-json/wrat/password/change'
            ];

            $wrat_endpoints = apply_filters('wrat_endpoints',  $wrat_default_endpoints);

            // check url match 
            $checkAuth = false;
            foreach ($wrat_endpoints as $wrat_endpoint) {
                $wrat_endpoint = trim($wrat_endpoint, '/');
                $wrat_endpoint = str_replace(['*'], ['.+'], $wrat_endpoint);
                preg_match('~^' . $wrat_endpoint . '+$~', $requested_url, $match);
                if ($match[0]) {
                    $checkAuth = $match[0];
                    break;
                }
            }

            return $checkAuth && !is_user_logged_in();
        }


        # Registering Endpoints
        function register_wrat_rests($server)
        {

            $routes = [
                ['register',                ['POST'],               'wrat_register'],

                // auth 
                ['auth',                    ['POST'],               'wrat_password_auth'],

                ['verify',                  ['GET', 'POST'],        'verifyWRAT'],
                ['refresh',                 ['GET', 'POST'],        'refreshWRAT'],
                ['logout',                  ['GET', 'POST'],        'wrat_logout'],
                ['password/forgot',         ['POST'],               'wrat_sendPasswordResetEmail'],
                ['password/change',         ['POST'],               'wrat_changePassword'],

            ];

            foreach ($routes as $route) {
                register_rest_route('/wrat', $route[0], [
                    'methods'  => $route[1],
                    'callback' => [$this, $route[2]]
                ]);
            }
        }


        // endpoints 
        function wrat_password_auth($request)
        {
            # before wat login hook
            do_action('wrat_before_login');

            $user = $this->wrat_getUser();
            if ($user) {
                $user->token = $this->getWRAT();
                return new WP_REST_Response($this->success(false, $user));
            }

            # authenticate user 
            $email = $request['email'] ?? $request['username'] ?? null;
            $password = $request['password'] ?? null;

            $authenticated = wp_authenticate($email, $password);

            if ($authenticated->data == null) {

                $errors = array_map(function ($error) {
                    return $error;
                }, array_keys($authenticated->errors));

                return new WP_REST_Response($this->error(false, $errors[0]));
            }

            # logged in 
            $user = $this->wrat_loginUser($authenticated);

            # after wat auth hook 
            do_action('wrat_after_login', $user->id);

            return new WP_REST_Response($this->success(false, $user));
        }

        function refreshWRAT()
        {
            $user = $this->wrat_getUser();
            if ($user) {
                $user->token = $this->updateWRAT();
                return new WP_REST_Response($this->success(false, $user));
            }
            return new WP_REST_Response($this->error('invalid_wrat'));
        }

        function wrat_logout()
        {
            $this->deleteWRAT();
            wp_logout();
            return new WP_REST_Response($this->success('LOGGED_OUT'));
        }

        function wrat_register($request)
        {

            $register_allow = get_option('users_can_register');
            if (!$register_allow) return new WP_REST_Response($this->error('not_allowed'));

            # before wat registration hook 
            do_action('wrat_before_registration', $request);

            $username = $request['username'] ?? null;
            $password = $request['password'] ?? null;
            $email = $request['email'] ?? null;

            if (!$email || !is_email($email)) {
                return new WP_REST_Response($this->error('invalid_email'));
            }

            $user_id = $this->wrat_createUser($email, $password, $username);
            if (!$user_id) {
                return new WP_REST_Response($this->error('existing_user_email'));
            }

            # before wat registration hook 

            add_user_meta($user_id, '_wrat_passwordless', false);

            update_user_meta($user_id, 'first_name', $request['first_name'] ?? '');
            update_user_meta($user_id, 'last_name', $request['last_name'] ?? '');
            update_user_meta($user_id, 'evr_reference', $request['reference'] ?? '');

            do_action('wrat_after_registration', $user_id);
            
            $user = $this->wrat_loginUser(get_user_by('id', $user_id));
            return new WP_REST_Response($this->success('registration_success', $user));
        }

        function wrat_sendPasswordResetEmail($request)
        {
            $email = $request['email'] ?? false;
            $username = $request['username'] ?? false;
            if (!$email && !$username) return new WP_REST_Response($this->error('empty_username'));

            $isUser = NULL;
            if ($email && is_email($email)) {
                $isUser = get_user_by('email', $email);
            } else if ($username) {
                $isUser = get_user_by('login', $username);
            } else {
                // do nothing 
            }

            if (!$isUser) return new WP_REST_Response($this->error('user_does_not_exist'));

            $user = new \WP_User(intval($isUser->ID));
            $reset_key = get_password_reset_key($user);
            $wc_emails = WC()->mailer()->get_emails();
            $sent = $wc_emails['WC_Email_Customer_Reset_Password']->trigger($user->user_login, $reset_key);

            return new WP_REST_Response($this->success('reset_email_sent'));
        }

        function wrat_changePassword($request)
        {

            $user = $this->wrat_getUser();

            // check if the user is passwordless user 
            $passwordless = get_user_meta($user->id, '_wrat_passwordless', true);

            if (!$passwordless) {
                $userIntance = get_user_by_email($user->email);

                $old_pass = $request['old'] ?? false;
                $force = $request['force'] ?? false;

                if (!$force && !$old_pass) return new WP_REST_Response($this->error('empty_old_password'));
                if (!wp_check_password($old_pass, $userIntance->data->user_pass, $user->id)) return new WP_REST_Response($this->error('incorrect_old_password'));
            }

            $new_pass = $request['password'] ?? false;
            if (!$new_pass) return new WP_REST_Response($this->error('empty_password'));

            wp_set_password($new_pass, $user->id);
            do_action('wrat_password_changed', get_current_user_id());

            // turn off passwordless mode 
            update_user_meta($user->id, '_wrat_passwordless', false);

            return new WP_REST_Response($this->success('password_changed'));
        }
    }

    // init wrat
    $_wrat = \WRAT\WRAT::init();
}
