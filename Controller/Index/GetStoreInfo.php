<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class GetStoreInfo
 * Retrieves store information from the core_config_data table and returns it as JSON.
 */
class GetStoreInfo extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * GetStoreInfo constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * Executes the action to fetch store information and return it as JSON.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        // Get the database connection
        $connection = $this->resourceConnection->getConnection();

        // Define the query to fetch store information
        $query = "SELECT path, value FROM core_config_data WHERE path IN (
            'general/store_information/name',
            'general/store_information/phone',
            'general/store_information/hours',
            'general/store_information/country_id',
            'general/store_information/region_id',
            'general/store_information/postcode',
            'general/store_information/city',
            'general/store_information/street_line1'
        ) AND scope = 'default' AND scope_id = 0";

        // Execute the query and fetch the results
        $results = $connection->fetchPairs($query);

        // Prepare the store information array
        $storeInfo = [
            'city' => $results['general/store_information/city'] ?? '',
            'country' => $results['general/store_information/country_id'] ?? '',
            'stateProvince' => $results['general/store_information/region_id'] ?? '',
            'streetAddress' => $results['general/store_information/street_line1'] ?? '',
            'zipPostalCode' => $results['general/store_information/postcode'] ?? ''
        ];

        // Create a JSON result object and return the store information
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($storeInfo);
    }
}
