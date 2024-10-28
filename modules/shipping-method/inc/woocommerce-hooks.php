<?php

class Aveeto_Woocommerce_Hooks{
	
	
	public static function  init() {
		
		add_filter('woocommerce_webhook_should_deliver', array(__CLASS__, 'webhook_should_deliver'), 10, 3);	

	}

	public static function webhook_should_deliver($should_deliver, $class, $arg) {
		
		if($class->get_status() !== 'active') {
			
			$class->set_status( 'active' );
			$should_deliver = true;
		}

		if($should_deliver && in_array($class->get_resource(), array('order', 'product'))) {
				
			if($class->get_resource() === 'order') {
				
				if($class->get_event() === 'created' || $class->get_event() === 'updated') {
					
					$should_deliver = false;
					
					$order = $class->build_payload( $arg );
					
					foreach($order['line_items'] as $item) {
						
						$product = self::get_main_product($item['product_id']);
						
						if(!empty($product)) {
							$aveeto_product = $product->get_meta( 'aveeto_product', true);
							
							if(!empty($aveeto_product)) {
								$should_deliver = true;
								break;
							}
						}	
					}
				}	

			}else if($class->get_resource() === 'product') {
				
				if($class->get_event() === 'deleted') {
					
					$should_deliver = false;
						
					$product = $class->build_payload( $arg );
				
					$product = self::get_main_product($product['id']);
					
					if(!empty($product)) {
			
						$aveeto_product = $product->get_meta( 'aveeto_product', true);
			
						if(!empty($aveeto_product)) {
							$should_deliver = true;
						}
					}	
				}	
		
			}
		}		

		return $should_deliver;
	}
	
	public static function get_main_product($id) {
		
		$cache_key = 'aveeto_wc_product_'.$id;
		
		$product = wp_cache_get($cache_key);
		
		if ( false === $product ) {
			
			$product = wc_get_product($id);
			
			if(!empty($product)) {
				
				$parent_id = $product->get_parent_id();
				
		        if(!empty($parent_id)) {
			        
		            $product = wc_get_product($parent_id);
		        
		        }
	        }
	            
            if(!empty($product)) {
            	wp_cache_set($cache_key, $product);
            }
		} 
		
		return $product;
	}
}	


add_action('init', array('Aveeto_Woocommerce_Hooks', 'init'));