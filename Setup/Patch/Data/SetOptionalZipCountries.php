<?php
namespace EdgeTariff\EstDutyTax\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SetOptionalZipCountries implements DataPatchInterface
{
    private $configWriter;
    private $scopeConfig;

    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    public function apply()
    {
        $existingCountries = $this->scopeConfig->getValue('general/country/optional_zip_countries');
        $existingArray = $existingCountries ? explode(',', $existingCountries) : [];

        $newCountries = [
            'HK','MO','IE','KY','BS','BM','BW','DM','FJ','GD','JM','MW','MU',
            'KN','LC','VC','SC','ZA','TT','AE','SA','QA','KW','OM'
        ];

        $mergedCountries = array_unique(array_merge($existingArray, $newCountries));

        $this->configWriter->save(
            'general/country/optional_zip_countries',
            implode(',', $mergedCountries),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        return $this;
    }

    public static function getDependencies() { return []; }

    public function getAliases() { return []; }
}
