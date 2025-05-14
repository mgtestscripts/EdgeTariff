<?php

namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class ValidateBundlePrice implements ObserverInterface
{
    protected $request;

    public function __construct(RequestInterface $request) 
    {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
    
        if ( $product->getTypeId() !== \Magento\Bundle\Model\Product\Type::TYPE_CODE || $product->getPriceType() != 1) 
        {
            return;
        }
    
        $bundlePrice = (float) $product->getPrice();
        $postData = $this->request->getPostValue();
    
        if (!isset($postData['bundle_options']['bundle_options']) || !is_array($postData['bundle_options']['bundle_options'])) 
        {
            return;
        }
    
        $totalOptionPrice = 0;
        $bundleOptions = $postData['bundle_options']['bundle_options'];
    
        foreach ($bundleOptions as $option) {
            if (!isset($option['bundle_selections'])) 
            {
                continue;
            }
        
            foreach ($option['bundle_selections'] as $selection) {
                $priceValue = 0;
        
                $selectionPriceType = isset($selection['selection_price_type']) ? (int)$selection['selection_price_type'] : 0;
                $selectionPriceValue = isset($selection['selection_price_value']) ? (float)$selection['selection_price_value'] : 0;
                $selectionQty = isset($selection['selection_qty']) ? (float)$selection['selection_qty'] : 1;

                $basePrice = 0;
                if (isset($selection['price'])) {
                    $basePrice = (float) preg_replace('/[^\d.]/', '', $selection['price']);
                }
        
                if ($selectionPriceType === 1) {
                    $priceValue = ($basePrice * $selectionPriceValue) / 100;
                } else {
                    $priceValue = $selectionPriceValue;
                }
        
                $totalOptionPrice += $priceValue * $selectionQty;
            }
        }        
    
        if ($bundlePrice > $totalOptionPrice) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Bundle price (%1) cannot be greater than total options price (%2). Please set the bundle price equal to or less than the total options price.', $bundlePrice, $totalOptionPrice)
            );
        }
    }
    
}

