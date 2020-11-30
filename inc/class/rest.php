<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Rest extends Mother {
        /**
         * Hooks Register All Hook.
         */
        function register_rest() {
           add_action('rest_api_init', [$this, 'register_app_information']);
        }
        

        function register_app_information(){
             register_rest_route( 'combopos/v1', '/app', array(
                // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
                'methods'  => \WP_REST_Server::READABLE,
                // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
                'callback' => [$this, 'get_app_information'],
            ) );
        }

        function get_app_information(){
            $cpos_options = [
                'cpos_order_disable',
                'cpos_order_disable_reason',
                'cpos_delivery_time',
                'cpos_app_primary_color',
                'cpos_app_placeholder_url'
            ];
            $out = [

                // wordpress default 
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),


                // added by woocommerce 

                // added by combosoft
                'disable_order' => get_option('cpos_order_disable') == '1' ? true : false,
                'disable_order_reason' => esc_html(get_option('cpos_order_disable_reason')),
                'delivery_time' => esc_html(get_option('cpos_delivery_time')),
            ];


            return rest_ensure_response( $out );
        }

       
        
    }
