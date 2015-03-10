<?php

namespace EbayEnterprise\Address\Model\Plugin;

use eBayEnterprise\RetailOrderManagement\Api\HttpApi;
use eBayEnterprise\RetailOrderManagement\Api\HttpConfig;
use EbayEnterprise\Address\Helper\Sdk;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class Validator
{
    protected $sdkHelper;
    protected $scopeConfig;
    protected $logger;

    public function __construct(
        Sdk $sdkHelper,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Plugin method to perform an address validation service request.
     *
     * @param AbstractAddress
     * @param array|bool Array of already found errors or "true" if address is valid
     * @return array|bool Array of errors found or "true" if address is valid
     */
    public function afterValidate(AbstractAddress $address, $result)
    {
        $this->logger->warning('validating address');
        if ($result === true) {
            return $this->validateAddress($address);
        }
        return $result;
    }

    protected function validateAddress(AbstractAddress $address)
    {
        $httpConfig = new HttpConfig(
            $this->scopeConfig->getValue('ebay_enterprise/web_services/api_key'),
            $this->scopeConfig->getValue('ebay_enterprise/web_services/hostname'),
            $this->scopeConfig->getValue('ebay_enterprise/web_services/major_version'),
            $this->scopeConfig->getValue('ebay_enterprise/web_services/minor_version'),
            $this->scopeConfig->getValue('ebay_enterprise/general/store_id'),
            $this->scopeConfig->getValue('ebay_enterprise/address_validation/service'),
            $this->scopeConfig->getValue('ebay_enterprise/address_validation/operation'),
            [],
            $this->logger
        );
        $api = new HttpApi($httpConfig);
        $req = $this->sdkHelper->transferAddressToPhysicalAddressPayload(
            $address->getDataModel(),
            $api->getRequestBody()
        );
        $req->setMaxSuggestions($this->scopeConfig->getValue('ebay_enterprise/address_validation/max_suggestions'));
        $api->setRequestBody($req)->send();
        $resp = $api->getResponseBody();
        return $resp->isAcceptable() ? true : ['Address is invalid'];
    }
}
