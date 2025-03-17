define([
    'underscore',
    'uiRegistry',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/step-navigator' // Add this line
], function (_, uiRegistry, ko, Component, modal, quote, shippingService, selectShippingMethod, $, url, storage, resourceUrlManager, rateRegistry, errorProcessor, stepNavigator) {
    'use strict';

    return Component.extend({
        isButtonVisible: ko.observable(true),
        shopUrl: '',
        shopName: '',
        storeCurrency: '',
        storeWeightUnit: '',
        CurrentCurrency: '',
        CheckEnable: '',
        ShopAddress: {},
        PostalFreeCountry: {},
        data: {},

        defaults: {
            template: 'EdgeTariff_EstDutyTax/form/button'
        },

        initialize: function () {
            this._super();
            var self = this;
            this.shopUrl = url.build(''); // Builds the base URL (current store URL)
            var urlObject = new URL(this.shopUrl);
            this.shopName = urlObject.hostname.replace(/^www\./, '');
            $.ajax({
                url: '/storeconfig/index/checkmethod', // Controller URL
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    self.CheckEnable = response;
                    if (response.is_enabled) {

                        $(document).on('click', '.action-select-shipping-item', function () {
                            $('.checkout-shipping-method').hide();
                            if ($('.table-checkout-shipping-method').length && $('.table-checkout-shipping-method').is(':hidden')) {
                                $('.custom-button-classmain').show();
                            }
                        });
                        // Add keyup event listener for multiple input fields
                        $(document).on('keyup', 'input[name="city"], input[name="postcode"], input[name="region"], input[name="country_id"], input[name="telephone"]', function () {
                            $('.checkout-shipping-method').hide();
                            $('.checkboxClass_Wtt_wrap_parent').hide();
                        });
                        setTimeout(function () {
                            var availableRates = shippingService.getShippingRates();
                            selectShippingMethod(availableRates()[0]);
                        }, 2000);
                        setTimeout(function () {
                            if (/^\d+$/.test(quote.getQuoteId())) {
                                // Check if the class 'checkout-step-shipping_method' exists
                                if ($('.table-checkout-shipping-method').length) {
                                    $('.custom-button-classmain').hide();
                                } else {
                                    $('.custom-button-classmain').show();
                                }
                            }
                        }, 2500);
                        $.ajax({
                            url: '/storeconfig/index/countries',
                            type: 'GET',
                            dataType: 'json',
                            success: function (response) {
                                if (response.status === 'success') {
                                    self.PostalFreeCountry = response.selected_countries;
                                } else {
                                    console.log('No countries found.');
                                }
                            },
                            error: function () {
                                console.error('Failed to fetch currency data.');
                            }
                        });

                        // Make the AJAX call
                        $.ajax({
                            url: '/storeconfig/index/fetch',
                            type: 'GET',
                            dataType: 'json',
                            success: function (response) {
                                self.CurrentCurrency = response.country_code;
                            },
                            error: function () {
                                console.error('Failed to fetch currency data.');
                            }
                        });
                        // Fetch store configuration
                        $.ajax({
                            url: '/storeconfig/index/config',
                            type: 'GET',
                            dataType: 'json',
                            success: function (response) {
                                if (response) {
                                    self.storeCurrency = response.base_currency_code;
                                    self.storeWeightUnit = response.weight_unit;
                                } else {
                                    console.warn('Store configuration response is empty');
                                }
                            },
                            error: function (error) {
                                console.error('Error fetching store configuration:', error);
                            }
                        });
                        $.ajax({
                            url: '/storeconfig/index/GetStoreinfo',
                            type: 'GET',
                            dataType: 'json',
                            success: function (StoreAddress) {
                                if (StoreAddress) {
                                    self.ShopAddress = StoreAddress;

                                    // Fetch State ID using the stateProvince value
                                    const codee = self.ShopAddress.stateProvince;
                                    $.ajax({
                                        url: '/storeconfig/index/Getstateid',
                                        type: 'POST',
                                        data: { code: codee },
                                        dataType: 'json',
                                        success: function (response) {
                                            if (response && response.state_id) {
                                                self.ShopAddress.stateId = response.state_id; // Assign the State ID
                                            } else {
                                                console.warn('State ID response is empty or missing state_id');
                                            }
                                        },
                                        error: function (error) {
                                            console.error('Error fetching State ID:', error);
                                        }
                                    });
                                } else {
                                    console.warn('Store address response is empty');
                                }
                            },
                            error: function (error) {
                                console.error('Error fetching store address:', error);
                            }
                        });
                    } else {
                        $('.custom-button-class').hide();
                    }
                },
                error: function () {
                    console.error('Error while checking the shipping method.');
                }
            });
        },


        checkShippingAddressFields: function () {
            if (this.CheckEnable.is_enabled) {

                var self = this;
                $('.checkboxClass_Wtt_wrap_child').hide();
                // Select the shipping address fields by their name attributes
                var firstName = $('input[name="firstname"]').val().trim();
                var lastName = $('input[name="lastname"]').val().trim();
                var street = $('input[name="street[0]"]').val().trim();
                var city = $('input[name="city"]').val().trim();
                var postcode = $('input[name="postcode"]').val().trim();
                var telephone = $('input[name="telephone"]').val().trim();

                var countryId = quote.shippingAddress().countryId;
                if (this.PostalFreeCountry.includes(countryId)) {
                    // Check if any of these fields are empty
                    if (firstName === "" || lastName === "" || street === "" || city === "" || telephone === "") {
                        $('.checkboxClass_Wtt_wrap_child').show();
                        $('.checkout-shipping-method').hide();
                    } else {
                        this.displayShippingRates();
                    }
                } else {
                    // Check if any of these fields are empty
                    if (firstName === "" || lastName === "" || street === "" || city === "" || postcode === "" || telephone === "") {
                        $('.checkboxClass_Wtt_wrap_child').show();
                        $('.checkout-shipping-method').hide();
                    } else {
                        this.displayShippingRates();
                    }
                }
            } else {
                $('.custom-button-class').hide();
            }
        },

        displayShippingRates: function () {
            var self = this;

            $('.checkboxClass_Wtt_wrap_parent').hide();
            $('.checkboxClass_Wtt_wrap_child').hide();
            $('.checkboxClass_Wtt_wrap_parentmain').hide();
            $('.checkboxClass_Wtt_wrap_childmain').hide();
            shippingService.setShippingRates([]);

            // Show the shipping method section when this function is called
            $('.checkout-shipping-method').show();
            shippingService.isLoading(true);
            var shippingAddress = quote.shippingAddress();
            var billingAddress = quote.billingAddress();
            var cartItems = quote.getItems();
            var CardID = quote.getQuoteId();
            var self = this;


            async function wpacLikeCnicAjaxAction() {
                try {
                    const siteUrl = window.location.origin;
                    const userId = cartItems.quote_id; // Use quoteId from quote model
                    const firstName = shippingAddress.firstname;
                    const lastName = shippingAddress.lastname;

                    let billingAddress = shippingAddress.street[0];
                    let billingCity = shippingAddress.city;
                    let billingState = shippingAddress.region;
                    let billingCountryCode = shippingAddress.countryId;
                    let billingPostalCode = shippingAddress.postcode;

                    let shippingAddressLine = shippingAddress.street[0];
                    let shippingCity = shippingAddress.city;
                    let shippingState = shippingAddress.region;
                    let shippingCountryCode = shippingAddress.countryId;
                    let shippingPostalCode = shippingAddress.postcode;

                    try {
                        const response = await $.ajax({
                            url: '/storeconfig/index/Getstateid',
                            type: 'POST',
                            data: { Name: shippingState },
                            dataType: 'json'
                        });

                        if (response && response.state_id) {
                            shippingState = response.state_id; // Assign the State ID
                        } else {
                            console.warn('State ID response is empty or missing state_id');
                        }
                    } catch (error) {
                    }

                    // Update billingState if it was initially empty
                    billingState = shippingState;

                    if (!shippingAddressLine && billingAddress) {
                        shippingAddressLine = billingAddress;
                        shippingCity = billingCity;
                        shippingState = billingState;
                        shippingCountryCode = billingCountryCode;
                        shippingPostalCode = billingPostalCode;
                    }

                    if (!billingAddress && shippingAddressLine) {
                        billingAddress = shippingAddressLine;
                        billingCity = shippingCity;
                        billingState = shippingState;
                        billingCountryCode = shippingCountryCode;
                        billingPostalCode = shippingPostalCode;
                    }
                    var EstimateAddress = {
                        "address": {
                            "city": shippingAddress.city,
                            "country_id": shippingAddress.countryId,
                            "postcode": shippingAddress.postcode,
                        }
                    };
                    let data = {
                        action: 'RequestEDT',
                        user_id: userId,
                        user_name: shippingAddress.firstname + ' ' + shippingAddress.lastname,
                        first_name: firstName,
                        last_name: lastName,
                        shopurl: siteUrl,
                        shopname: siteUrl.replace(/(http:\/\/|https:\/\/|www\.)/, ''),
                        currency: self.storeCurrency,
                        countrycode: self.CurrentCurrency,
                        shop_address: {
                            address_line: self.ShopAddress.streetAddress,
                            city: self.ShopAddress.city,
                            state: self.ShopAddress.stateId || 'N/A',
                            country_code: self.ShopAddress.country,
                            postal_code: self.ShopAddress.zipPostalCode
                        },
                        shop_billing_address: {
                            address_line: shippingAddress.street[0],
                            city: shippingCity,
                            state: shippingState,
                            country_code: shippingCountryCode,
                            postal_code: shippingPostalCode
                        },
                        shop_shipping_address: {
                            address_line: shippingAddress.street[0],
                            city: shippingCity,
                            state: shippingState,
                            country_code: shippingCountryCode,
                            postal_code: shippingPostalCode
                        },
                        orderitems: [],
                        Bundleorderitems: []
                    };

                    $.ajax({
                        url: url.build('/storeconfig/index/saveShopAddress'), // Use your custom controller URL
                        type: 'POST',
                        data: { shopAddress: data.shop_address },
                        success: function (response) {
                        },
                        error: function (xhr, status, error) {
                            console.error('Failed to save shop address:', error);
                        }
                    });

                    await Promise.all(cartItems.map(async (cartItem) => {
                        const product = cartItem;
                        const productLength = "1";
                        const productHeight = "1";
                        const productWidth = "1";
                        const productWeight = self.storeWeightUnit !== 'g' ? convertToGrams(product.weight, self.storeWeightUnit) : product.weight;
                        const qtyOptionNumbers = Object.keys(product.qty_options || {});
                        var numericPrice = parseFloat(product.price);
                        var ProductPrice = !isNaN(numericPrice) ? numericPrice.toFixed(2) : '0.00';
                        if (product.product_type == "bundle") {
                            let relatedIds = [];
                            await Promise.all(qtyOptionNumbers.map(async (number) => {
                                const productId = number;
                                try {
                                    const resp = await $.ajax({
                                        url: '/storeconfig/index/getproductinfo',
                                        type: 'GET',
                                        data: { id: productId },
                                        dataType: 'json'
                                    });
                                    const typeId = resp.type_id;
                                    const type = resp.product_type;
                                    if (type === "configurable") {
                                        relatedIds.push({
                                            new_product_id: resp.variant_parent_ids[0],
                                            new_var_id: number
                                        });
                                    } else if (typeId === "simple") {
                                        relatedIds.push({
                                            new_product_id: number,
                                            new_var_id: "0"
                                        });
                                    } else {
                                    }
                                } catch (error) {
                                }
                            }));

                            const discount = product.discount_amount || product.base_discount_amount ? true : false;
                            data.Bundleorderitems.push({
                                product_id: product.product_id,
                                product_name: product.name,
                                product_price: ProductPrice,
                                product_type: product.product_type,
                                product_quantity: product.qty,
                                product_weight: productWeight,
                                product_total: numericPrice * product.qty,
                                product_length: productLength,
                                product_height: productHeight,
                                product_width: productWidth,
                                related_ids: relatedIds,
                                discount: discount
                            });
                        } else if (product.product_type == "configurable") {
                            const productId = qtyOptionNumbers[0];
                            try {
                                const resp = await $.ajax({
                                    url: '/storeconfig/index/getproductinfo',
                                    type: 'GET',
                                    data: { id: productId },
                                    dataType: 'json'
                                });
                                data.orderitems.push({
                                    product_id: product.product_id,
                                    variation_id: qtyOptionNumbers[0],
                                    product_name: resp.name,
                                    product_price: ProductPrice,
                                    product_type: product.product_type,
                                    product_quantity: product.qty,
                                    product_weight: productWeight,
                                    product_total: numericPrice * product.qty,
                                    product_length: productLength,
                                    product_height: productHeight,
                                    product_width: productWidth
                                });
                            } catch (error) {
                                console.error('Error fetching configurable product info:', error);
                            }
                        } else {
                            data.orderitems.push({
                                product_id: product.product_id,
                                variation_id: "0",
                                product_name: product.name,
                                product_price: ProductPrice,
                                product_type: product.product_type,
                                product_quantity: product.qty,
                                product_weight: productWeight,
                                product_total: numericPrice * product.qty,
                                product_length: productLength,
                                product_height: productHeight,
                                product_width: productWidth
                            });
                        }
                    }));
                    // Make the AJAX call to submit the data
                    $.ajax({
                        //url: 'https://edgeswan.zugdev.com/MagentoEstimatedDutyAndTaxes/CarrierServices?shop=' + siteUrl,
                        url: '/storeconfig/index/estimatedutyandtaxes',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(data),
                        complete: function (response) {
                            $.ajax({
                                url: '/storeconfig/index/shippingmethod',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify(response.responseJSON),
                                success: function (data) {
                                    $.ajax({
                                        url: '/storeconfig/index/getrates',
                                        type: 'POST',
                                        contentType: 'application/json',
                                        data: JSON.stringify(response),
                                        success: function (result) {
                                            if (/^\d+$/.test(CardID)) {
                                                $.ajax({
                                                    url: siteUrl + '/rest/default/V1/carts/mine/estimate-shipping-methods',
                                                    type: 'POST',
                                                    dataType: 'json',
                                                    contentType: 'application/json',
                                                    data: JSON.stringify(EstimateAddress),
                                                    success: function (response) {
                                                        if (response) {
                                                            if (response.length) {
                                                                rateRegistry.set(shippingAddress.getKey(), response);
                                                                shippingService.setShippingRates(response);
                                                                var availableRates = shippingService.getShippingRates();
                                                                selectShippingMethod(availableRates()[0]);
                                                                shippingService.isLoading(false);
                                                                location.reload();

                                                            } else {
                                                                shippingService.isLoading(false);
                                                                $('.checkout-shipping-method').hide();
                                                                $('.checkboxClass_Wtt_wrap_parent').show();
                                                                if (/^\d+$/.test(CardID)) {
                                                                    $('.checkboxClass_Wtt_wrap_parentmain').show();
                                                                }
                                                            }
                                                        }
                                                    },
                                                    error: function (error) {
                                                        console.error('Error fetching store configuration:', error);
                                                    }
                                                });
                                            } else if (/^[a-zA-Z0-9]+$/.test(CardID)) {
                                                $.ajax({
                                                    url: siteUrl + '/rest/default/V1/guest-carts/' + CardID + '/estimate-shipping-methods',
                                                    type: 'POST',
                                                    dataType: 'json',
                                                    contentType: 'application/json',
                                                    data: JSON.stringify(EstimateAddress),
                                                    success: function (response) {
                                                        if (response) {
                                                            if (response.length) {
                                                                rateRegistry.set(shippingAddress.getKey(), response);
                                                                shippingService.setShippingRates(response);
                                                                var availableRates = shippingService.getShippingRates();
                                                                selectShippingMethod(availableRates()[0]);
                                                                shippingService.isLoading(false);
                                                                location.reload();
                                                            } else {
                                                                shippingService.isLoading(false);
                                                                $('.checkout-shipping-method').hide();
                                                                $('.checkboxClass_Wtt_wrap_parent').show();
                                                                if (/^\d+$/.test(CardID)) {
                                                                    $('.checkboxClass_Wtt_wrap_parentmain').show();
                                                                }
                                                            }
                                                        }
                                                    },
                                                    error: function (error) {
                                                        console.error('Error fetching store configuration:', error);
                                                    }
                                                });
                                            }
                                        },
                                        error: function (xhr, status, error) {
                                            console.error('Error fetching shipping rates:', error);
                                        }
                                    });
                                },
                                error: function (error) {
                                    console.error('Error updating shipping rates:', error);
                                }
                            });
                        },
                        error: function (error) {
                            console.error('Error saving product data:', error);
                        }
                    });
                } catch (error) {
                    console.error('Error in wpacLikeCnicAjaxAction:', error);
                }

            }

            function convertToGrams(weight, unit) {
                let weightInGrams;
                switch (unit) {
                    case 'kgs':
                        weightInGrams = weight * 1000;
                        break;
                    case 'lbs':
                        weightInGrams = weight * 453.592;
                        break;
                    default:
                        weightInGrams = weight; // Assuming it's already in grams
                        break;
                }
                return weightInGrams;
            }

            // Execute the function
            wpacLikeCnicAjaxAction();
        }
    });
});
