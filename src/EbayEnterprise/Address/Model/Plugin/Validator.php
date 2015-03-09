<?php

namespace EbayEnterprise\Address\Model\Plugin;

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
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
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
        if ($result === true) {

        }
        return $result;
    }
}
