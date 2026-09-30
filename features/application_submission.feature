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

  Scenario: Reject duplicate email
    Given an application exists with:
      | fullName | Ada Lovelace           |
      | email    | ada@example.com        |
      | phone    | +441111111111          |
      | position | Engineering Manager    |
      | cvText   | Pioneer of computing.  |
    And I am on "/apply"
    When I submit an application with:
      | fullName | Ada Lovelace II        |
      | email    | ada@example.com        |
      | phone    | +442222222222          |
      | position | CTO                    |
      | cvText   | Another CV.            |
    Then the response status code should be 200
    And I should see "An application with this email already exists."
    And I should not see "Application Submitted"

  Scenario: Filter applications by position
    Given an application exists with:
      | fullName | Alice Smith     |
      | email    | alice@example.com |
      | phone    | +441111111111   |
      | position | Engineer        |
      | cvText   | CV text.        |
    And an application exists with:
      | fullName | Bob Jones       |
      | email    | bob@example.com   |
      | phone    | +442222222222   |
      | position | Manager         |
      | cvText   | CV text.        |
    When I am on "/applications" with position "Engineer"
    Then I should see "Alice Smith"
    And I should not see "Bob Jones"

  Scenario: Filter applications by search
    Given an application exists with:
      | fullName | Alice Smith     |
      | email    | alice@example.com |
      | phone    | +441111111111   |
      | position | Engineer        |
      | cvText   | CV text.        |
    And an application exists with:
      | fullName | Bob Jones       |
      | email    | bob@example.com   |
      | phone    | +442222222222   |
      | position | Manager         |
      | cvText   | CV text.        |
    When I am on "/applications" with search "alice@example.com"
    Then I should see "Alice Smith"
    And I should not see "Bob Jones"

  Scenario: Filter applications by status
    Given an application exists with:
      | fullName | Alice Smith     |
      | email    | alice@example.com |
      | phone    | +441111111111   |
      | position | Engineer        |
      | cvText   | CV text.        |
    And an application exists with:
      | fullName | Bob Jones       |
      | email    | bob@example.com   |
      | phone    | +442222222222   |
      | position | Manager         |
      | cvText   | CV text.        |
    When the enrichment process handles 1 message
    And I am on "/applications" with status "enriched"
    Then I should see "Alice Smith"
    And I should not see "Bob Jones"
