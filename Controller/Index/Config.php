<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 * Returns store configuration details in JSON format.
 */
class Config extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute the action to return store configuration details.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            // Retrieve store's base currency code and weight unit
            $store = $this->storeManager->getStore();
            $baseCurrencyCode = $store->getBaseCurrencyCode();
            $weightUnit = $this->scopeConfig->getValue('general/locale/weight_unit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE); // phpcs:ignore

            // Prepare response data
            $response = [
                'base_currency_code' => $baseCurrencyCode,
                'weight_unit' => $weightUnit,
            ];

            return $resultJson->setData($response);
        } catch (\Exception $e) {
            // Handle any exceptions and return error message
            return $resultJson->setData([
                'error' => true,
                'message' => __('An error occurred while fetching configuration data.'),
            ]);
        }
    }
}
