<?php
namespace EdgeTariff\EstDutyTax\Model;

use Magento\Framework\App\Config\Storage\WriterInterface;
use EdgeTariff\EstDutyTax\Api\DeliveryManagementInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class DeliveryManagement implements DeliveryManagementInterface
{
    private $configWriter;
    private $cacheTypeList;
    private $cacheFrontendPool;

    public function __construct(
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function updateCustomCheckbox($value)
    {
        if (!in_array($value, ['0', '1'])) {
            throw new \InvalidArgumentException('Invalid value. Use "1" for Yes, "0" for No.');
        }

        $this->configWriter->save('carriers/EdgeTariffEstDutyTax/custom_checkbox', $value);

        // Clean config cache programmatically
        $this->cacheTypeList->cleanType('config');

        if ($value === '0') {
            return 'EdgeTariff Domestic Functionality OFF';
        } else {
            return 'EdgeTariff Domestic Functionality ON';
        }
    }
}
