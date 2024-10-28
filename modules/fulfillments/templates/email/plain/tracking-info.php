<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the plain text order email
 *
 * @author  Aveeto

 */

if ( $tracking_items ) :

	echo apply_filters( 'aveeto_shipment_tracking_my_orders_title', __( 'TRACKING INFORMATION', 'aveeto-helper' ) );

		echo  "\n";

		foreach ( $tracking_items as $tracking_item ) {
			echo esc_html( $tracking_item[ 'date_shipped' ] ) . "\n";
			echo esc_html( $tracking_item['shipping_method'] ) . "\n";
			echo esc_html( $tracking_item['tracking_number'] ) . "\n";
			echo esc_url( $tracking_item[ 'tracking_url' ] ) . "\n\n";
		}

	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= \n\n";

endif;

?>
