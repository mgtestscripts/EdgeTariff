<?php

namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use EdgeTariff\EstDutyTax\Helper\Data;
use Laminas\Uri\UriFactory;

class OrderAddresses implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param Curl $curl
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        Curl $curl,
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Execute observer method to handle order address and shipping details.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Replace 'customshipping' with your carrier code
        $carrierCode = 'EdgeTariffEstDutyTax';
        $isEnabled = $this->scopeConfig->isSetFlag(
            "carriers/{$carrierCode}/active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isEnabled == true) {
            // Inside your observer method
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            $description = $order->getShippingDescription();

            if (strpos($description, 'Delivery Date') !== false) {
                $order->setShippingDescription($description . ", Shipping Fee = ");
            } else {
                $shippingAmount = 0.0;
                $estDutyAmount = 0.0;
                $generalSalesTaxAmount = 0.0;
                $declarationAmount = 0.0;

                // Define a regex to match the shipping line and extract the currency symbol
                preg_match('/Shipping[ :]*([-\s]*[\D]*\d*\.?\d*)/', $description, $shippingMatch);

                // If a match for shipping was found, extract currency and value
                if (isset($shippingMatch[1])) {
                    // Extract currency symbol from the shipping string
                    preg_match('/([^\d\s]+)/', $shippingMatch[1], $currencyMatch);
                    $currency = isset($currencyMatch[1]) ? trim($currencyMatch[1]) : '';

                    // Set shipping value without currency
                    $shippingAmount = floatval(preg_replace('/[^\d.]/', '', $shippingMatch[1]));
                    // Format shipping amount to two decimal places
                    $shippingAmount = number_format($shippingAmount, 2, '.', '');
                }
                // Now use the extracted currency symbol to parse Est. Duty, GST/VAT, and Declaration
                preg_match('/Est\. Duty:[ ]*([-\s]*[\D]*\d*\.?\d*)/', $description, $dutyMatch);

                if (isset($dutyMatch[1])) {
                    $estDutyAmount = floatval(preg_replace('/[^\d.]/', '', $dutyMatch[1]));
                    $estDutyAmount = number_format($estDutyAmount, 2, '.', '');
                }

                preg_match('/GST\/VAT:[ ]*([-\s]*[\D]*\d*\.?\d*)/', $description, $gstMatch);
                if (isset($gstMatch[1])) {
                    $generalSalesTaxAmount = floatval(preg_replace('/[^\d.]/', '', $gstMatch[1]));
                    $generalSalesTaxAmount = number_format($generalSalesTaxAmount, 2, '.', '');
                }

                preg_match('/Declaration:[ ]*([-\s]*[\D]*\d*\.?\d*)/', $description, $declarationMatch);
                if (isset($declarationMatch[1])) {
                    $declarationAmount = floatval(preg_replace('/[^\d.]/', '', $declarationMatch[1]));
                    $declarationAmount = number_format($declarationAmount, 2, '.', '');
                }
                $TotalTax = $estDutyAmount + $generalSalesTaxAmount + $declarationAmount;
                $TotalAmount = $shippingAmount + $TotalTax;

                // Update order with calculated amounts
                $order->setShippingAmount($shippingAmount);
                $order->setBaseShippingAmount($shippingAmount);
                $order->setTaxAmount($TotalTax);
                $order->setBaseTaxAmount($TotalTax);
                $description = preg_replace('/\+ Est\. Duties & Taxes \(Shown as USD; Est\. Duties and Taxes are not refundable\) - Shipping \$(\d+\.\d+) \+ Est\. Duty: \$(\d+\.\d+) \+ GST\/VAT: \$(\d+\.\d+)/', '+ Est. Duties & Taxes - Shipping $1 + Est. Duty: $2 + GST/VAT: $3 (Shown as USD; Est. Duties and Taxes are not refundable)', $description);
                $order->setShippingDescription($description);
                // Check if the specific phrase exists and replace it
                $description = str_replace("any Duty & Tax will be payable by you separately", "any Duty & Tax will be payable by customer separately", $description);
                $order->setShippingDescription($description);
                $order->setShippingDescription($description . ", Shipping Fee = ");
            }

            // Calculate and set the grand total
            $grandTotal = $order->getSubtotal() + $order->getShippingAmount() + $order->getTaxAmount();
            $order->setGrandTotal($grandTotal);
            $order->setBaseGrandTotal($grandTotal);

            $shopAddressData = $this->session->getShopAddressData();

            if (!$shopAddressData) {
                $this->logger->info('No shop address data available in session.');
                return;
            }

            $order = $observer->getEvent()->getOrder();
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $currencyCode = $order->getOrderCurrencyCode();
            $baseUrl = $order->getStore()->getBaseUrl();
            $uri = UriFactory::factory($baseUrl);
            $host = $uri->getHost();
            $userID = $order->getCustomerId() ?? 0;
            $webshopUrl = $order->getStore()->getBaseUrl();
            $shopUrl = rtrim($webshopUrl, '/');

            $data = [
                'action' => 'OrderCompletion',
                'user_id' => $userID,
                'order_number' => $order->getIncrementId(),
                'user_name' => $billingAddress->getFirstname() . " " . $billingAddress->getLastname(),
                'first_name' => $billingAddress->getFirstname(),
                'last_name' => $billingAddress->getLastname(),
                'currency' => $currencyCode,
                'shopurl' => $shopUrl,
                'shopname' => $host,
                'shop_address' => [
                    'address_line' => $shopAddressData['address_line'],
                    'city' => $shopAddressData['city'],
                    'state' => $shopAddressData['state'],
                    'country_code' => $shopAddressData['country_code'],
                    'postal_code' => $shopAddressData['postal_code']
                ],
                'shop_billing_address' => [
                    'address_line' => $billingAddress->getStreet()[0] ?? '',
                    'city' => $billingAddress->getCity(),
                    'state' => $billingAddress->getRegion(),
                    'country_code' => $billingAddress->getCountryId(),
                    'postal_code' => $billingAddress->getPostcode()
                ],
                'shop_shipping_address' => [
                    'address_line' => $shippingAddress->getStreet()[0] ?? '',
                    'city' => $shippingAddress->getCity(),
                    'state' => $shippingAddress->getRegion(),
                    'country_code' => $shippingAddress->getCountryId(),
                    'postal_code' => $shippingAddress->getPostcode()
                ]
            ];

            // Array to keep track of product IDs to exclude
            $excludedProductIds = [];

            // First pass: Collect all product IDs to exclude
            foreach ($order->getAllVisibleItems() as $item) {
                $parent_item = $item->getParentItemId();
                $product = $item->getProduct();
                $productType = $product->getTypeId();

                if ($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    // Collect all child items of configurable products
                    $children = $item->getChildrenItems();
                    if ($children) {
                        foreach ($children as $childItem) {
                            // Track the simple product IDs that are variants
                            $excludedProductIds[$childItem->getProductId()] = true;
                        }
                    }
                } elseif ($productType == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    // Collect all child items of bundle products
                    $children = $item->getChildrenItems();
                    if ($children) {
                        foreach ($children as $childItem) {
                            // Track the bundle product child IDs to exclude
                            $excludedProductIds[$childItem->getProductId()] = true;
                        }
                    }
                }
            }

            // Second pass: Process items and exclude tracked products
            $data['orderitems'] = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $parent_item = $item->getParentItemId();
                $product = $item->getProduct();
                $productId = $item->getProductId();
                $productType = $product->getTypeId();

                // Default order item details
                $orderItem = [
                    'product_id' => $productId,
                    'variation_id' => 0,
                    'product_name' => $item->getName(),
                    'product_price' => $item->getPrice(),
                    'product_quantity' => $item->getQtyOrdered(),
                    'product_weight' => $product->getWeight(),
                    'product_total' => $item->getRowTotal(),
                    'product_type' => $productType,
                ];
                $parent_item = $item['parent_item'];

                // Handle configurable products
                if ($parent_item == null && $productType == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {

                    $data['orderitems'][] = $orderItem;
                } elseif ($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    if ($children) {
                        foreach ($children as $childItem) {
                            $childOrderItem = $orderItem; // Clone base item info
                            $childOrderItem['variation_id'] = $childItem->getProductId();
                            $childOrderItem['product_name'] = $childItem->getName();
                            $data['orderitems'][] = $childOrderItem;
                        }
                    }
                } elseif ($productType == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    $data['orderitems'][] = $orderItem;
                }
            }
            // Send the API request only if the store and shipping countries differ
            if ($shopAddressData['country_code'] != $shippingAddress->getCountryId()) {
                $webUrl = $order->getStore()->getBaseUrl();
                $siteUrl = rtrim($webUrl, '/');
                $jsondata = json_encode($data);

                $apiUrl = $this->helper->getWttSwanBaseUrl() .
                    "/MagentoRestrictedPartyScreening/UpdateRPSOnOrder?shop={$siteUrl}";
                $this->curl->post($apiUrl, $jsondata);
            }

            /* --- Shipstation - START --- */
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $dimensionUnit = $this->scopeConfig->getValue(
                'general/locale/weight_unit',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($dimensionUnit === null) {
                $dimensionUnit = 'lbs';
            }
            $shipdata = [];
            // Extract basic order details
            $shipdata['orderKey'] = (int) $order->getIncrementId();
            $shipdata['orderNumber'] = (int) $order->getIncrementId();
            // Get the current date and time
            $dateTime = new \DateTime('now');
            $shipdata['orderDate'] = [
                'date' => $dateTime->format('Y-m-d H:i:s.u'),
                'timezone_type' => 1,
                'timezone' => $dateTime->format('P')
            ];
            $shipdata["paymentDate"] = null;
            $shipdata['orderStatus'] = $order->getStatus();
            // Get billing and shipping addresses
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $shipdata['shop_billing_address'] = [
                'name' => $billingAddress->getName(),
                'company' => $billingAddress->getCompany(),
                'address_line' => $billingAddress->getStreetLine(1),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegion(),
                'country' => $billingAddress->getCountryId(),
                'phone' => $billingAddress->getTelephone(),
                'postalCode' => $billingAddress->getPostcode(),
                'residential' => 'yes'
            ];
            $shipdata['shop_shipping_address'] = [
                'name' => $shippingAddress->getName(),
                'company' => $shippingAddress->getCompany(),
                'address_line' => $shippingAddress->getStreetLine(1),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegion(),
                'country' => $shippingAddress->getCountryId(),
                'phone' => $shippingAddress->getTelephone(),
                'postalCode' => $shippingAddress->getPostcode(),
                'residential' => 'no'
            ];
            $shipdata['amountPaid'] = $order->getGrandTotal();
            $shipdata['taxAmount'] = $order->getTaxAmount();
            $shipdata['shippingAmount'] = $order->getShippingAmount();
            $shippingDescription = $order->getShippingDescription();
            $shippingParts = explode(' +', $shippingDescription, 2);
            $shippingMethodText = $shippingParts[0];
            $shipdata['requestedShippingService'] = $shippingMethodText;

            $imageUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product';

            /* Get order items - START */
            // (excluding bundle products and their child items)
            $orderItems = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $productType = $product->getTypeId();

                // Skip bundle products and their children
                if ($productType === 'configurable' ||
                    $productType === 'bundle' ||
                    ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle')
                ) {
                    continue;
                }

                $productId = (int) $item->getProductId();
                $productName = $item->getName();
                $productPrice = $item->getPrice();
                $productQuantity = $item->getQtyOrdered();
                $productTotal = $item->getRowTotal();
                $weight = number_format($item->getWeight(), 2);
                $dimensionUnit = 'lbs';
                $productImage = $product->getImage();

                if ($item->getParentItem()) {
                    $parentItem = $item->getParentItem();
                    $productId = (int) $parentItem->getProductId();
                    $productName = $parentItem->getProductName();
                    $variationId = (int) $item->getProduct()->getId(); // Variation ID
                    $productName = $item->getName(); // Variation name
                    $productPrice = $parentItem->getPrice();
                    $productQuantity = $item->getQtyOrdered();
                    $productTotal = $parentItem->getRowTotal();
                    $weight = number_format($item->getWeight(), 2);
                    $dimensionUnit = 'lbs';
                    $productImage = $product->getImage();
                    // Extract and format options
                    $productOptions = $parentItem->getProductOptions();
                    $options = [];
                    if (isset($productOptions['attributes_info'])) {
                        foreach ($productOptions['attributes_info'] as $attribute) {
                            $options[] = [
                                'name' => $attribute['label'],
                                'value' => $attribute['value']
                            ];
                        }
                    }
                } else {
                    // Handle simple products
                    $variationId = 0;
                    $options = []; // Simple products have no options
                }

                $orderItems[] = [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_quantity' => $productQuantity,
                    'parent_variation_id' => 0,
                    'weight' => [
                        'value' => $weight,
                        'units' => $dimensionUnit,
                    ],
                    'dimensions' => [
                        'units' => $dimensionUnit,
                        'length' => '1',
                        'width' => '1',
                        'height' => '1'
                    ],
                    'product_total' => $productTotal,
                    'options' => $options,
                    'image' => $productImage ? $imageUrl . $productImage : ""
                ];
            }
            $shipdata['orderItems'] = $orderItems;
            /* Get order items - END */

            /* Get bundle products - START */
            $bundleItems = [];
            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getProductType() === 'bundle') { // Include only bundle parent products
                    $bundleItems[] = [
                        'product_id' => (int) $item->getProductId(),
                        'variation_id' => 0,
                        'product_name' => $item->getName(),
                        'product_price' => $item->getPrice(),
                        'product_quantity' => $item->getQtyOrdered(),
                        'parent_variation_id' => 0,
                        'weight' => [
                            'value' => number_format($item->getWeight(), 2),
                            'units' => $dimensionUnit,
                        ],
                        'dimensions' => [
                            'units' => $dimensionUnit,
                            'length' => '1',
                            'width' => '1',
                            'height' => '1'
                        ],
                        'product_total' => $item->getRowTotal(),
                        'options' => $item->getProductOptions()['options'] ?? [],
                        'image' => $item->getProduct()->getImage() ? $imageUrl . $item->getProduct()->getImage() : ""
                    ];
                }
            }
            $shipdata['bundleItems'] = $bundleItems;
            /* Get bundle products - END */

            /* Get bundle items - START */
            // Get bundle items (children of bundle products)
            $bundleOrderItems = [];
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItem() &&
                    $item->getParentItem()->getProductType() === 'bundle'
                ) { // Only include child items of bundles
                    $bundleName = $item->getParentItem()->getName(); // Get the parent bundle name
                    $itemName = $item->getName();

                    $bundleOrderItems[] = [
                        'product_id' => (int) $item->getProductId(),
                        'variation_id' => $item->getProductOptionByCode('simple_sku'),
                        'product_name' => $bundleName . ' : ' . $itemName, // Format as bundle_name : item_name
                        'product_price' => $item->getPrice(),
                        'product_quantity' => $item->getQtyOrdered(),
                        'parent_variation_id' => 0,
                        'weight' => [
                            'value' => $item->getWeight(),
                            'units' => $dimensionUnit,
                        ],
                        'dimensions' => [
                            'units' => $dimensionUnit,
                            'length' => '1',
                            'width' => '1',
                            'height' => '1'
                        ],
                        'product_total' => $item->getParentItem()->getRowTotal(),
                        'options' => [],
                        'image' => $item->getProduct()->getImage() ? $imageUrl . $item->getProduct()->getImage() : ""
                    ];
                }
            }
            $shipdata['bundleOrderItems'] = $bundleOrderItems;
            /* Get bundle items - END */

            /* Get dimensions sums - START */
            $dimensions_sums = [
                'units' => $dimensionUnit,
                'length' => '1',
                'width' => '1',
                'height' => '1'
            ];
            $shipdata['dimensions_sums'] = $dimensions_sums;
            /* Get dimensions sums - END */

            /* Get customs items - START */
            // Get customs items (without bundle parent products, but including bundle children)
            $customsItems = [];
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();
                $productType = $product->getTypeId();

                // Skip bundle products and their children
                if ($productType === 'configurable' || $productType === 'bundle') {
                    continue;
                }

                $productPrice = $item->getPrice();

                if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'configurable') {
                    $productPrice = $item->getParentItem()->getPrice();
                }

                $hsCode = $item->getProduct()->getData('EdgeTariff_hs_code') ?? '';
                $countryOfOrigin = $item->getProduct()->getData('EdgeTariff_country_of_origin') ?? '';

                $description = $item->getName();

                // If the item is a child of a bundle, prepend the parent's name
                if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'bundle') {
                    $parentName = $item->getParentItem()->getName();
                    $description = $parentName . ' : ' . $description;
                }

                $customsItems[] = [
                    'description' => $description, // Use the modified description
                    'value' => $productPrice ?? 0,
                    'quantity' => $item->getQtyOrdered(),
                    'harmonizedTariffCode' => $hsCode,
                    'countryOfOrigin' => $countryOfOrigin
                ];
            }
            $shipdata['customsItems'] = $customsItems;
            /* Get customs items - END */
            $jsondata = json_encode($shipdata);
            /* --- Shipstation - END --- */

            //$this->coreSession->unsCustomShippingRates();

            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getProductType() === 'bundle') {
                    $webUrl = $order->getStore()->getBaseUrl();
                    $siteUrl = rtrim($webUrl, '/');
                    $shipdata['shop'] = $siteUrl;
                    $jsondata = json_encode($shipdata);
                    $apiUrl = $this->helper->getWttSwanBaseUrl() .
                        "/MagentoRestrictedPartyScreening/ShipstationOrderUpdate?shop={$siteUrl}";
                    $this->curl->post($apiUrl, $jsondata);
                }
            }
            $this->session->clearStorage();
        }
    }
}
