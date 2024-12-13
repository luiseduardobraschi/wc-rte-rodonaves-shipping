<div id="show-shipping-additional-infos" class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
	<h2 class="woocommerce-column__title">
		<?php echo __( 'Additional information\'s', 'wc-rte-rodonaves-shipping' ) ?>
	</h2>
	<address>
		<?php echo __( 'Estimated shipping time after payment confirmation:', 'wc-rte-rodonaves-shipping' ) ?><br>
		<strong>
			<?php echo esc_html( $delivery_time ) . __( ' working day(s).', 'wc-rte-rodonaves-shipping' ) ?>
		</strong>
	</address>
</div>