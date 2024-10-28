<?php
	
class Aveeto_AliExpress_Api	{
			
	public function get_countries() {
		
		$countries = [];

		$data = $this->remote_get('https://freight.aliexpress.com/ajaxFreightGetAddressListNew.htm?callback=callback');

		if(!empty($data['countries'])) {

			foreach($data['countries'] as $item) {
				
				$countries[] = $item['c'];
			}
		}
		
		return $countries;
	}
	
	
	public function get_shipping_rates($product_id, $count, $country) {
		
		$rates = [];
		
		$data = $this->remote_get('https://freight.aliexpress.com/ajaxFreightCalculateService.htm?callback=callback&f=d&productid='.$product_id.'&count='.$count.'&currencyCode=USD&sendGoodsCountry=&country='.$country.'&province=&city=&abVersion=1');

		if(!empty($data['freight'])) {
			
			$rates = [];
			foreach($data['freight'] as $item) {
				
				$rates[] = array(
					'id' => $item['company'],
					'label' => $this->rename_shipping_label($item['companyDisplayName']),
					'cost' => floatval($item['price']),
					'processing' => $item['time'].' Days',
					'handling' => $item['processingTime'].' Days',
					'country' => $country
				);
			}
		}
		
		return $rates;
	}
	
	public function get_shipping_rate($shipping_method, $product_id, $count, $country) {
		
		$rates = $this->get_shipping_rates($product_id, $count, $country);
		
		return $this->find_shipping_rate($shipping_method, $rates);
	}
	
	public function get_shipping_fee($shipping_method, $product_id, $count, $country) {
		
		$rate = $this->get_shipping_rate($shipping_method, $product_id, $count, $country);
		
		if(!empty($rate)) {
			return $rate['cost'];
		}	
		
		return 0;
	}
	
	public function find_shipping_rate($shipping_method, &$rates) {
		
		foreach($rates as $rate) {
			
			if($rate['id'] == $shipping_method || $rate['label'] == $shipping_method) {
				return $rate;
			}
		}
		
		if(!empty($rates)) {
			return $rates[0];
		}
		
		return null;
	}

	public function rename_shipping_label($label) {
		
		return trim(str_replace(
			array(
				"aliexpress",
				"AliExpress"
			),
			array(
				""
			),
			$label
		));
	}

	protected function remote_get($url) {
			
		$response = null;
		
		$request = wp_remote_get($url, array(
			'headers' => array(
				'Content-Type'  => 'application/json; charset=UTF-8'
			)
		));
		
		if( is_wp_error( $request ) ) {
			return $response; // Bail early
		}
			
		$data = wp_remote_retrieve_body( $request );

		preg_match('/callback\((.+)\)/', $data, $match);
		
		if(!empty($match[1])) {
			
			$response = json_decode( $match[1], true );
		}
		
		return $response;
	}
}