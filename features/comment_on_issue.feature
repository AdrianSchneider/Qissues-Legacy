Feature: Comment on Issue
  Scenario: Comment on Issue
    Given the following issues:
      | title     | description          |
      | Blameless | Somebody screwed up! |
     When I leave the comment "Whodunit!" on issue number "1"
     Then issue number "1" should have "1" comments
