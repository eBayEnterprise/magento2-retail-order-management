<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\AddressInterfaceFactory;
use EbayEnterprise\Address\Helper\Region as RegionHelper;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Model\Address\AbstractAddress as AbstractCustomerAddress;

class Converter
{
    /** @var AddressInterfaceFactory */
    protected $addressFactory;
    /** @var RegionHelper */
    protected $regionHelper;

    /**
     * @param AddressInterfaceFactory
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        RegionHelper $regionHelper
    ) {
        $this->addressFactory = $addressFactory;
        $this->regionHelper = $regionHelper;
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
            'street' => array_filter($address->getStreet(), [$this, 'filterEmptyStreets']),
            'city' => $address->getCity(),
            'region_code' => $address->getRegionCode(),
            'region_id' => $address->getRegionId(),
            'region_name' => $address->getRegion(),
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
        $region = $this->regionHelper->loadRegion(
            $customerRegion->getRegionId(),
            $customerRegion->getRegionCode(),
            $customerRegion->getRegion(),
            $address->getCountryId()
        );

        $data = [
            'street' => array_filter($address->getStreet(), [$this, 'filterEmptyStreets']),
            'city' => $address->getCity(),
            'region_code' => $region->getCode(),
            'region_id' => $region->getId(),
            'region_name' => $region->getName(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
        ];
        return $this->addressFactory
            ->create(['data' => $data]);
    }

    /**
     * Transfer data from an address validation address to a customer address.
     *
     * @param CustomerAddressInterface
     * @param AddressInterface|null
     * @return CustomerAddressInterface
     */
    public function transferDataAddressToCustomerAddress(
        CustomerAddressInterface $customerAddress,
        AddressInterface $dataAddress = null
    ) {
        if ($dataAddress) {
            $region = $customerAddress->getRegion();
            $region->setRegionCode($dataAddress->getRegionCode())
                ->setRegionId($dataAddress->getRegionId())
                ->setRegion($dataAddress->getRegionName());
            $customerAddress->setStreet($dataAddress->getStreet())
                ->setCity($dataAddress->getCity())
                ->setCountryId($dataAddress->getCountryId())
                ->setPostcode($dataAddress->getPostcode())
                ->setRegion($region);
        }
        return $customerAddress;
    }

    /**
     * Method used to filter out any street data that is empty.
     *
     * @param string
     * @return bool
     */
    protected function filterEmptyStreets($street)
    {
        return (bool) $street;
    }
}
