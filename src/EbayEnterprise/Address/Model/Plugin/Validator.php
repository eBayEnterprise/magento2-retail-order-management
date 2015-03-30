<?php

namespace EbayEnterprise\Address\Model\Plugin;

use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Helper\Converter as AddressConverter;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\PhraseFactory;
use Psr\Log\LoggerInterface;

class Validator
{
    /** @var AddressValidationInterface */
    protected $addressValidation;
    /** @var AddressConverter */
    protected $addressConverter;
    /** @var LoggerInterface */
    protected $logger;
    /** @var PhraseFactory */
    protected $phraseFactory;
    /**
     * @param AddressValidationInterface
     * @param AddressConverter
     * @param LoggerInterface
     */
    public function __construct(
        AddressValidationInterface $addressValidation,
        AddressConverter $addressConverter,
        LoggerInterface $logger,
        PhraseFactory $phraseFactory
    ) {
        $this->addressValidation = $addressValidation;
        $this->addressConverter = $addressConverter;
        $this->logger = $logger;
        $this->phraseFactory = $phraseFactory;
    }

    /**
     * Plugin method to perform an address validation service request. Validate
     * the address and, if acceptable by the address validation service, return
     * the address to be passed through to be saved. If invalid, throw an
     * InputExepction to prevent the address from being saved.
     *
     * @param AddressInterface
     * @param array|bool Array of already found errors or "true" if address is valid
     * @return array|bool Array of errors found or "true" if address is valid
     */
    public function beforeSave(AddressRepositoryInterface $addressRepository, AddressInterface $address)
    {
        $this->logger->debug('Validating customer address');
        $validationResult = $this->addressValidation->validate(
            $this->addressConverter->convertCustomerAddressToDataAddress($address)
        );
        $this->logger->debug(
            sprintf(
                'Received validation results: Result Code %s, Valid %d, Acceptable %d',
                $validationResult->getResultCode(),
                $validationResult->isValid(),
                $validationResult->isAcceptable()
            )
        );
        // Allow the address through if it is acceptable - return arguments to
        // continue through the plug-in chain as array of arguments to original
        // method.
        if ($validationResult->isAcceptable()) {
            return [$address];
        }
        // Prevent the address save. Exception message will be the text of the
        // message displayed to the user.
        throw new InputException($this->phraseFactory->create(['text' => $validationResult->getFailureReason()]));
    }
}
