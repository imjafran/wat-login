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
define('_WAT', __FILE__);

require_once __DIR__  . '/inc/class.wat.php';


// initializing plugin 
$_wat = new \WAT\WAT();
$_wat->init();
global $_wat;
