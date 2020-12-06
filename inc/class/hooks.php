<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class Hooks {
    /**
     * Hooks Register
     */
    function register_hooks() {
        add_action('tgmpa_register',                                                    [$this, 'plugin_activation']);
        add_action( 'admin_enqueue_scripts' ,                                           [$this, 'admin_scripts']);
        add_action( 'admin_menu' ,                                                      [$this, 'combopos_admin_menu']);
        add_action( 'init' ,                                                            [$this, 'custom_post_status']);
        add_action( 'admin_init' ,                                                      [$this, 'reset_default_values']);
        add_action( 'rest_api_init' ,                                                   [$this, 'reset_default_values']);
        add_action( 'wc_order_statuses' ,                                               [$this, 'custom_wc_order_status']);        
        add_action( 'woocommerce_admin_order_data_after_order_details' ,                [$this, 'custom_delivery_time_options']);
        add_action( 'woocommerce_process_shop_order_meta' ,                             [$this, 'custom_delivery_time_save']);
        add_action( 'woocommerce_update_product' ,                                      [$this, 'change_updated_at_option'], 10, 1);
        add_action( 'saved_product_cat' ,                                               [$this, 'change_updated_at_option']);
        add_action( 'rest_product_collection_params' ,                                  [$this, 'change_default_wc_rest_product_collection'], 10, 1);        
        add_action( 'init' ,                                                            [$this, 'register_post_type_notification']);        
        add_action( 'woocommerce_order_status_changed' ,                                       [$this, 'on_change_order_status'], 10, 2);        
            
    }
    
    // // before plugin activation 
    function plugin_activation(){
        $plugins = [
            // [
            //     'name'               => 'Woocommerce',
            //     'slug'               => 'woocommerce',
            //     'required'           => true
            // ],

            // [
            //     'name'               => 'JWT Auth',
            //     'slug'               => 'jwt-auth', 
            //     'required'           => false
            // ],

            [
                'name'               => 'Advanced Custom Fields Pro',
                'slug'               => 'advanced-custom-fields-pro',
                'source'             => 'https://updates.theme-fusion.com/?avada_action=get_download&item_name=Advanced%20Custom%20Fields%20PRO&nonce=4bcbbdcf8b&t=1604210331&ver=5.9', 
                'required'           => true
            ],

            [
                'name'               => 'Advanced Custom Fields: Extended',
                'slug'               => 'acf-extended',
                'required'           => true
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
        wp_enqueue_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.4.0/jscolor.min.js', ['jquery'], false, true);        
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', ['jquery'], false, true);        
        wp_enqueue_script('combopos-admin', plugins_url('combopos/assets/js/admin.js'), ['jquery'], false, true);
        
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css');
        wp_enqueue_style('combopos-admin', plugins_url('combopos/assets/css/admin.min.css'));
    }

    // init admin menu 
    function combopos_admin_menu()
    {
        add_menu_page( 'ComboPOS Admin', 'ComboPOS', 'manage_options', 'combopos', function(){
            include_once __DIR__ . '/../page/settings.php';
        }, 'dashicons-welcome-widgets-menus', 9999 ); 
        add_menu_page( 'Broadcast Notification', 'Notification', 'manage_options', 'notification', function(){
            include_once __DIR__ . '/../page/notifications.php';
        }, 'dashicons-bell', 9999 );              
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
        $order_created_timestamp = strtotime(time());
        update_post_meta( $ord_id, 'delivery_time',  $order_created);		
    }





    // reset_default_values  
    function reset_default_values(){
        $default_options = [
            'cpos_order_disable' => false,
            'cpos_order_disable_reason' => '',
            'cpos_delivery_time' => 45,
            'cpos_app_primary_color' => '#cd5c5c',
            'cpos_app_secondary_color' => '#FFC14F',
            'cpos_app_logo_url' => 'https://image.shutterstock.com/image-vector/ui-image-placeholder-wireframes-apps-260nw-1037719204.jpg',
            'cpos_app_placeholder_url' => 'https://image.shutterstock.com/image-vector/ui-image-placeholder-wireframes-apps-260nw-1037719204.jpg',
            'cpos_updated_at' => time(),
        ];

        foreach($default_options as $default_option => $value){
            register_setting('combopos_options', $default_option);            
            add_option($default_option, $value);
        }
    }

    function change_updated_at_option( $product_id ){
        update_option('cpos_updated_at', time());
    }

    function change_default_wc_rest_product_collection( $query_params ){
        $total_products = count( get_posts( array('post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => '-1') ) );
        $query_params['per_page']['maximum'] = $total_products;
        $query_params['per_page']['default'] = $total_products;
        return $query_params;
    }


    function register_post_type_notification(){
         $labels = [
            'name'                  => _x( 'Notification', 'Post type general name', 'combopos' ),
            'singular_name'         => _x( 'Notification', 'Post type singular name', 'combopos' ),
            'menu_name'             => _x( 'Notifications', 'Admin Menu text', 'combopos' ),
            'name_admin_bar'        => _x( 'Notification', 'Add New on Toolbar', 'combopos' ),
            'add_new'               => __( 'Add New', 'combopos' ),
            'add_new_item'          => __( 'Add New Notification', 'combopos' ),
            'new_item'              => __( 'New Notification', 'combopos' ),
            'edit_item'             => __( 'Edit Notification', 'combopos' ),
            'view_item'             => __( 'View Notification', 'combopos' ),
            'all_items'             => __( 'All Notifications', 'combopos' ),
            'search_items'          => __( 'Search Notifications', 'combopos' ),
            'parent_item_colon'     => __( 'Parent Notifications:', 'combopos' ),
            ];     
        $args = array(
            'labels'             => $labels,
            'description'        => 'Notification custom post type.',
            'public'             => true,
            'publicly_queryable' => true,
            // 'show_ui'            => false,
            // 'show_in_menu'       => false,
            'query_var'          => false,
            'rewrite'            => ['slug' => 'cs_notification' ],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'supports'           => [ 'title', 'custom-fields' ],
            'show_in_rest'       => false
        );
        
        register_post_type( 'cs_notification', $args );
    }


    // on_change_order_status
    function on_change_order_status($orderId = null, $changedFrom = null){
        $order = new \WC_Order($orderId);

        $order_statuses = [
            'pending' => 'is pending',
            'processing' => 'is processing',
            'on-hold' => 'is hold',
            'completed' => 'is completed',
            'cancelled' => 'is cancelled',
            'refunded' => 'is refunded',
            'failed' => 'is faild',
            'on-the-way' => 'is on the way',
        ];

        $message = array_key_exists($order->get_status(), $order_statuses) ? $order_statuses[$order->get_status()] : $order_statuses['processing'];

        // add to notification database
        $args = array(
            'post_type'    => 'cs_notification',
            'post_status'  => 'publish',
            'post_title' => 'Order ID ' . $orderId . ' ' .  $message,
            'post_content' =>  'Order is ' . $message,
            'meta_input'   => [
                'type'      => 'notification',
                'orderId'   =>  $orderId,
                'userId'    => $order->get_customer_id(),
                'by'        => 0
            ]
        );
        
        $notification_id = wp_insert_post($args);


        // push notification to mobile
        $push_notification_data = array(
            'token' => 'public', 
            'data' => [
                'type' => 'notification',
                'message' => 'Your order ' . $message,
                'room' => 'test',
                'orderId' => $orderId,
                'notificationId' => $notification_id,
                'userId' => $order->get_customer_id(),
            ]
        );  

       $this->push_notification_socket($push_notification_data);
       
    }
    

    function push_notification_socket($data = null){       
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, 'https://socket.maxkhaninc.com'); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [ 'content-type: application/json']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($curl);
        curl_close($curl);
        return true;
    }
}
