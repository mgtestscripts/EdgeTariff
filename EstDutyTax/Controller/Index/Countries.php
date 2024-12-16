<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Countries extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute function to retrieve selected countries.
     *
     * This method fetches the list of countries where 'Zip/Postal Code is Optional'
     * from the configuration settings, converts the list into an array,
     * and returns it in JSON format.
     *
     * Note: The logic here assumes a comma-separated list is stored in
     * the configuration, which is converted to an array of country codes.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        // Fetching the configuration value for selected countries in 'Zip/Postal Code is Optional for'
        $selectedCountries = $this->scopeConfig->getValue(
            'general/country/optional_zip_countries',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Splitting the comma-separated list into an array of country codes
        $countriesList = explode(',', $selectedCountries);

        // Returning the selected countries list in JSON format
        return $resultJson->setData([
            'status' => 'success',
            'selected_countries' => $countriesList
        ]);
    }
}
