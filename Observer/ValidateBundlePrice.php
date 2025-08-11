<?php

declare(strict_types=1);

namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer to validate that the bundle product's base price
 * does not exceed the total price of selected bundle options.
 */
class ValidateBundlePrice implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getProduct();

        if ($product->getTypeId() !== BundleType::TYPE_CODE || (int)$product->getPriceType() !== 1) {
            return;
        }

        $bundlePrice = (float)$product->getPrice();
        $postData = $this->request->getPostValue();

        if (empty($postData['bundle_options']['bundle_options']) || !is_array($postData['bundle_options']['bundle_options'])) {
            return;
        }

        $totalOptionPrice = 0.0;
        $bundleOptions = $postData['bundle_options']['bundle_options'];

        foreach ($bundleOptions as $option) {
            if (empty($option['bundle_selections']) || !is_array($option['bundle_selections'])) {
                continue;
            }

            foreach ($option['bundle_selections'] as $selection) {
                $selectionPriceType = isset($selection['selection_price_type']) ? (int)$selection['selection_price_type'] : 0;
                $selectionPriceValue = isset($selection['selection_price_value']) ? (float)$selection['selection_price_value'] : 0.0;
                $selectionQty = isset($selection['selection_qty']) ? (float)$selection['selection_qty'] : 1.0;

                $basePrice = 0.0;
                if (isset($selection['price'])) {
                    $basePrice = (float)preg_replace('/[^\d.]/', '', $selection['price']);
                }

                $priceValue = ($selectionPriceType === 1)
                    ? ($basePrice * $selectionPriceValue) / 100
                    : $selectionPriceValue;

                $totalOptionPrice += $priceValue * $selectionQty;
            }
        }

        if ($bundlePrice > $totalOptionPrice) {
            throw new LocalizedException(
                __(
                    'Bundle price (%1) cannot be greater than total options price (%2). Please set the bundle price equal to or less than the total options price.',
                    $bundlePrice,
                    $totalOptionPrice
                )
            );
        }
    }
}


