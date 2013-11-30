Feature: Transition Issue
  Scenario: Transition from New to Closed
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
     When I transition issue number "1" to "closed"
     Then issue number "1" should be "closed"

  Scenario: Transition with more details
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
     When I transition issue number "1" to "closed" with:
      | Resolution | Fixed |
     Then issue number "1" should be "closed"
      And issue number "1" should have been "Resolution" with "Fixed"

  Scenario: Transition with comment
    Given the following issues:
      | title         | description                  |
      | Joe's Problem | This should be Joe's problem |
     When I transition issue number "1" to "closed" noting "oops"
     Then issue number "1" should be "closed"
      And issue number "1" should have "1" comment
