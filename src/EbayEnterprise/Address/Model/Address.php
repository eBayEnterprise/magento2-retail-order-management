<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;

class Address implements AddressInterface
{
    /**
     * {@inheritDoc}
     */
    public function getStreet();

    /**
     * {@inheritDoc}
     */
    public function getCity();

    /**
     * {@inheritDoc}
     */
    public function getRegionCode();

    /**
     * {@inheritDoc}
     */
    public function getCountryId();

    /**
     * {@inheritDoc}
     */
    public function getPostcode();
}
