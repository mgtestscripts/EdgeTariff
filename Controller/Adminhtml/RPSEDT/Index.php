<?php

namespace EdgeTariff\EstDutyTax\Controller\Adminhtml\RPSEDT;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * Controller for displaying the RPSEDT page in the admin panel.
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
     * Execute the action to load and display the RPSEDT page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Create the result page
        $resultPage = $this->resultPageFactory->create();

        // Set the page title
        $resultPage->getConfig()->getTitle()->prepend(__("RPS+EDT"));

        return $resultPage;
    }
}
