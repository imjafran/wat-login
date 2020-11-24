<?php
/**
 *
 * @package combopos
 *
 * Plugin Name: ComboPOS
 * Plugin URI: http://combopos.co.uk/plugin/
 * Description: ComboPOS Integration Plugin By Combosoft Ltd.
 * Version: 1.0.0
 * Author: Combosoft Ltd
 * Author URI: http://combosoft.co.uk/
 * License: GPLv2 or latter
 * Text Domain: combopos
 * User: Jafran
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die('Direct Script not Allowed');
require_once __DIR__ . '/inc/init.php';

// initializing plugin 
$_combopos = new Combosoft\ComboPOS();
global $_combopos;


// Activation Plugin
register_activation_hook( __FILE__, ['Combosoft\ComboPOS', 'activate'] );

// // Deactivation Plugin
register_deactivation_hook( __FILE__, ['Combosoft\ComboPOS', 'deactivate'] );
