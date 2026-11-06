@local @local_delegateaccount @javascript
Feature: Delegate access to an account
  In order to let someone act on behalf of another user
  As an administrator
  I need to create, use, restrict and revoke account delegations

  Background:
    Given the following "users" exist:
      | username | firstname | lastname   | email           |
      | ada      | Ada       | Authorised | ada@example.com |
      | tom      | Tom       | Target     | tom@example.com |
      | tia      | Tia       | Target     | tia@example.com |
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
    And I change window size to "large"
    And I visit "/local/delegateaccount/pages/manage.php"
    When I click on "Create delegations for Ada Authorised" "button" in the "Ada Authorised" "table_row"
    And I set the field "Delegated accounts" to "Tom Target"
    And I click on "Save changes" "button" in the ".modal-dialog" "css_element"
    Then I should see "Delegations created: 1."
    And I click on "Ada Authorised" "link" in the "Ada Authorised" "table_row"
    And "Tom Target" "table_row" should exist
    And I click on "Tom Target" "link" in the "Tom Target" "table_row"
    And I should see "Created by Admin User"

  Scenario: An authorised user opens a delegated account from the user menu
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "ada"
    When I follow "Delegated accounts" in the user menu
    Then I should see "Delegated accounts" user submenu
    And I click on "Tom Target" "link" in the "#carousel-item-delegatedaccounts" "css_element"
    And I should see "You are logged in as Tom Target"

  Scenario: An authorised user opens a delegated account from their page
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "ada"
    And I visit "/local/delegateaccount/pages/accounts.php"
    And I click on "Skip tour" "button"
    When I click on "Open account" "link" in the "Tom Target" "list_item"
    Then I should see "You are logged in as Tom Target"

  Scenario: An administrator revokes a delegation
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "admin"
    And I change window size to "large"
    And I visit "/local/delegateaccount/pages/manage.php"
    And I click on "Ada Authorised" "link" in the "Ada Authorised" "table_row"
    When I click on "Revoke Ada Authorised as Tom Target" "button" in the "Tom Target" "table_row"
    And I click on "Revoke delegation" "button" in the ".modal-dialog" "css_element"
    Then I should see "Delegations revoked: 1."
    And I click on "Revoked" "button" in the "region-main" "region"
    And I should see "Revoked" in the "Tom Target" "table_row"

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
    Then I should see "Your delegated session has ended"
    And "Log in" "button" should exist

  Scenario: An override of a delegation applies to the session it opens
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
    And I log in as "admin"
    And I change window size to "large"
    And I visit "/local/delegateaccount/pages/manage.php"
    And I click on "Ada Authorised" "link" in the "Ada Authorised" "table_row"
    And I click on "Session permissions of Ada Authorised as Tom Target" "link" in the "Tom Target" "table_row"
    When I set the field "Search capabilities" to "moodle/user:editownprofile"
    And I click on "Prohibit" "button" in the "moodle/user:editownprofile" "list_item"
    And I click on "Save changes" "button" in the "[data-region='savebar']" "css_element"
    Then I should see "Session permissions saved."
    And I log out
    And I log in as "ada"
    And I visit "/local/delegateaccount/pages/accounts.php"
    And I click on "Skip tour" "button"
    And I click on "Open account" "link" in the "Tom Target" "list_item"
    And I should see "You are logged in as Tom Target"
    And I visit "/user/profile.php"
    And I should not see "Edit profile" in the "region-main" "region"

  Scenario: An administrator restricts every delegated session from the settings
    Given I log in as "admin"
    And I visit "/admin/settings.php?section=local_delegateaccount_settings"
    And I click on "Session permissions" "link" in the "region-main" "region"
    When I set the field "Search capabilities" to "moodle/user:editownprofile"
    And I click on "Prohibit" "button" in the "moodle/user:editownprofile" "list_item"
    And I click on "Save changes" "button" in the "[data-region='savebar']" "css_element"
    Then I should see "Session permissions saved."
    And I reload the page
    And I click on "Session permissions" "link" in the "region-main" "region"
    And I click on "Overridden" "button" in the "region-main" "region"
    And I should see "moodle/user:editownprofile"

  Scenario: An administrator changes the session permissions of several delegations at once
    Given the following "local_delegateaccount > delegations" exist:
      | user | account |
      | ada  | tom     |
      | ada  | tia     |
    And I log in as "admin"
    And I change window size to "large"
    And I visit "/local/delegateaccount/pages/manage.php?tab=delegations"
    When I click on "[data-region='selectpage']" "css_element"
    And I click on "Session permissions" "button" in the "[data-region='floatbar']" "css_element"
    And I set the field "Capabilities to change" to "editownprofile"
    And I click on "moodle/user:editownprofile" "button" in the "[data-region='capresults']" "css_element"
    And I click on "Apply to 2 delegations" "button" in the ".modal-dialog" "css_element"
    Then I should see "Session permissions changed for 2 delegations."
    And I should see "Overrides: 1" in the "Tom Target" "table_row"
    And I should see "Overrides: 1" in the "Tia Target" "table_row"

  Scenario: An administrator sends a test notification from the settings
    Given I log in as "admin"
    And I visit "/admin/settings.php?section=local_delegateaccount_settings"
    And I click on "Notifications" "link" in the "region-main" "region"
    When I click on "Send yourself a test notification" "link"
    And I set the field "Notification" to "Delegated account access revoked"
    And I press "Send a test notification"
    Then I should see "Test notifications sent: 2."
