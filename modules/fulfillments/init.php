<?php

if (!class_exists('Aveeto_Fulfillments')) {
     
    class Aveeto_Fulfillments {
         
		
		/**
		* @var string plugin version
		*/
		public $version ;
		
		/**
		* @var string path to plugin root url
		*/
		public $plugin_url;
		
		/**
		* @var string path to plugin root dir
		*/
		public $plugin_path;
         
           
        /**
		* @var plugin classes
		*/
		public $classes;
		    
        public function __construct() {
             
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			$plugin_data = get_plugin_data(__FILE__);
			
			$this->version = $plugin_data['Version'];
			$this->plugin_url = plugin_dir_url(__FILE__);
			$this->plugin_path = plugin_dir_path(__FILE__);
			
			include_once($this->plugin_path.'/inc/Aveeto_Autoload.php');
			Aveeto_Autoload::init_classes($this->plugin_path . 'inc/classes', 'aveeto_init');
	
        }

        public function get_tracking_url($fulfillment){
            
            $tracking_number = $fulfillment['tracking_number'];
            $tracking_url_prefix = !empty($fulfillment['tracking_url_prefix']) ? $fulfillment['tracking_url_prefix'] : 'https://t.17track.net/en#nums=';
     
            $tracking_service = aveeto_helper()->get_setting('tracking_service');
            
            if ($tracking_service === 'aftership') {
                $tracking_url_prefix = "https://track.aftership.com/";
            }else if($tracking_service === '17track') {
                $tracking_url_prefix = "https://t.17track.net/en#nums=";
            }else if ($tracking_service === 'cainiao') {
                $tracking_url_prefix = "https://global.cainiao.com/detail.htm?mailNoList=";
            }
            
            return $tracking_url_prefix.$tracking_number;
            
        }
        
        public function get($order_id) {
	        
	        $cache_key = 'aveeto_fulfillments_'.$order_id;
	        
	        $aveeto_fulfillments = wp_cache_get( $cache_key );
	        
			if ( false === $aveeto_fulfillments ) {
				$aveeto_fulfillments = get_post_meta( $order_id, '_aveeto_fulfillments', true);
				wp_cache_set( $cache_key, $aveeto_fulfillments );
			} 
			
			return $aveeto_fulfillments;
        }
    
    }

}
 
 /**
 * Returns an instance of Aveeto_Fulfillments.
 *
 *
 * @return Aveeto_Fulfillments
 */
function aveeto_fulfillments() {
    static $instance;

    if ( ! isset( $instance ) ) {
        $instance = new Aveeto_Fulfillments();
    }

    return $instance;
}


/**
 * Register this class globally.
 *
 * Backward compatibility.
 */
$GLOBALS['Aveeto_Fulfillments'] = aveeto_fulfillments();

/**
 * Aveeto_Fulfillments global init action
 */
do_action('aveeto_init');
