<div id="rte-shipping-calc">
        <div id="rte-chipping-calc-form">
            <span id="rte-label"><?php echo __( 'Simulate your shipping: ', 'wc-rte-rodonaves-shipping' ) ?></span><br>
            <input id="user-postcode" type="hidden" value="<?php echo esc_attr( wrrs_func_get_last_order_id() ) ?>">
            <input id="rte-postcode" type="text" maxlength="9" placeholder="00000-000">
            <button id="rte-shipping-request"><?php echo __( 'Calculate', 'wc-rte-rodonaves-shipping' ) ?></button>
        </div>            
    <div id="rte-shipping-result"></div>
</div>