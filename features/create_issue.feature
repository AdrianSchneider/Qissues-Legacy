Feature: Create Issue
  Scenario: Create Basic Issue
    When I create an issue "Major Bug" described as "Please don't break"
    Then I should get the number "1" back
     And Issue number "1" should be titled "Major Bug"
