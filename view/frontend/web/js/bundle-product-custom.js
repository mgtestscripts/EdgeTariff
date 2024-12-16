require(['jquery'], function ($) {
    $(document).ready(function () {

        $('.bundle-options-wrapper').hide();

        // Function to select all checkboxes and disable them
        function selectAllCheckboxes(checkboxes) {
            checkboxes.forEach(checkbox => {
                checkbox.required = true; // Select all checkboxes

            });
        }

        // Handle bundle-slide button click to select all inputs and disable them
        $('#bundle-slide').on('click', function () {
            $('.bundle-options-wrapper').show();

            // Check all checkboxes and disable them
            const checkboxes = document.querySelectorAll('.checkbox.product.bundle');
            if (checkboxes.length > 0) {
                selectAllCheckboxes(checkboxes); // Select all checkboxes
            }
        });

    });
});
