<?php

namespace EbayEnterprise\Address\Model\Plugin;

use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Helper\Converter as AddressConverter;
use Magento\Customer\Model\Address\AbstractAddress;
use Psr\Log\LoggerInterface;

class Validator
{
    /** @var AddressValidationInterface */
    protected $addressValidation;
    /** @var AddressConverter */
    protected $addressConverter;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param AddressValidationInterface
     * @param AddressInterfaceBuilder
     * @param AddressConverter
     * @param LoggerInterface
     */
    public function __construct(
        AddressValidationInterface $addressValidation,
        AddressConverter $addressConverter,
        LoggerInterface $logger
    ) {
        $this->addressValidation = $addressValidation;
        $this->addressConverter = $addressConverter;
        $this->logger = $logger;
    }

    /**
     * Plugin method to perform an address validation service request.
     *
     * @param AbstractAddress
     * @param array|bool Array of already found errors or "true" if address is valid
     * @return array|bool Array of errors found or "true" if address is valid
     */
    public function afterValidate(AbstractAddress $address, $result)
    {
        if ($result === true) {
            try {
                $validationResult = $this->addressValidation->validate(
                    $this->addressConverter->convertAbstractAddressToDataAddress($address)
                );
            } catch (\Exception $e) {
                return [$e->getMessage()];
            }
            return $validationResult->isAcceptable() ?: ['Address is invalid.'];
        }
        return $result;
    }
}
