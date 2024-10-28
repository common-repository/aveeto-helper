<?php
/* * class
 * Description of Aveeto_Order_Fulfillment_Metabox
 *
 * @author Aveeto
 * @autoload: aveeto_init
 */ 
 
if (!class_exists('Aveeto_Order_Fulfillment_Metabox') ) {

    class Aveeto_Order_Fulfillment_Metabox {

        public function __construct() {
	        
            add_action('add_meta_boxes', array($this, 'fulfillments_metabox') );
            add_action('save_post', array($this, 'fulfillments_metabox_save'));  
    	}
     
        public function fulfillments_metabox() {
	        
	        add_meta_box( 'aveeto_fulfillments', __('Aveeto Fulfillments','woocommerce'), array($this, 'fulfillments_metabox_fields'), 'shop_order', 'normal', 'default' );
	    }
	    
	    function fulfillments_metabox_fields()
	    {
	        global $post;
	        
	        $order = wc_get_order( $post->ID );
	        
	    	$aveeto_fulfillments = aveeto_fulfillments()->get( $post->ID );
				
			wp_nonce_field( 'aveeto_fulfillments_meta_box_nonce', 'aveeto_fulfillments_meta_box_nonce' );
			?>
			<script type="text/javascript">
			jQuery(document).ready(function( $ ){
				$( '#add-row' ).on('click', function() {
					var row = $( '.empty-row.screen-reader-text' ).clone(true);
					row.removeClass( 'empty-row screen-reader-text' );
					row.insertBefore( '#aveeto-fulfillments-fieldset tbody>tr:last' );
					return false;
				});
		  	
				$( '.remove-row' ).on('click', function() {
					$(this).parents('tr').remove();
					return false;
				});
			});
			</script>
		  
			<table id="aveeto-fulfillments-fieldset" width="100%">
			<thead>
				<tr>
					<th width="40%">Supplier Order ID</th>
					<th width="12%">Shipping Method</th>
					<th width="40%">Tracking Number</th>
					<th width="8%"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			
			if ( $aveeto_fulfillments ) :
			
			foreach ( $aveeto_fulfillments as $field ) {
			?>
			<tr>
				
				<td><input type="text" class="widefat" name="aveeto_fulfillments[order_id][]" value="<?php if($field['order_id'] != '') echo esc_attr( $field['order_id'] ); ?>" /></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[shipping_method][]" value="<?php if($field['shipping_method'] != '') echo esc_attr( $field['shipping_method'] ); ?>" /></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[tracking_number][]" value="<?php if($field['tracking_number'] != '') echo esc_attr( $field['tracking_number'] ); ?>" /></td>
			
				<td><a class="button remove-row" href="#">Remove</a></td>
			</tr>
			<?php
			}
			else :
			// show a blank one
			?>
			<tr>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[order_id][]"/></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[shipping_method][]"/></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[tracking_number][]"/></td>
			
				<td><a class="button remove-row" href="#">Remove</a></td>
			</tr>
			<?php endif; ?>
			
			<!-- empty hidden one for jQuery -->
			<tr class="empty-row screen-reader-text">
				
				<td><input type="text" class="widefat" name="aveeto_fulfillments[order_id][]" /></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[shipping_method][]"/></td>
				<td><input type="text" class="widefat" name="aveeto_fulfillments[tracking_number][]" /></td>
				  
				<td><a class="button remove-row" href="#">Remove</a></td>
			</tr>
			</tbody>
			</table>
			
			<p><a id="add-row" class="button" href="#">Add another</a></p>
			<?php
		}
		
		
		public function fulfillments_metabox_save($order_id) {
			if ( ! isset( $_POST['aveeto_fulfillments_meta_box_nonce'] ) ||
			! wp_verify_nonce( $_POST['aveeto_fulfillments_meta_box_nonce'], 'aveeto_fulfillments_meta_box_nonce' ) )
				return;
			
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return;
			
			if (!current_user_can('edit_post', $order_id))
				return;
			
			$order = wc_get_order( $order_id );

			$old = aveeto_fulfillments()->get( $order_id );
			$old_tracking_numbers = $this->get_fulfillments_tracking_numbers($old);

			$new = array();
			
			$postData = $_POST['aveeto_fulfillments'];
			
			$order_ids = $postData['order_id'];
			$shipping_methods = $postData['shipping_method'];
			$tracking_numbers = $postData['tracking_number'];
			
			$count = count( $order_ids );
			
			for ( $i = 0; $i < $count; $i++ ) {
				if ( $order_ids[$i] != '' ) :
					$new[$i]['order_id'] = stripslashes( strip_tags( $order_ids[$i] ) );
					$new[$i]['shipping_method'] = stripslashes( strip_tags( $shipping_methods[$i] ) );
					$new[$i]['tracking_number'] = stripslashes( strip_tags( $tracking_numbers[$i] ) );
					
				endif;
			}
			
			$new_tracking_numbers = $this->get_fulfillments_tracking_numbers($new);
			$deleted_tracking_numbers = array_diff($old_tracking_numbers, $new_tracking_numbers);
			$added_tracking_numbers = array_diff($new_tracking_numbers, $old_tracking_numbers);
				
			if ( !empty( $new ) && $new !== $old ) {

				update_post_meta( $order_id, '_aveeto_fulfillments', $new );

			}elseif ( empty($new) && $old ) {
				
				delete_post_meta( $order_id, '_aveeto_fulfillments', $old );
			}
			
			if(!empty($added_tracking_numbers)) {
				$order->add_order_note( 
					sprintf(
						__( 'The following tracking %s (%s) have been added to the order.', 'aveeto-helper' ), 
						_n( 'number', 'numbers', count($added_tracking_numbers), 'aveeto-helper' ), 
						implode(", ", $added_tracking_numbers)
					), 
					true, 
					true 
				);
            }
            
            if(!empty($deleted_tracking_numbers)) {
            	$order->add_order_note( 
            		sprintf(
            			__( 'The following tracking %s (%s) have been deleted from the order.', 'aveeto-helper' ), 
						_n( 'number', 'numbers', count($deleted_tracking_numbers), 'aveeto-helper' ), 
            			implode(", ", $deleted_tracking_numbers)
            		), 
            		true, 
            		true 
            	);
			}
		}
		
		public function get_fulfillments_tracking_numbers($fulfillments) {
			
			if(empty($fulfillments)) {
				$fulfillments = [];
			}
			
			$tracking_numbers = [];
			foreach($fulfillments as $fulfillment) {
				$tracking_numbers[] = $fulfillment['tracking_number'];
			}

			return $tracking_numbers;
		}

    }

}
