<?php

namespace EbayEnterprise\Address\Model\Validation;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\AddressInterfaceBuilderFactory;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply;

class Result implements ValidationResultInterface
{
    /** @var SdkHelper */
    protected $sdkHelper;
    /** @var IValidationReply */
    protected $replyPayload;
    /** @var AddressInterfaceBuilderFactory */
    protected $addressBuilderFactory;
    /** @var AddressInterface */
    protected $originalAddress;

    /**
     * @param SdkHelper
     * @param IValidationReply
     * @param AddressInterfaceBuilderFactory
     * @param AddressInterface
     */
    public function __construct(
        SdkHelper $sdkHelper,
        IValidationReply $replyPayload,
        AddressInterfaceBuilderFactory $addressBuilderFactory,
        AddressInterface $originalAddress
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->replyPayload = $replyPayload;
        $this->addressBuilderFactory = $addressBuilderFactory;
        $this->originalAddress = $originalAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        return $this->replyPayload->isValid();
    }

    /**
     * {@inheritDoc}
     */
    public function isAcceptable()
    {
        return $this->replyPayload->isAcceptable();
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorLocations()
    {
        return $this->replyPayload->getResultErrorLocations();
    }

    /**
     * {@inheritDoc}
     */
    public function hasSuggestions()
    {
        return $this->replyPayload->hasSuggestions();
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestionCount()
    {
        return $this->replyPayload->getResultSuggestionCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestions()
    {
        foreach ($this->replyPayload->getSuggestedAddresses() as $suggestedAddress) {
            yield $suggestedAddress => $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
                $suggestedAddress,
                $this->addressBuilderFactory->create()
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalAddress()
    {
        return $this->originalAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function getCorrectedAddress()
    {
        return $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
            $this->replyPayload,
            $this->addressBuilderFactory->create()
        );
    }
}
