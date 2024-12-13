(function ($) {
    const resultDiv = $('#rte-shipping-result');
    const labelLine = $('#rte-label');
    const postcodeInput = $('#rte-postcode');
    const sendButton = $('#rte-shipping-request');
    const quantityInput = $('[id^="quantity_"]');
    const adversity = $(`<span id="rte-adversity">${rteShippingData.text.adversity}</span>`);
    const error = $(`<span id="rte-error">${rteShippingData.text.error}</span>`);
    const cookiePostcodeName = 'wc_rte_shipping_postcode_cache';

    let productData = '';
    let currentData = null;
    let postcode = '';
    let isRequesting = false;

    $(document).ready(function () {
        sendButton.on('click', function () {
            handleClick();
        });

        $('.single_variation_wrap').on('show_variation', function (e, data) {
            getProductData(data, data.display_price);
            if (!$('#rte-adversity').length && $('#rte-price').length) {
                resultDiv.append(adversity);
            }
        });

        // Makes "Enter" calculate shipping costs
        postcodeInput.on('keydown', function (e) {
            if (e.keyCode === 13) {
                handleClick();
                e.preventDefault();
                return false;
            }
        });

        if (verifyCache() === true) {
            handleClick({ firstRun: true });
        }
    });

    function handleClick(data = { firstRun: false }) {
        if (!isRequesting) {
            if (!data.firstRun) {
                postcode = normalizePostCode(postcodeInput.val());
            }

            adversity.remove();

            if (postcode === '' || postcode === undefined) {
                labelLine.append(error);
                return;
            }

            error.remove();

            if (!rteShippingProductData.variable || productData === '') {
                getProductData(rteShippingProductData.data, rteShippingProductData.data.display_price);
            }

            if (!verifyData(productData, currentData)) {
                showLoader();
                currentData = productData;

                isRequesting = true;

                $.ajax({
                    url: rteShippingData.url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: rteShippingData.action,
                        data: productData,
                    },
                    success: function (response) {
                        if (typeof response === 'string') {
                            showError(response);
                            return;
                        }
                        showResult(response.Value, response.DeliveryTime);
                        setCookie(cookiePostcodeName, postcode, 7);
                    },
                    error: function (error) {
                        showError(error);
                    },
                    complete: function () {
                        isRequesting = false;
                    },
                });

                postcodeInput.val('');
            }
        }
    }

    function getProductData(data, price) {
        productData = {
            amountPackages: getQuantity(),
            weight: data.weight * getQuantity(),
            length: data.dimensions.length,
            height: data.dimensions.height,
            width: data.dimensions.width,
            postcode: postcode,
            price: price,
            product_page: true,
        };
    }

    function normalizePostCode(postcode) {
        postcode = postcode.replace(/\D+/g, '');
        if (postcode !== '' || $.isNumeric(postcode) || postcode.length === 8) {
            return postcode;
        } else {
            return;
        }
    }

    function getQuantity() {
        let qt = quantityInput.val().replace(/\D+/g, '');
        if (qt.length === 0) {
            return 1;
        } else {
            return Number(qt);
        }
    }

    function showResult(price, deliveryTime) {
        resultDiv.html(`
            <div id="rte-current-postcode"><strong>${rteShippingData.text.postcode}:</strong> ${postcode}.</div>
            <div id="rte-price"><strong>RTE Rodonaves:</strong> R$ ${price}.</div>
            <div id="rte-delivery-time"><strong>${rteShippingData.text.estimated}:</strong> ${deliveryTime} ${rteShippingData.text.days}.</div>
        `);
    }

    function showLoader() {
        resultDiv.html(`
            <div class="rte-loader"></div>
            <div class="rte-loader"></div>            
            <div class="rte-loader"></div>            
        `);
    }

    function showError(error) {
        resultDiv.html(`
            <div>${error}</div>        
        `);
    }

    function verifyCache() {
        let cachePostcode = getCookie(cookiePostcodeName);

        if (cachePostcode === undefined) {
            cachePostcode = $('#user-postcode').val();
            if (cachePostcode === '') {
                return false;
            }
        }
        postcode = normalizePostCode(cachePostcode);
        return true;
    }

    function verifyData(a, b) {
        if (!a || !b) {
            return false;
        }
        let aProps = Object.getOwnPropertyNames(a);
        let bProps = Object.getOwnPropertyNames(b);

        if (aProps.length !== bProps.length) {
            return false;
        }

        for (let i = 0; i < aProps.length; i++) {
            let propName = aProps[i];

            if (typeof a[propName] === 'object' && aProps[i] !== null) {
                let verifyChild = verifyData(a[propName], b[propName]);
                if (!verifyChild) {
                    return false;
                }
            } else {
                if (a[propName] !== b[propName]) {
                    return false;
                }
            }
        }
        return true;
    }

    function setCookie(e, o, i) {
        let t = '';
        if (i) {
            const n = new Date();
            n.setTime(n.getTime() + 24 * i * 60 * 60 * 1e3), (t = '; expires=' + n.toUTCString());
        }
        document.cookie = e + '=' + (o || '') + t + '; path=/';
    }

    function getCookie(e) {
        for (var o = e + '=', i = document.cookie.split(';'), t = 0; t < i.length; t++) {
            for (var n = i[t]; ' ' == n.charAt(0); ) n = n.substring(1, n.length);
            if (0 == n.indexOf(o)) return n.substring(o.length, n.length);
        }
        return;
    }
})(jQuery);
