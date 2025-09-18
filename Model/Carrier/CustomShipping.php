<?php
namespace EdgeTariff\EstDutyTax\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use EdgeTariff\EstDutyTax\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;

class CustomShipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'EdgeTariffEstDutyTax';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

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

    protected $productRepository;
    protected $bundleType;
    protected $configurableType;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var string JSON encoded dynamic shipping rates
     */
    public $_rates = '';

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param SessionManagerInterface $coreSession
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        SessionManagerInterface $coreSession,
        StoreManagerInterface $storeManager,
        Curl $curl,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        BundleType $bundleType,
        Configurable $configurableType,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->coreSession = $coreSession;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->bundleType = $bundleType;
        $this->configurableType = $configurableType;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Set dynamic shipping rates as a JSON encoded string.
     *
     * @param array $rates
     * @return void
     */
    public function setDynamicRates(array $rates)
    {
        $this->_rates = json_encode($rates);
    }

    /**
     * Retrieve allowed shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['EdgeTariffEstDutyTax' => $this->getConfigData('name')];
    }

    /**
     * Collect available shipping rates based on the rate request.
     *
     * This method pulls dynamic rates stored in the session and creates
     * shipping methods with the given rate information.
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        // Check if the shipping method is active
        if (!$this->isActive()) {
            return false;
        }

        $content = [];
        $items = $request->getAllItems();
   
        if (!empty($items)) {
            $quoteItem = $items[0];
            $quote     = $quoteItem->getQuote();
            $quoteId   = $quoteItem->getQuoteId();

            // Customer info
            $shippingAddress = $quote->getShippingAddress();
            $firstName = $shippingAddress->getFirstname();
            $lastName  = $shippingAddress->getLastname();
            $userName  = $firstName . ' ' . $lastName;

            // Shipping Address
            $shippingAddressLine = $request->getDestStreet();
            $shippingCity        = $request->getDestCity();
            $shippingState       = $this->helper->getStateId($request->getDestRegionCode() , null);
            $shippingCountryCode = $request->getDestCountryId();
            $shippingPostalCode  = $request->getDestPostcode();

            // Billing address
            $billingAddressLine = $shippingAddressLine;
            $billingCity        = $shippingCity;
            $billingState       = $shippingState;
            $billingCountryCode = $shippingCountryCode;
            $billingPostalCode  = $shippingPostalCode;

            if (empty($shippingAddressLine) && !empty($billingAddressLine)) {
                $shippingAddressLine = $billingAddressLine;
                $shippingCity        = $billingCity;
                $shippingState       = $billingState;
                $shippingCountryCode = $billingCountryCode;
                $shippingPostalCode  = $billingPostalCode;
            }

            if (empty($billingAddressLine) && !empty($shippingAddressLine)) {
                $billingAddressLine  = $shippingAddressLine;
                $billingCity         = $shippingCity;
                $billingState        = $shippingState;
                $billingCountryCode  = $shippingCountryCode;
                $billingPostalCode   = $shippingPostalCode;
            }

            // Store Information
            $store    = $this->storeManager->getStore();
            $shopUrl  = rtrim($store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB), '/');
            $parsed   = parse_url($shopUrl);
            $shopName = $parsed['host'];
            $baseCurrencyCode = $store->getBaseCurrencyCode();
            $storeWeightUnit = $this->_scopeConfig->getValue(
                'general/locale/weight_unit',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $countryData  = $this->helper->getCountryCode();
            $countryCode  = $countryData['country_code'];
            $storeInfo = $this->helper->getStoreInfo();
            $addressLine = $storeInfo['streetAddress']; 
            $city = $storeInfo['city']; 
            $state = $storeInfo['stateId']; 
            $countryCode = $storeInfo['country']; 
            $postalCode = $storeInfo['zipPostalCode']; 

            $content = [
                "action" => "RequestEDT",
                "user_id" => $quoteId,
                "user_name" => $userName,
                "first_name" => $firstName,
                "last_name" => $lastName,
                "shopurl" => $shopUrl,
                "shopname" => $shopName,
                "currency" => $baseCurrencyCode,
                "countrycode" => $countryCode,
                "shop_address" => [
                    "address_line" => $addressLine,
                    "city" => $city,
                    "state" => $state,
                    "country_code" => $countryCode,
                    "postal_code" => $postalCode,
                ],
                "shop_billing_address" => [
                    "address_line" => $billingAddressLine,
                    "city"         => $billingCity,
                    "state"        => $billingState,
                    "country_code" => $billingCountryCode,
                    "postal_code"  => $billingPostalCode
                ],
                "shop_shipping_address" => [
                    "address_line" => $shippingAddressLine,
                    "city"         => $shippingCity,
                    "state"        => $shippingState,
                    "country_code" => $shippingCountryCode,
                    "postal_code"  => $shippingPostalCode
                ],
            ];

            $content['orderitems'] = [];
            $content['Bundleorderitems'] = [];

            foreach ($request->getAllItems() as $item) {
                $productType = $item->getProductType();

                // ✅ STEP 1: collect related_ids for bundle children first
                if ($productType === 'bundle') {
                    foreach ($item->getChildren() as $child) {
                        $childProduct = $child->getProduct();
                        $childType = $childProduct->getTypeId();

                        if ($childType === 'simple') {
                            // check if simple belongs to a configurable
                            $parentIds = $this->configurableType->getParentIdsByChild($childProduct->getId());
                            if (!empty($parentIds)) {
                                // simple under configurable
                                $configurableId = current($parentIds);
                                $relatedIdsMap[$item->getId()][] = [
                                    'new_product_id' => (string)$configurableId,
                                    'new_var_id'     => (string)$childProduct->getId(),
                                ];
                            } else {
                                // plain simple (no configurable parent)
                                $relatedIdsMap[$item->getId()][] = [
                                    'new_product_id' => (string)$childProduct->getId(),
                                    'new_var_id'     => "0",
                                ];
                            }
                        }
                    }
                }
            }



            foreach ($items as $item) { 
                // Skip child items (Magento attaches them under parent) 
                if ($item->getParentItem()) { 
                    continue; 
                } 
                
                $product = $this->productRepository->getById($item->getProductId()); 
                $productType = $product->getTypeId(); 
                $qty = $item->getQty(); 
                $price = $item->getPrice(); 

                $weight = $this->helper->convertToGrams($item->getWeight(), $storeWeightUnit); 
                
                $productLength = 1; 
                $productHeight = 1; 
                $productWidth = 1;

                if ($productType === 'bundle') {
                    $qty   = $item->getQty();
                    $price = $item->getPrice();
                    $weight = $this->helper->convertToGrams($item->getWeight(), $storeWeightUnit);

                    $productLength = 1;
                    $productHeight = 1;
                    $productWidth  = 1;

                    $discount = ($item->getDiscountAmount() > 0 || $item->getBaseDiscountAmount() > 0);

                    $content['Bundleorderitems'][] = [
                        'product_id'     => (string)$product->getId(),
                        'product_name'   => $product->getName(),
                        'product_price'  => $this->helper->formatPrice($price),
                        'product_type'   => 'bundle',
                        'product_quantity'=> $qty,
                        'product_weight' => $weight,
                        'product_total'  => $this->helper->formatPrice($price * $qty),
                        'product_length' => $productLength,
                        'product_height' => $productHeight,
                        'product_width'  => $productWidth,
                        'related_ids'    => $relatedIdsMap[$item->getId()] ?? [],
                        'discount'       => $discount
                    ];
                        
                } elseif ($productType === 'configurable') { 
                    // :white_check_mark: Get selected child product 
                    $children = $item->getChildren(); 
                    if (!empty($children)) { 
                        $childItem = current($children); 
                        $childProduct = $childItem->getProduct(); 
                        
                        // Convert weight for child product 
                        $childWeight = $this->helper->convertToGrams($childProduct->getWeight(), $storeWeightUnit); 
                        
                        $content['orderitems'][] = [ 
                            'product_id' => $product->getId(), // configurable parent id 
                            'variation_id' => $childProduct->getId(), // selected variation id 
                            'product_name' => $childProduct->getName(), 
                            'product_price' => $this->helper->formatPrice($childProduct->getFinalPrice()), 
                            'product_type' => $productType, 
                            'product_quantity' => (int)$item->getQty(), 
                            'product_weight' => $childWeight, 
                            'product_total' => $this->helper->formatPrice($childProduct->getFinalPrice() * $item->getQty()), 
                            'product_length' => $childProduct->getData('length') ?: 1, 
                            'product_height' => $childProduct->getData('height') ?: 1, 
                            'product_width' => $childProduct->getData('width') ?: 1 
                        ]; 
                    } 
                } else { 
                    if ($productType === 'virtual' || $productType === 'downloadable') { 
                        continue; 
                    } 
                    
                    $content['orderitems'][] = [ 
                        'product_id' => $product->getId(), 
                        'variation_id' => "0", 
                        'product_name' => $product->getName(), 
                        'product_price' => $this->helper->formatPrice($price), 
                        'product_type' => $productType, 
                        'product_quantity' => $qty, 
                        'product_weight' => $weight, 
                        'product_total' => $this->helper->formatPrice($price * $qty), 
                        'product_length' => $productLength, 
                        'product_height' => $productHeight, 
                        'product_width' => $productWidth 
                    ]; 
                } 
            }
        }

        // Initialize the result object for storing rate methods
        $result = $this->_rateResultFactory->create();

        $postData = $content;
        $fullBaseUrl = $this->storeManager->getStore()->getBaseUrl();
        $parsedUrl = parse_url($fullBaseUrl);

        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        $externalUrl = $this->helper->getWttSwanBaseUrl() .
         '/MagentoEstimatedDutyAndTaxes/CarrierServices?shop='. $baseUrl;
     
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true); // Allow redirects
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->post($externalUrl, json_encode($postData));

        $responseBody = $this->curl->getBody();
        $statusCode = $this->curl->getStatus();

        $data = json_decode($responseBody, true);
        
        $i = 1;
        $selectedMethod = $shippingAddress->getShippingMethod(); // user selected shipping method

        // Check if rates are available in session and iterate over them
        if (isset($data['rates']) && !empty($data['rates'])) {
            foreach ($data['rates'] as $record) {
                $totalPrice = $record['total_price'];
                $amount = $totalPrice / 100; // Convert to a numeric value
                $MethodTitle = $record['description'];

                // Check if 'General Sales Tax' exists in the carrier title and replace it with 'GST/VAT'
                if (strpos($MethodTitle, 'General Sales Tax') !== false) {
                    $MethodTitle = str_replace('General Sales Tax', 'GST/VAT', $MethodTitle);
                }

                // Create a new shipping method
                $method = $this->_rateMethodFactory->create();

                $methodCode = $this->_code . '_' . $i;

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($record['service_name']);
                $method->setMethod($i);
                $method->setMethodTitle($MethodTitle);

                // Calculate the final shipping price with handling fee
                $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
                $method->setPrice($shippingPrice);
                $method->setCost($amount);

                // 👇 Custom data (frontend ke liye response me)
                $method->setData('no_packages', $record['noPackages']); 
                $method->setData('packing_rule_name', $record['packingRuleName']); 
                $method->setData('packing_dimensions', $record['packingDimensions']); 
                $method->setData('address_type', $record['addressType']);

                // 👇 Sirf user ke selected method ka data quote address me save karo
                if ($selectedMethod === $methodCode) {
                    $shippingAddress->setData('no_packages', $record['noPackages']);
                    $shippingAddress->setData('packing_rule_name', $record['packingRuleName']);
                    $shippingAddress->setData('packing_dimensions', $record['packingDimensions']);
                    $shippingAddress->setData('address_type_custom', $record['addressType']);
                    $shippingAddress->save();
                }

                // Append the method to the result object
                $result->append($method);
                $i++;
            }
        }

        return $result;
    }
}
