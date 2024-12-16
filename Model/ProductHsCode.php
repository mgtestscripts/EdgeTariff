<?php

namespace EdgeTariff\EstDutyTax\Model;

use EdgeTariff\EstDutyTax\Api\ProductHsCodeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductHsCode implements ProductHsCodeInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductHsCode constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Update product attributes, specifically the HS code.
     *
     * This method updates the 'EdgeCTP_hs_code' custom attribute for a given product.
     *
     * @param int $productId
     * @param string $hsCode
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateAttributes($productId, $hsCode)
    {
        try {
            // Load the product by ID
            $product = $this->productRepository->getById($productId);

            // Set the custom attribute 'EdgeCTP_hs_code'
            $product->setCustomAttribute('EdgeTariff_hs_code', $hsCode);

            // Optionally set the 'EdgeCTP_country_of_origin' attribute if needed
            // $product->setCustomAttribute('EdgeCTP_country_of_origin', $countryOfOrigin);

            // Save the updated product
            $this->productRepository->save($product);

            return $product;
        } catch (NoSuchEntityException $e) {
            // Handle the case where the product is not found
            throw new LocalizedException(__('Product not found.'));
        } catch (\Exception $e) {
            // Handle other potential exceptions
            throw new LocalizedException(__('An error occurred while updating attributes.'));
        }
    }
}
