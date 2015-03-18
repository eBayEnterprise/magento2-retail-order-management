<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterfaceBuilderFactory;
use Magento\Customer\Model\Address\AbstractAddress as AbstractCustomerAddress;
use Psr\Log\LoggerInterface;

class Converter
{
    /** @var AddressInterfaceBuilderFactory */
    protected $addressBuilderFactory;
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param AddressInterfaceBuilderFactory
     */
    public function __construct(
        AddressInterfaceBuilderFactory $addressBuilderFactory,
        LoggerInterface $logger
    ) {
        $this->addressBuilderFactory = $addressBuilderFactory;
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
        $this->logger->debug('Creating address with data.', $data);
        return $this->addressBuilderFactory
            ->create()
            ->populateWithArray($data)
            ->create();
    }
}
