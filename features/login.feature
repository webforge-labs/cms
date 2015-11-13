Feature: login
  In order to access the cms
  As a user of the cms
  I need to login and see it

  Background:
    Given the alice fixtures were loaded:
    """
    users
    """

  Scenario: using the login form with success and loggin out
    When I visit "/cms"
    Then I should see a headline "Zugang zum CMS"

    When I fill in "petra.platzhalter@ps-webforge.net" for "Benutzername oder E-Mail"
    And I fill in "secret" for "Passwort"
    And I press the button "Anmelden »"

    Then I should see a headline "Howdy Petra"
    And I see "Petra Platzhalter" as loggedin user

    When I click on "Abmelden"
    Then I should see a headline "Zugang zum CMS"

  Scenario: using the login form with error
    When I visit "/cms"
    Then I should see a headline "Zugang zum CMS"

    When I fill in "petra.platzhalter@ps-webforge.net" for "Benutzername oder E-Mail"
    And I fill in "wrongpassword" for "Passwort"
    And I press the button "Anmelden »"

    Then I should see a headline "Zugang zum CMS"
    And I should see an alert with "Fehlerhafte Zugangsdaten"
