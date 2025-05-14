require(['jquery'], function ($) {
    $(document).ready(function () {

        // START --- Country of Origin (Request re- classification button should be disable.) ---
        var ppcButton = $('#Get\\ RPS');
        
        // Function to initialize country selection
        function initCountrySelection() {
            var countrySelect = $('select[name^="product"][name$="[EdgeTariff_country_of_origin]"]');
            
            if (countrySelect.length) {
                var initialValue = countrySelect.val();
                togglePPCButton(initialValue);
                
                // Also check for dynamically loaded selects
                if (!initialValue || initialValue.trim() === '') {
                    var dynamicSelect = $('select[name^="product[options"]').filter(function() {
                        return $(this).attr('name').includes('EdgeTariff_country_of_origin');
                    });
                    
                    if (dynamicSelect.length) {
                        initialValue = dynamicSelect.val();
                        togglePPCButton(initialValue);
                    }
                }
            } else {
                setTimeout(initCountrySelection, 500); // Retry after delay
            }
        }

        // Enable/Disable button based on selected country
        function togglePPCButton(countryCode) {
            if (countryCode && countryCode.trim() !== '') {
                ppcButton.prop('disabled', false);
            } else {
                ppcButton.prop('disabled', true);
            }
        }

        // --- Country of Origin Change Handler ---
        $(document).on('change', 'select[name*="EdgeTariff_country_of_origin"]', function() {
            var countryCode = $(this).val();
            togglePPCButton(countryCode);
        });

        // Initialize with retry mechanism
        initCountrySelection();

        // Additional check after a delay in case of dynamic loading
        setTimeout(initCountrySelection, 1000);
        // END --- Country of Origin (Request re- classification button should be disable.) ---
    });
});
