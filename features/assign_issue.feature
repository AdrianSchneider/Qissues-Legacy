Feature: Assign Issue
  Scenario: Assign Issue
    Given the following issues:
      | title     | description          |
      | Blameless | Somebody screwed up! |
     When I assume issue number "1" to "joe"
     Then issue number "1" should be assigned to "joe"
