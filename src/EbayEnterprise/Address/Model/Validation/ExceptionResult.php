<?php

namespace EbayEnterprise\Address\Model\Validation;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use Exception;

/**
 * Address validation result for when an address could not be validated by the
 * validation service, e.g. due to network error or misconfigured SDK.
 */
class ExceptionResult implements ValidationResultInterface
{
    /** @var AddressInterface */
    protected $originalAddress;
    /** @var Exception */
    protected $failureException;

    /**
     * @param AddressInterface
     * @param Exception
     */
    public function __construct(
        AddressInterface $originalAddress,
        Exception $failureException
    ) {
        $this->originalAddress = $originalAddress;
        $this->failureException = $failureException;
    }

    /**
     * Indicates if the address has been successfully validated. When the
     * request fails, the address could not be determined to be valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return false;
    }

    /**
     * Indicates that the address was either successfully validated or could
     * not be validated at the time and may be accepted. When the service fails
     * allow the address to be accepted.
     *
     * @return bool
     */
    public function isAcceptable()
    {
        return true;
    }

    /**
     * Get the result code of the address validation request. When the service
     * request fails, there will be no result code.
     *
     * @return string
     */
    public function getResultCode()
    {
        return '';
    }

    /**
     * Get the fields on which errors were detected in the request.
     *
     * @TODO Either method needs to be made complient with the return type
     *       or the return type needs to be made complient with this method.
     * @return eBayEnterprise\RetailOrderManagement\Payload\Address\IErrorLocationIterable
     */
    public function getErrorLocations()
    {
        return [];
    }

    /**
     * Provide a user friendly reson for the address validation failure. Will
     * only return a reason if the address validated is invalid. As addresses
     * that cannot be validated are considered acceptable, there will never
     * be a failure reason for the address.
     *
     * @return string|null
     */
    public function getFailureReason()
    {
        return null;
    }

    /**
     * Indicates if suggestions to correct the address are available. When the
     * service can not be reached, there will never be any suggestions.
     *
     * @return bool
     */
    public function hasSuggestions()
    {
        return false;
    }

    /**
     * The number of suggestions available for the validated address. When the
     * service can not be reached, there will never be any suggestions.
     *
     * @return int
     */
    public function getSuggestionCount()
    {
        return 0;
    }

    /**
     * Get any addresses returned as suggested corrections to the address
     * being validated. When the service can not be reached, there cannot
     * be any suggestions. Always return an empty array.
     *
     * @return array Always empty array.
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
     * Get the corrected or normalized address provided by the validation
     * service if available. When the service cannot be reached, the address
     * could not have been corrected so always return null.
     *
     * @return null
     */
    public function getCorrectedAddress()
    {
        return null;
    }
}
