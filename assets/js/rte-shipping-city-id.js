(function ($) {
    const usernameField = document.querySelector('#woocommerce_rte-rodonaves_username').value;
    const passwordField = document.querySelector('#woocommerce_rte-rodonaves_password').value;
    const registrationField = document.querySelector('#woocommerce_rte-rodonaves_costumer_registration').value;
    const postcodeField = document.querySelector('#woocommerce_rte-rodonaves_zip_origin');
    const cityIdField = document.querySelector('#woocommerce_rte-rodonaves_city_id');
    const getCityID = document.querySelector('#wc-get-city-id');
    const errorField = document.querySelector('#wc-get-city-id-response');

    getCityID.addEventListener('click', (e) => {
        e.preventDefault();
        if (usernameField === '' || passwordField === '' || registrationField === '' || postcodeField.value === '') {
            errorField.innerHTML = rteShippingCityData.error;
        } else {
            $.ajax({
                url: rteShippingCityData.url,
                type: 'GET',
                dataType: 'json',
                data: {
                    action: rteShippingCityData.action,
                    data: postcodeField.value,
                },
                success: function (response) {
                    if (response === 'C') {
                        errorField.innerHTML = rteShippingCityData.error;
                    }
                    cityIdField.value = response;
                },
                error: function (error) {
                    errorField.innerHTML = error;
                },
            });
        }
    });
})(jQuery);
