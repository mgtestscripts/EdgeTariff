<?php
namespace EdgeTariff\EstDutyTax\Api;

interface DeliveryManagementInterface
{
    /**
     * Update custom delivery checkbox
     * @param string $value
     * @return string
     */
    public function updateCustomCheckbox($value);
}
