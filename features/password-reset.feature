Feature: password-reset
  In order to recover my password
  As a Webforge User
  I need to be able to send an email to myself and reset my password

  Background:
    Given the alice fixtures were loaded:
    """
    users
    """
    And the mail spool is empty

    # going to the password-resetting page:
    When I visit "/cms"
    And I click on "Passwort vergessen"
    Then I should see a headline "Neues Passwort anfordern"

  Scenario: Navigating to the resetting form
    When I fill in "petra.platzhalter@ps-webforge.net" as "username"
    And I click on "Passwort zurücksetzen"

    Then I should see a headline "Neues Passwort angefordert"
    And an password reset mail should be mailed to "petra.platzhalter@ps-webforge.net"

    When I follow the link from the reset mail
    
    Then I should see a headline "Neues Passwort festlegen"
    When I fill in "some89Save-Pass" as "Neues Passwort"
    When I fill in "some89Save-Pass" as "Neues Passwort bestätigen"

    And I click on "Passwort ändern"
    Then a message is shown "Dein Passwort wurde erfolgreich zurückgesetzt"

    Then I visit "/cms"
    And I am logged in as "petra.platzhalter@ps-webforge.net" with password "some89Save-Pass"

# well ... they removed this .. due to security reasons ... not very user friendly :/
#  Scenario: using a wrong username or email in the resetting form
#
#    When I fill in "wrong" as "username"
#    And I click the submit button
#
#    Then a message is shown "Der Benutzername oder die E-Mail-Adresse"
#    And a message is shown "existiert nicht"
