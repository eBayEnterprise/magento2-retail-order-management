<?php

namespace EbayEnterprise\Address\Model\Service;

use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationRequest;
use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterfaceFactory;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use EbayEnterprise\Address\Model\HttpApiFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class AddressValidation implements AddressValidationInterface
{
    /** @var SdkHelper */
    protected $sdkHelper;
    /** @var LoggerInterface */
    protected $logger;
    /** @var ScopeConfigInterface */
    protected $scopeConfig;
    /** @var HttpApiFactory */
    protected $httpApiFactory;
    /** @var ValidationResultInterface */
    protected $resultFactory ;

    /**
     * @param SdkHelper $sdkHelper,
     * @param LoggerInterface $logger,
     * @param ScopeConfigInterface $scopeConfig,
     * @param HttpApiFactory $httpApiFactory,
     * @param ValidationResultInterfaceFactory $resultFactory
     */
    public function __construct(
        SdkHelper $sdkHelper,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        HttpApiFactory $httpApiFactory,
        ValidationResultInterfaceFactory $resultFactory
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->httpApiFactory = $httpApiFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(AddressInterface $address)
    {
        $api = $this->httpApiFactory->create($this->scopeConfig);
        $api->setRequestBody($this->prepareSdkRequest($api->getRequestBody(), $address))
            ->send();
        return $this->resultFactory->create(['replyPayload' => $api->getResponseBody(), 'originalAddress' => $address]);
    }

    /**
     * Prepare a validation request payload, setting the address and config
     * data on the payload.
     *
     * @param IValidationRequest
     * @param AddressInterface
     * @return IValidationRequest
     */
    protected function prepareSdkRequest(IValidationRequest $request, AddressInterface $address)
    {
        return $this->sdkHelper
            ->transferAddressToPhysicalAddressPayload($address, $request)
            ->setMaxSuggestions($this->scopeConfig->getValue('ebay_enterprise/address_validation/max_suggestions'));
    }
}
