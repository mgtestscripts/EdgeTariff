<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use EdgeTariff\EstDutyTax\Model\Carrier\CustomShipping;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class ShippingMethod
 * Handles the AJAX request to store and return custom shipping method data.
 */
class ShippingMethod extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomShipping
     */
    protected $customShipping;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * ShippingMethod constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomShipping $customShipping
     * @param LoggerInterface $logger
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomShipping $customShipping,
        LoggerInterface $logger,
        SessionManagerInterface $coreSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customShipping = $customShipping;
        $this->logger = $logger;
        $this->coreSession = $coreSession;
        parent::__construct($context); // Initialize parent class
    }

    /**
     * Execute the action to process the shipping method data.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Create a JSON result object
        $result = $this->resultJsonFactory->create();
        // Retrieve raw POST data from the request
        $postData = $this->getRequest()->getContent();
        $data = json_decode($postData, true);
        // Store the custom shipping rates in the session
        $this->coreSession->setCustomShippingRates($data);
        // Return the received data as a JSON response
        return $result->setData($data);
    }
}
