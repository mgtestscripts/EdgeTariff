<?php
namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;

class ConfigSaveAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Curl $curl
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->curl = $curl;
    }

    /**
     * Execute observer method to handle configuration save events.
     *
     * This method checks if the configuration path 'carriers/EstDutyTax/active' has been changed,
     * logs the status, and optionally sends a curl request with the new status and store URL.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            // Get event data
            $eventData = $observer->getEvent()->getData();
            $changedPaths = $eventData['changed_paths'] ?? [];

            // Check if the specific path 'carriers/EstDutyTax/active' is in the changed paths
            if (in_array('carriers/EstDutyTax/active', $changedPaths)) {
                $newValue = $this->scopeConfig->getValue('carriers/EstDutyTax/active', ScopeInterface::SCOPE_STORE);
                $status = $newValue ? 'install' : 'uninstall';

                // Get the store's base URL
                $storeUrl = $this->storeManager->getStore()->getBaseUrl();
                $data = json_encode(['action' => $status, 'sourceStoreName' => $storeUrl]);

                // Optionally, send a request to an external service with status and store URL
                // $url = 'http://localhost:35534/admin/settings/Magento/MagentoPluginActivateDeactivate.aspx';
                // $this->curl->post($url, $data);

                // Log the status based on the new value
                $this->logger->info('Method Activate', ['action' => $status, 'sourceStoreName' => $storeUrl]);
            } else {
                $this->logger->info('The path carriers/EstDutyTax/active was not changed.');
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the execution
            $this->logger->error('Error in ConfigSaveAfter observer: ' . $e->getMessage());
        }
    }
}
