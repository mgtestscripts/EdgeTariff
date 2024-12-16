<?php

namespace EdgeTariff\EstDutyTax\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class GetStateId extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var array
     */
    protected $statesAndRegions = [
    ["code" => "1", "StateId" => "AL", "Name" => "Alabama", "CountryCode" => "US"],
    ["code" => "2", "StateId" => "AK", "Name" => "Alaska", "CountryCode" => "US"],
    ["code" => "3", "StateId" => "AS", "Name" => "American Samoa", "CountryCode" => "US"],
    ["code" => "4", "StateId" => "AZ", "Name" => "Arizona", "CountryCode" => "US"],
    ["code" => "5", "StateId" => "AR", "Name" => "Arkansas", "CountryCode" => "US"],
    ["code" => "6", "StateId" => "AF", "Name" => "Armed Forces Africa", "CountryCode" => "US"],
    ["code" => "7", "StateId" => "AM", "Name" => "Armed Forces Americas", "CountryCode" => "US"],
    ["code" => "8", "StateId" => "CA", "Name" => "Armed Forces Canada", "CountryCode" => "US"],
    ["code" => "9", "StateId" => "EU", "Name" => "Armed Forces Europe", "CountryCode" => "US"],
    ["code" => "10", "StateId" => "ME", "Name" => "Armed Forces Middle East", "CountryCode" => "US"],
    ["code" => "11", "StateId" => "PA", "Name" => "Armed Forces Pacific", "CountryCode" => "US"],
    ["code" => "12", "StateId" => "CA", "Name" => "California", "CountryCode" => "US"],
    ["code" => "13", "StateId" => "CO", "Name" => "Colorado", "CountryCode" => "US"],
    ["code" => "14", "StateId" => "CT", "Name" => "Connecticut", "CountryCode" => "US"],
    ["code" => "15", "StateId" => "DE", "Name" => "Delaware", "CountryCode" => "US"],
    ["code" => "16", "StateId" => "DC", "Name" => "District of Columbia", "CountryCode" => "US"],
    ["code" => "17", "StateId" => "FM", "Name" => "Federated States Of Micronesia", "CountryCode" => "US"],
    ["code" => "18", "StateId" => "FL", "Name" => "Florida", "CountryCode" => "US"],
    ["code" => "19", "StateId" => "GA", "Name" => "Georgia", "CountryCode" => "US"],
    ["code" => "20", "StateId" => "GU", "Name" => "Guam", "CountryCode" => "US"],
    ["code" => "21", "StateId" => "HI", "Name" => "Hawaii", "CountryCode" => "US"],
    ["code" => "22", "StateId" => "ID", "Name" => "Idaho", "CountryCode" => "US"],
    ["code" => "23", "StateId" => "IL", "Name" => "Illinois", "CountryCode" => "US"],
    ["code" => "24", "StateId" => "IN", "Name" => "Indiana", "CountryCode" => "US"],
    ["code" => "25", "StateId" => "IA", "Name" => "Iowa", "CountryCode" => "US"],
    ["code" => "26", "StateId" => "KS", "Name" => "Kansas", "CountryCode" => "US"],
    ["code" => "27", "StateId" => "KY", "Name" => "Kentucky", "CountryCode" => "US"],
    ["code" => "28", "StateId" => "LA", "Name" => "Louisiana", "CountryCode" => "US"],
    ["code" => "29", "StateId" => "ME", "Name" => "Maine", "CountryCode" => "US"],
    ["code" => "30", "StateId" => "MH", "Name" => "Marshall Islands", "CountryCode" => "US"],
    ["code" => "31", "StateId" => "MD", "Name" => "Maryland", "CountryCode" => "US"],
    ["code" => "32", "StateId" => "MA", "Name" => "Massachusetts", "CountryCode" => "US"],
    ["code" => "33", "StateId" => "MI", "Name" => "Michigan", "CountryCode" => "US"],
    ["code" => "34", "StateId" => "MN", "Name" => "Minnesota", "CountryCode" => "US"],
    ["code" => "35", "StateId" => "MS", "Name" => "Mississippi", "CountryCode" => "US"],
    ["code" => "36", "StateId" => "MO", "Name" => "Missouri", "CountryCode" => "US"],
    ["code" => "37", "StateId" => "MT", "Name" => "Montana", "CountryCode" => "US"],
    ["code" => "38", "StateId" => "NE", "Name" => "Nebraska", "CountryCode" => "US"],
    ["code" => "39", "StateId" => "NV", "Name" => "Nevada", "CountryCode" => "US"],
    ["code" => "40", "StateId" => "NH", "Name" => "New Hampshire", "CountryCode" => "US"],
    ["code" => "41", "StateId" => "NJ", "Name" => "New Jersey", "CountryCode" => "US"],
    ["code" => "42", "StateId" => "NM", "Name" => "New Mexico", "CountryCode" => "US"],
    ["code" => "43", "StateId" => "NY", "Name" => "New York", "CountryCode" => "US"],
    ["code" => "44", "StateId" => "NC", "Name" => "North Carolina", "CountryCode" => "US"],
    ["code" => "45", "StateId" => "ND", "Name" => "North Dakota", "CountryCode" => "US"],
    ["code" => "46", "StateId" => "MP", "Name" => "Northern Mariana Islands", "CountryCode" => "US"],
    ["code" => "47", "StateId" => "OH", "Name" => "Ohio", "CountryCode" => "US"],
    ["code" => "48", "StateId" => "OK", "Name" => "Oklahoma", "CountryCode" => "US"],
    ["code" => "49", "StateId" => "OR", "Name" => "Oregon", "CountryCode" => "US"],
    ["code" => "50", "StateId" => "PW", "Name" => "Palau", "CountryCode" => "US"],
    ["code" => "51", "StateId" => "PA", "Name" => "Pennsylvania", "CountryCode" => "US"],
    ["code" => "52", "StateId" => "PR", "Name" => "Puerto Rico", "CountryCode" => "US"],
    ["code" => "53", "StateId" => "RI", "Name" => "Rhode Island", "CountryCode" => "US"],
    ["code" => "54", "StateId" => "SC", "Name" => "South Carolina", "CountryCode" => "US"],
    ["code" => "55", "StateId" => "SD", "Name" => "South Dakota", "CountryCode" => "US"],
    ["code" => "56", "StateId" => "TN", "Name" => "Tennessee", "CountryCode" => "US"],
    ["code" => "57", "StateId" => "TX", "Name" => "Texas", "CountryCode" => "US"],
    ["code" => "58", "StateId" => "UT", "Name" => "Utah", "CountryCode" => "US"],
    ["code" => "59", "StateId" => "VT", "Name" => "Vermont", "CountryCode" => "US"],
    ["code" => "60", "StateId" => "VI", "Name" => "Virgin Islands", "CountryCode" => "US"],
    ["code" => "61", "StateId" => "VA", "Name" => "Virginia", "CountryCode" => "US"],
    ["code" => "62", "StateId" => "WA", "Name" => "Washington", "CountryCode" => "US"],
    ["code" => "63", "StateId" => "WV", "Name" => "West Virginia", "CountryCode" => "US"],
    ["code" => "64", "StateId" => "WI", "Name" => "Wisconsin", "CountryCode" => "US"],
    ["code" => "65", "StateId" => "WY", "Name" => "Wyoming", "CountryCode" => "US"],
    ["code" => "569", "StateId" => "AN", "Name" => "Andaman and Nicobar Islands", "CountryCode" => "IN"],
    ["code" => "570", "StateId" => "AP", "Name" => "Andhra Pradesh", "CountryCode" => "IN"],
    ["code" => "571", "StateId" => "AR", "Name" => "Arunachal Pradesh", "CountryCode" => "IN"],
    ["code" => "572", "StateId" => "AS", "Name" => "Assam", "CountryCode" => "IN"],
    ["code" => "573", "StateId" => "BR", "Name" => "Bihar", "CountryCode" => "IN"],
    ["code" => "574", "StateId" => "CH", "Name" => "Chandigarh", "CountryCode" => "IN"],
    ["code" => "575", "StateId" => "CG", "Name" => "Chhattisgarh", "CountryCode" => "IN"],
    ["code" => "576", "StateId" => "DN", "Name" => "Dadra and Nagar Haveli", "CountryCode" => "IN"],
    ["code" => "577", "StateId" => "DD", "Name" => "Daman and Diu", "CountryCode" => "IN"],
    ["code" => "578", "StateId" => "DL", "Name" => "Delhi", "CountryCode" => "IN"],
    ["code" => "579", "StateId" => "GA", "Name" => "Goa", "CountryCode" => "IN"],
    ["code" => "580", "StateId" => "GJ", "Name" => "Gujarat", "CountryCode" => "IN"],
    ["code" => "581", "StateId" => "HR", "Name" => "Haryana", "CountryCode" => "IN"],
    ["code" => "582", "StateId" => "HP", "Name" => "Himachal Pradesh", "CountryCode" => "IN"],
    ["code" => "583", "StateId" => "JK", "Name" => "Jammu and Kashmir", "CountryCode" => "IN"],
    ["code" => "584", "StateId" => "JH", "Name" => "Jharkhand", "CountryCode" => "IN"],
    ["code" => "585", "StateId" => "KA", "Name" => "Karnataka", "CountryCode" => "IN"],
    ["code" => "586", "StateId" => "KL", "Name" => "Kerala", "CountryCode" => "IN"],
    ["code" => "1156", "StateId" => "LA", "Name" => "Ladakh", "CountryCode" => "IN"],
    ["code" => "587", "StateId" => "LD", "Name" => "Lakshadweep", "CountryCode" => "IN"],
    ["code" => "588", "StateId" => "MP", "Name" => "Madhya Pradesh", "CountryCode" => "IN"],
    ["code" => "589", "StateId" => "MH", "Name" => "Maharashtra", "CountryCode" => "IN"],
    ["code" => "590", "StateId" => "MN", "Name" => "Manipur", "CountryCode" => "IN"],
    ["code" => "591", "StateId" => "ML", "Name" => "Meghalaya", "CountryCode" => "IN"],
    ["code" => "592", "StateId" => "MZ", "Name" => "Mizoram", "CountryCode" => "IN"],
    ["code" => "593", "StateId" => "NL", "Name" => "Nagaland", "CountryCode" => "IN"],
    ["code" => "594", "StateId" => "OR", "Name" => "Odisha", "CountryCode" => "IN"],
    ["code" => "595", "StateId" => "PY", "Name" => "Puducherry", "CountryCode" => "IN"],
    ["code" => "596", "StateId" => "PB", "Name" => "Punjab", "CountryCode" => "IN"],
    ["code" => "597", "StateId" => "RJ", "Name" => "Rajasthan", "CountryCode" => "IN"],
    ["code" => "598", "StateId" => "SK", "Name" => "Sikkim", "CountryCode" => "IN"],
    ["code" => "599", "StateId" => "TN", "Name" => "Tamil Nadu", "CountryCode" => "IN"],
    ["code" => "600", "StateId" => "TG", "Name" => "Telangana", "CountryCode" => "IN"],
    ["code" => "601", "StateId" => "TR", "Name" => "Tripura", "CountryCode" => "IN"],
    ["code" => "602", "StateId" => "UP", "Name" => "Uttar Pradesh", "CountryCode" => "IN"],
    ["code" => "603", "StateId" => "UT", "Name" => "Uttarakhand", "CountryCode" => "IN"],
    ["code" => "604", "StateId" => "WB", "Name" => "West Bengal", "CountryCode" => "IN"],
    ["code" => "942", "StateId" => "AG", "Name" => "Aguascalientes", "CountryCode" => "MX"],
    ["code" => "943", "StateId" => "BC", "Name" => "Baja California", "CountryCode" => "MX"],
    ["code" => "944", "StateId" => "BS", "Name" => "Baja California Sur", "CountryCode" => "MX"],
    ["code" => "945", "StateId" => "CM", "Name" => "Campeche", "CountryCode" => "MX"],
    ["code" => "946", "StateId" => "CH", "Name" => "Chiapas", "CountryCode" => "MX"],
    ["code" => "947", "StateId" => "CI", "Name" => "Chihuahua", "CountryCode" => "MX"],
    ["code" => "948", "StateId" => "CMX", "Name" => "Ciudad de México", "CountryCode" => "MX"],
    ["code" => "949", "StateId" => "CO", "Name" => "Coahuila", "CountryCode" => "MX"],
    ["code" => "950", "StateId" => "CL", "Name" => "Colima", "CountryCode" => "MX"],
    ["code" => "951", "StateId" => "DU", "Name" => "Durango", "CountryCode" => "MX"],
    ["code" => "952", "StateId" => "EM", "Name" => "Estado de México", "CountryCode" => "MX"],
    ["code" => "953", "StateId" => "GT", "Name" => "Guanajuato", "CountryCode" => "MX"],
    ["code" => "954", "StateId" => "GR", "Name" => "Guerrero", "CountryCode" => "MX"],
    ["code" => "955", "StateId" => "HI", "Name" => "Hidalgo", "CountryCode" => "MX"],
    ["code" => "956", "StateId" => "JA", "Name" => "Jalisco", "CountryCode" => "MX"],
    ["code" => "957", "StateId" => "MI", "Name" => "Michoacán", "CountryCode" => "MX"],
    ["code" => "958", "StateId" => "MO", "Name" => "Morelos", "CountryCode" => "MX"],
    ["code" => "959", "StateId" => "NA", "Name" => "Nayarit", "CountryCode" => "MX"],
    ["code" => "960", "StateId" => "NL", "Name" => "Nuevo León", "CountryCode" => "MX"],
    ["code" => "961", "StateId" => "OA", "Name" => "Oaxaca", "CountryCode" => "MX"],
    ["code" => "962", "StateId" => "PU", "Name" => "Puebla", "CountryCode" => "MX"],
    ["code" => "963", "StateId" => "QE", "Name" => "Querétaro", "CountryCode" => "MX"],
    ["code" => "964", "StateId" => "QR", "Name" => "Quintana Roo", "CountryCode" => "MX"],
    ["code" => "965", "StateId" => "SL", "Name" => "San Luis Potosí", "CountryCode" => "MX"],
    ["code" => "966", "StateId" => "SI", "Name" => "Sinaloa", "CountryCode" => "MX"],
    ["code" => "967", "StateId" => "SO", "Name" => "Sonora", "CountryCode" => "MX"],
    ["code" => "968", "StateId" => "TB", "Name" => "Tabasco", "CountryCode" => "MX"],
    ["code" => "969", "StateId" => "TL", "Name" => "Tlaxcala", "CountryCode" => "MX"],
    ["code" => "970", "StateId" => "TM", "Name" => "Tamaulipas", "CountryCode" => "MX"],
    ["code" => "971", "StateId" => "VE", "Name" => "Veracruz", "CountryCode" => "MX"],
    ["code" => "972", "StateId" => "YU", "Name" => "Yucatán", "CountryCode" => "MX"],
    ["code" => "973", "StateId" => "ZA", "Name" => "Zacatecas", "CountryCode" => "MX"],
    ["code" => "485", "StateId" => "AC", "Name" => "Acre", "CountryCode" => "BR"],
    ["code" => "486", "StateId" => "AL", "Name" => "Alagoas", "CountryCode" => "BR"],
    ["code" => "487", "StateId" => "AP", "Name" => "Amapá", "CountryCode" => "BR"],
    ["code" => "488", "StateId" => "AM", "Name" => "Amazonas", "CountryCode" => "BR"],
    ["code" => "489", "StateId" => "BA", "Name" => "Bahia", "CountryCode" => "BR"],
    ["code" => "490", "StateId" => "CE", "Name" => "Ceará", "CountryCode" => "BR"],
    ["code" => "511", "StateId" => "DF", "Name" => "Distrito Federal", "CountryCode" => "BR"],
    ["code" => "491", "StateId" => "ES", "Name" => "Espírito Santo", "CountryCode" => "BR"],
    ["code" => "492", "StateId" => "GO", "Name" => "Goiás", "CountryCode" => "BR"],
    ["code" => "493", "StateId" => "MA", "Name" => "Maranhão", "CountryCode" => "BR"],
    ["code" => "494", "StateId" => "MT", "Name" => "Mato Grosso", "CountryCode" => "BR"],
    ["code" => "495", "StateId" => "MS", "Name" => "Mato Grosso do Sul", "CountryCode" => "BR"],
    ["code" => "496", "StateId" => "MG", "Name" => "Minas Gerais", "CountryCode" => "BR"],
    ["code" => "497", "StateId" => "PA", "Name" => "Pará", "CountryCode" => "BR"],
    ["code" => "498", "StateId" => "PB", "Name" => "Paraíba", "CountryCode" => "BR"],
    ["code" => "499", "StateId" => "PR", "Name" => "Paraná", "CountryCode" => "BR"],
    ["code" => "500", "StateId" => "PE", "Name" => "Pernambuco", "CountryCode" => "BR"],
    ["code" => "501", "StateId" => "PI", "Name" => "Piauí", "CountryCode" => "BR"],
    ["code" => "502", "StateId" => "RJ", "Name" => "Rio de Janeiro", "CountryCode" => "BR"],
    ["code" => "503", "StateId" => "RN", "Name" => "Rio Grande do Norte", "CountryCode" => "BR"],
    ["code" => "504", "StateId" => "RS", "Name" => "Rio Grande do Sul", "CountryCode" => "BR"],
    ["code" => "505", "StateId" => "RO", "Name" => "Rondônia", "CountryCode" => "BR"],
    ["code" => "506", "StateId" => "RR", "Name" => "Roraima", "CountryCode" => "BR"],
    ["code" => "507", "StateId" => "SC", "Name" => "Santa Catarina", "CountryCode" => "BR"],
    ["code" => "508", "StateId" => "SP", "Name" => "São Paulo", "CountryCode" => "BR"],
    ["code" => "509", "StateId" => "SE", "Name" => "Sergipe", "CountryCode" => "BR"],
    ["code" => "510", "StateId" => "TO", "Name" => "Tocantins", "CountryCode" => "BR"],
    ["code" => "66", "StateId" => "AB", "Name" => "Alberta", "CountryCode" => "CA"],
    ["code" => "67", "StateId" => "BC", "Name" => "British Columbia", "CountryCode" => "CA"],
    ["code" => "68", "StateId" => "MB", "Name" => "Manitoba", "CountryCode" => "CA"],
    ["code" => "70", "StateId" => "NB", "Name" => "New Brunswick", "CountryCode" => "CA"],
    ["code" => "69", "StateId" => "NL", "Name" => "Newfoundland and Labrador", "CountryCode" => "CA"],
    ["code" => "72", "StateId" => "NT", "Name" => "Northwest Territories", "CountryCode" => "CA"],
    ["code" => "71", "StateId" => "NS", "Name" => "Nova Scotia", "CountryCode" => "CA"],
    ["code" => "73", "StateId" => "NU", "Name" => "Nunavut", "CountryCode" => "CA"],
    ["code" => "74", "StateId" => "ON", "Name" => "Ontario", "CountryCode" => "CA"],
    ["code" => "75", "StateId" => "PE", "Name" => "Prince Edward Island", "CountryCode" => "CA"],
    ["code" => "76", "StateId" => "QC", "Name" => "Quebec", "CountryCode" => "CA"],
    ["code" => "77", "StateId" => "SK", "Name" => "Saskatchewan", "CountryCode" => "CA"],
    ["code" => "78", "StateId" => "YT", "Name" => "Yukon Territory", "CountryCode" => "CA"]
    ];
 
    /**
     * GetStateId constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, JsonFactory $resultJsonFactory)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute the action to get state ID.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        $name = $this->getRequest()->getParam('Name');

        $stateId = null;

        // Search by code
        if ($code && is_numeric($code)) {
            foreach ($this->statesAndRegions as $state) {
                if ($state['code'] == $code) {
                    $stateId = $state['StateId'];
                    break;
                }
            }
        }

        // Search by name
        if ($name) {
            foreach ($this->statesAndRegions as $state) {
                if ($state['Name'] == $name) {
                    $stateId = $state['StateId'];
                    break;
                }
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['state_id' => $stateId]);
    }
}
