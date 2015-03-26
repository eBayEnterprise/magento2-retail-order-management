<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Address\AbstractAddress as AbstractCustomerAddress;
use Psr\Log\LoggerInterface;

class Converter
{
    /** @var AddressInterfaceFactory */
    protected $addressFactory;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param AddressInterfaceFactory
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        LoggerInterface $logger
    ) {
        $this->addressFactory = $addressFactory;
        $this->logger = $logger;
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
            ->create()
            ->populateWithArray($data)
            ->create();
    }
}
