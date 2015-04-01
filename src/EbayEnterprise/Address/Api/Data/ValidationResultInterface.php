<?php

namespace EbayEnterprise\Address\Api\Data;

interface ValidationResultInterface
{
    /**
     * Unique identified for the validation result.
     *
     * @return string
     */
    public function getId();

    /**
     * Indicates if the address has been successfully validated.
     *
     * @return bool
     */
    public function isValid();

    /**
     * Indicates that the address was either successfully validated or could
     * not be validated at the time and may be accepted.
     *
     * @return bool
     */
    public function isAcceptable();

    /**
     * Get the result code of the address validation request.
     *
     * @return string
     */
    public function getResultCode();

    /**
     * Get the fields on which errors were detected in the request.
     *
     * @TODO Maybe promise a less specific type. What if there is not an
     *       error location iterable easily available? E.g. in
     *       EbayEnterprise\Address\Model\Validation\ExceptionResult where we
     *       will not have a result payload.
     * @return \eBayEnterprise\RetailOrderManagement\Payload\Address\IErrorLocationIterable
     */
    public function getErrorLocations();

    /**
     * Provide a user friendly reson for the address validation failure. Will
     * only return a reason if the address validated is invalid.
     *
     * @return string|null
     */
    public function getFailureReason();

    /**
     * Indicates if suggestions to correct the address are available.
     *
     * @return bool
     */
    public function hasSuggestions();

    /**
     * The number of suggestions available for the validated address.
     *
     * @return int
     */
    public function getSuggestionCount();

    /**
     * Get any addresses returned as suggested corrections to the address
     * being validated.
     *
     * @return \EbayEnterprise\Address\Api\Data\AddressInterface[]
     */
    public function getSuggestions();

    /**
     * Get the original, unaltered address sent to be validated.
     *
     * @return \EbayEnterprise\Address\Api\Data\AddressInterface
     */
    public function getOriginalAddress();

    /**
     * Get the corrected or normalized address provided by the validation
     * service if available.
     *
     * @return \EbayEnterprise\Address\Api\Data\AddressInterface|null
     */
    public function getCorrectedAddress();
}
