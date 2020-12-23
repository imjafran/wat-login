<?php

namespace Combosoft\ComboPOS\DeliveryZones;

defined( 'ABSPATH' ) or die('Direct Script not Allowed');

 class Rest {
        /**
         * Hooks Register All Hook.
         */
        function __construct() {
            // add_action('jwt_auth_whitelist',                             [$this, 'jwt_auth_whitelist']);
        }
        

        // jwt whitelist 
        function jwt_auth_whitelist( $endpoints ){
          
        }

}
