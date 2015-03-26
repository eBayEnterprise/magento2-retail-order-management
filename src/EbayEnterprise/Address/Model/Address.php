<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class Address extends AbstractSimpleObject implements AddressInterface
{
    /**
     * {@inheritDoc}
     */
    public function getStreet()
    {
        return $this->_get('street');
    }

    /**
     * {@inheritDoc}
     */
    public function setStreet($street = [])
    {
        return $this->setData('street', (array) $street);
    }

    /**
     * {@inheritDoc}
     */
    public function getCity()
    {
        return $this->_get('city');
    }

    /**
     * {@inheritDoc}
     */
    public function setCity($city)
    {
        return $this->setData('city', $city);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegionCode()
    {
        return $this->_get('region_code');
    }

    /**
     * {@inheritDoc}
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData('region_code', $regionCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryId()
    {
        return $this->_get('country_id');
    }

    /**
     * {@inheritDoc}
     */
    public function setCountryId($countryId)
    {
        return $this->setData('country_id', $countryId);
    }

    /**
     * {@inheritDoc}
     */
    public function getPostcode()
    {
        return $this->_get('postcode');
    }

    /**
     * {@inheritDoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData('postcode', $postcode);
    }
}
