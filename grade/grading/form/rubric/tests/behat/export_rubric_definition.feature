@gradingform @gradingform_rubric @javascript @aids
Feature: Rubrics can be exported
  In order to export a rubric
  As a teacher
  I need to enable advanced grading and create a rubric

  Scenario: I can import a rubric definition into an assignment
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activity" exists:
      | activity                          | assign                      |
      | course                            | C1                          |
      | section                           | 1                           |
      | name                              | Test assignment 1 name      |
      | intro                             | Test assignment description |
      | assignfeedback_comments_enabled   | 1                           |
      | assignfeedback_editpdf_enabled    | 1                           |
      | advancedgradingmethod_submissions | rubric                      |
    And I am on the "Test assignment 1 name" "Activity" page logged in as "teacher1"
    And I click on "Advanced grading" "link"
    And I click on "Define new grading form from scratch" "link"
    And I set the following fields to these values:
      | Name | Assignment 1 rubric |
      | Description | Rubric test description |
    And I define the following rubric:
      | TMP Criterion 1 | TMP Level 11 | 11 | TMP Level 12 | 12 |
      | TMP Criterion 2 | TMP Level 21 | 21 | TMP Level 22 | 22 |
      | TMP Criterion 3 | TMP Level 31 | 31 | TMP Level 32 | 32 |
      | TMP Criterion 4 | TMP Level 41 | 41 | TMP Level 42 | 42 |
    And I press "Save rubric and make it ready"
    When I click on "Export this form definition" "link"
    # There should be no exception thrown.
    Then I should see "Assignment 1 rubric Ready for use"

