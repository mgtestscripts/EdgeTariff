<?php
namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\GetOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Index
 * Controller for loading and displaying order data in the admin panel.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Execute the action and return the result page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Get the order ID from the request
        $orderId = $this->getRequest()->getParam('order_id');

        // Load the order
        $order = $this->orderFactory->create()->load($orderId);

        // Create the result page
        $resultPage = $this->resultPageFactory->create();

        // Set the page title
        $resultPage->getConfig()->getTitle()->prepend(__("Perform RPS"));

        // Pass order data to the block
        $block = $resultPage->getLayout()->getBlock('get_order');
        if ($block) {
            $block->setData('order', $order);
            $block->setData('order_id', $order->getId());
        }

        return $resultPage;
    }
}
