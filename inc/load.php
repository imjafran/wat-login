<?php
namespace Combosoft;
defined( 'ABSPATH' ) or die('Direct Script not Allowed');

// requiring files 
require_once __DIR__ . '/class/tgm-plugin-activation.php';
require_once __DIR__ . '/class/functions.php';
require_once __DIR__ . '/class/ajax.php';
require_once __DIR__ . '/class/rest.php';


// DeliveryZones class 
class DeliveryZones {
    
    // DeliveryZones member variables 
    public $functions;
    public $rest;
    public $ajax;
    

    function __construct(){
    //    register all hooks 
        $this->functions    = new \Combosoft\ComboPOS\DeliveryZones\Functions();
        $this->ajax         = new \Combosoft\ComboPOS\DeliveryZones\Ajax();
        $this->rest         = new \Combosoft\ComboPOS\DeliveryZones\Rest(); 
    }

    // activated 
    static function activate(){
        // do staff 
        flush_rewrite_rules();
    }

    // deactivated 
    static function deactivate(){
        // do staff 
        flush_rewrite_rules();
    }
    
}



// initializing plugin 
$_combopos_delivery_zones   = new \Combosoft\DeliveryZones();

// Activation Plugin
register_activation_hook( _CPOS_DeliveryZone_FILE, ['\Combosoft\DeliveryZones', 'activate'] );

// // Deactivation Plugin
register_deactivation_hook( _CPOS_DeliveryZone_FILE, ['\Combosoft\DeliveryZones', 'deactivate'] );
