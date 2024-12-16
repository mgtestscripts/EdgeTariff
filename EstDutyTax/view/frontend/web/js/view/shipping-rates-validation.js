/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'EdgeTariff_EstDutyTax/js/model/shipping-rates-validator', // Updated path
        'EdgeTariff_EstDutyTax/js/model/shipping-rates-validation-rules' // Updated path
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        customShippingRatesValidator,
        customShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('EdgeTariffEstDutyTax', customShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('EdgeTariffEstDutyTax', customShippingRatesValidationRules);
        return Component;
    }
);
