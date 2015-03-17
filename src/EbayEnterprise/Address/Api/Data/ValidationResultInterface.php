<?php

namespace EbayEnterprise\Address\Api\Data;

interface ValidationResultInterface
{
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
     * Indicates if suggestions to correct the address are available.
     *
     * @return bool
     */
    public function hasSuggestions();

    /**
     * Get any addresses returned as suggested corrections to the address
     * being validated.
     *
     * @return \Generator
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
