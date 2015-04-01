<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use Magento\Framework\Session\SessionManager;

class Session
{
    /** @var SessionManagerInterface */
    protected $sessionManager;
    /** @var AddressResultPairFactory */
    protected $addressResultPairFactory;

    /**
     * @param SessionManagerInterface
     * @param AddressResultPairFactory
     */
    public function __construct(
        SessionManager $sessionManager,
        AddressResultPairFactory $addressResultPairFactory
    ) {
        $this->sessionManager = $sessionManager;
        $this->addressResultPairFactory = $addressResultPairFactory;
        $this->sessionManager->start();
    }

    /**
     * Get the stashed result for a given address. If no result exist for the
     * address already, return null.
     *
     * @param AddressInterface
     * @return ValidationResultInterface
     */
    public function getResultForAddress(AddressInterface $address)
    {
        $addressResultPair = $this->getAddressResultPairForAddress($address);
        return $addressResultPair ? $addressResultPair->getResult() : null;
    }

    /**
     * Add an address validation result for a given address.
     *
     * @param AddressInterface
     * @param ValidationResultInterface
     * @return self
     */
    public function setResultForAddress(AddressInterface $address, ValidationResultInterface $result)
    {
        $addressResultPair = $this->getAddressResultPairForAddress($address);
        // If the pair already has a result, update the result and return
        if ($addressResultPair) {
            $addressResultPair->setResult($result);
            return $this;
        }
        $addressResultPair = $this->addressResultPairFactory->create(['address' => $address, 'result' => $result]);
        /** @var AddressResultPair[] */
        $results = (array) $this->sessionManager->getAddressResultPairs();
        $results[$result->getId()] = $addressResultPair;
        $this->sessionManager->setAddressResultPairs($results);
        return $this;
    }

    public function getResultById($resultId)
    {
        $addressResultPairs = $this->sessionManager->getAddressResultPairs();
        return isset($addressResultPairs[$resultId]) ? $addressResultPairs[$resultId]->getResult() : null;
    }

    /**
     * Get the validation result for the last address validated. When set to
     * clear the current result, the result set will be removed from the current
     * position but not from the session storage. Addresses removed from this
     * position can still be retrieved by address but will not be returned as
     * the current result.
     *
     * @param bool
     * @return ValidateResultInterface
     */
    public function getCurrentResult($clear = false)
    {
        return $this->sessionManager->getData('current_result', $clear);
    }

    /**
     * @param ValidationResultInterface
     * @return self
     */
    public function setCurrentResult(ValidationResultInterface $result)
    {
        return $this->sessionManager->setData('current_result', $result);
    }

    /**
     * Get an address result pair for the address. If one is already in the
     * session storage, return that instance. Otherwise, return a new address
     * result pair for the address.
     *
     * @param AddressInterface
     * @return AddressResultPair
     */
    protected function getAddressResultPairForAddress(AddressInterface $address)
    {
        /** @var AddressResultPair $addressResultPair */
        foreach ((array) $this->sessionManager->getAddressResultPairs() as $addressResultPair) {
            if ($addressResultPair->compareAddress($address)) {
                return $addressResultPair;
            }
        }
        return null;
    }
}
