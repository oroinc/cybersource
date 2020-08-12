@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: Cancel authorization
  In order to reverse payment
  As an Administrator
  I want to have ability to cancel order payment authorization transaction

  Scenario: Create new CyberSource Integration
    Given I login as AmandaRCole@example.org the "Buyer" at "first_session" session
    And I login as administrator and use in "second_session" as "Admin"
    When I go to System/Integrations/Manage Integrations
    And I click "Create Integration"
    And I select "CyberSource" from "Type"
    And I fill "CyberSourceForm" with:
      | Name                | CyberSource               |
      | Label               | CyberSource               |
      | Short Label         | CyberSource               |
      | Method              | Checkout API              |
      | Test Mode           | true                      |
      | Merchant ID         | merchant_id_behat         |
      | Merchant Descriptor | merchant_descriptor_behat |
      | Profile ID          | profile_id_behat          |
      | Access Key          | access_key_behat          |
      | API Key             | api_key_behat             |
      | API Secret Key      | api_secret_key_behat      |
      | Secret Key          | secret_key_behat          |
    And I save and close form
    Then I should see "Integration saved" flash message
    And I should see CyberSource in grid
    And I create payment rule with "CyberSource" payment method

  Scenario: Place order using CyberSource payment method
    Given There are products in the system available for order
    And I operate as the Buyer
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Billing Information" checkout step and press Continue
    And I select "Fifth avenue, 10115 Berlin, Germany" on the "Shipping Information" checkout step and press Continue
    And I check "Flat Rate" on the "Shipping Method" checkout step and press Continue
    And I fill "CyberSourceCreditCardForm" with:
      | Month | 10   |
      | Year  | 2028 |
    And I type "4111111111111111" in "microform_number"
    And I type "321" in "microform_securityCode"
    And I click "Continue"
    And I fill form with:
      | PO Number | 12345 |
    And I press "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    When I click "click here to review"
    Then I should see "Payment Method CyberSource"
    And I should see "Payment Status Payment authorized"

  Scenario: Failed cancel action for payment authorization transaction
    Given I operate as the Admin
    When I click edit CyberSource in grid
    And I fill "CyberSourceForm" with:
      # Value "invalid_merchant_id_behat" is not expected by ApiClientMock. Only "merchant_id_behat" is valid.
      | Merchant ID | invalid_merchant_id_behat |
    And I save and close form
    Then I should see "Integration saved" flash message
    When I go to Sales/Orders
    Then I should see "12345" in grid with following data:
      | Payment Status | Payment authorized |
    When I click view "12345" in grid
    Then I should see following "Order Payment Transaction Grid" grid:
      | Payment Method | Type      | Successful |
      | CyberSource    | Authorize | Yes        |
    And I should see following actions for Authorize in grid:
      | Capture |
      | Cancel  |
    When I click "Cancel" on row "Authorize" in grid "Order Payment Transaction Grid"
    Then I should see "Cancel Payment" in the "UiWindow Title" element
    And I should see "The $13.00 payment will be canceled. Are you sure you want to continue?"
    When I click "Yes, Cancel Payment" in modal window
    Then I should see "Could not cancel authorization." flash message

  Scenario: Successful cancel action for payment authorization transaction
    Given I operate as the Admin
    And I reload the page
    When I go to System/Integrations/Manage Integrations
    And I click edit CyberSource in grid
    And I fill "CyberSourceForm" with:
      | Merchant ID | merchant_id_behat |
    And I save and close form
    Then I should see "Integration saved" flash message
    When I go to Sales/Orders
    Then I should see "12345" in grid with following data:
      | Payment Status | Payment authorized |
    When I click view "12345" in grid
    Then I should see following "Order Payment Transaction Grid" grid:
      | Payment Method | Type      | Successful |
      | CyberSource    | Cancel    | No         |
      | CyberSource    | Authorize | Yes        |
    And I should see following actions for Authorize in grid:
      | Capture |
      | Cancel  |
    And I should not see following actions for Cancel in grid:
      | Capture |
      | Cancel  |
    When I click "Cancel" on row "Authorize" in grid "Order Payment Transaction Grid"
    Then I should see "Cancel Payment" in the "UiWindow Title" element
    And I should see "The $13.00 payment will be canceled. Are you sure you want to continue?"
    When I click "Yes, Cancel Payment" in modal window
    Then I should see "The payment of $13.00 has been canceled successfully." flash message
    And I should see following "Order Payment Transaction Grid" grid:
      | Payment Method | Type      | Successful |
      | CyberSource    | Cancel    | Yes        |
      | CyberSource    | Cancel    | No         |
      | CyberSource    | Authorize | Yes        |
    And I should not see following actions for Authorize in grid:
      | Capture |
      | Cancel  |
    And I should not see following actions for Cancel in grid:
      | Capture |
      | Cancel  |

    Given I operate as the Buyer
    When I reload the page
    Then I should see "Payment Status Payment canceled"
