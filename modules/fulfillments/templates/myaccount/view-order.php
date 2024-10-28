<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View Order: Tracking information
 *
 * Shows tracking numbers view order page
 *
 * @author  Aveeto
 */

if ( $tracking_items ) : ?>

	<h2><?php echo apply_filters( 'aveeto_shipment_tracking_my_orders_title', __( 'Tracking Information', 'aveeto-helper' ) ); ?></h2>

	<table class="shop_table shop_table_responsive my_account_tracking">
		<thead>
			<tr>
				<th class="tracking-number"><span class="nobr"><?php _e( 'Tracking Number', 'aveeto-helper' ); ?></span></th>
				<th class="date-shipped"><span class="nobr"><?php _e( 'Date', 'aveeto-helper' ); ?></span></th>
				<th class="order-actions">&nbsp;</th>
			</tr>
		</thead>
		<tbody><?php
		foreach ( $tracking_items as $tracking_item ) {
				?><tr class="tracking">
					<td class="shipping-method" data-title="<?php _e( 'Shipping Method', 'aveeto-helper' ); ?>">
						<?php echo esc_html( $tracking_item['shipping_method'] ); ?>
					</td>
					<td class="tracking-number" data-title="<?php _e( 'Tracking Number', 'aveeto-helper' ); ?>">
						<?php echo esc_html( $tracking_item['tracking_number'] ); ?>
					</td>
					<td class="date-shipped" data-title="<?php _e( 'Date', 'aveeto-helper' ); ?>" style="text-align:left; white-space:nowrap;">
						<?php echo esc_html( $tracking_item['date_shipped'] ); ?>
					</td>
					<td class="order-actions" style="text-align: center;">
					   <a href="<?php echo esc_url( $tracking_item['tracking_url'] ); ?>" target="_blank" class="button"><?php _e( 'Track', 'aveeto-helper' ); ?></a>
					</td>
				</tr><?php
		}
		?></tbody>
	</table>

<?php
endif;
