Feature: Query Issues
  Scenario: Empty Query
    Given the following issues:
      | title        | description              |
      | First Issue  | This is the first issue  |
      | Second Issue | This is the second issue |
    When I query issues
    Then I should get "2" results
