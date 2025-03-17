<?php
namespace EdgeTariff\EstDutyTax\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Data
 *
 * This helper class provides constants and methods to retrieve the base URLs
 * used for WTT and Swan services. It also provides methods to get the base URL
 * of the store. It can be used across the module to fetch these URLs.
 */
class Data extends AbstractHelper
{
    /**
     * Base URL for WTT service
     */
    public const WTT_BASE_URL = 'https://account.zugdev.com';

    /**
     * Base URL for Swan service
     */
    public const WTT_SWANBASE_URL = 'https://edgeswan.zugdev.com';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * Get the base URL for the WTT service.
     *
     * @return string
     */
    public function getWttBaseUrl()
    {
        return self::WTT_BASE_URL;
    }

    /**
     * Get the base URL for the Swan service.
     *
     * @return string
     */
    public function getWttSwanBaseUrl()
    {
        return self::WTT_SWANBASE_URL;
    }

    /**
     * Get the base URL of the store.
     *
     * @return string
     */
    public function getStoreBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl();
    }
}
