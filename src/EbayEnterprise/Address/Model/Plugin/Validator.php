<?php

namespace EbayEnterprise\Address\Model\Plugin;

use EbayEnterprise\Address\Api\AddressValidationInterface;
use EbayEnterprise\Address\Helper\Converter as AddressConverter;
use EbayEnterprise\Address\Model\Session as AddressSession;
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
    /** @var AddressSession */
    protected $session;

    /**
     * @param AddressValidationInterface
     * @param AddressConverter
     * @param AddressSession
     * @param LoggerInterface
     * @param PhraseFactory
     */
    public function __construct(
        AddressValidationInterface $addressValidation,
        AddressConverter $addressConverter,
        AddressSession $session,
        LoggerInterface $logger,
        PhraseFactory $phraseFactory
    ) {
        $this->addressValidation = $addressValidation;
        $this->addressConverter = $addressConverter;
        $this->logger = $logger;
        $this->phraseFactory = $phraseFactory;
        $this->session = $session;
    }

    /**
     * Plugin method to perform an address validation service request. Validate
     * the address and, if acceptable by the address validation service, return
     * the address to be passed through to be saved. If invalid, throw an
     * InputExepction to prevent the address from being saved.
     *
     * @param AddressRepositoryInterface $addressRepository Object the method is being invoked upon - $this in the wrapped method
     * @param AddressInterface
     * @return array Array of args to be passed through to original method call, e.g. array container the address being validated
     * @throws InputException If the address is not valid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            // If the address is acceptable, apply any difinitive corrections or
            // normalizations to the address.
            return [$this->addressConverter->transferDataAddressToCustomerAddress(
                $address,
                $validationResult->getCorrectedAddress()
            )];
        }
        $this->session->setOriginalCustomerAddress($address);
        // Prevent the address save. Exception message will be the text of the
        // message displayed to the user.
        throw new InputException($this->phraseFactory->create(['text' => $validationResult->getFailureReason()]));
    }
}
