<?php
namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\GetProduct;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Index
 * Controller for fetching and displaying product details in the admin panel.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductRepository $productRepository
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        ProductRepository $productRepository,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute the action to load product details and display them.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Get the product ID from the request
        $productId = $this->getRequest()->getParam('id');
        $product = $this->productRepository->getById($productId);

        // Initialize variables
        $hs6code = $product->getData('EdgeTariff_hs_code') ?? "";
        $countryOfOrigin = $product->getData('EdgeTariff_country_of_origin') ?? "";
        $productUnitPrice = $product->getPrice() ?? "";

        // If the product is configurable, check the child products
        if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            if (!empty($children)) {
                // Fetch the first child product
                $childProductFirstIndex = reset($children);
                $childProduct = $this->productRepository->getById($childProductFirstIndex->getId());

                // Override with child product details
                $hs6code = $childProduct->getData('EdgeTariff_hs_code') ?? $hs6code;
                $countryOfOrigin = $childProduct->getData('EdgeTariff_country_of_origin') ?? $countryOfOrigin;
                $productUnitPrice = $childProduct->getPrice() ?? $productUnitPrice;
            }
        }

        // Prepare product details
        $productDetails = [
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'hs6code' => $hs6code,
            'country_of_origin' => $countryOfOrigin,
            'product_unit_price' => $productUnitPrice,
        ];

        // Create result page and set title
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Perform PPC"));

        // Pass product details to the block
        $resultPage->getLayout()->getBlock('get_product_block')->setProductDetails($productDetails);

        return $resultPage;
    }
}
