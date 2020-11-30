<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Ajax extends Mother {
        /**
         * Ajax Register
         */


        function register_ajax() {
            add_action('wp_ajax_save_cs_settings', [$this, 'save_cs_settings']);
            add_action('wp_ajax_reset_cs_settings', [$this, 'reset_cs_settings']);
        }


        // functions 
        function response($status = true, $data = []){
            $out = [
                'status' => $status
            ];
            if(is_array($data)){
                $out['data'] = $data;
            } elseif(is_object($data)){
                $out['data'] = (object) $data;
            } else {
                $out['message'] = $data;
            }

            header('content-type: application/json');
            echo json_encode($out);
            wp_die();
        }

        function adminOnly(): void {
            if( !current_user_can("manage_options") ) {
                $this->response(false, 'You are not authorized');
            }            
        }
         

        function reset_cs_settings(){
            $this->adminOnly();
             $default_options = [
                'cpos_order_disable' => false,  
                'cpos_order_disable_reason' => '',
                'cpos_delivery_time' => 45,
                'cpos_app_primary_color' => '#cd5c5c',
                'cpos_app_logo_url' => 'https://image.shutterstock.com/image-vector/ui-image-placeholder-wireframes-apps-260nw-1037719204.jpg',
                'cpos_app_placeholder_url' => 'https://image.shutterstock.com/image-vector/ui-image-placeholder-wireframes-apps-260nw-1037719204.jpg',
            ];

            foreach($default_options as $default_option => $value){
                update_option($default_option, $value);
            }

            $this->response(true, $default_options);
        }

        function save_cs_settings(){
            $this->adminOnly();
            $default_options = [
                'cpos_order_disable' => $_POST['cpos_order_disable'] === 'on' ? true : false ,  
                'cpos_order_disable_reason' => sanitize_text_field($_POST['cpos_order_disable_reason']),
                'cpos_delivery_time' => intval($_POST['cpos_delivery_time']),
                'cpos_app_primary_color' => sanitize_text_field($_POST['cpos_app_primary_color']),
                'cpos_app_logo_url' => sanitize_text_field($_POST['cpos_app_logo_url']),
                'cpos_app_placeholder_url' => sanitize_text_field($_POST['cpos_app_placeholder_url']),
            ];

            foreach($default_options as $default_option => $value){
                update_option($default_option, $value);
            }

            $this->response(true);
        }

        
        
    }
