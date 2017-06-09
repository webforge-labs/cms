Feature: file-manager
  In order to manage the media of the cms
  As a user of the cms
  I need to see, move, create directories and files, and upload files

  Scenario: Moving a file up to a folder
    When I open the file-manager
    And I click on the folder "montpellier"
    And I click on the folder "traeumen-nach-disney"
    And I select the file "20160109-IMGP5853-heller.jpg"
    And I click on "verschieben"

    Then I see the text "Dateien verschieben nach"
    And I see the directory tree with data
    And I see the button "abbrechen"
    And I see the button "verschieben"

    When I click on "montpellier" / "traeumen-nach-disney" from tree

    When I create a folder "kale-essen-in-sf"
    And I click on "montpellier" / "traeumen-nach-disney" / "kale-essen-in-sf" from tree
    And I click on "verschieben"
    Then a message is shown "Okay, die Dateien habe ich verschoben"

    When I click on the folder "kale-essen-in-sf"
    Then there is the file "20160109-IMGP5853-heller.jpg"

#  Scenario: Moving to a not allowed sub-folder
