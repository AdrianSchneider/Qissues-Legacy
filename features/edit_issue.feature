Feature: Edit Issue
  Scenario: Edit Issue
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
    When I update issue number "1" with:
      | title         | description                  |
      | Jim's Problem | This should be Jim's problem |
    Then Issue number "1" should be titled "Jim's Problem"
