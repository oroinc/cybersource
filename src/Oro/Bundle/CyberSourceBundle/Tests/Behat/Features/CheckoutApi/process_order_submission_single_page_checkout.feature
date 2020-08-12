@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: Process order submission single page checkout
  In order to purchase goods using CyberSource payment system
  As a Buyer
  I want to enter and complete Checkout with payment via CyberSource (single page checkout)

  Scenario: Feature Background
    Given I activate "Single Page Checkout" workflow

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

  Scenario: CyberSource payment method is not available when configuration is invalid
    Given I click edit CyberSource in grid
    And I fill "CyberSourceForm" with:
      # Value "invalid_merchant_id_behat" is not expected by ApiClientMock. Only "merchant_id_behat" is valid.
      | Merchant ID | invalid_merchant_id_behat |
    And I save and close form
    Then I should see "Integration saved" flash message

    Given There are products in the system available for order
    And I operate as the Buyer
    When I open page with shopping list List 2
    And I click "Create Order"
    Then I should see "Payment method is not available. Please try again or contact us for assistance."

  Scenario: Credit card validation errors
    Given I operate as the Admin
    When I click edit CyberSource in grid
    And I fill "CyberSourceForm" with:
      | Merchant ID | merchant_id_behat |
    And I save and close form
    Then I should see "Integration saved" flash message

    Given There are products in the system available for order
    And I operate as the Buyer
    When I open page with shopping list List 2
    And I click "Create Order"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Billing Address"
    And I select "Fifth avenue, 10115 Berlin, Germany" from "Select Shipping Address"
    And I check "Flat Rate" on the checkout page
    And I fill "CyberSourceCreditCardForm" with:
      # Month with value 12 is a marker to throw gateway error(see FlexStub.js).
      | Month            | 12   |
      | Year             | 2028 |
    And I click "Submit Order"
    Then I should see "This value should not be blank." in the "CardNumberValidationMessage" element
    And I should see "This value should not be blank." in the "CvvValidationMessage" element
    When I type "411111" in "microform_number" from "CyberSourceCreditCardForm"
    Then I should see "Invalid card number." in the "CardNumberValidationMessage" element
    And I should not see "This value should not be blank." in the "CardNumberValidationMessage" element
    When I type "43" in "microform_securityCode"
    Then I should see "This value is not valid." in the "CvvValidationMessage" element
    And I should not see "This value should not be blank." in the "CvvValidationMessage" element
    When I type "4111111111111111" in "microform_number"
    And I type "321" in "microform_securityCode"
    Then I should not see "Invalid card number." in the "CardNumberValidationMessage" element
    And I should not see "This value should not be blank." in the "CardNumberValidationMessage" element
    And I should not see "This value is not valid." in the "CvvValidationMessage" element
    And I should not see "This value should not be blank." in the "CvvValidationMessage" element
    When I click "Submit Order"
    Then I should see "Payment gateway error." flash message

  Scenario: Failed payment authorization due to invalid credit card token
    When I fill "CyberSourceCreditCardForm" with:
      # Month with value 11 is a marker to return token that will be not acceptable for authorization(see FlexStub.js).
      | Month | 11 |
    And I click "Submit Order"
    Then I should see "We were unable to process your payment. Please verify your payment information and try again." flash message

  Scenario: Place order using CyberSource payment method
    When I fill "CyberSourceCreditCardForm" with:
      | Month | 10   |
      | Year  | 2028 |
    And I type "4111111111111111" in "microform_number"
    And I type "321" in "microform_securityCode"
    And I click "Submit Order"
    Then I see the "Thank You" page with "Thank You For Your Purchase!" title
    When I click "click here to review"
    Then I should see "Payment Method CyberSource"
    And I should see "Payment Status Payment authorized"
