<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class CheckMethod
 *
 * This class checks the method for the EdgeTariffEstDutyTax carrier and returns its enabled state.
 */
class CheckMethod extends Action
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Executes the controller action.
     *
     * This method checks whether the EdgeTariffEstDutyTax carrier is enabled in the configuration
     * and returns the carrier code and its enabled state as a JSON response.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Replace 'customshipping' with your carrier code
        $carrierCode = 'EdgeTariffEstDutyTax';
        $isEnabled = $this->scopeConfig->isSetFlag(
            "carriers/{$carrierCode}/active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $result = $this->jsonFactory->create();
        return $result->setData([
            'carrier_code' => $carrierCode,
            'is_enabled' => $isEnabled ? true : false,
        ]);
    }
}
