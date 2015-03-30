<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Model\Address\AbstractAddress as AbstractCustomerAddress;
use Magento\Directory\Model\RegionFactory;

class Converter
{
    /** @var AddressInterfaceFactory */
    protected $addressFactory;
    /** @var RegionFactory */
    protected $regionFactory;

    /**
     * @param AddressInterfaceFactory
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        RegionFactory $regionFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
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
        $data = [
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region_code' => $address->getRegionCode(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
        ];
        return $this->addressFactory
            ->create(['data' => $data]);
    }

    /**
     * Convert a customer address object to an address object compatible
     * with the address validation service.
     *
     * @param CustomerAddressInterface
     * @return \EbayEnterprise\Address\Api\Data\AddressInterface
     */
    public function convertCustomerAddressToDataAddress(CustomerAddressInterface $address)
    {
        // When the region object on the customer address already has a region
        // code, it isn't necessary to load the direction region model to get the code.
        $customerRegion = $address->getRegion();
        $regionCode = $customerRegion->getRegionCode() ?: $this->loadRegion($customerRegion->getRegionId())->getCode();

        $data = [
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region_code' => $regionCode,
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
        ];
        return $this->addressFactory
            ->create(['data' => $data]);
    }

    /**
     * Create and load a region model for the region id.
     *
     * @param int
     * @return \Magento\Directory\Model\Region
     */
    protected function loadRegion($regionId)
    {
        return $this->regionFactory->create()->load($regionId);
    }
}
