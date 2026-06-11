Feature: Secure Private Messaging
  In order to communicate with other fight club members
  As an active member
  I want to send and receive private messages securely

  Scenario: Active members exchange messages successfully
    Given an active member account exists for "tyler@example.com" with display number "FC-00001"
    And an active member account exists for "marla@example.com" with display number "FC-00002"
    When I log in as "tyler@example.com"
    And I go to the messages page
    Then I should see the messaging page
    When I send a message "First rule of fight club" to member "FC-00002"
    Then I should see the message "First rule of fight club" in the chat thread as sent to "FC-00002"

  Scenario: Outsiders cannot see private messages
    Given an active member account exists for "tyler@example.com" with display number "FC-00001"
    And an active member account exists for "marla@example.com" with display number "FC-00002"
    And a private message "First rule of fight club" exists from "tyler@example.com" to "marla@example.com"
    And an active member account exists for "jack@example.com" with display number "FC-00003"
    When I log in as "jack@example.com"
    And I go to the messages page
    Then I should see the messaging page
    And I should not see the message "First rule of fight club"
