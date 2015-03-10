<?php

namespace EbayEnterprise\Address\Helper;

use eBayEnterprise\RetailOrderManagement\Payload\Checkout\IPhysicalAddress;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Sdk extends AbstractHelper
{
    /** @var RegionInterfaceFactory */
    protected $regionDataFactory;

    /**
     * @param AddressDataBuilder $context
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
    ) {
        parent::__construct($context);
        $this->regionDataFactory = $regionDataFactory;
    }

    public function transferAddressToPhysicalAddressPayload(
        AddressInterface $address,
        IPhysicalAddress $addressPayload
    ) {
        $addressPayload
            ->setLines(implode("\n", (array) $address->getStreet()))
            ->setCity($address->getCity())
            ->setMainDivision($address->getRegion()->getRegionCode())
            ->setCountryCode($address->getCountryId())
            ->setPostalCode($address->getPostcode());
        return $addressPayload;
    }

    public function transferPhysicalAddressPayloadToAddress(
        IPhysicalAddress $addressPayload,
        AddressDataBuilder $addressBuilder
    ) {
        $region = $this->regionDataFactory->create();
        $region->setRegionCode($addressPayload->getMainDivision());
        $addressBuilder
            ->setStreet(explode("\n", $addressPayload->getLines()))
            ->setCity($addressPayload->getCity())
            ->setCountryId($addressPayload->getCountryCode())
            ->setRegion($region)
            ->setPostcode($addressPayload->getPostalCode());
        return $addressBuilder->create();
    }
}
