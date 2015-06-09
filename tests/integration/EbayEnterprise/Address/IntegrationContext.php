<?php

namespace EbayEnterprise\Address;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface as CustomerRegion;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap as TestBootstrap;
use PHPUnit_Framework_Assert as Assert;

/**
 * Defines application features from the specific context.
 */
class IntegrationContext implements Context, SnippetAcceptingContext
{
    /** @var \Magento\TestFramework\ObjectManager */
    protected $objectManager;
    /** @var \Magento\Customer\Api\Data\CustomerInterface */
    protected $customer;
    /** @var AddressInterface */
    protected $customerAddress;
    /** @var AddressInterface */
    protected $selectedAddress;
    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    protected $customerRepository;
    /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory */
    protected $customerFactory;
    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory */
    protected $addressFactory;
    /** @var \Magento\Customer\Api\AddressRepositoryInterface */
    protected $addressRepository;
    /** @var \Magento\Customer\Api\Data\RegionFactory */
    protected $customerRegionFactory;
    /** @var \Magento\Directory\Model\RegionFactory */
    protected $directoryRegionFactory;
    /** @var array Mapping of country names used in tests to a country id. */
    protected $countryIdMap = ['United States' => 'US'];
    /** @var LocalizedException Captures exceptions encountered while saving the address. */
    protected $saveAddressException;
    /** @var \EbayEnterprise\Address\Model\Session */
    protected $addressSession;
    /** @var \EbayEnterprise\Address\Helper\Converter */
    protected $addressConverter;
    /** @var int */
    protected $maxSuggestions;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($apiTimeout, $maxSuggestions)
    {
        $this->objectManager = TestBootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerFactory = $this->objectManager->create('\Magento\Customer\Api\Data\CustomerInterfaceFactory');
        $this->addressFactory = $this->objectManager->create('\Magento\Customer\Api\Data\AddressInterfaceFactory');
        $this->addressRepository = $this->objectManager->create('\Magento\Customer\Api\AddressRepositoryInterface');
        $this->customerRegionFactory = $this->objectManager->create('\Magento\Customer\Api\Data\RegionInterfaceFactory');
        $this->directoryRegionFactory = $this->objectManager->create('\Magento\Directory\Model\RegionFactory');
        $this->addressSession = $this->objectManager->create('\EbayEnterprise\Address\Model\Session');
        $this->addressConverter = $this->objectManager->create('\EbayEnterprise\Address\Helper\Converter');
        $this->maxSuggestions = $maxSuggestions;

        $scopeConfig = $this->objectManager->get('\Magento\Framework\App\Config\MutableScopeConfigInterface');
        $scopeConfig->setValue(
            'ebay_enterprise/general/store_id',
            $_ENV['STORE_ID'],
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $scopeConfig->setValue(
            'ebay_enterprise/web_services/hostname',
            $_ENV['API_HOSTNAME'],
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $scopeConfig->setValue(
            'ebay_enterprise/web_services/api_key',
            $_ENV['API_KEY'],
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $scopeConfig->setValue(
            'ebay_enterprise/web_services/api_timeout',
            $apiTimeout,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $scopeConfig->setValue(
            'ebay_enterprise/address_validation/max_suggestions',
            $maxSuggestions,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @Given customer accounts:
     */
    public function customerAccounts(TableNode $customerAccountTable)
    {
        foreach ($customerAccountTable as $row) {
            // Check if the customer already exists. If it does, no need to
            // re-create it. If it doesn't, catch the exception and create
            // the customer account.
            try {
                $customer = $this->customerRepository->get($row['email_address']);
            } catch (NoSuchEntityException $e) {
                $customer = $this->customerFactory->create();
                $customer->setFirstname($row['firstname'])
                    ->setLastname($row['lastname'])
                    ->setEmail($row['email_address']);
                $this->customerRepository->save($customer);
            }
        }
    }

    /**
     * @Given I am logged in as :customerEmail
     */
    public function iAmLoggedInAs($customerEmail)
    {
        $this->customer = $this->customerRepository->get($customerEmail);
    }

    /**
     * @Given I have a new address with :street :city :region :zip :country
     */
    public function iHaveANewAddressWith($street, $city, $region, $zip, $country)
    {
        $countryId = $this->getCountryId($country);
        $region = $this->loadRegionByName($region, $countryId);
        $address = $this->addressFactory->create()
            ->setStreet((array) $street)
            ->setCity($city)
            ->setPostcode($zip)
            ->setCountryId($countryId)
            ->setRegion($region)
            ->setRegionId($region->getRegionId())
            ->setFirstname($this->customer->getFirstname())
            ->setLastname($this->customer->getLastname())
            ->setTelephone('555-555-5555')
            ->setCustomerId($this->customer->getId());
        $this->customerAddress = $address;
    }

    /**
     * @When I add the address to my address book
     */
    public function iAddTheAddressToMyAddressBook()
    {
        try {
            $this->customerAddress = $this->addressRepository->save($this->customerAddress);
            $this->saveAddressException = null;
        } catch (LocalizedException $e) {
            $this->saveAddressException = $e;
        }
    }

    /**
     * @Then The address should be saved
     */
    public function theAddressShouldBeSaved()
    {
        Assert::assertNull($this->saveAddressException, 'Exceptions were encountered saving the address');
        Assert::assertNotNull($this->customerAddress->getId(), 'Address was not saved.');
    }

    /**
     * @Then The address should match :fullAddress
     */
    public function theAddressShouldMatch($fullAddress)
    {
        Assert::assertSame(
            $fullAddress,
            $this->formatAddressData($this->customerAddress)
        );
    }

    /**
     * @Then The address should not be saved
     */
    public function theAddressShouldNotBeSaved()
    {
        Assert::assertNull($this->customerAddress->getId(), 'Address was saved and assigned an id');
        Assert::assertNotNull($this->saveAddressException, 'Exception was not thrown while saving the address');
    }

    /**
     * @Then I should have suggestions available to correct the address
     * @Given I have suggestions available to correct the address
     */
    public function iShouldHaveSuggestionsAvailableToCorrectTheAddress()
    {
        $results = $this->getResultForAddress($this->customerAddress);
        Assert::assertNotNull($results);
        Assert::assertTrue($results->hasSuggestions());
    }

    /**
     * @Then I should have an option to confirm the original address
     */
    public function iShouldHaveAnOptionToConfirmTheOriginalAddress()
    {
        $results = $this->getResultForAddress($this->customerAddress);
        Assert::assertNotNull($results->getOriginalAddress());
    }

    /**
     * @Then I should have an option to use a different address
     */
    public function iShouldHaveAnOptionToUseADifferentAddress()
    {
        // Step not applicable to integration context.
    }

    /**
     * @Then I should have the maximum number of available suggestions
     */
    public function iShouldHaveTheMaximumNumberOfAvailableSuggestions()
    {
        $results = $this->getResultForAddress($this->customerAddress);
        Assert::assertLessThanOrEqual($this->maxSuggestions, $results->getSuggestionCount());
    }

    /**
     * @When I confirm :confirmationType
     */
    public function iConfirm($confirmationType)
    {
        $results = $this->getResultForAddress($this->customerAddress);
        $originalAddress = $this->customerAddress;
        $selectedAddress = $confirmationType === 'original' ? $results->getOriginalAddress()
            : current($results->getSuggestions());
        $this->customerAddress = $this->addressSession->confirmSelection($originalAddress, $selectedAddress);
        $this->iAddTheAddressToMyAddressBook();
    }

    /**
     * @When I select to use a new address :street :city :region :zip :country
     */
    public function iSelectToUseANewAddress($street, $city, $region, $zip, $country)
    {
        $this->iHaveANewAddressWith($street, $city, $region, $zip, $country);
        $this->iAddTheAddressToMyAddressBook();
    }

    /**
     * Create an address region for the region provided by name.
     *
     * @param string
     * @return CustomerRegion
     */
    protected function loadRegionByName($regionName, $countryId)
    {
        $directoryRegion = $this->directoryRegionFactory->create()
            ->loadByName($regionName, $countryId);
        $customerRegion = $this->customerRegionFactory->create();
        $customerRegion
            ->setRegionCode($directoryRegion->getCode())
            ->setRegion($directoryRegion->getName())
            ->setRegionID($directoryRegion->getRegionId());
        return $customerRegion;
    }

    /**
     * Get the country id based upon the localized country name.
     *
     * Simply uses a hard-coded mapping of country names to country ids until
     * a simple yet more robust solution can be developed.
     *
     * @param string
     * @return string
     */
    protected function getCountryId($countryName)
    {
        return isset($this->countryIdMap[$countryName]) ? $this->countryIdMap[$countryName] : null;
    }

    /**
     * Get a country name based upon the country id.
     *
     * Simply uses a hard-coded mapping of country names to country ids until
     * a simple yet more robust solution can be developed.
     *
     * @param string
     * @return string
     */
    protected function getCountryName($countryId)
    {
        foreach ($this->countryIdMap as $countryName => $countryId) {
            if ($countryId === $countryId) {
                return $countryName;
            }
        }
        return null;
    }

    /**
     * Format the address object to match an expected format.
     *
     * @param AddressInterface
     * @return string
     */
    protected function formatAddressData(AddressInterface $address)
    {
        return implode(' ', $address->getStreet()) . ' ' .
            $address->getCity() . ', ' .
            $address->getRegion()->getRegion() . ', ' .
            $address->getPostcode() . ' ' .
            $this->getCountryName($address->getCountryId());
    }

    /**
     * Get validation results for a customer address.
     *
     * @param AddressInterface
     * @return \EbayEnterprise\Address\Model\Validation\Result
     */
    protected function getResultForAddress(AddressInterface $customerAddress)
    {
        return $this->addressSession->getResultForAddress(
            $this->addressConverter->convertCustomerAddressToDataAddress($this->customerAddress)
        );
    }
}
