<?php

namespace EbayEnterprise\Address\Model\Validation;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\AddressInterfaceFactory;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply;

class Result implements ValidationResultInterface
{
    /** @var SdkHelper */
    protected $sdkHelper;
    /** @var IValidationReply */
    protected $replyPayload;
    /** @var AddressInterfaceFactory */
    protected $addressFactory;
    /** @var AddressInterface */
    protected $originalAddress;

    /**
     * @param SdkHelper
     * @param IValidationReply
     * @param AddressInterfaceFactory
     * @param AddressInterface
     */
    public function __construct(
        SdkHelper $sdkHelper,
        IValidationReply $replyPayload,
        AddressInterfaceFactory $addressFactory,
        AddressInterface $originalAddress
    ) {
        $this->sdkHelper = $sdkHelper;
        $this->replyPayload = $replyPayload;
        $this->addressFactory = $addressFactory;
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
    public function getResultCode()
    {
        return $this->replyPayload->getResultCode();
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
    public function getFailureReason()
    {
        // @TODO provide better failure message based on validation results.
        return !$this->isAcceptable() ? 'Invalid address.' : null;
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
        $addresses = [];
        foreach ($this->replyPayload->getSuggestedAddresses() as $suggestedAddress) {
            $addresses[] = $this->sdkHelper->transferPhysicalAddressPayloadToAddress(
                $suggestedAddress,
                $this->addressFactory->create()
            );
        }
        return $addresses;
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
            $this->addressFactory->create()
        );
    }
}
