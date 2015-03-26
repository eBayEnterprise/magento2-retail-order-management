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
     * Set array of street lines.
     *
     * @param string[]
     * @return self
     */
    public function setStreet($street = []);

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city.
     *
     * @param string
     * @return self
     */
    public function setCity($city);

    /**
     * Get two character region code.
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Set two character region code.
     *
     * @param string
     * @return self
     */
    public function setRegionCode($regionCode);

    /**
     * Get two-letter country code in ISO_3166-2 format.
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country id.
     *
     * @param string
     * @return self
     */
    public function setCountryId($countryId);

    /**
     * Get postcode.
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode.
     *
     * @param string
     * @return self
     */
    public function setPostcode($postcode);
}
