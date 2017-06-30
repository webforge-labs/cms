Feature: content-manager
  In order to manage contents of an entity of the cms
  As a user of the cms
  I need to see, move, create blocks defined by blocktypes

  Background:
    When I open the content-manager


  Scenario: Existance of Blocks defined in the config
    Then I see the blocks to add:
    | label |
    | Fließtext |
    | Introtext |
    | Frage |
    | Button |

  @cmadd
  Scenario: Adding Fließtext to the content-stream
    When I add a new block "Fließtext"
    And I write "# headline" into the textblock 1

    Then the content-stream contains a text block 1 with content "# headline"

    When I add a new block "Fließtext"
    And I write "content1" into the textblock 2

    Then the content-stream contains a text block 2 with content "content1"

  @cm-read
  Scenario: Having a block prefilled from database
    Then the content-stream contains a text block 0 with content "backend model text"

  @cm-compounds
  Scenario: Adding a compound teaser to the content-stream
    When I add a new block "Frage"

    And I write "question1" into the textfield from block 1
    And I write "answer1" into the textarea from block 1

    Then the content-stream contains a block 1 with question "question1" and answer "answer1"

  @cmdefault
  Scenario: Textline with default text value
    When I add a new block "Button"

    Then the content-stream contains a block 1 with textline "abschicken"
