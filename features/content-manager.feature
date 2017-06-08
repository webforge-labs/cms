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

  @cmadd
  Scenario: Adding Fließtext to the content-stream
    When I add a new block "Fließtext"
    And I write "# headline" into the textblock 0

    Then the content-stream contains a text block 0 with content "# headline"

    When I add a new block "Fließtext"
    And I write "content1" into the textblock 1

    Then the content-stream contains a text block 1 with content "content1"


  @cm-compounds
  Scenario: Adding a compound teaser to the content-stream
    When I add a new block "Frage"

    And I write "question1" into the textarea from block 0
    And I write "answer1" into the textfield from block 0

    Then the content-stream contains a block 0 with question "question1" and answer "answer1"
