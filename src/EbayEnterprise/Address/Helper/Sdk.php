<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Helper\Region as RegionHelper;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationRequest;
use eBayEnterprise\RetailOrderManagement\Payload\Checkout\IPhysicalAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Sdk
{
    /** @var RegionHelper */
    protected $regionHelper;

    /**
     * @param RegionHelper
     */
    public function __construct(
        RegionHelper $regionHelper
    ) {
        $this->regionHelper = $regionHelper;
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
        AddressInterface $address
    ) {
        $region = $this->regionHelper->loadRegion(null, $addressPayload->getMainDivision(), null, $addressPayload->getCountryCode());
        return $address
            ->setStreet(explode("\n", $addressPayload->getLines()))
            ->setCity($addressPayload->getCity())
            ->setCountryId($addressPayload->getCountryCode())
            ->setRegionCode($region->getCode())
            ->setRegionId($region->getId())
            ->setRegionName($region->getName())
            ->setPostcode($addressPayload->getPostalCode());
    }

    /**
     * Prepare the address validation API request.
     *
     * @param IValidationRequest
     * @param AddressInterface
     * @param ScopeConfigInterface
     * @return IValidationRequest
     */
    public function prepareSdkRequest(
        IValidationRequest $apiRequest,
        AddressInterface $address,
        ScopeConfigInterface $scopeConfig
    ) {
        return $this
            ->transferAddressToPhysicalAddressPayload($address, $apiRequest)
            ->setMaxSuggestions($scopeConfig->getValue('ebay_enterprise/address_validation/max_suggestions'));
    }
}
