Feature: Edit Issue
  Scenario: Edit Issue
    Given the following issues:
      | title         | description                  | assignee |
      | Joe's Problem | This should be Joe's problem |          |
    When I assign issue "1" to "Joe"
    Then Issue number "1" should be assigned to "Joe"
