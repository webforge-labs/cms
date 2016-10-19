Feature: tabs
  In order to manage all contents of the cms
  As a user of the cms
  I need to open, close and activate tabs

  Background:
    Given I am logged in as "petra.platzhalter@ps-webforge.net"

  @focus
  Scenario: Opening a tab through the right content
    When I click on "Benutzer verwalten" in section "CMS" in the sidebar
    Then a tab with title "Benutzer verwalten" is added

    When I activate the tab "Benutzer verwalten"
    Then the content from the active tab contains a headline "Benutzer verwalten"

  Scenario: Closing an opened tab
    When I goto the tab "Benutzer verwalten" in section "CMS" in the sidebar
    Then the content from the active tab contains a headline "Benutzer verwalten"

    When I click on the x on the tab "Benutzer verwalten"
    Then the tab with title "Benutzer verwalten" is removed
