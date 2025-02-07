<?php

namespace EdgeTariff\EstDutyTax\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use EdgeTariff\EstDutyTax\Model\Attribute\Source\Material as Source;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class AddProductAttributes
 *
 * This data patch adds custom product attributes 'Country of Origin' and 'HS Code' to the catalog product entity.
 */
class AddProductAttributes implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Apply the data patch.
     *
     * Adds 'Country of Origin' and 'HS Code' attributes to the product entity.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Add 'Country of Origin' attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'EdgeTariff_country_of_origin',
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Country of Origin',
                'input' => 'select',
                'source' => Source::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'is_html_allowed_on_frontend' => true,
                'visible_on_front' => true,
                'apply_to' => 'simple,configurable', // Only for Simple and Configurable products
            ]
        );

        // Add 'HS Code' attribute
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'EdgeTariff_hs_code',
            [
                'type' => 'text',
                'label' => 'HS Code',
                'input' => 'text',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'frontend_class' => 'validate-digits',
                'apply_to' => 'simple,configurable', // Only for Simple and Configurable products
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get list of dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get list of aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
