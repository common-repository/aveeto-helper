<?php
					
class Aveeto_Helper {
	
	public $plugin_id = 'aveeto_shipping';
	public $plugin_name = 'Aveeto Shipping';
	public $plugin_version = AVEETO_HELPER_VERSION;
	public $plugin_path = AVEETO_HELPER_PATH;
	public $plugin_file = AVEETO_HELPER_FILE;
	public $script_suffix = '';
	
	protected $apis = [];
	protected $settings = [];
	
	public function __construct() {
		
		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
		
		$this->apis['aliexpress'] = new Aveeto_AliExpress_Api;
		
		add_action('admin_notices', array($this, 'woocommerce_missing_notice'), 1 );
			
		add_action('init', array($this, 'init')); 
	
	}

	
	/**
	 * Check if woocommerce is activated, error if not
	 *
	 * @since    1.0.0
	 */
	public function woocommerce_missing_notice() {
		
		if ( ! class_exists( 'WooCommerce' ) ) {
			
			$class = 'notice notice-error';
			$message = sprintf( 
				__( '<strong>%1$s</strong> plugin requires %2$s to be installed and active.', 'aveeto-helper' ), 
				$this->plugin_name,
				'<a target="_blank" href="https://en-ca.wordpress.org/plugins/woocommerce/">WooCommerce</a>'
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
			
		} 
	}
	
	function api($type) {
		
		return $this->apis['aliexpress'];
	}

	function init() {
		
		if (!class_exists( 'WooCommerce' ) ) {
			return false;
		}
		
		$this->set_country();
		$this->set_settings();
		
		if($this->get_setting("enabled") !== 'yes') {
			return false;
		}
		
		add_action( 'woocommerce_after_cart_item_name', array($this, 'cart_item_display_shipping_method'), 1, 2 );
		add_filter( 'woocommerce_cart_item_name', array($this, 'checkout_cart_item_display_shipping_method'), 1, 3 );
		add_filter( 'woocommerce_order_item_name', array($this, 'order_item_display_shipping_method'), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array($this, 'cart_item_add_shipping_method'), 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', array($this, 'cart_item_update_shipping_method'), 10, 1 );
		
		add_action( 'woocommerce_checkout_create_order_line_item', array($this, 'cart_item_add_shipping_method_meta'), 10, 4 );

		add_action( 'wp_ajax_aveeto_get_shipping_rates', array($this, 'ajax_get_shipping_rates') );
		add_action( 'wp_ajax_nopriv_aveeto_get_shipping_rates', array($this, 'ajax_get_shipping_rates') );
		
		add_action( 'wp_ajax_aveeto_update_shipping_rates', array($this, 'ajax_update_shipping_rates') );
		add_action( 'wp_ajax_nopriv_aveeto_update_shipping_rates', array($this, 'ajax_update_shipping_rates') );
		
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'), 99 );
	}

	
	function set_settings() {
		
		$shipping_methods = WC()->shipping->get_shipping_methods();
		foreach($shipping_methods as $method) {
			if($method->id === "aveeto_shipping") {
				$this->settings = $method->settings;
				return;
			}
		}
	}
	
	function get_settings() {
		
		return $this->settings;
	
	}
	
	function set_country() {
		
		if(class_exists('WC_Geolocation')) {
			
			$location = WC_Geolocation::geolocate_ip();
		
			$this->country = $location['country'];
		}
	}

	function get_setting($key) {
		
		return !empty($this->settings[$key]) ? $this->settings[$key] : null;
	
	}
			
	function get_country() {

		$country = WC()->customer->get_shipping_country();

		if(empty($country)) {
			$country = $this->country;
		}
		
		if(!$this->country_allowed($country)) {
			$country = null;
		}

		return $country;
	}
	
	function get_country_name() {
		
		$country = $this->get_country();
		$countries = $this->get_countries();
		
		if(!empty($countries[$country])) {
			
			return $countries[$country];
		}
		
		return $country;
	}
	
	function get_countries() {
		
		return WC()->countries->countries;
	}
	
	function get_allowed_countries() {
		
		return $this->api('aliexpress')->get_countries();
	}
	
	
	function country_allowed($country) {
					
		$countries = $this->get_allowed_countries();
		$key = array_search($country, $countries);
		
		return isset($key);
	}
	

	function get_post_input($key) {
		
		$data = $_POST;
		$keys = explode(".", $key);

		foreach($keys as $_key) {
			
			if(isset($data[$_key])) {
				
				$data = $data[$_key];
				
			}else{
				
				$data = null;
				break;
			}
		}
	
		if(is_array($data)) {
			
			foreach($data as $_key => $val) {
				$data[$_key] = sanitize_text_field($val);
			}
			
		}else{
			
			$data = sanitize_text_field($data);
		}

		return $data;
	}
				
				
	function get_shipping_rates($supplier_type, $supplier_product_id, $quantity) {
		
		$country = $this->get_country();

	    return $this->api($supplier_type)->get_shipping_rates($supplier_product_id, $quantity, $country);
	}
	
	function get_shipping_rate($supplier_type, $shipping_method, $supplier_product_id, $quantity) {
		
		$country = $this->get_country();
		
	    return $this->api($supplier_type)->get_shipping_rate($shipping_method, $supplier_product_id, $quantity, $country);
	}
	
	function get_shipping_fee($supplier_type, $shipping_method, $supplier_product_id, $quantity) {
			
		$country = $this->get_country();
		
	    return $this->api($supplier_type)->get_shipping_fee($shipping_method, $supplier_product_id, $quantity, $country);
	}
	
	function find_shipping_rate($shipping_method, &$rates) {
		
	    return $this->api($supplier_type)->find_shipping_rate($shipping_method, $rates);
	}
	
	function ajax_get_shipping_rates() {
		
	    $supplier_type = $this->get_post_input('aveeto_supplier_type');
	    $supplier_product_id = $this->get_post_input('aveeto_supplier_product_id');
	    $aveeto_shipping_id = $this->get_post_input('aveeto_shipping.id');
		$quantity = $this->get_post_input('quantity');

		$dropdown = $this->render_shipping_dropdown($supplier_type, $supplier_product_id, $quantity, null, $aveeto_shipping_id);
		
		echo json_encode(array('html' => $dropdown));
		
	    // Don't forget to stop execution afterward.
	    wp_die();
	}
	
	function ajax_update_shipping_rates() {
		
	   	$supplier_type = $this->get_post_input('aveeto_supplier_type');
	    $supplier_product_id = $this->get_post_input('aveeto_supplier_product_id');
	    $aveeto_shipping_id = $this->get_post_input('aveeto_shipping.id');
	    $key =  $this->get_post_input('aveeto_cart_key');
		$quantity = $this->get_post_input('quantity');
		
	    $success = $this->update_item_shipping($key, $supplier_type, $aveeto_shipping_id, $supplier_product_id, $quantity);
		
		if($success) {
			WC()->cart->calculate_totals();
		}
		
		echo json_encode(array('success' => $success));
		
	    // Don't forget to stop execution afterward.
	    wp_die();
	}

	
	function render_shipping_dropdown($supplier_type, $supplier_product_id, $quantity = 1, $cart_item_key = null, $selected_method = null) {
		 
		$html = '';
	
		$rates = $this->get_shipping_rates($supplier_type, $supplier_product_id, $quantity);
		
		foreach($rates as $item) {
			
			$selected = "";
			if(!empty($selected_method) && ($selected_method == $item['id'] || $selected_method == $item['label'])) {
				$selected = "selected";
			}
			$fee = (empty($item['cost']) ? 'Free' : '$'.$item['cost']);
							
			$label = $item['label'] . ' - ' . $item['processing'] . ' - '.$fee;
							
			$html .= '<option '.$selected.' data-label="'.$item['label'].'" data-fees="'.$item['cost'].'" value="'.$item['id'].'">'.$label.'</option>';
		}
	
		ob_start();
		?>
		<div class="aveeto_shipping">  
			<?php if(!empty($html)): ?>
			<div class="aveeto_shipping_dropdown_wrap">
				<label for="<?php echo $this->get_input_id('aveeto_shipping_id', $cart_item_key);?>"><?php _e( 'Select Shipping Method', 'aveeto-helper' ); ?></label>
		        <div class="aveeto_shipping_dropdown">
			        <select class="aveeto_shipping_id" name="<?php echo $this->get_input_name('aveeto_shipping', 'id', $cart_item_key);?>"><?php echo $html;?></select>
			        <input class="aveeto_shipping_label" name="<?php echo $this->get_input_name('aveeto_shipping', 'label', $cart_item_key);?>" value="" type="hidden">
			        <input class="aveeto_shipping_cost" name="<?php echo $this->get_input_name('aveeto_shipping', 'cost', $cart_item_key);?>" value="" type="hidden">
		        </div>
			</div>
			<?php else: ?>
	        <div class="aveeto_shipping_error" style="color:red">
		        <?php echo sprintf( __( 'This product is no longer available or cannot be shipped to %1$s.', 'aveeto-helper' ), $this->get_country_name()); ?>
				<input class="aveeto_shipping_error" name="<?php echo $this->get_input_name('aveeto_shipping_error', null, $cart_item_key);?>" value="1" type="hidden">
	        </div>
	        <?php endif; ?>
	        <input class="aveeto_supplier_type" name="<?php echo $this->get_input_name('aveeto_supplier_type', null, $cart_item_key);?>" value="<?php echo $supplier_type;?>" type="hidden">
			<input class="aveeto_supplier_product_id" name="<?php echo $this->get_input_name('aveeto_supplier_product_id', null, $cart_item_key);?>" value="<?php echo $supplier_product_id;?>" type="hidden">
			<input class="aveeto_quantity" name="<?php echo $this->get_input_name('aveeto_quantity', null, $cart_item_key);?>" value="<?php echo $quantity;?>" type="hidden">
			<input class="aveeto_cart_key" name="<?php echo $this->get_input_name('aveeto_cart_key', null, $cart_item_key);?>" value="<?php echo $cart_item_key;?>" type="hidden">
         </div>
        <?php
	        
		$dropdown = ob_get_contents();

		ob_end_clean();
		
		return $dropdown;
	}
	
	function get_input_name($name, $subname = null, $cart_item_key = null) {
		
		if(!empty($cart_item_key)) {
			$name = 'cart['.$cart_item_key.']['.$name.']';
		}
		
		if(!empty($subname)) {
			$name = $name.'['.$subname.']';
		}
		
		return $name;
	}
	
	function get_input_id($name, $subname = null, $cart_item_key = null) {
		
		if(!empty($cart_item_key)) {
			$name = $name.'_'.$cart_item_key;
		}
		
		if(!empty($subname)) {
			$name = $name.'_'.$subname;
		}
		
		return $name;
	}


	/* Add shipping method to cart item.
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	function cart_item_add_shipping_method( $cart_item_data, $product_id, $variation_id ) {
		
		$aveeto_product = get_post_meta($product_id, 'aveeto_product', true);

		if(empty($aveeto_product)) {
			return $cart_item_data;
		}

		$epacket = $this->get_setting("epacket") === "yes";
		$shipping_id = $epacket ? 'ePacket' : '';
		
		$aveeto_shipping = ['id' => $shipping_id];
		$aveeto_product_id = $aveeto_product['id'];
		$aveeto_supplier_type = $aveeto_product['supplier_type'];
		$aveeto_supplier_product_id = $aveeto_product['supplier_product_id'];
		
		$cart_item_data['aveeto_shipping'] = $aveeto_shipping;
		$cart_item_data['aveeto_supplier_type'] = $aveeto_supplier_type;
		$cart_item_data['aveeto_supplier_product_id'] = $aveeto_supplier_product_id;
		$cart_item_data['aveeto_product_id'] = $aveeto_product_id;
		
	    return $cart_item_data;
	}
	 
	/* Update cart item shipping method.
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 *
	 * @return array
	 */
	function cart_item_update_shipping_method( $cart_object ) {

		// Shipping Calculation Country

		foreach ( $cart_object->cart_contents as $key => $value ) {
			
			$quantity = $value["quantity"];
         		
         	if(!empty($value['aveeto_supplier_type'])) {
	         	
	         	if(!empty($this->get_post_input("cart.$key.aveeto_shipping.id"))) {
	         	
	         		$shipping_method = $this->get_post_input("cart.$key.aveeto_shipping.id");
			 		$supplier_product_id = $this->get_post_input("cart.$key.aveeto_supplier_product_id");
			 		$supplier_type = $this->get_post_input("cart.$key.aveeto_supplier_type");
	         	
	         	}else{ 
		         	
				 	$shipping_method = !empty($value['aveeto_shipping']['id']) ? $value['aveeto_shipping']['id'] : null;
				 	$supplier_product_id = $value['aveeto_supplier_product_id'];
				 	$supplier_type = $value['aveeto_supplier_type'];
		         	
		        }	

			 	// If shipping method exists
			 	
			 	if($shipping_method) {
			 
			 		$rate = $this->get_shipping_rate($supplier_type, $shipping_method, $supplier_product_id, $quantity);
				
					if(!empty($rate)) {
						
						$cart_object->cart_contents[$key]['aveeto_shipping'] = $rate;
						
					}else{
						
						unset($cart_object->cart_contents[$key]['aveeto_shipping']);
					}
				
				// if not unset item shipping data	
				}else{
	            	
	            	unset($cart_object->cart_contents[$key]['aveeto_shipping']);
		
				}
            }
        }
	}
	
	public function update_item_shipping($key, $supplier_type, $shipping_method, $supplier_product_id, $quantity) {
		
		if(!empty(WC()->cart->cart_contents[$key])) {
			
	        $rate = $this->get_shipping_rate($supplier_type, $shipping_method, $supplier_product_id, $quantity);
		
			if(!empty($rate)) {
				
				WC()->cart->cart_contents[$key]['aveeto_shipping'] = $rate;
				
				return true;
			}
		}	

		return false;
	}


	/* Display shipping method in the cart.
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 *
	 * @return array
	 */
	 
	 	
	function cart_item_display_shipping_method( $cart_item = null, $cart_item_key = null ) {

		if ( empty( $cart_item['aveeto_supplier_type'] ) ) {
	        return false;
	    }

	    if( $cart_item_key && (is_cart() || is_checkout()) ) {
		    
			$quantity = $cart_item['quantity'];
			$supplier_product_id = $cart_item['aveeto_supplier_product_id'];
			$supplier_type = $cart_item['aveeto_supplier_type'];
			$shipping_method = !empty($cart_item['aveeto_shipping']['id']) ? $cart_item['aveeto_shipping']['id'] : null;
		    
		    echo $this->render_shipping_dropdown($supplier_type, $supplier_product_id, $quantity, $cart_item_key, $shipping_method);
	    }
	    
	}

	/* Display shipping method in the cart at the checkout.
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 *
	 * @return array
	 */
	 
	 	
	function checkout_cart_item_display_shipping_method( $title = null, $cart_item = null, $cart_item_key = null ) {

		if ( !is_checkout() || empty( $cart_item['aveeto_supplier_type'] ) ) {
	        return $title;
	    }

	    if( $cart_item_key && (is_cart() || is_checkout()) ) {
		    
			$quantity = $cart_item['quantity'];
			$supplier_product_id = $cart_item['aveeto_supplier_product_id'];
			$supplier_type = $cart_item['aveeto_supplier_type'];
			$shipping_method = !empty($cart_item['aveeto_shipping']['id']) ? $cart_item['aveeto_shipping']['id'] : null;
		    
		    $title .= $this->render_shipping_dropdown($supplier_type, $supplier_product_id, $quantity, $cart_item_key, $shipping_method);
	    }
	    
	    return $title;
	}	
	
	/* Display shipping method in the order review.
	 *
	 * @param array $item_data
	 * @param array $order_item
	 *
	 * @return array
	 */
	 	
	function order_item_display_shipping_method( $title = null, $order_item = null) {
		
		if ( empty( $order_item['aveeto_supplier_type'] ) ) {
	        return $title;
	    }
	
		$shipping = $order_item['aveeto_shipping'];
		
		$shipping_label = !empty($shipping['id']) ? $shipping['label'] : null;
		$shipping_fees = $shipping['cost'];
		$processing_time = $shipping['processing'];
		$handling_time = $shipping['handling'];
		
	    if(!empty($shipping_label)) {
	    
	    	$title .= '<p class="aveeto_shipping_order_display">';
	    	$title .= __( 'Shipping via: ', 'aveeto' );
	    	$title .= $shipping_label .' - '.$processing_time.' Days - $'.$shipping_fees;
	    	$title .= '</p>';
	    	
		}
	    
	    return $title;
	}
	

	 
	/**
	 * Add shipping method to order.
	 *
	 * @param WC_Order_Item_Product $item
	 * @param string                $cart_item_key
	 * @param array                 $values
	 * @param WC_Order              $order
	 */
	function cart_item_add_shipping_method_meta( $item, $cart_item_key, $values, $order ) {
		
		if ( !empty( $values['aveeto_product_id'] ) ) {
			
			$data = [
				'id' => $values['aveeto_product_id'],
				'supplier_product_id' => $values['aveeto_supplier_product_id'],
				'supplier_type' => $values['aveeto_supplier_type']	
			];
			
			$item->add_meta_data('aveeto_product', $data, true );
 		}
 	
	    if ( !empty( $values['aveeto_shipping'] ) ) {
		    
	    	$item->add_meta_data('aveeto_shipping', $values['aveeto_shipping'], true );
	    }
		
	}
	
	
	function enqueue_scripts() {
		
		if ( function_exists( 'is_woocommerce' )) {
			
			wp_enqueue_style( 'aveeto-helper-product', plugins_url( 'assets/css/style.css', $this->plugin_file ), array(), $this->plugin_version );


			$vars = array( 
	            'ajaxurl' => admin_url( 'admin-ajax.php' )
	        );
		        
			if (is_checkout()) {
				
				wp_enqueue_script( 'aveeto-helper-checkout', plugins_url( 'assets/js/checkout'.$this->script_suffix.'.js', $this->plugin_file ), array('jquery'), $this->plugin_version );
				wp_localize_script( 'aveeto-helper-checkout', 'aveeto_vars', $vars);
			}
    
		}
	}
}	