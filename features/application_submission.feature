Feature: Application submission and enrichment flow
  In order to evaluate candidates effectively
  As a hiring manager
  I want submitted applications to be automatically enriched and visible

  Scenario: Submit application and see enriched results
    Given I am on "/apply"
    When I submit an application with:
      | fullName | Ada Lovelace                                                    |
      | email    | ada@example.com                                                 |
      | phone    | +441111111111                                                   |
      | position | Engineering Manager                                             |
      | notes    | Remote only                                                     |
      | cvText   | Pioneer of computer science with extensive analytical experience. |
    Then the response status code should be 200
    And I should see "Application Submitted"
    When the enrichment process runs
    And I am on "/applications"
    Then I should see "Ada Lovelace"
    And I should see "Engineering Manager"
    And I should see a score between 0 and 100
    When I am on "/applications/" followed by the application id for "ada@example.com"
    Then I should see "Ada Lovelace"
    And I should see a summary
    And I should see a score between 0 and 100
