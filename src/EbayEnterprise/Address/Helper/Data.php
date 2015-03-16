<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterfaceBuilderFactory;
use Magento\Customer\Model\Address\AbstractAddress as AbstractCustomerAddress;

class Data
{
    /** @var AddressInterfaceBuilderFactory */
    protected $addressBuilderFactory;

    /**
     * @param AddressInterfaceBuilderFactory
     */
    public function __construct(
        AddressInterfaceBuilderFactory $addressBuilderFactory
    ) {
        $this->addressBuilderFactory = $addressBuilderFactory;
    }

    /**
     * Convert an AbstractAddress object to an address object compatible
     * with the address validation service.
     *
     * @param AbstractCustomerAddress
     * @return \EbayEnterprise\Address\Api\Data\AddressInterface
     */
    public function convertAbstractAddressToDataAddress(AbstractCustomerAddress $address)
    {
        return $this->addressBuilderFactory()
            ->create([
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'region_code' => $address->getRegion()->getRegionCode(),
                'country_id' => $address->getCountryId(),
                'postcode' => $address->getPostcode(),
            ])
            ->create();
    }
}
