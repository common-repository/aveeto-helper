<?php
/* * class
 * Description of Aveeto_Order_Fulfillment_Frontend
 *
 * @author Aveeto
 * 
 * @autoload: aveeto_init
 */

if (!class_exists('Aveeto_Order_Fulfillment_Frontend')) {

    class Aveeto_Order_Fulfillment_Frontend {

        public function __construct() {
	        
			// View Order Page.
			add_action( 'woocommerce_view_order', array( $this, 'display_tracking_info' ) );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_display' ), 0, 4 );
			
			add_shortcode( 'aveeto-tracking', array($this, 'init_shortcode_aveeto_tracking') );
        }


        /**
         * Display Shipment info in the frontend (order view/tracking page).
         */
        public function display_tracking_info( $order_id ) {
            wc_get_template( 'myaccount/view-order.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ) ), 'aveeto-shipment-tracking/', aveeto_fulfillments()->plugin_path . '/templates/' );
        }        
        
        /**
         * Display shipment info in customer emails.
         *
         * @version 1.6.8
         *
         * @param WC_Order $order         Order object.
         * @param bool     $sent_to_admin Whether the email is being sent to admin or not.
         * @param bool     $plain_text    Whether email is in plain text or not.
         * @param WC_Email $email         Email object.
         */
        public function email_display( $order, $sent_to_admin, $plain_text = null, $email = null ) {
            /**
             * Don't include tracking information in refunded email.
             *
             * When email instance is `WC_Email_Customer_Refunded_Order`, it may
             * full or partial refund.
            
             */

            if ( is_a( $email, 'WC_Email_Customer_Refunded_Order' ) ) {
                return;
            }

            $order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
            if ( true === $plain_text ) {
                wc_get_template( 'email/plain/tracking-info.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ) ), 'aveeto-shipment-tracking/', aveeto_fulfillments()->plugin_path . '/templates/' );
            } else {
                wc_get_template( 'email/tracking-info.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ) ), 'aveeto-shipment-tracking/', aveeto_fulfillments()->plugin_path . '/templates/' );
            }
        }
        
    
        public function init_shortcode_aveeto_tracking( $atts ){
             wc_get_template( 'shortcode/aveeto-tracking.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ) ), 'aveeto-shipment-tracking/', aveeto_fulfillments()->plugin_path . '/templates/' );
        }


        /*
         * Gets all tracking items from the post meta array for an order
         *
         * @param int  $order_id  Order ID
         * @param bool $formatted Wether or not to reslove the final tracking link
         *                        and provider in the returned tracking item.
         *                        Default to false.
         *
         * @return array List of tracking items
         */
        private function get_tracking_items( $order_id, $formatted = false ) {
            global $wpdb;

            $aveeto_fulfillments = aveeto_fulfillments()->get( $order_id );

            if ( $aveeto_fulfillments && is_array( $aveeto_fulfillments ) ) {
                
                $order = new WC_Order($order_id);
                $order_date = $order->get_date_completed();

                $tracking_items = array();
                
                foreach ($aveeto_fulfillments as $fulfillment){
                    $tracking_items[] = array(
	                    'order_id' => $fulfillment['order_id'],
						'shipping_method' => $fulfillment['shipping_method'], 
						'tracking_number' => $fulfillment['tracking_number'], 
						'tracking_url' => aveeto_fulfillments()->get_tracking_url($fulfillment), 
						'date_shipped' => $order_date
                    );    
                }
                
                return $tracking_items;
                
            } else {
	            
                return array();
            }
        }
        
    }	
}