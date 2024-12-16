<?php

namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\Connection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * Controller for the EdgeCTP Admin Page.
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
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute the action and return the result page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Create a new result page
        $resultPage = $this->resultPageFactory->create();
        
        // Set the title of the page
          $resultPage->getConfig()->getTitle()->prepend(__("Connection"));

        return $resultPage;
    }
}
