<?php
namespace EdgeTariff\EstDutyTax\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;

class PluginBefore
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }

    /**
     * Before plugin for adding custom buttons to the sales order view page
     *
     * @param Interceptor $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return void
     */
    public function beforePushButtons(
        Interceptor $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        // Check if the current page is the sales order view page
        if ($this->_request->getFullActionName() == 'sales_order_view') {
            // Add a custom button to perform RPS with order_id as a parameter
            $buttonList->add(
                'get_order_button',
                [
                    'label' => __('Perform RPS'),
                    'onclick' => 'window.location.href = \'' . $context->getUrl(
                        'EstDutyTax/getorder/index',
                        ['order_id' => $this->_request->getParam('order_id')]
                    ) . '\';'
                ],
                -1
            );

            // Add another custom button to show RPS with order_id as a parameter
            $buttonList->add(
                'show_order_button',
                [
                    'label' => __('Show RPS'),
                    'onclick' => 'window.location.href = \'' . $context->getUrl(
                        'EstDutyTax/showorder/index',
                        ['order_id' => $this->_request->getParam('order_id')]
                    ) . '\';'
                ],
                -1
            );
        }
    }
}
