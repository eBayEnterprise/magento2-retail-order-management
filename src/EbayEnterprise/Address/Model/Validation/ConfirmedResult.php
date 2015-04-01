<?php

namespace EbayEnterprise\Address\Model\Validation;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationReply;

/**
 * Address validation result for when an address has been confirmed by the
 * address validation service. Constists of mostly static responses indicating
 * that the address is valid.
 */
class ConfirmedResult implements ValidationResultInterface
{
    /** @var string */
    protected $id;
    /** @var AddressInterface */
    protected $originalAddress;

    /**
     * @param AddressInterface
     */
    public function __construct(
        AddressInterface $originalAddress
    ) {
        $this->id = uniqid('AVCR-');
        $this->originalAddress = $originalAddress;
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
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAcceptable()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultCode()
    {
        return IValidationReply::RESULT_VALID;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorLocations()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getFailureReason()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasSuggestions()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestionCount()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestions()
    {
        return [];
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
        return $this->originalAddress;
    }
}
