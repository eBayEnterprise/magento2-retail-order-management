<?php

namespace EbayEnterprise\Address\Model\Service;

use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use EbayEnterprise\Address\Model\HttpApiFactory;
use EbayEnterprise\Address\Model\ResultFactory;
use EbayEnterprise\Address\Api\QuoteAddressValidationInterface;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class AddressValidation implements AddressValidationInterface
{
    /** @var \EbayEnterprise\Address\Helper\Sdk */
    protected $sdkHelper;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;
    /** @var \EbayEnterprise\Address\Model\HttpApiFactory */
    protected $httpApiFactory;
    /** @var \EbayEnterprise\Address\Model\ResultFactory */
    protected $resultFactory ;

    /**
     * @param \EbayEnterprise\Address\Helper\Sdk $sdkHelper,
     * @param \Psr\Log\LoggerInterface $logger,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     * @param \EbayEnterprise\Address\Model\HttpApiFactory $httpApiFactory,
     * @param \EbayEnterprise\Address\Model\ResultFactory $resultFactory
     */
    public function __construct(
        SdkHelper $sdkHelper,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        HttpApiFactory $httpApiFactory,
        ResultFactory $resultFactory
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
    public function validate(\EbayEnterprise\Address\Api\Data\AddressInterface $address)
    {
        $api = $this->httpApiFactory->create($this->scopeConfig);
        $req = $this->sdkHelper->transferAddressToPhysicalAddressPayload(
            $address->getDataModel(),
            $api->getRequestBody()
        );
        $req->setMaxSuggestions($this->scopeConfig->getValue('ebay_enterprise/address_validation/max_suggestions'));
        $api->setRequestBody($req)->send();
        return $this->resultFactory->create(['replyPayload' => $api->getResponseBody()]);
    }
}