<?php
/**
 *
 * @package combopos
 *
 * Plugin Name: WAT login
 * Plugin URI: http://combopos.co.uk/plugin/
 * Description: Web Auth Token system designed by Combosoft Ltd
 * Version: 1.0.0
 * Author: Combosoft Ltd
 * Author URI: http://combosoft.co.uk/
 * License: GPLv2 or latter
 * Text Domain: combopos
 * User: Jafran
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die('Direct Script not Allowed');
define('WAT_Login', __FILE__);


require_once __DIR__  . '/inc/functions.php';




// _WATLogin class 
class _WATLogin {
    
    // _WATLogin member variables 
    public $functions;
    
    function __construct(){
    //    register all hooks 
        $this->functions    = new \Combosoft\WATLogin\Functions();
        $this->functions->register_hooks();
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
$WATLogin   = new _WATLogin();
global $WATLogin;

// Activation Plugin
register_activation_hook( __FILE__, [$WATLogin, 'activate'] );

// // Deactivation Plugin
register_deactivation_hook( __FILE__, [$WATLogin, 'deactivate'] );
