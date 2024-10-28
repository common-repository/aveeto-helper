<?php

define('AVEETO_SHIPPING_METHOD_PATH', plugin_dir_path( __FILE__ ));
define('AVEETO_SHIPPING_METHOD_FILE', __FILE__);
		
include_once(AVEETO_SHIPPING_METHOD_PATH.'/inc/aliexpress-api.php');	
include_once(AVEETO_SHIPPING_METHOD_PATH.'/inc/shipping-method.php');
include_once(AVEETO_SHIPPING_METHOD_PATH.'/inc/woocommerce-hooks.php');
