<?php
/*
 * Aveeto Helper
 *	
 * Plugin Name: Aveeto Helper
 * Plugin URI: https://wordpress.org/plugins/aveeto-helper/
 * Description: Aveeto Helper is a plugin that can be installed on your WooCommerce site and will help Aveeto.com APP by adding new shipping methods based on AliExpress shipping rates. Shipping methods & rates dropdown menu will appear within the cart and on checkout below each product and your customer will be able to select the shipping method of his choice before completing the order. 
 * Version: 1.0.5
 * Author: Aveeto
 * Author URI: https://aveeto.com
 * Requires at least: 3.8
 * Tested up to: 4.9.6
 *
 * Text Domain: aveeto-helper
 * Domain Path: /languages/
 *
 * @package	Aveeto_Helper
 * @author  Aveeto <sales@aveeto.com>
 * @since 	1.0.1
 *
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('AVEETO_HELPER_VERSION', '1.0.5');
define('AVEETO_HELPER_PATH', plugin_dir_path( __FILE__ ));
define('AVEETO_HELPER_FILE', __FILE__);

include_once(AVEETO_HELPER_PATH.'/inc/core.php');
include_once(AVEETO_HELPER_PATH.'/modules/shipping-method/init.php');
include_once(AVEETO_HELPER_PATH.'/modules/fulfillments/init.php');

$Aveeto_Helper = new Aveeto_Helper;

function aveeto_helper() {
	
	global $Aveeto_Helper;
	
	return $Aveeto_Helper;
}