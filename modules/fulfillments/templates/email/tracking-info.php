<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the HTML order email
 *
 * @author  Aveeto
 */

if ( $tracking_items ) : ?>
	<h2><?php echo apply_filters( 'aveeto_shipment_tracking_my_orders_title', __( 'Tracking Information', 'aveeto-helper' ) ); ?></h2>

	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">

		<thead>
			<tr>
				<th class="tracking-number" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;"><?php _e( 'Tracking Number', 'aveeto-helper' ); ?></th>
				<th class="date-shipped" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;"><?php _e( 'Date', 'aveeto-helper' ); ?></th>
				<th class="order-actions" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">&nbsp;</th>
			</tr>
		</thead>

		<tbody><?php
		foreach ( $tracking_items as $tracking_item ) {
				?><tr class="tracking">
					<td class="shipping-method" data-title="<?php _e( 'Shipping Method', 'aveeto-helper' ); ?>">
						<?php echo esc_html( $tracking_item['shipping_method'] ); ?>
					</td>
					<td class="tracking-number" data-title="<?php _e( 'Tracking Number', 'aveeto-helper' ); ?>" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
						<?php echo esc_html( $tracking_item['tracking_number'] ); ?>
					</td>
					<td class="date-shipped" data-title="<?php _e( 'Status', 'aveeto-helper' ); ?>" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
			            <?php echo esc_html( $tracking_item['date_shipped'] ); ?>
					</td>
					<td class="order-actions" style="text-align: center; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
							<a href="<?php echo esc_url( $tracking_item['tracking_url'] ); ?>" target="_blank"><?php _e( 'Track', 'aveeto-helper' ); ?></a>
					</td>
				</tr><?php
		}
		?></tbody>
	</table><br /><br />

<?php
endif;
