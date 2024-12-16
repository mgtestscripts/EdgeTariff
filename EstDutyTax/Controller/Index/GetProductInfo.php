<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class GetProductInfo
 * Returns product information including configurable parent details if applicable.
 */
class GetProductInfo extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * GetProductInfo constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        ResourceConnection $resourceConnection
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * Execute the action to fetch product information.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');

        try {
            // Fetch the product by ID
            $product = $this->productRepository->getById($productId);

            // Fetch all parent IDs based on the child ID from the catalog_product_relation table
            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('catalog_product_relation');
            
            $select = $connection->select()
                ->from($tableName, ['parent_id'])
                ->where('child_id = ?', $product->getId());
            
            $parentIds = $connection->fetchCol($select);

            $productType = null;
            $variantParentIds = [];
            $isConfigurableParent = false;

            if (!empty($parentIds)) {
                $productEntityTable = $connection->getTableName('catalog_product_entity');

                foreach ($parentIds as $parentId) {
                    $select = $connection->select()
                        ->from($productEntityTable, ['type_id'])
                        ->where('entity_id = ?', $parentId);
                    
                    $typeId = $connection->fetchOne($select);

                    if ($typeId === 'configurable') {
                        $isConfigurableParent = true;
                        $variantParentIds[] = $parentId;
                    }
                }
            }

            // Base product data
            $productData = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'type_id' => $product->getTypeId(),
            ];

            // Conditionally add fields if a configurable parent exists
            if ($isConfigurableParent) {
                $productData['variant_parent_ids'] = $variantParentIds;
                $productData['product_type'] = 'configurable';
            }

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($productData);

        } catch (NoSuchEntityException $e) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['error' => __('Product not found')]);
        } catch (LocalizedException $e) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['error' => __('An error occurred while fetching the product information')]);
        } catch (\Exception $e) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['error' => __('An unexpected error occurred')]);
        }
    }
}
