<?php

if ( ! is_null( $product ) ) {
    ?>

    <div id="rte-meta_<?php echo esc_html( $product->get_id() ) ?>">
        <table class="rte-meta-table">
            <tbody>
                <tr>
                    <th><?php echo __( 'Weight:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $product->get_weight() . get_option('woocommerce_weight_unit') ) ?> </td>
                </tr>
                <tr>
                    <th><?php echo __( 'Length:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $product->get_length() . get_option('woocommerce_dimension_unit') ) ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Width:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $product->get_width() . get_option('woocommerce_dimension_unit') ) ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Height:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $product->get_height() . get_option('woocommerce_dimension_unit') ) ?></td>
                </tr>
            </tbody>
        </table>
</div>

    <?php

} else {
    ?>

    <div id="rte-meta_">
        <table class="rte-meta-table">
            <tbody>
                <tr>
                    <th><?php echo __( 'Total Weight:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $total_weight ) ?></td>
                </tr>
                <tr>
                    <th><?php echo __( 'Delivery Time:', 'wc-rte-rodonaves-shipping' ) ?></th>
                    <td><?php echo esc_html( $delivery_time ) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php
}

