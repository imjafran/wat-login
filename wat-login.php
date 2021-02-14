<?php
/**
 *
 * @package watlogin
 *
 * Plugin Name: WAT - Web Auth Token
 * Plugin URI: http://combopos.co.uk/plugin/
 * Description: WAT - Web Auth Token; simple authenticationsystem for WordPress REST API; Designed by Jafran Hasan
 * Version: 1.0.0
 * Author: Jafran Hasan
 * Author URI: https://girhub.co,/iamjafran
 * License: GPLv2 or latter
 * Text Domain: combopos
 * User: Jafran
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die('Direct Script not Allowed');
define('WAT_File', __FILE__);

require_once __DIR__  . '/inc/class.wat.php';



// _WATLogin class 
class _WAT_Parent {
    
    // _WATLogin member variables 
    public $functions;
    
    function __construct(){
    //    register all hooks 
        $this->functions    = new \WAT\WAT();
        $this->functions->init();
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
$WATLogin   = new \_WAT_Parent();
global $WATLogin;

// Activation Plugin
register_activation_hook( __FILE__, [$WATLogin, 'activate'] );

// // Deactivation Plugin
register_deactivation_hook( __FILE__, [$WATLogin, 'deactivate'] );
