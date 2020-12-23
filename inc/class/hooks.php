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
        add_action( 'woocommerce_process_shop_order_meta' ,                             [$this, 'save_order_meta_admin_panel'], 10);
        add_action( 'woocommerce_update_product' ,                                      [$this, 'change_updated_at_option'], 10, 1);
        add_action( 'saved_product_cat' ,                                               [$this, 'change_updated_at_option']);
        add_action( 'rest_product_collection_params' ,                                  [$this, 'change_default_wc_rest_product_collection'], 10, 1);        
        add_action( 'init' ,                                                            [$this, 'register_post_type_notification']);        
        add_action( 'woocommerce_order_status_changed' ,                                [$this, 'on_change_order_status'], 10, 2);        
        add_action( 'woocommerce_product_data_tabs' ,                                   [$this, 'custom_product_data_tabs']);        
        add_action( 'woocommerce_product_data_panels' ,                                 [$this, 'custom_product_data_panels']);        
        add_action( 'woocommerce_process_product_meta_simple' ,                         [$this, 'custom_process_product_meta']);        
        add_action( 'woocommerce_process_product_meta_variable' ,                       [$this, 'custom_process_product_meta']);        
        add_action( 'product_type_options' ,                                            [$this, 'custom_product_type_options']);        
        add_action( 'woocommerce_product_options_pricing' ,                             [$this, 'custom_product_options_pricing']);        
        add_action( 'woocommerce_variation_options_pricing' ,                           [$this, 'custom_variation_options_pricing']);        
        add_action( 'init' ,                                                            [$this, 'custom_settings_page']);        
        add_action( 'plugins_loaded' ,                                                  [$this, 'custom_admin_column_shop_order']);        
        add_action( 'woocommerce_admin_order_data_after_billing_address' ,             [$this, 'custom_shop_order_admin_data']);        
            
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
        // wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
        wp_enqueue_style('fontawesome', 'https://pro.fontawesome.com/releases/v5.15.1/css/all.css');
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css');
        wp_enqueue_style('combopos-admin', plugins_url('combopos/assets/css/admin.min.css'));
    }

    // init admin menu 
    function combopos_admin_menu()
    {
        add_menu_page( 'ComboPOS Settings', 'ComboPOS', 'edit_posts', 'combopos', function(){
            include_once __DIR__ . '/../page/notifications.php';
        }, '', 900000  );  

        add_submenu_page( 'admin.php?page=combopos', 'Broadcast Notification', 'Notification', 'edit_posts', 'notification', function(){
            include_once __DIR__ . '/../page/notifications.php';
        }, '', 9000 );  

        add_submenu_page( 'hidden.php', 'License ComboPOS', 'License', 'edit_posts', 'license', function(){
            include_once __DIR__ . '/../page/notifications.php';
        }, '', 9000 );    



        // if( function_exists('acf_add_options_page') ) {
	
        //     acf_add_options_page(array(
        //         'page_title' 	=> 'ComboPOS Settings',
        //         'menu_title'	=> 'ComboPOS',
        //         'menu_slug' 	=> 'combopos',
        //         'capability'	=> 'edit_posts',
        //         'redirect'		=> false
        //     ));
            
        //     acf_add_options_sub_page(array(
        //         'page_title' 	=> 'ComboPOS Settings',
        //         'menu_title'	=> 'Settings',
        //         'parent_slug'	=> 'combopos',
        //         'menu_slug'     => 'combopos-settings'
        //     ));

        //     add_submenu_page( 'combopos', 'Broadcast Notification', 'Notification', 'edit_posts', 'admin.php?page=notification', '', '', 900000000 ); 
        //     add_submenu_page( 'combopos', 'License ComboPOS', 'License', 'edit_posts', 'admin.php?page=license', '', '', 90000000 ); 
        // }          
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
        array_splice( $order_statuses, 2, 0, ['wc-on-the-way' => 'On the Way'] );
        return $order_statuses;
    }


    function custom_delivery_time_options($order){ 
       
        $time = get_post_meta( $order->get_id(), '_delivery_time', true );
        $order_created = $order->get_date_created(); 
        $diff = time() - strtotime($order_created);

        woocommerce_wp_text_input( array(
				'id' => 'delivery_time',
				'label' => 'Estimated Delivery Time: [in minutes]',
				'value' => ceil($diff/60)/60,
				'wrapper_class' => 'form-field-wide'
            ) );
        ?>

<div class="delivery_time_quick">
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



    function custom_product_data_tabs( $tabs ){
        
        $tabs['food_op'] = [
            'label'		=> __( 'Food Options', 'woocommerce' ),
            'target'	=> 'food_op',
            'class'		=> [ 'show_if_simple', 'show_if_variable'],
        ];

        return $tabs;

    }

    function custom_product_data_panels(){
        global $post;
	
	// Note the 'id' attribute needs to match the 'target' parameter set above
	?><div id='food_op' class='panel woocommerce_options_panel'><?php

        ?><div class='options_group'><?php
        
            
            $spicy = get_post_meta($post->ID, 'cs_spicy');

		?>
        <fieldset class="form-field cs_spicy_field ">
            <label style="float: left">Hide Food from</label>
            <ul class="wc-radios" style="display: flex; justify-content: space-between">
                <?php 
                $hide_froms = [
                    'cs_hide_web' => 'Web', 
                    'cs_hide_customer' => 'Customer App', 
                    'cs_hide_waiter' => 'Waiter'
                ];                    
                foreach($hide_froms as $hide_from => $hide_from_label): 
                        $value = get_post_meta($post->ID, $hide_from)[0]; 
                        ?>
                <li><label><input name="<?=$hide_from?>" value="<?=$value?>" type="checkbox" class="select short"
                            <?=$value == 'yes' ? 'checked' : ''?>>
                        <?=$hide_from_label?></label>
                </li>
                <?php endforeach; ?>
            </ul>
        </fieldset>

        <fieldset class="form-field cs_spicy_field ">
            <label style="float: left">Spicy Level</label>
            <ul class="wc-radios" style="display: flex; justify-content: space-between">
                <?php for($i = 0; $i <= 3; $i++): ?>
                <li><label><input name="cs_spicy" value="<?=$i?>" type="radio" class="select short"
                            <?=$spicy[0] == $i ? 'checked' : ''?>>
                        <?=str_repeat('<i
                            class="fad fa-pepper-hot" style="--fa-primary-color: green; --fa-secondary-color: red; --fa-primary-opacity: 1; --fa-secondary-opacity: 1;"></i>', $i)?>
                        <?=$i == 0 ? 'No Chilli' : ''?></label>
                </li>
                <?php endfor; ?>
            </ul>
        </fieldset>

    </div>

</div><?php
    }



    function custom_product_type_options($product_type_options){
        // unset($product_type_options["_op_drink"]);
        //  $product_type_options["_op_drink"] = [
        //     "id"            => "_op_drink",
        //     "wrapper_class" => ["show_if_simple", "show_if_variable"],
        //     "label"         => "Bar Item",
        //     "description"   => "Product is Bar Item",
        //     "default"       => "no",
        // ];

        return $product_type_options;
    }



    function custom_process_product_meta($post_id){
        
        // process custom meta for products 

        // hide on customer app 
        $hide_on_customer_app = $_POST['cs_hide_customer'] ? 'yes' : 'no';
        update_post_meta($post_id, 'cs_hide_customer', $hide_on_customer_app);
        
        // hide from web 
        $hide_from_web = $_POST['cs_hide_web'] ? 'yes' : 'no';
        update_post_meta($post_id, 'cs_hide_web', $hide_from_web);
        
        // hide from waiter 
        $hide_waiter = $_POST['cs_hide_waiter'] ? 'yes' : 'no';
        update_post_meta($post_id, 'cs_hide_waiter', $hide_waiter);
        
        // spicy level 
        $spicy_level = $_POST['cs_spicy'] ?? 0;
        update_post_meta($post_id, 'cs_spicy', $spicy_level );
        
    }

    function custom_product_options_pricing(){
        woocommerce_wp_text_input( array( 'id' => 'cs_takeaway_price', 'class' => 'wc_input_price short', 'label' => __( 'Take-away Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
    }

    function custom_variation_options_pricing(){
        woocommerce_wp_text_input( array( 'id' => 'cs_variation_takeaway_price', 'class' => 'form-field form-row form-row-last', 'label' => __( 'Take-away Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
    //     woocommerce_wp_text_input(
    //     array(
    //         'id' => 'cs_variation_takeaway_price[' . $variation->ID . ']',
    //         'label' => __('Take-away Price', 'woocommerce'),
    //         'placeholder' => 'http://',
    //         'wrapper_class' => 'form-row',
    //         'desc_tip' => 'true',
    //         'description' => __('Enter the custom value here.', 'woocommerce'),
    //         'value' => get_post_meta($variation->ID, '_text_field', true)
    //     )
    // );
    }

    function custom_settings_page(){
        if( function_exists('acf_add_options_page') ):

            acf_add_options_page([
                'page_title' => 'Combopos Settings',
                'menu_title' => 'Combopos Settings',
                'menu_slug' => 'combopos-settings',
                'capability' => 'edit_posts',
                'position' => '',
                'parent_slug' => 'combopos',
                'icon_url' => '',
                'redirect' => true,
                'post_id' => 'options',
                'autoload' => false,
                'update_button' => 'Update',
                'updated_message' => 'Options Updated',
            ]);

            endif;
    }

    function custom_admin_column_shop_order(){
        // Just to make clear how the filters work
        $posttype = "shop_order";

        // Priority 20, with 1 parameter (the 1 here is optional)
        add_filter( "manage_edit-{$posttype}_columns", function ($columns) {
            array_splice( $columns, 2, 0, ['order_type' => 'Order Type'] );
            return $columns;
        }, 20, 1 ); 

        // Priority 20, with 2 parameters
        add_action( "manage_{$posttype}_posts_custom_column", function ( $column_name, $post_id ) {
            if ( 'order_type' != $column_name )
                return;

            $sales_information = 'Your custom get_order_sales_information($post_id)';

            if ( $sales_information ) {
                echo get_field('order_type', $post_id)['label'];
            }
        }, 20, 2 ); 

        // Default priority, default parameters (zero or one)
        add_filter( "manage_edit-{$posttype}_sortable_columns", function ( $columns ) {
            $columns['order_type'] = 'order_type';
            return $columns;
        } ); 
        
    }


    function custom_shop_order_admin_data($order){
         // Radio Buttons field
        //  var_dump($order->get_id());
        $_order_type = get_post_meta($order->get_id(),'order_type',  true);
        $_order_source = get_post_meta($order->get_id(), 'order_source', true);
        var_dump($_order_type);
        var_dump($_order_source);
       ?>
<div class="cs-form">
    <h5>Order Type</h5>
    <div class="cs_form_radio">
        <?php 
            $order_types = [
                "takeaway" => [
                    "Takeaway", "fa fa-bags-shopping"
                ],
                "delivery" => [
                    "Delivery", "fa fa-shipping-fast"
                ],
                "dinein" => [
                    "Dine In", "fa fa-utensils-alt"
                ]
            ];

            foreach($order_types as $order_type_slug => $order_type):
        ?>
        <input type="radio" name="order_type" value="<?=$order_type_slug;?>" id="order_type_<?=$order_type_slug;?>"
            <?=$_order_type == $order_type_slug ? 'checked' : ''?>>
        <label for="order_type_<?=$order_type_slug;?>" class="<?=$_order_type == $order_type_slug ? 'active' : ''?>"><i
                class="<?=$order_type[1];?>"></i>
            <?=$order_type[0];?></label>
        <?php endforeach; ?>
    </div>

    <h5>Order Source</h5>
    <div class="cs_form_radio">
        <?php 
            $order_sources = [
                "web" => [
                    "Website", "fa fa-globe"
                ],
                "app" => [
                    "Customer App", "fa fa-mobile"
                ],
                "pos" => [
                    "POS / Manual", "fa fa-window"
                ]
            ];

            foreach($order_sources as $order_source_slug => $order_source):
        ?>
        <input type="radio" name="order_source" value="<?=$order_source_slug;?>"
            id="order_source_<?=$order_source_slug;?>" <?=$_order_source == $order_source_slug ? 'checked' : ''?>>
        <label for="order_source_<?=$order_source_slug;?>"
            class="<?=$_order_source == $order_source_slug ? 'active' : ''?>"><i class="<?=$order_source[1];?>"></i>
            <?=$order_source[0];?></label>
        <?php endforeach; ?>
    </div>
</div>
<?php 
    }



    
    function save_order_meta_admin_panel($ord_id){
        $order = wc_get_order( $ord_id );
        $time = wc_clean( $_POST[ 'delivery_time' ] );
        $order_created = $order->get_date_created(); 
        $order_created_timestamp = strtotime(time());
        update_post_meta( $ord_id, 'delivery_time',  $order_created_timestamp);		

        $order_type = wc_clean( $_POST['order_type']);
        $order_source = wc_clean( $_POST['order_source']);
        
        update_post_meta( $ord_id, 'order_type',  $order_type);        
        update_post_meta( $ord_id, 'order_source',  $order_source);
    }

}
