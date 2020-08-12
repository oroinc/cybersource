@fixture-OroFlatRateShippingBundle:FlatRateIntegration.yml
@fixture-OroCheckoutBundle:Shipping.yml
@fixture-OroPaymentBundle:ProductsAndShoppingListsForPayments.yml
Feature: CyberSource integration can not be deleted
  In order to have working payment method
  As an Administrator
  I want CyberSource integration can not be deleted if it is used as payment method.

  Scenario: Integration can be deleted
    Given I login as administrator
    And I go to System/Integrations/Manage Integrations
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
    And I should see following actions for CyberSource in grid:
      | Delete |

  Scenario: Integration can not be deleted
    And I create payment rule with "CyberSource" payment method
    And I reload the page
    And I should not see following actions for CyberSource in grid:
      | Delete |
