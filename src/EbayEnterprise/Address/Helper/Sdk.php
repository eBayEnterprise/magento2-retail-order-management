<?php

namespace EbayEnterprise\Address\Helper;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use eBayEnterprise\RetailOrderManagement\Payload\Address\IValidationRequest;
use eBayEnterprise\RetailOrderManagement\Payload\Checkout\IPhysicalAddress;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Sdk
{
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
        return $address
            ->setStreet(explode("\n", $addressPayload->getLines()))
            ->setCity($addressPayload->getCity())
            ->setCountryId($addressPayload->getCountryCode())
            ->setRegionCode($addressPayload->getMainDivision())
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
