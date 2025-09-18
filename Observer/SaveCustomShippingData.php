<?php

namespace EdgeTariff\EstDutyTax\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SaveCustomShippingData implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();

        $shippingAddress = $quote->getShippingAddress();
        $orderShippingAddress = $order->getShippingAddress();

        if ($shippingAddress && $orderShippingAddress) {
            $orderShippingAddress->setNoPackages($shippingAddress->getNoPackages());
            $orderShippingAddress->setPackingRuleName($shippingAddress->getPackingRuleName());
            $orderShippingAddress->setPackingDimensions($shippingAddress->getPackingDimensions());
            $orderShippingAddress->setAddressType($shippingAddress->getAddressType());
        }
    }
}
