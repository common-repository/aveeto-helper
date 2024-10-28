jQuery(document).ready(function($) {
    
    var had_errors = false;
    var ajaxing = false;
        
    function aveeto_get_shipping_rates(elem) {
		
		var wrap = $(elem).closest('.aveeto_shipping');
		
	    if(wrap.length === 0 || ajaxing) {
		    return false;
	    }
	    
	    var quantity = $(wrap).find('.aveeto_quantity').val();
		
	    var selector = $(wrap).find('.aveeto_shipping_dropdown_wrap');
		var dropdown = $(wrap).find('.aveeto_shipping_id');
	    var method = dropdown.attr('value');
		var supplier_type = $(wrap).find('.aveeto_supplier_type').val();
	    var supplier_product_id = $(wrap).find('.aveeto_supplier_product_id').val();
	    var cart_key = $(wrap).find('.aveeto_cart_key').val();
		
		aveeto_checkout_loading(true);
		ajaxing = true;
		 
		$.ajax({
		    url: aveeto_vars.ajaxurl, 
		    type: 'post',
		    dataType: 'json',
		    data: {
		        'action': 'aveeto_update_shipping_rates',
		        'aveeto_supplier_type': supplier_type,
		        'aveeto_supplier_product_id': supplier_product_id,
		        'aveeto_shipping': {
		        	'id': method
		        },
		        'aveeto_cart_key':  cart_key,
		        'quantity': quantity
		    }
		    
		}).always(function() {
			
			aveeto_checkout_loading(false);
			ajaxing = false;
				
		}).then(function(data){
			
	        if(data.success) {
		        
	        	$(document.body).trigger('update_checkout');
	        }	
		});
		
    }
    
    function aveeto_checkout_loading(flag) {
	    
	    var func = flag ? 'block' : 'unblock';
	    
	    $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' )[func]({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
    }
    
    $(document).on('change', '.aveeto_shipping_id', function() {
	    
	    aveeto_get_shipping_rates(this);
	    
    });
    

    $(document.body).on('updated_checkout', function(e) {
		
		console.log(had_errors)
		
		if(had_errors && !ajaxing) {
	    	$('.aveeto_shipping_id').trigger('change');
	    }
	
		had_errors = $('.aveeto_shipping_error').length !== 0;
		
    });
    
});