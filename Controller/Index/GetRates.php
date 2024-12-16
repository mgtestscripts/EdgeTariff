<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use EdgeTariff\EstDutyTax\Model\Carrier\CustomShipping;
use Magento\Framework\Controller\Result\JsonFactory;

class GetRates extends Action
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CustomShipping
     */
    protected $customShipping;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * GetRates constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param CustomShipping $customShipping
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        CustomShipping $customShipping,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->customShipping = $customShipping;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute the action to get shipping rates.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $cartId = $this->checkoutSession->getQuoteId();
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $shippingAddress = $quote->getShippingAddress();

            $rateRequest = new RateRequest();
            $rateRequest->setDestinationAddress($shippingAddress);
            $rateRequest->setPackageValue($quote->getGrandTotal());
            $rateRequest->setPackageValueWithDiscount($quote->getGrandTotal());
            $rateRequest->setPackageWeight($shippingAddress->getWeight());

            $result = $this->customShipping->collectRates($rateRequest);

            $rateMethods = [];
            if ($result) {
                foreach ($result->getAllRates() as $rate) {

                    // $MethodTitle = $rate->getMethodTitle();

                    // // Check if 'General Sales Tax' exists in the carrier title and replace it with 'GST/VAT'
                    // if (strpos($MethodTitle, 'General Sales Tax') !== false) {
                    //     $MethodTitle = str_replace('General Sales Tax', 'GST/VAT', $MethodTitle);

                    //     // Assign the modified carrier title back to the rate object
                    //     $rate->setMethodTitle($MethodTitle);
                    // }
                    $rateMethods[] = [
                        'carrier_code' => $rate->getCarrier(),
                        'method_code' => (string)$rate->getMethod(),
                        'carrier_title' => $rate->getCarrierTitle() ?: '',
                        'method_title' =>  $rate->getMethodTitle() ?: '',
                        'amount' => $rate->getPrice(),
                        'base_amount' => $rate->getPrice(),
                        'available' => true,
                        'error_message' => '',
                        'price_excl_tax' => $rate->getPrice(),
                        'price_incl_tax' => $rate->getCost() ?: $rate->getPrice(),
                    ];
                }
            }

            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($rateMethods);

        } catch (\Exception $e) {
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['error' => __('Unable to fetch shipping rates')]);
        }
    }
}
