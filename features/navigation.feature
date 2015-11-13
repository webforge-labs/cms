Feature: navigation
  In order manage the pages
  As a user of the cms
  I need to see the tree of pages

  Background:
    Given the alice fixtures were loaded:
    """
    users
    nestedset.hgdrn
    """
    And I am logged in as "petra.platzhalter@ps-webforge.net"

  Scenario: seeing the pages tree
    When I goto the tab "Seiten verwalten" in section "Webseite" in the sidebar

    Then I should see a headline "Seiten"
    And I should see the table with pages:
    | title              |  
    | Startseite         |  
    | --Unternehmen      |  
    | --Produkte         |  
    | --Dienstleistungen |  
    | --Lösungen         |  
    | ----HMS            |  
    | ----HTS            |  
    | ----INT            |  
    | ------container    |  
    | ------model        |  
    | ------win          |  
    | --Kunden           |  
