@local @local_delegateaccount @javascript
Feature: Delegate access to an account
  In order to let someone act on behalf of another user
  As an administrator
  I need to create, use and revoke account delegations

  Background:
    Given the following "users" exist:
      | username | firstname | lastname   | email           |
      | ada      | Ada       | Authorised | ada@example.com |
      | tom      | Tom       | Target     | tom@example.com |
    And the following "roles" exist:
      | shortname    | name                   | archetype |
      | delegateuser | Delegated account user |           |
    And the following "permission overrides" exist:
      | capability                | permission | role         | contextlevel | reference |
      | local/delegateaccount:use | Allow      | delegateuser | System       |           |
    And the following "role assigns" exist:
      | user | role         | contextlevel | reference |
      | ada  | delegateuser | System       |           |

  Scenario: An administrator creates a delegation from the management page
    Given I log in as "admin"
    And I navigate to "Users > Accounts > Manage delegated accounts" in site administration
    When I click on "Add delegated account" "button" in the "Ada Authorised" "table_row"
    And I set the field "Delegated accounts" to "Tom Target"
    And I click on "Save changes" "button" in the ".modal-dialog" "css_element"
    And I click on "Manage this user's delegated accounts" "link" in the "Ada Authorised" "table_row"
    Then "Tom Target" "table_row" should exist

  Scenario: An authorised user opens a delegated account from the user menu
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "ada"
    When I follow "Delegated accounts" in the user menu
    Then I should see "Delegated accounts" user submenu
    And I click on "Tom Target" "link" in the "#carousel-item-delegatedaccounts" "css_element"
    And I should see "You are logged in as Tom Target"

  Scenario: An administrator revokes a delegation
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "admin"
    And I navigate to "Users > Accounts > Manage delegated accounts" in site administration
    And I click on "Manage this user's delegated accounts" "link" in the "Ada Authorised" "table_row"
    When I click on "Revoke delegation" "button" in the "Tom Target" "table_row"
    And I click on "Revoke delegation" "button" in the ".modal-dialog" "css_element"
    Then I should see "Delegations revoked: 1."
    And "Tom Target" "table_row" should not exist
    And I click on "Revoked" "link" in the ".nav-tabs" "css_element"
    And "Tom Target" "table_row" should exist

  Scenario: Revoking a delegation ends the session that is using it
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "ada"
    And I follow "Delegated accounts" in the user menu
    And I click on "Tom Target" "link" in the "#carousel-item-delegatedaccounts" "css_element"
    And I should see "You are logged in as Tom Target"
    When the following "local_delegateaccount > revocations" exist:
      | user | account |
      | ada  | tom     |
    And I reload the page
    Then I should see "Delegated session ended"
    And I should see "Your delegated session has ended"
