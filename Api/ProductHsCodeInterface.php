<?php

namespace EdgeTariff\EstDutyTax\Api;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface ProductHsCodeInterface
 * @api
 */
interface ProductHsCodeInterface
{

    /**
     * Update HS Code and Country of Origin for a product by ID
     *
     * @param int $productId
     * @param string $hsCode
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    
    public function updateAttributes(
        $productId,
        $hsCode
    );
}
