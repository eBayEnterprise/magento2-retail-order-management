<?php

namespace EbayEnterprise\Address\Model;

use EbayEnterprise\Address\Api\Data\AddressInterface;
use EbayEnterprise\Address\Api\Data\ValidationResultInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerRegionInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session
{
    /** @var SessionManagerInterface */
    protected $sessionManager;
    /** @var AddressResultPairFactory */
    protected $addressResultPairFactory;
    /** @var CustomerAddressInterfaceFactory */
    protected $customerAddressFactory;
    /** @var CustomerRegionInterfaceFactory */
    protected $customerRegionFactory;

    /**
     * @param SessionManagerInterface
     * @param AddressResultPairFactory
     * @param CustomerAddressInterfaceFactory
     * @param CustomerRegionInterfaceFactory
     */
    public function __construct(
        SessionManager $sessionManager,
        AddressResultPairFactory $addressResultPairFactory,
        CustomerAddressInterfaceFactory $customerAddressFactory,
        CustomerRegionInterfaceFactory $customerRegionFactory
    ) {
        $this->sessionManager = $sessionManager;
        $this->addressResultPairFactory = $addressResultPairFactory;
        $this->sessionManager->start();
        $this->customerAddressFactory = $customerAddressFactory;
        $this->customerRegionFactory = $customerRegionFactory;
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

    /**
     * Get a validation result by id.
     *
     * @param string
     * @return ValidationResultInterface
     */
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

    /**
     * Store the original customer address data.
     *
     * @param CustomerAddressInterface
     * @return self
     */
    public function setOriginalCustomerAddress(CustomerAddressInterface $address)
    {
        $region = $address->getRegion() ?: $this->customerRegionFactory->create();
        $addressData = [
            'id' => $address->getId(),
            'customer_id' => $address->getCustomerId(),
            'region' => [
                'region_id' => $region->getRegionId(),
                'region_code' => $region->getRegionCode(),
                'region' => $region->getRegion(),
            ],
            'country_id' => $address->getCountryId(),
            'street' => $address->getStreet(),
            'company' => $address->getCompany(),
            'telephone' => $address->getTelephone(),
            'fax' => $address->getFax(),
            'postcode' => $address->getPostcode(),
            'city' => $address->getCity(),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'middlename' => $address->getMiddlename(),
            'prefix' => $address->getPrefix(),
            'suffix' => $address->getSuffix(),
            'vat_id' => $address->getVatId(),
            'is_default_shipping' => $address->isDefaultShipping(),
            'is_default_billing' => $address->isDefaultBilling(),
        ];
        $this->sessionManager->setOriginalCustomerAddressData($addressData);
    }

    /**
     * Get the original customer address.
     *
     * @return CustomerAddressInterface
     */
    public function getOriginalCustomerAddress()
    {
        $address = $this->customerAddressFactory->create();
        $region = $this->customerRegionFactory->create();
        $addressData = $this->sessionManager->getOriginalCustomerAddressData();
        if ($addressData) {
            $region
                ->setRegionId($addressData['region']['region_id'])
                ->setRegionCode($addressData['region']['region_id'])
                ->setRegion($addressData['region']['region']);
            $address
                ->setId($addressData['id'])
                ->setCustomerId($addressData['customer_id'])
                ->setCountryId($addressData['country_id'])
                ->setStreet($addressData['street'])
                ->setCompany($addressData['company'])
                ->setTelephone($addressData['telephone'])
                ->setFax($addressData['fax'])
                ->setPostcode($addressData['postcode'])
                ->setCity($addressData['city'])
                ->setFirstname($addressData['firstname'])
                ->setLastname($addressData['lastname'])
                ->setMiddlename($addressData['middlename'])
                ->setPrefix($addressData['prefix'])
                ->setSuffix($addressData['suffix'])
                ->setVatId($addressData['vat_id'])
                ->setIsDefaultShipping($addressData['is_default_shipping'])
                ->setIsDefaultBilling($addressData['is_default_billing'])
                ->setRegion($region);
        }
        return $address;
    }
}
