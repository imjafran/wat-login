<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Ajax{
        /**
         * Ajax Register
         */

         function __construct(){
             if( !is_admin() ) wp_die();
         }
        function register_ajax() {
            add_action('wp_ajax_cs_save_settings', [$this, 'save_settings']);
        }



        function save_settings(){
            echo 'saved';
        }

        
        
    }
