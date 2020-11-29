<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class Hooks{
    /**
     * Hooks Register
     */
    function register_hooks() {
        add_action('tgmpa_register',                [&$this, 'plugin_activation']);
        add_action( 'admin_enqueue_scripts' ,           [&$this, 'admin_scripts']);
        // add_action( 'woocommerce_thankyou' ,        array( &$this, 'socket_notify'), 10, 1);
        add_action( 'admin_menu' ,                      [&$this, 'combopos_admin_menu']);
        add_action( 'init' ,                            [&$this, 'custom_post_status']);
        add_action( 'wc_order_statuses' ,                            [&$this, 'custom_wc_order_status']);        
        add_action( 'woocommerce_admin_order_data_after_order_details' ,                array( &$this, 'custom_delivery_time_options'));
        add_action( 'woocommerce_process_shop_order_meta' ,                array( &$this, 'custom_delivery_time_save'));
            
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
                'required'           => false
            ]
        ];


        $config = [
            'id'           => 'combopos_plugins', 
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
        wp_enqueue_script('combopos-admin', plugins_url('combopos/assets/js/admin.js'), ['jquery'], false, true);
        
        // wp_enqueue_style('uikit', 'https://cdn.jsdelivr.net/npm/uikit@3.5.9/dist/css/uikit.min.css');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
        wp_enqueue_style('combopos-admin', plugins_url('combopos/assets/css/admin.min.css'));
    }

    // init admin menu 
    function combopos_admin_menu()
    {
        add_menu_page( 'ComboPOS Admin', 'ComboPOS', 'manage_options', 'combopos', function(){
            include_once __DIR__ . '/../page/admin-menu.php';
        }, 'dashicons-welcome-widgets-menus', 9999 );           
    }

    function custom_post_status(){
        // registering custom post status 
        register_post_status( 'wc-on-the-way', [
            'label'                     => 'On the Way',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'On the Way (%s)', 'On the Way (%s)' )
        ] );
    }


    function custom_wc_order_status($order_statuses){
        $new_order_statuses = [];
    
        // add new order status after processing
        foreach ( $order_statuses as $key => $status ) {
    
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-on-the-way'] = 'On the Way';
            }
        }
    
        return $new_order_statuses;
    }


    function custom_delivery_time_options($order){ 
       
        $time = get_post_meta( $order->get_id(), 'delivery_time', true );
        $order_created = $order->get_date_created(); 
        $diff = strtotime($order_created);

        woocommerce_wp_text_input( array(
				'id' => 'delivery_time',
				'label' => 'Estimated Delivery Time: [in minutes]',
				'value' => $time,
				'wrapper_class' => 'form-field-wide'
            ) );
        ?>

<div class="delivery_time_quick">
    <a href="#" data-value="10">10 mins</a>
    <a href="#" data-value="15">15 mins</a>
    <a href="#" data-value="20">20 mins</a>
    <a href="#" data-value="25">25 mins</a>
    <a href="#" data-value="30">30 mins</a>
    <a href="#" data-value="40">40 mins</a>
    <a href="#" data-value="45">45 mins</a>
    <a href="#" data-value="60">60 mins</a>
</div>

<?php 
    }

    function custom_delivery_time_save($ord_id){
        $order = wc_get_order( $ord_id );
        $time = wc_clean( $_POST[ 'delivery_time' ] );
        $order_created = $order->get_date_created(); 
        $order_created_timestamp = strtotime();
        update_post_meta( $ord_id, 'delivery_time',  $order_created);		
    }




    
}
