<?php
namespace EdgeTariff\EstDutyTax\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CustomShipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'EdgeTariffEstDutyTax';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var string JSON encoded dynamic shipping rates
     */
    public $_rates = '';

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param SessionManagerInterface $coreSession
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        SessionManagerInterface $coreSession,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->coreSession = $coreSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Set dynamic shipping rates as a JSON encoded string.
     *
     * @param array $rates
     * @return void
     */
    public function setDynamicRates(array $rates)
    {
        $this->_rates = json_encode($rates);
    }

    /**
     * Retrieve allowed shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['EdgeTariffEstDutyTax' => $this->getConfigData('name')];
    }

    /**
     * Collect available shipping rates based on the rate request.
     *
     * This method pulls dynamic rates stored in the session and creates
     * shipping methods with the given rate information.
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        
        // Check if the shipping method is active
        if (!$this->isActive()) {
            return false;
        }

        // Initialize the result object for storing rate methods
        $result = $this->_rateResultFactory->create();

        // Retrieve the dynamic shipping rates from session
        $data = $this->coreSession->getCustomShippingRates();
        
        $i = 1; // Counter for method values

        // Check if rates are available in session and iterate over them
        if (isset($data['rates']) && !empty($data['rates'])) {
            foreach ($data['rates'] as $record) {
                $totalPrice = $record['total_price'];
                $amount = $totalPrice / 100; // Convert to a numeric value
                $MethodTitle = $record['description'];

                // Check if 'General Sales Tax' exists in the carrier title and replace it with 'GST/VAT'
                if (strpos($MethodTitle, 'General Sales Tax') !== false) {
                    $MethodTitle = str_replace('General Sales Tax', 'GST/VAT', $MethodTitle);
                }

                // Create a new shipping method
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($record['service_name']);
                $method->setMethod($i);
                $method->setMethodTitle($MethodTitle);

                // Calculate the final shipping price with handling fee
                $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
                $method->setPrice($shippingPrice);
                $method->setCost($amount);

                // Append the method to the result object
                $result->append($method);
                $i++;
            }
        }

        return $result;
    }
}
