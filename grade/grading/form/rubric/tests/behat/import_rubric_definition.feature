@gradingform @gradingform_rubric @_file_upload @javascript
Feature: Rubrics can be imported
  In order to import a rubric
  As a teacher
  I need to enable advanced grading and select a valid file

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
    When I am on the "Test assignment 1 name" "Activity" page logged in as "teacher1"
    And I click on "Advanced grading" "link"
    # Importing a rubric.
    And I click on "Import form definition" "link"
    And I upload "grade/grading/form/rubric/tests/fixtures/rubric-import.json" file to "Grading method import file" filemanager
    And I click on "Import file" "button"
    Then I should see "Performance Improvement Projects Scoring Guide Ready for use"
