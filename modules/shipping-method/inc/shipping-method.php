<?php

/**
* Check if WooCommerce is active
*/


function aveeto_shipping_init() {
	if ( ! class_exists( 'WC_Aveeto_Shipping' ) ) {

		class WC_Aveeto_Shipping extends WC_Shipping_Method {
			/**
			 * Constructor for your shipping class
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				$this->id                 = 'aveeto_shipping'; // Id for your shipping method. Should be uunique.
				$this->method_title       = __( 'Aveeto Shipping', 'aveeto-helper' );  // Title shown in admin
				$this->method_description = __( 'Aveeto Shipping enables shipping methods based on AliExpress shipping rates. Shipping methods & rates dropdown menu will appear within the cart and on checkout below each product and your customer will be able to select the shipping method of his choice before completing the order. <a target="_blank" href="https://help.aveeto.com/settings/aveeto-helper-woocommerce-plugin">More Info</a>', 'aveeto-helper' ); // Description shown in admin
				
				$this->countries = $this->get_countries();
				$this->init();
			
				$this->enabled = isset( $this->settings['enabled'] ) ?  $this->settings['enabled'] : 'yes';
                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Shipping Fees', 'aveeto-helper' );
				$this->epacket = isset( $this->settings['epacket'] ) ? $this->settings['epacket'] : 'yes';
		
			}
			
			function get_countries() {
				
				return aveeto_helper()->get_countries();
			}
			
			
			/**
			 * Init your settings
			 *
			 * @access public
			 * @return void
			 */
			function init() {
				// Load the settings API
				$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
				$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

				// Save settings in admin if you have any defined
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}


			/**
             * Define settings field for this shipping
             * @return void 
             */
            function init_form_fields() { 

                $this->form_fields = array(
                 
                    'enabled' => array(
						'title' => __( 'Enable this shipping method', 'aveeto-helper' ),
						'type' => 'checkbox',
						'default' => 'yes'
                    ),
                    'title' => array(
                        
						'title' => __( 'Title', 'aveeto-helper' ),
						'type' => 'text',
						'description' => __( 'Title to be display on site', 'aveeto-helper' ),
						'default' => __( 'Aveeto Shipping', 'aveeto-helper' )
                    ),
                    'tracking_service' => array(
	                    'title' => __( 'Tracking service', 'aveeto-helper' ),
	                    'type' => 'select',
						'description' => __( 'Select a tracking service', 'aveeto-helper' ),
	                    'options' => array(  
	                    	'' => __( 'Default', 'aveeto-helper' ),
                            '17track'=> __( '17track', 'aveeto-helper' ),
                            'cainiao'=> __( 'Cainiao', 'aveeto-helper' ),
                            'aftership'=> __( 'Aftership', 'aveeto-helper' ),
	                    ),
	                    'id'   => 'wc_aveeto_tracking_service'
	                ),
                    'epacket' => array(
                        
						'title' => __( 'ePacket', 'aveeto-helper' ),
						'type' => 'checkbox',
						'description' => __( 'Auto select ePacket by default when available', 'aveeto-helper' ),
						'default' => 'yes'
                    )
                    
                );    
            }        


			/**
			 * calculate_shipping function.
			 *
			 * @access public
			 * @param mixed $package
			 * @return void
			 */
			public function calculate_shipping( $package = array() ) {
				
				if($this->enabled === 'no') {
					return false;	
				}
				
				$fees = 0;	
				$total = 0;
                foreach ( $package['contents'] as $item_id => $values ) 
                {  
                    if(empty($values['aveeto_shipping']['id'])) {
	                    continue;
                    }
                    
                    $shipping_method = $values['aveeto_shipping']['id'];
                    $shipping_label = $values['aveeto_shipping']['label'];
                    
                    $methods[$shipping_method] = $shipping_label;
                    
                    $fees += floatval($values['aveeto_shipping']['cost']);
                    
                    $total++;
                }
                
                if(!empty($total)) {
                    $this->add_rate([
	                    'id' => $this->id,
	                    'label' => implode(', ', $methods),
	                    'cost' => $fees
                    ]);
                }

			}

		}
	}
}

add_action( 'woocommerce_shipping_init', 'aveeto_shipping_init' );

function add_aveeto_shipping( $methods ) {
	$methods[] = 'WC_Aveeto_Shipping';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_aveeto_shipping' );
