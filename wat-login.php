<?php
/**
 *
 * @package watlogin
 *
 * Plugin Name: WAT - Web Auth Token
 * Plugin URI: http://combopos.co.uk/plugin/
 * Description: WAT - Web Auth Token; simple authentication system for WordPress REST API; Designed by Jafran Hasan
 * Version: 2.0.0
 * Author: Jafran Hasan
 * Author URI: https://github.com/iamjafran
 * License: GPLv2 or latter
 * Text Domain: combopos
 * User: Jafran
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die('Direct Script not Allowed');
define('_WAT', __FILE__);

require_once __DIR__  . '/class/wat.php';


// initializing plugin 
$_wat = new \WAT\WAT();
$_wat->init();
global $_wat;
