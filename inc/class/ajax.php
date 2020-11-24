<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Hooks{
        /**
         * Hooks Register All Hook.
         */
        function register_hooks() {
            // add_action( 'rest_api_init' ,               array( &$this, 'single_user_api_hook'));
            // add_action( 'wp_enqueue_scripts' ,          array( &$this, 'custom_style_hook' ));
            add_action( 'admin_enqueue_scripts' ,       array( &$this, 'combopos_admin_scripts' ));
            // add_action( 'woocommerce_thankyou' ,        array( &$this, 'socket_notify'), 10, 1);
            add_action( 'admin_menu' ,                  array( &$this, 'combopos_admin_menu'));
            // add_action( 'admin_footer' ,                array( &$this, 'add_nonce'));
            // add_action( 'wp_footer' ,                   array( &$this, 'add_option_to_footer'));
            // add_action( 'wp_ajax_save_option',          array( &$this, 'admin_save_option'));
            // add_action( 'wp_ajax_nopriv_save_option' ,  array( &$this, 'admin_save_option'));
        }

        function combopos_admin_scripts() {
            wp_enqueue_script('combopos-admin', plugins_url('combopos/assets/js/admin.js'), ['jquery'], false, true);
        }

        public function combopos_admin_menu()
        {
            add_menu_page( 'ComboPOS Admin', 'ComboPOS', 'manage_options', 'custom.php', '', 'dashicons-welcome-widgets-menus', 5 );
        }

        
    }
