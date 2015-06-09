Feature: Address Validation
  As a customer
  I want to validate my address in my address book
  So that my orders are delivered without issue

  Background:
    Given customer accounts:
      | firstname | lastname | email_address | password | password-confirmation |
      | Test | User | test@example.com | testing123 | testing123 |
    And I am logged in as "test@example.com"

  Scenario Outline: Normalized address is saved to address book
    Given I have a new address with "<street>" "<city>" "<region>" "<zip>" "<country>"
    When I add the address to my address book
    Then The address should be saved
    And The address should match "<normalized_address>"

    Examples:
      | street | city | region | zip | country | normalized_address |
      | 935 First Ave | King of Prussia | Pennsylvania | 19406 | United States | 935 1st Ave King of Prussia, Pennsylvania, 19406-1342 United States |

  Scenario Outline: Invalid address returns suggestions
    Given I have a new address with "<street>" "<city>" "<region>" "<zip>" "<country>"
    When I add the address to my address book
    Then The address should not be saved
    And I should have suggestions available to correct the address
    And I should have an option to confirm the original address
    And I should have an option to use a different address

    Examples:
      | street | city | region | zip | country |
      | 1 Rosedale St | Baltimore | Maryland | 21234 | United States |

  Scenario: Limit number of suggestions
    Given I have a new address with "1077 1st Ave" "King of Prussia" "Pennsylvania" "19406" "United States"
    When I add the address to my address book
    Then The address should not be saved
    And I should have the maximum number of available suggestions

  Scenario Outline: Confirm selected address suggestion
    Given I have a new address with "10777 1st Ave" "King of Prussia" "Pennsylvania" "19406" "United States"
    And I add the address to my address book
    And I have suggestions available to correct the address
    When I confirm "<confirmation_type>"
    Then The address should be saved
    And The address should match "<selected_address>"

    Examples:
      | confirmation_type | selected_address |
      | corrected | 701 1st Ave King of Prussia, Pennsylvania, 19406-1401 United States |
      | original | 10777 1st Ave King of Prussia, Pennsylvania, 19406 United States |

  Scenario: Use new address selection
    Given I have a new address with "1077 1st Ave" "King of Prussia" "Pennsylvania" "19406" "United States"
    And I add the address to my address book
    And I have suggestions available to correct the address
    When I select to use a new address "935 1st Ave" "King of Prussia" "Pennsylvania" "19406" "United States"
    Then The address should be saved
    And The address should match "935 1st Ave King of Prussia, Pennsylvania, 19406-1342 United States"
