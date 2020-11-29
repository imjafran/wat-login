<?php
namespace Combosoft;
defined( 'ABSPATH' ) or die('Direct Script not Allowed');

// requiring files 
require_once __DIR__ . '/class/tgm-plugin-activation.php';
require_once __DIR__ . '/class/hooks.php';
require_once __DIR__ . '/class/ajax.php';
require_once __DIR__ . '/class/rest.php';


// combopos class 
class ComboPOS {
    
    // combopos member variables 
    public $hooks;
    public $rest;
    public $ajax;
    
    // construct method 
    function __construct(){
       // register hooks 
        $this->hooks = new ComboPOS\Hooks();
        $this->hooks->register_hooks();  
        $this->hooks = new ComboPOS\Ajax();
        $this->hooks->register_ajax();  
        $this->hooks = new ComboPOS\Rest();
        $this->hooks->register_rest();  
        return $this;
    }

    // activated 
    public static function activate(){
        flush_rewrite_rules();
    }

    // deactivated 
    public static function deactivate(){
        flush_rewrite_rules();
    }
    
}



// initializing plugin 
$_combopos = new ComboPOS();

// switching to global variable 
global $_combopos;


// Activation Plugin
register_activation_hook( _CPOS_FILE, ['\Combosoft\ComboPOS', 'activate'] );

// // Deactivation Plugin
register_deactivation_hook( _CPOS_FILE, ['\Combosoft\ComboPOS', 'deactivate'] );
