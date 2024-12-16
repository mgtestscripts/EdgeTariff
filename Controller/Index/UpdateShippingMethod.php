<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\GuestShippingMethodManagementInterface;

/**
 * Class UpdateShippingMethod
 * Handles the AJAX request to update the shipping method for a guest cart.
 */
class UpdateShippingMethod extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var GuestShippingMethodManagementInterface
     */
    protected $guestShippingMethodManagement;

    /**
     * UpdateShippingMethod constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param GuestShippingMethodManagementInterface $guestShippingMethodManagement
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        GuestShippingMethodManagementInterface $guestShippingMethodManagement
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->guestShippingMethodManagement = $guestShippingMethodManagement;
        parent::__construct($context); // Initialize the parent class
    }

    /**
     * Execute the action to update the shipping method for a guest cart.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Create a JSON result object
        $result = $this->jsonFactory->create();
        
        // Initialize response array with default failure status
        $response = ['success' => false];

        try {
            // Retrieve parameters from the request
            $cartId = $this->getRequest()->getParam('cartId');
            $carrierCode = $this->getRequest()->getParam('carrierCode');
            $methodCode = $this->getRequest()->getParam('methodCode');

            // Set the shipping method for the guest cart
            $this->guestShippingMethodManagement->set($cartId, $carrierCode, $methodCode);

            // Update response to indicate success
            $response['success'] = true;
        } catch (\Exception $e) {
            // Capture any exceptions and include the error message in the response
            $response['message'] = $e->getMessage();
        }

        // Return the result as a JSON response
        return $result->setData($response);
    }
}
