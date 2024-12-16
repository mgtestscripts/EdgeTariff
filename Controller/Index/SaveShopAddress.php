<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class SaveShopAddress
 * Saves shop address data into session and returns a success response.
 */
class SaveShopAddress extends Action
{
    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * SaveShopAddress constructor.
     * @param Context $context
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $session
    ) {
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Execute the action to save shop address data.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Retrieve shop address data from the request
        $shopAddress = $this->getRequest()->getParam('shopAddress');
        
        // If shop address data is present, save it into the session
        if ($shopAddress) {
            $this->session->setShopAddressData($shopAddress);
        }
        
        // Prepare the success result
        $result = ['success' => true];
        
        // Return the result as a JSON response
        return $this->getResponse()->setBody(json_encode($result));
    }
}
