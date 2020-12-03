<?php

namespace Combosoft\ComboPOS;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Rest extends Mother {
        /**
         * Hooks Register All Hook.
         */
        function register_rest() {
            add_action('jwt_auth_whitelist',                             [$this, 'jwt_auth_whitelist']);
            add_action('rest_api_init',                                  [$this, 'register_app_information']);
            add_action('rest_api_init',                                  [$this, 'register_products']);
        }
        

        // jwt whitelist 
        function jwt_auth_whitelist( $endpoints ){
            return [
                '/wp-json/combopos/v1/app',
                '/wp-json/combopos/v1/products',
            ];
        }

        function register_app_information(){
             register_rest_route( 'combopos/v1', '/app', [
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_app_information'],
             ]);
        }

        
        function register_products(){
             register_rest_route( 'combopos/v1', '/products', [
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_products'],
             ] );
        }



        function get_app_information(){
            $cpos_options = [
                'cpos_order_disable',
                'cpos_order_disable_reason',
                'cpos_delivery_time',
                'cpos_app_primary_color',
                'cpos_app_placeholder_url'
            ];

            // The country/state
            $store_raw_country = get_option( 'woocommerce_default_country' );

            // Split the country/state
            $split_country = explode( ":", $store_raw_country );

            // Country and state separated:
            $store_country = $split_country[0];
            $store_state   = $split_country[1];


            // payment gatways 
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            $enabled_gateways = [];

            if( $gateways ) {
                foreach( $gateways as $gateway ) {

                    if( $gateway->enabled == 'yes' ) {

                        $enabled_gateways[] = $gateway;

                    }
                }
            }


            $country = new \WC_Countries();


            $out = [

                // wordpress default 
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'address'     => get_option( 'woocommerce_store_address' ),
                'address_2'   => get_option( 'woocommerce_store_address_2' ),
                'city'        => get_option( 'woocommerce_store_city' ),
                'postcode'    => get_option( 'woocommerce_store_postcode' ),

                // The country/state
                'country' => $store_country,
                'state' => $store_state,

                // added by woocommerce 

                // added by combosoft
                'disable_order' => get_option('cpos_order_disable') == '1' ? true : false,
                'disable_order_reason' => esc_html(get_option('cpos_order_disable_reason')),
                'delivery_time' => intval(get_option('cpos_delivery_time')),
                'logo_url' => (get_option('cpos_app_logo_url')),
                'placeholder_url' => (get_option('cpos_app_placeholder_url')),
                'primary_color' => esc_html(get_option('cpos_app_primary_color')),
                'secondary_color' => esc_html(get_option('cpos_app_secondary_color')),
                'updated_at' => intval(get_option('cpos_updated_at')),

                // 'payment_getways' => $enabled_gateways,
                'currency' => [
                    'code' =>  get_option( 'woocommerce_currency' ),
                    'symbol' => get_woocommerce_currency_symbol()
                ],

                

                // vat tax 


                // country 
                'country' => [
                    'a'
                ]
            ];


            return rest_ensure_response( $out );
        }

        function get_products(){

            $args = [
                'taxonomy'      => 'product_cat',
                'orderby'       => 'menu_order',
                'order'         => 'ASC',
                // 'show_count'    => 1,
                'hierarchical'  => 1,
                // 'hide_empty'    => 1,
            ];

            $all_categories = get_categories( $args );
            $out = [];

            foreach ($all_categories as $cat) :

                $category = [];

                if( $cat->category_parent == 0 ) :

                    $category['id']     = $cat->term_id;
                    $category['name']   = $cat->name;
                    $category['slug']   = $cat->slug;
                    $thumbnail_id       = get_term_meta( $cat->term_id, 'thumbnail_id', true );
                    $category['image']  = wp_get_attachment_url( $thumbnail_id );
                    $category['products']  = [];

                     $product_args = [                         
                        'type'          => 'product',
                        'post_status'   => 'publish',
                        'orderby'       => 'name',
                        'order'         => 'DESC',
                        // 'pad_counts'    => false,
                        // 'hierarchical'  => 1,
                        // 'hide_empty'    => 0,
                        'tax_query'     => [
                            [
                                'taxonomy' => 'product_cat',
                                'field' => 'id',
                                'terms' => $cat->term_id
                            ]
                        ],
                     ];

                    $loop = new \WP_Query( $product_args );

                        while ( $loop->have_posts() ) : $loop->the_post();
                            $cat_product = [];

                            global $product;
                            $cat_product['id'] = $product->get_ID();
                            $cat_product['name'] = $product->get_name();
                            // $cat_product['short_description'] = $product->get_short_description();
                            // $cat_product['description'] = $product->get_description();
                            $cat_product['image'] = $product->get_image_id();
                            $cat_product['sku'] = $product->get_sku();
                            $cat_product['price'] = [
                                'main' =>  $product->get_price(),
                                'regular' =>  $product->get_regular_price(),
                                'sale' =>  $product->get_sale_price(),
                            ];

                            $variations = $product->get_children();
                            if( !empty($variations) ):
                                // $cat_product['variations'] = $variations;   
                                foreach ($variations as $variation_id) {
                                    $variation = [];
                                    // $single_variation = new \WC_Product_Variation($variation_id);
                                    // // $variation['id'] = $variation_id;
                                    // // // $variation['name'] = $single_variation->get_name();
                                    // // $variation['attributes'] = $single_variation->get_variation_attributes();
                                    // // $variation['price'] = $single_variation->price;
                                    // // // echo '<option  value="'.$value.'">'.implode(" / ", $single_variation->get_variation_attributes()).'-'.get_woocommerce_currency_symbol().$single_variation->price.'</option>';
                                    $cat_product['variations'] = $variation;
                                }
                            endif;       
                            $category['products'][] = $cat_product;
                        endwhile;

                wp_reset_query();
                
                $out[] = $category;

            endif;


        endforeach;

    wp_reset_query();

     return rest_ensure_response( $out );

    }



}
