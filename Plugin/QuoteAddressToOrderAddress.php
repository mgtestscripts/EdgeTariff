<?php
namespace EdgeTariff\EstDutyTax\Plugin;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

class QuoteAddressToOrderAddress
{
    public function afterConvert(
        ToOrderAddress $subject,
        OrderAddress $result,
        QuoteAddress $quoteAddress,
        array $data = []
    ) {
        $result->setData('no_packages', $quoteAddress->getData('no_packages'));
        $result->setData('packing_rule_name', $quoteAddress->getData('packing_rule_name'));
        $result->setData('packing_dimensions', $quoteAddress->getData('packing_dimensions'));
        $result->setData('address_type_custom', $quoteAddress->getData('address_type_custom'));

        return $result;
    }
}
