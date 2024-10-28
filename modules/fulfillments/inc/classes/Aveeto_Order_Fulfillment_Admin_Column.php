<?php
/* * class
 * Description of Aveeto_Order_Fulfillment_Admin_Column
 *
 * @author Aveeto
 * 
 * @autoload: aveeto_init
 */

if (!class_exists('Aveeto_Order_Fulfillment_Admin_Column')) {

    class Aveeto_Order_Fulfillment_Admin_Column {

        public function __construct() {
	    
            if (is_admin()) {
                
                add_action('manage_shop_order_posts_custom_column',  array($this, 'manage_columns_data'));
                add_filter('manage_edit-shop_order_columns', array($this, 'manage_columns_headers'));
            }
        }

        public function manage_columns_data($name) {
            global $post;

            switch ($name) {
	            
	            case 'supplier_orders':
                     $aveeto_fulfillments = aveeto_fulfillments()->get( $post->ID );
                     if ($aveeto_fulfillments) {
                         foreach ($aveeto_fulfillments as $k => $fulfillment) {
	                         $order_id = $fulfillment['order_id'];
	                         if(empty($order_id)) {
		                         continue;
	                         }
							 echo $this->get_formatted_supplier_order_link($order_id);
							 if ($k < count($aveeto_fulfillments)-1) echo "<br>";  
                         }
                     }
                     else _e('Not available yet', 'aveeto-helper');
                    break;
                    
                case 'tracking_numbers':
                     $aveeto_fulfillments = aveeto_fulfillments()->get( $post->ID );
                     if ($aveeto_fulfillments) {
                         foreach ($aveeto_fulfillments as $k => $fulfillment) {
	                         $tracking_number = $fulfillment['tracking_number'];
	                         if(empty($tracking_number)) {
		                         continue;
	                         }
							 echo $this->get_formatted_tracking_link($fulfillment);
							 if ($k < count($aveeto_fulfillments)-1) echo "<br>";  
                         }
                     }
                     else _e('Not available yet', 'aveeto-helper');
                    break;
            }
        }
        
        private function get_formatted_supplier_order_link($order_id) {
            
            return '<a target="_blank" href="https://trade.aliexpress.com/order_detail.htm?orderId=' . $order_id . '">' . $order_id . '</a>';
        }
        
        private function get_formatted_tracking_link(&$fulfillment){
            $tracking_number = $fulfillment['tracking_number'];
            $tracking_url = aveeto_fulfillments()->get_tracking_url($fulfillment);    
            return '<a target="_blank" href="' . $tracking_url . '">' . $tracking_number . '</a>';
        }
        
        public function manage_columns_headers($columns) {
            $new_columns = array();

            foreach ( $columns as $column_name => $column_info ) {

                if ( 'order_total' === $column_name ) {
                    $new_columns['supplier_orders'] = __( 'Supplier Orders', 'aveeto-helper' );
                    $new_columns['tracking_numbers'] = __( 'Tracking Numbers', 'aveeto-helper' );
                }
                
                $new_columns[ $column_name ] = $column_info;
            }

            return $new_columns;
        }

    }	
}