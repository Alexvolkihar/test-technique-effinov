Feature: Secure Private Messaging
  In order to communicate with other fight club members
  As an active member
  I want to send and receive private messages securely

  Scenario: Active members exchange messages successfully in tabs
    Given an active member account exists for "tyler@example.com" with display number "FC-00001"
    And an active member account exists for "marla@example.com" with display number "FC-00002"
    And a private message "First rule of fight club" exists from "tyler@example.com" to "marla@example.com"
    When I log in as "tyler@example.com"
    And I go to the messages page
    Then I should see the messaging page
    And I should see the active conversation with "FC-00002" in the sidebar
    When I send a message "Second rule of fight club" to the active contact
    Then I should see the message "Second rule of fight club" in the chat thread as sent to "FC-00002"

  Scenario: Start a new conversation
    Given an active member account exists for "tyler@example.com" with display number "FC-00001"
    And an active member account exists for "marla@example.com" with display number "FC-00002"
    When I log in as "tyler@example.com"
    And I go to the messages page
    Then I should see the messaging page
    And the sidebar should not contain conversation with "FC-00002"
    When I start a new conversation with "FC-00002" and body "Hey Marla"
    Then I should see the active conversation with "FC-00002" in the sidebar
    And I should see the message "Hey Marla" in the chat thread as sent to "FC-00002"
