<?php
namespace EdgeTariff\EstDutyTax\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Framework\App\ResourceConnection;

class ProductRepositoryPlugin
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductExtensionInterfaceFactory
     */
    protected $productExtensionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Constructor to initialize dependencies.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductExtensionInterfaceFactory $productExtensionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductExtensionInterfaceFactory $productExtensionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * After plugin for get method to add custom attributes to a single product.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @return ProductInterface
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $result
    ) {
        $this->addCustomAttributes($result);
        return $result;
    }

    /**
     * After plugin for getList method to add custom attributes to a list of products.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductSearchResultsInterface $result
     * @return ProductSearchResultsInterface
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $result
    ) {
        foreach ($result->getItems() as $product) {
            $this->addCustomAttributes($product);

            // For bundle products, update the price based on child items
            if ($product->getTypeId() === 'bundle') {
                if ($product->getPrice() == 0) {
                    $totalPrice = $this->calculateTotalBundlePrice($product);
                    $product->setPrice($totalPrice);
                } else {
                    $totalPrice = $product->getOrigData('price');
                }
            }
        }
        return $result;
    }

    /**
     * Adds custom attributes to the product, including store currency, weight unit,
     * and any configurable parent association if applicable.
     *
     * @param ProductInterface $product
     */
    private function addCustomAttributes(ProductInterface $product)
    {
        $store = $this->storeManager->getStore();
        $currencyCode = $store->getBaseCurrencyCode();
        $weightUnit = $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );

        $extensionAttributes = $product->getExtensionAttributes() ?: $this->productExtensionFactory->create();

        $extensionAttributes->setStoreCurrency($currencyCode);
        $extensionAttributes->setWeightUnit($weightUnit);

        // Set custom product attributes
        $product->setCustomAttribute('EdgeTariff_hs_code', $product->getData('EdgeTariff_hs_code'));
        $product->setCustomAttribute('EdgeTariff_country_of_origin', $product->getData('EdgeTariff_country_of_origin'));

        // Check if product is a child of a configurable product and assign the parent ID if so
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_relation');

        $parentIds = $connection->fetchCol(
            $connection->select()->from($tableName, ['parent_id'])->where('child_id = ?', $product->getId())
        );

        $productEntityTable = $connection->getTableName('catalog_product_entity');
        foreach ($parentIds as $parentId) {
            $typeId = $connection->fetchOne(
                $connection->select()->from($productEntityTable, ['type_id'])->where('entity_id = ?', $parentId)
            );
            if ($typeId == 'configurable') {
                $extensionAttributes->setVariantParentId($parentId);
                $extensionAttributes->setProductType('configurable');
                break;
            }
        }

        // For bundle products, calculate the total price and set bundle selections
        if ($product->getTypeId() === 'bundle') {
            $totalPrice = $this->calculateTotalBundlePrice($product);
            $extensionAttributes->setMainProductIds($this->getBundleSelections($product));
            $product->setPrice($totalPrice);
        }

        $product->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Calculates the total price of a bundle product by summing up the prices
     * of its child products based on their quantity.
     *
     * @param ProductInterface $product
     * @return float
     */
    private function calculateTotalBundlePrice(ProductInterface $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_relation');
        $childIds = $connection->fetchCol(
            $connection->select()->from($tableName, ['child_id'])->where('parent_id = ?', $product->getId())
        );

        $totalPrice = 0;

        foreach ($childIds as $childId) {
            $quantity = $connection->fetchOne(
                $connection->select()->from('catalog_product_bundle_selection', ['selection_qty'])->where('product_id = ?', $childId)
            );
            if ($quantity !== false) {
                $childPrice = $connection->fetchOne(
                    $connection->select()->from('catalog_product_index_price', ['price'])->where('entity_id = ?', $childId)
                );
                $totalPrice += (float)$childPrice * (int)$quantity;
            }
        }

        return $totalPrice;
    }

    /**
     * Retrieves the bundle selections for a bundle product, including child ID,
     * quantity, and price for each selection.
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getBundleSelections(ProductInterface $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_relation');
        $childIds = $connection->fetchCol(
            $connection->select()->from($tableName, ['child_id'])->where('parent_id = ?', $product->getId())
        );

        $bundleSelections = [];
        foreach ($childIds as $childId) {
            $quantity = $connection->fetchOne(
                $connection->select()->from('catalog_product_bundle_selection', ['selection_qty'])->where('product_id = ?', $childId)
            );
            $price = $connection->fetchOne(
                $connection->select()->from('catalog_product_index_price', ['price'])->where('entity_id = ?', $childId)
            );

            if ($quantity !== false && $price !== false) {
                $bundleSelections[] = [
                    'id' => (int)$childId,
                    'quantity' => (int)$quantity,
                    'price' => (float)$price,
                ];
            }
        }

        return $bundleSelections;
    }
}
