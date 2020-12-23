<?php

namespace Combosoft\ComboPOS\DeliveryZones;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class Functions {
    /**
     * Hooks Register
     */
    function __construct() {
        add_action('tgmpa_register',                                                    [$this, 'plugin_activation']);
        add_action( 'admin_enqueue_scripts' ,                                           [$this, 'admin_scripts']);       
            
    }
    
    // // before plugin activation 
    function plugin_activation(){
        $plugins = [
            [
                'name'               => 'Woocommerce',
                'slug'               => 'woocommerce',
                'required'           => true
            ],

            [
                'name'               => 'JWT Auth',
                'slug'               => 'jwt-auth', 
                'required'           => true
            ],

            [
                'name'               => 'Advanced Custom Fields Pro',
                'slug'               => 'advanced-custom-fields-pro',
                'source'             => 'https://updates.theme-fusion.com/?avada_action=get_download&item_name=Advanced%20Custom%20Fields%20PRO&nonce=4bcbbdcf8b&t=1604210331&ver=5.9', 
                'required'           => true
            ],

            [
                'name'               => 'Advanced Custom Fields: Extended',
                'slug'               => 'acf-extended',
                'required'           => false
            ]
        ];


        $config = [
            'id'           => 'combopos_deliveryzones_plugins', 
            'default_path' => '',
            'menu'         => 'combopos-install-plugins',
            'parent_slug'  => 'plugins.php', 
            'capability'   => 'manage_options',
            'has_notices'  => true,
            'dismissable'  => false,
            'dismiss_msg'  => '',
            'is_automatic' => false,
            'message'      => '',  
        ];


        // register tgmpa
        tgmpa( $plugins, $config );
    }

    // load admin script
    function admin_scripts() {
        wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@10', ['jquery'], false, true);        
        wp_enqueue_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.4.0/jscolor.min.js', ['jquery'], false, true);        
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', ['jquery'], false, true);        
        wp_enqueue_script('combopos-admin', plugins_url('combopos/assets/js/admin.js'), ['jquery'], false, true);
        
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css');
        // wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
        wp_enqueue_style('fontawesome', 'https://pro.fontawesome.com/releases/v5.15.1/css/all.css');
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css');
        wp_enqueue_style('combopos-admin', plugins_url('combopos/assets/css/admin.min.css'));
    }

    
}
