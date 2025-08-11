<?php

declare(strict_types=1);

namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Observer for config save after event.
 */
class ConfigSaveAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * Constructor.
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
     * Execute observer.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $changedPaths = $observer->getEvent()->getData('changed_paths') ?? [];

            if (in_array('carriers/EstDutyTax/active', $changedPaths, true)) {
                $newValue = $this->scopeConfig->getValue(
                    'carriers/EstDutyTax/active',
                    ScopeInterface::SCOPE_STORE
                );

                $status = $newValue ? 'install' : 'uninstall';
                $storeUrl = $this->storeManager->getStore()->getBaseUrl();

                $data = json_encode([
                    'action' => $status,
                    'sourceStoreName' => $storeUrl
                ]);

                // Example external call (disabled)
                // $url = 'https://example.com/plugin-activation-endpoint';
                // $this->curl->post($url, $data);

                $this->logger->info(
                    'Magento Shipping Method Activation/Deactivation',
                    ['action' => $status, 'sourceStoreName' => $storeUrl]
                );
            } else {
                $this->logger->info('Config path "carriers/EstDutyTax/active" was not changed.');
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error in ConfigSaveAfter observer: ' . $e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            );
        }
    }
}
