<?php

namespace EbayEnterprise\Address\Api\Data;

interface AddressInterface
{
    /**
     * Get street.
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity();

    /**
     * Get two character region code.
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Get two-letter country code in ISO_3166-2 format.
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Get post code.
     *
     * @return string
     */
    public function getPostcode();
}
