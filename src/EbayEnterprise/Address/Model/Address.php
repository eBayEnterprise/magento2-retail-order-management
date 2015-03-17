<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use Magento\Framework\Object as FrameworkObject;

class Address extends FrameworkObject implements AddressInterface
{
    /**
     * {@inheritDoc}
     */
    public function getStreet()
    {
        return $this->getData('street');
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
        return $this->getData('city');
    }

    /**
     * {@inheritDoc}
     */
    public function getRegionCode()
    {
        return $this->getData('region_code');
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryId()
    {
        return $this->getData('country_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getPostcode()
    {
        return $this->getData('postcode');
    }
}
