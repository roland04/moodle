@core @core_course @theme_boost
Feature: "Jump to" menu is not displayed on "One section per page" setting
  In order to hide "Jump to" menu from course section
  As a teacher
  I should be able to change display setting to "One section per page"

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | teacher1 | Teacher   | One      | teacher1@email.com |

  Scenario Outline: "Jump to" menu is no displayed on "One section per page" course setting
    # Implicitly set course start date as January 1 to have a fixed date for weekly course format.
    Given the following "courses" exist:
      | fullname | shortname | format      | coursedisplay | startdate  |
      | Course 1 | C1        | <coursefmt> | 1             | 1735711255 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    # Add activities in different sections to test that only the selected section's activity is visible.
    And the following "activities" exist:
      | activity | course | name     | section |
      | forum    | C1     | Forum 1  | 1       |
      | assign   | C1     | Assign 1 | 2       |
    And I am on the "Course 1" course page logged in as teacher1
    # Click on a section
    When I click on "<sectionname>" "link" in the "#section-1" "css_element"
    # Confirm that only the selected section's activity is visible.
    Then "Forum 1" "text" should exist
    And "Assign 1" "text" should not exist
    # Confirm that the jump to menu does not exist on the screen.
    And "jump" "select" should not exist

    Examples:
      | coursefmt | sectionname           |
      | topics    | New section           |
      | weeks     | 1 January - 7 January |
