Feature: Delete Issue
  Scenario: Delete Issue
    Given I create an issue "title" described as "description"
     When I delete issue number "1"
     Then Issue number "1" should be deleted
