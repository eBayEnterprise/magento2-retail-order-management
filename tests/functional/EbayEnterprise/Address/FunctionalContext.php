<?php

namespace EbayEnterprise\Address;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assert;

/**
 * Defines application features from the specific context.
 */
class FunctionalContext extends RawMinkContext implements SnippetAcceptingContext
{
    const ACCOUNT_PAGE = '/customer/account/';
    const LOGIN_PAGE = '/customer/account/login/';
    const REGISTER_PAGE = '/customer/account/create/';
    const LOGOUT_PAGE = '/customer/account/logout/';
    const ADDRESS_BOOK_PAGE = '/customer/address/index/';
    const ADD_ADDRESS_PAGE = '/customer/address/new/';
    const EDIT_ADDRESS_PAGE = '/customer/address/edit/';

    /**
     * Customer login credentials for known customer accounts. Keys are account email address, values are password.
     *
     * @var array
     */
    protected $customerCredentials = [];

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function fillField($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $this->getSession()->getPage()->fillField($field, $value);
        return $this;
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }

    /**
     * Select a drop down option by the option title.
     *
     * @param string
     * @param string
     * @return self
     */
    protected function selectOptionByText($field, $value)
    {
        $this->getSession()->getPage()->find(
            'xpath',
            "//select[@id='$field']/option[text()='$value']"
        )->click();
        return $this;
    }

    /**
     * Select a radio button option by the option title.
     *
     * @param string
     * @param integer
     * @return self
     */
    protected function selectRadioByPosition($field, $position)
    {
        $suggestions = $this->getSession()->getPage()->findAll(
            'xpath',
            "//input[@type='radio' and @name='$field']"
        );

        if ($position < 0) {
            $position = count($suggestions) + $position;
        }

        $suggestions[$position]->click();

        return $this;
    }

    /**
     * Create a test customer accounts if they does not yet exist. Capture
     * account login credentials so customers can be authenticated during
     * scenarios.
     *
     * @Given customer accounts:
     */
    public function customerAccount(TableNode $customerAccountTable)
    {
        $session = $this->getSession();
        foreach ($customerAccountTable as $row) {
            // Go to customer register page.
            $this->visitPath(self::REGISTER_PAGE);
            // Attempt to create customer account using customer credentials.
            foreach ($row as $field => $value) {
                $this->fillField($field, $value);
            }
            $this->customerCredentials[$row['email_address']] = $row['password'];

            $session->getPage()->findById('form-validate')->submit();
            // Log customer out.
            $this->visitPath(self::LOGOUT_PAGE);
        }
    }

    /**
     * @Given I am logged in as :customerEmail
     */
    public function iAmLoggedInAs($customerEmail)
    {
        // Go to login page
        $this->visitPath(self::LOGIN_PAGE);

        // Enter user/pass and submit login form
        $this->fillField('email', $customerEmail)
            ->fillField('pass', $this->customerCredentials[$customerEmail]);

        $this->getSession()->getPage()->findById('login-form')->submit();

        $this->assertSession()->addressMatches('#' . self::ACCOUNT_PAGE . '/?$#');
    }

    /**
     * @When I add the address to my address book
     */
    public function iAddTheAddressToMyAddressBook()
    {
        $this->getSession()->getPage()->findById('form-validate')->submit();
    }

    /**
     * @Given I have a new address with :street :city :region :zip :country
     */
    public function iHaveANewAddressWith($street, $city, $region, $zip, $country)
    {
        $this->visitPath(self::ADD_ADDRESS_PAGE);
        $this->fillField('street_1', $street)
            ->selectOptionByText('country', $country)
            ->selectOptionByText('region_id', $region)
            ->fillField('city', $city)
            ->fillField('zip', $zip)
            ->fillField('telephone', '555-555-5555');
    }

    /**
     * @Then The address should be saved
     */
    public function theAddressShouldBeSaved()
    {
        $this->assertSession()->addressEquals(self::ADDRESS_BOOK_PAGE);
    }

    /**
     * @Then The address should not be saved
     */
    public function theAddressShouldNotBeSaved()
    {
        $this->assertSession()->addressEquals(self::EDIT_ADDRESS_PAGE);
    }

    /**
     * @Then I should have suggestions available to correct the address
     */
    public function iShouldHaveSuggestionsAvailableToCorrectTheAddress()
    {
        $suggestions = $this->getSession()->getPage()->findAll('css', '#form-validate .suggestion-list input[name="suggestion"]');
        // Two suggestions will always be included - confirm address or use new
        // address. To ensure that additional suggestions to pick a corrected
        // address are available, there must be more than those two options.
        Assert::assertGreaterThan(2, count($suggestions));
    }

    /**
     * @Then I should have an option to confirm the original address
     */
    public function iShouldHaveAnOptionToConfirmTheOriginalAddress()
    {
        $this->assertSession()->elementExists('css', '#suggestion-original');
    }

    /**
     * @Then I should have an option to use a different address
     */
    public function iShouldHaveAnOptionToUseADifferentAddress()
    {
        $this->assertSession()->elementExists('css', '#suggestion-use-new-address');
    }

    /**
     * @Then I should have the maximum number of available suggestions
     */
    public function iShouldHaveTheMaximumNumberOfAvailableSuggestions()
    {
        $this->assertSession()->elementsCount('css', 'input[name="suggestion"]', 5);
    }

    /**
     * @Given I have suggestions available to correct the address
     */
    public function iHaveSuggestionsAvailableToCorrectTheAddress()
    {
        $this->assertSession()->elementExists('css', 'input[name="suggestion"]');
    }

    /**
     * @When I confirm corrected
     */
    public function iConfirmCorrected()
    {
        $this->selectRadioByPosition('suggestion', 0);
    }

    /**
     * @When I confirm original
     */
    public function iConfirmOriginal()
    {
        $this->selectRadioByPosition('suggestion', -2);
    }

    /**
     * @When I confirm :confirmation_type
     */
    public function iConfirm($confirmation_type)
    {
        if ($confirmation_type == 'corrected') {
            $this->iConfirmCorrected();
        } else if ($confirmation_type == 'original') {
           $this->iConfirmOriginal();
        }
        $this->getSession()->getPage()->findById('form-validate')->submit();
    }

    /**
     * @Then The address should match :fullAddress
     */
    public function theAddressShouldMatch($fullAddress)
    {
        $address = $this->getSession()->getPage()->find('css', '.addresses li:last-of-type address');
        $addressText = preg_replace('#\s+#m', ' ', $address->getText());
        Assert::assertContains($fullAddress, $addressText);
    }

    /**
     * @When I select to use a new address :street :city :regionId :zip :country
     */
    public function iSelectToUseANewAddress($street, $city, $regionId, $zip, $country)
    {
        $this->getSession()->getPage()
            ->find('css', 'input[name="suggestion"]')->selectOption('new-address');
        $this->iHaveANewAddressWith($street, $city, $regionId, $zip, $country);
        $this->iAddTheAddressToMyAddressBook();
    }
}
