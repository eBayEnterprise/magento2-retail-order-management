<?php

namespace EbayEnterprise\Address\Model\Service;

use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterfaceFactory;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use EbayEnterprise\Address\Model\HttpApiFactory;
use eBayEnterprise\RetailOrderManagement\Api\Exception\NetworkError;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedHttpAction;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedOperation;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationRequest;
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
    /** @var ValidationResultInterface */
    protected $exceptionResultFactory ;

    /**
     * @param SdkHelper $sdkHelper,
     * @param LoggerInterface $logger,
     * @param ScopeConfigInterface $scopeConfig,
     * @param HttpApiFactory $httpApiFactory,
     * @param ValidationResultInterfaceFactory $resultFactory
     * @param ValidationResultInterfaceFactory $exceptionResultFactory
     */
    public function __construct(
        SdkHelper $sdkHelper,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        HttpApiFactory $httpApiFactory,
        ValidationResultInterfaceFactory $resultFactory,
        ValidationResultInterfaceFactory $exceptionResultFactory
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->httpApiFactory = $httpApiFactory;
        $this->resultFactory = $resultFactory;
        $this->exceptionResultFactory = $exceptionResultFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(AddressInterface $address)
    {
        try {
            /** @var \eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi */
            $api = $this->httpApiFactory->create($this->scopeConfig);
            /** @var \eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply */
            $response = $api
                ->setRequestBody($this->sdkHelper->prepareSdkRequest($api->getRequestBody(), $address, $this->scopeConfig))
                ->send()
                ->getResponseBody();
        } catch (NetworkError $e) {
            $this->logger->warning($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        } catch (UnsupportedOperation $e) {
            $this->logger->warning($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        } catch (UnsupportedHttpAction $e) {
            $this->logger->warning($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        }
        return $this->resultFactory->create(['replyPayload' => $response, 'originalAddress' => $address]);
    }
}
