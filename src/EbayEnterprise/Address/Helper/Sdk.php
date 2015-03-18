<?php

namespace EbayEnterprise\Address\Helper;

use eBayEnterprise\RetailOrderManagement\Payload\Checkout\IPhysicalAddress;
use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\AddressInterfaceBuilder;
use Psr\Log\LoggerInterface;

class Sdk
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Transfer the Magento address object to a physical address SDK payload.
     *
     * @param AddressInterface
     * @param IPhysicalAddress
     * @return IPhysicalAddress
     */
    public function transferAddressToPhysicalAddressPayload(
        AddressInterface $address,
        IPhysicalAddress $addressPayload
    ) {
        $addressPayload
            ->setLines(implode("\n", (array) $address->getStreet()))
            ->setCity($address->getCity())
            ->setMainDivision($address->getRegionCode())
            ->setCountryCode($address->getCountryId())
            ->setPostalCode($address->getPostcode());
        return $addressPayload;
    }

    /**
     * Transfer the SDK payload data to a Magento address object.
     *
     * @param IPhysicalAddress
     * @param AddressInterface
     * @return AddressInterface
     */
    public function transferPhysicalAddressPayloadToAddress(
        IPhysicalAddress $addressPayload,
        AddressInterfaceBuilder $addressBuilder
    ) {
        return $addressBuilder
            ->setStreet(explode("\n", $addressPayload->getLines()))
            ->setCity($addressPayload->getCity())
            ->setCountryId($addressPayload->getCountryCode())
            ->setRegionCode($addressPayload->getMainDivision())
            ->setPostcode($addressPayload->getPostalCode())
            ->create();
    }
}
