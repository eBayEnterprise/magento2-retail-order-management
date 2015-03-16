<?php

namespace EbayEnterprise\Address\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class HttpApiFactory
{
    const HTTP_API = '\eBayEnterprise\RetailOrderManagement\Api\HttpApi';
    const HTTP_API_CONFIG = '\eBayEnterprise\RetailOrderManagement\Api\HttpConfig';

    /** @var ObjectManagerInterface */
    protected $objectManager;
    /** @var LoggerInterface */
    protected $logger;
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * @param ObjectManagerInterface
     * @param LoggerInterface
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Create an API object, configured using the provided scope configuration.
     *
     * @param ScopeConfigInterface
     * @param array
     * @return \eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi
     */
    public function create(ScopeConfigInterface $scopeConfig = null, $args = [])
    {
        $scopeConfig = $scopeConfig ?: $this->scopeConfig;
        $apiConfig = $this->createConfig($scopeConfig);
        return $this->objectManager->create(self::HTTP_API, array_merge(['config' => $apiConfig, 'logger' => $this->logger], $args));
    }

    /**
     * Create a config object to configure the API.
     *
     * @param ScopeConfigInterface
     * @return \eBayEnterprise\RetailOrderManagement\Api\IHttpConfig
     */
    protected function createConfig(ScopeConfigInterface $scopeConfig)
    {
        return $this->objectManager->create(
            self::HTTP_API_CONFIG,
            [
                'apiKey' => $scopeConfig->getValue('ebay_enterprise/web_services/api_key'),
                'host' => $scopeConfig->getValue('ebay_enterprise/web_services/hostname'),
                'majorVersion' => $scopeConfig->getValue('ebay_enterprise/web_services/major_version'),
                'minorVersion' => $scopeConfig->getValue('ebay_enterprise/web_services/minor_version'),
                'storeId' => $scopeConfig->getValue('ebay_enterprise/general/store_id'),
                'service' => $scopeConfig->getValue('ebay_enterprise/address_validation/service'),
                'operation' => $scopeConfig->getValue('ebay_enterprise/address_validation/operation'),
                'endpointParams' => [],
                'logger' => $this->logger,
            ]
        );
    }
}
