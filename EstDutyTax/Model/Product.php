<?php
namespace EdgeTariff\EstDutyTax\Model;

use Magento\Catalog\Model\Product as MagentoProduct;

class Product extends MagentoProduct
{
    /**
     * Retrieve the 'hs_code' attribute value.
     *
     * This method returns the value of the 'EdgeCTP_hs_code' attribute for the product.
     *
     * @return string|null
     */
    public function getHsCode()
    {
        return $this->getData('EdgeTariff_hs_code');
    }

    /**
     * Retrieve the 'country_of_origin' attribute value.
     *
     * This method returns the value of the 'EdgeCTP_country_of_origin' attribute for the product.
     *
     * @return string|null
     */
    public function getCountryOfOrigin()
    {
        return $this->getData('EdgeTariff_country_of_origin');
    }
}
