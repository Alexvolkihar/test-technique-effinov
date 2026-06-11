Feature: Finalize Member Password Setup
  In order to activate my account and access the portal
  As an approved candidate
  I want to follow the validation link and set my password

  Scenario: Successfully activate account with valid password
    Given an approved member account exists for "tyler@example.com"
    And a valid validation token exists for "tyler@example.com" with raw token "secrettoken123"
    When I click the validation link with token "secrettoken123"
    Then I should be redirected to the password setup page
    And I should see the password setup form
    When I fill in the password form with "securepassword123" and confirmation "securepassword123"
    And I submit the password form
    Then I should be logged in and redirected to the portal page

  Scenario: Fail to access portal when password is not set
    Given an approved member account exists for "marla@example.com"
    And a valid validation token exists for "marla@example.com" with raw token "token456"
    When I click the validation link with token "token456"
    Then I should be redirected to the password setup page
    When I try to navigate to the portal page
    Then I should be redirected back to the password setup page

  Scenario: Fail to set password if they do not match
    Given an approved member account exists for "marla2@example.com"
    And a valid validation token exists for "marla2@example.com" with raw token "token789"
    When I click the validation link with token "token789"
    Then I should be redirected to the password setup page
    When I fill in the password form with "secure123" and confirmation "different123"
    And I submit the password form
    Then I should see a password setup error "Les mots de passe ne correspondent pas."

  Scenario: Fail to set password if it is too short
    Given an approved member account exists for "marla3@example.com"
    And a valid validation token exists for "marla3@example.com" with raw token "tokenabc"
    When I click the validation link with token "tokenabc"
    Then I should be redirected to the password setup page
    When I fill in the password form with "short" and confirmation "short"
    And I submit the password form
    Then I should see a password setup error "Le mot de passe doit contenir au moins 8 caractères."
