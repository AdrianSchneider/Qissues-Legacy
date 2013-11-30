Feature: Lookup Issue
  Scenario: Lookup Issue
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
    When I lookup issue number "1"
    Then I should see an issue containing:
      | title         | Joe's Problem                |
      | description   | This should be Joe's problem |
