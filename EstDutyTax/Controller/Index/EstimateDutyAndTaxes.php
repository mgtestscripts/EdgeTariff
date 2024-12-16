<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use EdgeTariff\EstDutyTax\Helper\Data;

/**
 * Class EstimateDutyAndTaxes
 *
 * Controller responsible for estimating duty and taxes by sending JSON data to an external API.
 */
class EstimateDutyAndTaxes extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Curl
     */

    protected $curl;

         /**
          * @var Data
          */
      protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Curl $curl,
        Data $helper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute method for handling the request and sending data to external API
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
         $result = $this->jsonFactory->create();
         $content = $this->request->getContent();
         $postData = json_decode($content, true);
     
         $baseUrl = rtrim($this->storeManager->getStore()->getBaseUrl(), '/') . '/';
         $storeUrl = rtrim($baseUrl, '/');

         $externalUrl = $this->helper->getWttSwanBaseUrl() .
         '/MagentoEstimatedDutyAndTaxes/CarrierServices?shop='. $storeUrl;
     
             $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
             $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true); // Allow redirects
             $this->curl->addHeader("Content-Type", "application/json");
             $this->curl->post($externalUrl, json_encode($postData));

             $responseBody = $this->curl->getBody();
             $statusCode = $this->curl->getStatus();
     
             $responseData = json_decode($responseBody, true); // Decode as an associative array
                // dd($responseData);
            return $result->setData($responseData);
    }
}
