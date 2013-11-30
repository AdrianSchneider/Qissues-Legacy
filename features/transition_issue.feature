Feature: Transition Issue
  Scenario: Transition from New to Closed
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
     When I transition issue number "1" to "closed"
     Then issue number "1" should be "closed"
