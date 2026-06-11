Feature: Candidate Registration
  In order to apply for fight club membership
  As a visitor
  I want to submit a registration request

  Scenario: Register successfully with valid information
    When I go to the registration page
    And I fill in "Nom" with "Durden"
    And I fill in "Prénom" with "Tyler"
    And I fill in "Adresse" with "Paper Street"
    And I fill in "Date de naissance" with "1980-05-15"
    And I fill in "Numéro de sécurité sociale" with "180051512345678"
    And I fill in "Pseudo" with "soap_maker"
    And I fill in "Numéro d'accréditation" with "CERFA-666-999"
    And I select "Salamèche" from "Starter Pokémon"
    And I fill in "Adresse email" with "tyler.durden@example.com"
    And I submit the registration form
    Then I should see a success message
    And a pending registration request should exist for "tyler.durden@example.com"

  Scenario: Fail to register with missing fields
    When I go to the registration page
    And I fill in "Nom" with "Durden"
    And I submit the registration form
    Then I should see validation errors

  Scenario: Fail to register with a minor birth date
    When I go to the registration page
    And I fill in "Nom" with "Durden"
    And I fill in "Prénom" with "Tyler"
    And I fill in "Adresse" with "Paper Street"
    And I fill in "Date de naissance" with "2015-05-15"
    And I fill in "Numéro de sécurité sociale" with "180051512345678"
    And I fill in "Pseudo" with "soap_maker"
    And I fill in "Numéro d'accréditation" with "CERFA-666-999"
    And I select "Salamèche" from "Starter Pokémon"
    And I fill in "Adresse email" with "tyler.durden@example.com"
    And I submit the registration form
    Then I should see validation errors

  Scenario: Fail to register with invalid social security number format
    When I go to the registration page
    And I fill in "Nom" with "Durden"
    And I fill in "Prénom" with "Tyler"
    And I fill in "Adresse" with "Paper Street"
    And I fill in "Date de naissance" with "1980-05-15"
    And I fill in "Numéro de sécurité sociale" with "12345"
    And I fill in "Pseudo" with "soap_maker"
    And I fill in "Numéro d'accréditation" with "CERFA-666-999"
    And I select "Salamèche" from "Starter Pokémon"
    And I fill in "Adresse email" with "tyler.durden@example.com"
    And I submit the registration form
    Then I should see validation errors

  Scenario: Fail to register with invalid accreditation number format
    When I go to the registration page
    And I fill in "Nom" with "Durden"
    And I fill in "Prénom" with "Tyler"
    And I fill in "Adresse" with "Paper Street"
    And I fill in "Date de naissance" with "1980-05-15"
    And I fill in "Numéro de sécurité sociale" with "180051512345678"
    And I fill in "Pseudo" with "soap_maker"
    And I fill in "Numéro d'accréditation" with "CERFA-777-999"
    And I select "Salamèche" from "Starter Pokémon"
    And I fill in "Adresse email" with "tyler.durden@example.com"
    And I submit the registration form
    Then I should see validation errors

  Scenario: Fail to register with a duplicate SSN (discreet error message)
    Given a registration request exists with SSN "180051512345678"
    When I go to the registration page
    And I fill in "Nom" with "Singer"
    And I fill in "Prénom" with "Marla"
    And I fill in "Adresse" with "Wilmington"
    And I fill in "Date de naissance" with "1985-08-20"
    And I fill in "Numéro de sécurité sociale" with "180051512345678"
    And I fill in "Pseudo" with "marla_singer"
    And I fill in "Numéro d'accréditation" with "CERFA-666-888"
    And I select "Carapuce" from "Starter Pokémon"
    And I fill in "Adresse email" with "marla.singer@example.com"
    And I submit the registration form
    Then I should see the duplicate error message "Une demande d'inscription existe déjà pour les informations fournies."
