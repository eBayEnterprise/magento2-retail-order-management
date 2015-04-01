<?php

namespace EbayEnterprise\Address\Model\Service;

use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterfaceFactory;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use EbayEnterprise\Address\Model\HttpApiFactory;
use EbayEnterprise\Address\Model\Session as AddressSession;
use eBayEnterprise\RetailOrderManagement\Api\Exception\NetworkError;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedHttpAction;
use eBayEnterprise\RetailOrderManagement\Api\Exception\UnsupportedOperation;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        ValidationResultInterfaceFactory $exceptionResultFactory,
        AddressSession $session
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->httpApiFactory = $httpApiFactory;
        $this->resultFactory = $resultFactory;
        $this->exceptionResultFactory = $exceptionResultFactory;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(AddressInterface $address)
    {
        return $this->getStashedResultForAddress($address) ?: $this->makeRequestForAddress($address);
    }

    /**
     * Make a new request via the SDK to validate the address.
     *
     * @param AddressInterface
     * @return IValidationResult
     */
    protected function makeRequestForAddress(AddressInterface $address)
    {
        try {
            /** @var \eBayEnterprise\RetailOrderManagement\Api\IBidirectionalApi */
            $api = $this->httpApiFactory->create($this->scopeConfig);
            /** @var \eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply */
            $response = $api
                ->setRequestBody($this->sdkHelper->prepareSdkRequest($api->getRequestBody(), $address, $this->scopeConfig))
                ->send()
                ->getResponseBody();
        // @TODO evaluate log level for each of these, some may be more severe -
        //       UnsupportedOperation and UnsupportedHttpAction - or less severe
        //       - maybe NetworkError.
        } catch (NetworkError $e) {
            $this->logger->warning($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        } catch (UnsupportedOperation $e) {
            $this->logger->error($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        } catch (UnsupportedHttpAction $e) {
            $this->logger->error($e);
            return $this->exceptionResultFactory->create(['originalAddress' => $address, 'failureException' => $e]);
        }
        $result = $this->resultFactory->create(['replyPayload' => $response, 'originalAddress' => $address]);
        $this->session->setResultForAddress($address, $result)->setCurrentResult($result);
        return $result;
    }

    /**
     * Check the session for an existing result for the address.
     *
     * @param AddressInterface
     * @return IValidationResultInterface|null
     */
    protected function getStashedResultForAddress(AddressInterface $address)
    {
        $stashedResult = $this->session->getResultForAddress($address);
        if ($stashedResult) {
            $this->session->setCurrentResult($stashedResult);
        }
        return $stashedResult;
    }
}
