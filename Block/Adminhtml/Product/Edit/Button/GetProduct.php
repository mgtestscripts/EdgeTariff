<?php

namespace EdgeTariff\EstDutyTax\Block\Adminhtml\Product\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\ResourceConnection;

/**
 * Class GetProduct
 * EdgeCTP\EstDutyTax\Block\Adminhtml\Product\Edit\Button
 */
class GetProduct extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * GetProduct constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $coreRegistry, $data);
    }

    /**
     * Get button data for the product edit page.
     *
     * @return array
     */
    public function getButtonData()
    {

        // Check if we are on the edit page by verifying the existence of a product ID
        $productId = $this->getProductId();
        if (!$productId) {
            // If there is no product ID, it's the new product creation page, so don't show the button
            return [];
        }

        // Hide the button for bundle products AND variant children
        $product = $this->getProduct();
        if ($product && $product->getTypeId() == Type::TYPE_BUNDLE) {
            return [];
        }

        if ($product && $this->isChildOfConfigurableProduct($product)) {
            return [];
        }

        $url = $this->getUrl('EstDutyTax/getproduct', ['id' => $productId]);

        return [
            'label' => __('Perform PPC'),
            'class' => 'action-secondary',
            'on_click' => 'window.location.href = \'' . $url . '\';',
            'sort_order' => 10
        ];
    }

    /**
     * Get product ID from registry.
     *
     * @return int|null
     */
    protected function getProductId()
    {
        $product = $this->coreRegistry->registry('current_product');
        return $product ? $product->getId() : null;
    }

    /**
     * Check if the product is a child of a configurable product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function isChildOfConfigurableProduct($product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_relation');
        
        $select = $connection->select()
            ->from($tableName, ['parent_id'])
            ->where('child_id = ?', $product->getId());
        
        $parentIds = $connection->fetchCol($select);

        // Check if any parent is configurable
        if (!empty($parentIds)) {
            $productEntityTable = $connection->getTableName('catalog_product_entity');
            $select = $connection->select()
                ->from($productEntityTable, ['type_id'])
                ->where('entity_id IN (?)', $parentIds);
                
            $typeIds = $connection->fetchCol($select);
            return in_array("configurable", $typeIds);
        }

        return false;
    }
}
