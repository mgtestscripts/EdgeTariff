require(['jquery'], function ($) {
    $(document).ready(function () {

        var currentURL = window.location.href;

        // Check if "type/bundle" is present in the URL
        if (currentURL.includes("type/bundle")) {
            console.log("Hello There - This is a bundle product");
            var totalPrice = 0;
            var priceValue = 0;
            // Find all selected checkboxes
            var selectedProducts = [];
            var GrandTotalPrice = 0;

            // Use event delegation to bind the click event to the button inside the aside div
            $(document).on('click', '.action-primary', function () {
                console.log('Button clicked!');

                selectedProduct = [];
                totalPrice = 0;

                $('table.data-grid input.admin__control-checkbox:checked').each(function () {
                    var $row = $(this).closest('tr');

                    // Gather data from each column
                    var productData = {
                        id: $row.find('td:nth-child(2) .data-grid-cell-content').text(),
                        thumbnail: $row.find('td:nth-child(3) img').attr('src'),
                        name: $row.find('td:nth-child(4) .data-grid-cell-content').text(),
                        type: $row.find('td:nth-child(5) .data-grid-cell-content').text(),
                        sku: $row.find('td:nth-child(6) .data-grid-cell-content').text(),
                        quantity: $row.find('td:nth-child(7) .data-grid-cell-content').text(),
                        price: $row.find('td:nth-child(8) .data-grid-cell-content').text()
                    };

                    // Add product to selected products array
                    selectedProduct.push(productData);


                    // Convert price to a float and add to totalPrice
                    var price = parseFloat(productData.price.replace(/[^0-9.-]+/g, "")) || 0;
                    totalPrice += price;

                });
                selectedProducts.push(selectedProduct);
                GrandTotalPrice += totalPrice;

                // Log the selected product data and the total price
                console.log('Selected Products:', selectedProducts);
                console.log('Total Price of Selected Products:', totalPrice);
                console.log('Grand Total Price of Selected Products:', GrandTotalPrice);
                priceValue = $('input[name="product[price]"]').val();
                console.log('Price:', priceValue || 0);
                if (priceValue > totalPrice) {
                    // Disable the save button
                    $('#save-button').prop('disabled', true);

                    // Check if the error message is not already added to avoid duplicates
                    if ($('#customError').length === 0) {
                        $('#container').prepend(`
                            <div class="messages" id="customError">
                                <div class="message message-error error">
                                    <div data-ui-id="messages-message-error">
                                        Bundle Price is more than Bundle Product Price. Please Verify.
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                } else {
                    // Enable the save button and remove the error message if it exists
                    $('#save-button').prop('disabled', false);
                    $('#customError').remove();
                    console.log("Well Defined Price");
                }

                $(document).on('keyup', 'input[name="product[price]"]', function () {
                    // Get and log the current value of the input field
                    const priceVal = $(this).val();
                    console.log(priceVal);
                    if (priceVal > totalPrice) {
                        // Disable the save button
                        $('#save-button').prop('disabled', true);

                        // Check if the error message is not already added to avoid duplicates
                        if ($('#customError').length === 0) {
                            $('#container').prepend(`
                                <div class="messages" id="customError">
                                    <div class="message message-error error">
                                        <div data-ui-id="messages-message-error">
                                            Bundle Price is more than Bundle Product Price. Please Verify.
                                        </div>
                                    </div>
                                </div>
                            `);
                        }
                    } else {
                        // Enable the save button and remove the error message if it exists
                        $('#save-button').prop('disabled', false);
                        $('#customError').remove();
                        console.log("Well Defined Price");
                    }
                });

            });
        } else {
            console.log("Hello There - This is not a bundle product");
        }
    });
});
