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
     * Set the street, ensuring that it is an array of street lines.
     *
     * @param array
     * @return self
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
    public function getRegionCode()
    {
        return $this->_get('region_code');
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
    public function getPostcode()
    {
        return $this->_get('postcode');
    }
}
