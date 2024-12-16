<?php
namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\ShowProduct;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * Controller for displaying product information on the ShowProduct admin page.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute the action to display the product details on the ShowProduct page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Create the result page
        $resultPage = $this->resultPageFactory->create();
        
        // Get the product ID from the request
        $productId = $this->getRequest()->getParam('id');

        // Set the page title
        $resultPage->getConfig()->getTitle()->prepend(__("Show PPC"));

        // Pass the product ID to the layout block
        $block = $resultPage->getLayout()->getBlock('show_product_block');
        if ($block) {
            $block->setProductId($productId);
        }

        return $resultPage;
    }
}
