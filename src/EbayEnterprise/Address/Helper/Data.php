<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterface;

class Data
{
    /**
     * Compare two addresses to have the same data.
     *
     * @param AddressInterface|null
     * @param AddressInterface|null
     * @return bool
     */
    public function compareAddresses(
        AddressInterface $a = null,
        AddressInterface $b = null
    ) {
        // If either is null, cannot be matching addresses.
        if (!$a || !$b) {
            return false;
        }
        return $a->getStreet() === $b->getStreet()
            && $a->getCity() === $b->getCity()
            && $a->getRegionCode() === $b->getRegionCode()
            && $a->getPostcode() === $b->getPostcode()
            && $a->getCountryId() === $b->getCountryId();
    }
}
