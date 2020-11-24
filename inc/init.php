<?php
namespace Combosoft;


// requiring files 
require_once __DIR__ . '/class/hooks.php';
// require_once __DIR__ . '/class/admin-menu.php';


defined( 'ABSPATH' ) or die('Direct Script not Allowed');

class ComboPOS {
    public $hooks;
    function __construct(){
       // register hooks 
        $this->hooks = new ComboPOS\Hooks();
        $this->hooks->register_hooks();  
    }

    public static function activate(){
        flush_rewrite_rules();
    }

    public static function deactivate(){
        flush_rewrite_rules();
    }
}
