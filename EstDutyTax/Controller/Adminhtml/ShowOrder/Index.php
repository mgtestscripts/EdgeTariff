<?php
namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\ShowOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;

/**
 * Class Index
 * Controller for displaying order information on the ShowOrder admin page.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Order $order
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Order $order
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->order = $order;
    }

    /**
     * Execute the action to display the order details on the ShowOrder page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Get the order ID from the request
        $orderId = $this->getRequest()->getParam('order_id');
        
        // Load the order
        $order = $this->order->load($orderId);

        // Create the result page
        $resultPage = $this->resultPageFactory->create();
        
        // Set the page title
        $resultPage->getConfig()->getTitle()->prepend(__("Show RPS"));

        // Pass the order data to the view block
        $block = $resultPage->getLayout()->getBlock('show_order');
        if ($block) {
            $block->setData('order_id', $order->getId());
        }

        return $resultPage;
    }
}
