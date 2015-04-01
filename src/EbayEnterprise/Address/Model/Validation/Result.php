<?php

namespace EbayEnterprise\Address\Model\Validation;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\AddressInterfaceFactory;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use EbayEnterprise\Address\Helper\Sdk as SdkHelper;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply;

class Result implements ValidationResultInterface
{
    /** @var string */
    protected $id;
    /** @var AddressInterface */
    protected $originalAddress;
    /** @var bool */
    protected $isValid;
    /** @var bool */
    protected $isAcceptable;
    /** @var string */
    protected $resultCode;
    /** @var array */
    protected $errorLocations;
    /** @var bool */
    protected $hasSuggestions;
    /** @var AddressInterface[] */
    protected $suggestions;
    /** @var int */
    protected $suggestionCount;
    /** @var AddressInterface */
    protected $correctedAddress;

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
        $this->id = uniqid('AVR-');
        $this->originalAddress = $originalAddress;
        // Extract data from the payload so the payload instance doesn't need
        // to be stored (may not be session safe and this object may need
        // to go into the session).
        $this->isValid = $replyPayload->isValid();
        $this->isAcceptable = $replyPayload->isAcceptable();
        $this->resultCode = $replyPayload->getResultCode();
        $this->errorLocations = [];
        foreach ($replyPayload->getErrorLocations() as $errorLocation) {
            $this->errorLocations[] = $errorLocation->getFieldName();
        }
        $this->hasSuggestions = $replyPayload->hasSuggestions();
        $this->suggestions = [];
        foreach ($replyPayload->getSuggestedAddresses() as $suggestedAddress) {
            $this->suggestions[uniqid('AVS-')] = $sdkHelper->transferPhysicalAddressPayloadToAddress(
                $suggestedAddress,
                $addressFactory->create()
            );
        }
        $this->suggestionCount = $replyPayload->getResultSuggestionCount();
        $this->correctedAddress = $sdkHelper->transferPhysicalAddressPayloadToAddress(
            $replyPayload,
            $addressFactory->create()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * {@inheritDoc}
     */
    public function isAcceptable()
    {
        return $this->isAcceptable;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorLocations()
    {
        return $this->errorLocations;
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
        return $this->hasSuggestions;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestionCount()
    {
        return $this->suggestionCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestions()
    {
        return $this->suggestions;
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
        return $this->correctedAddress;
    }
}
