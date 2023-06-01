@core @core_courseformat
Feature: Activity type tooltip.
  In order to see the activity type
  As a Teacher
  I need to be able to see a tooltip with the plugin name in editing mode.

  Background:
    Given the following "course" exists:
      | fullname         | Course 1 |
      | shortname        | C1       |
      | category         | 0        |
      | numsections      | 1        |
    And the following "activities" exist:
      | activity | name              | intro                       | course | idnumber | section |
      | assign   | Activity sample 1 | Test assignment description | C1     | sample1  | 1       |
      | page     | Activity sample 2 | Test page description       | C1     | sample2  | 1       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Teacher can see the activity type tooltip while editing.
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    When I click on "Show activity type" "button" in the "Activity sample 1" activity
    Then I should see "Assignment" in the "body>.tooltip" "css_element"
    And I click on "Show activity type" "button" in the "Activity sample 2" activity
    And I should see "Page" in the "body>.tooltip" "css_element"
    And I turn editing mode off
    And "Show activity type" "button" should not exist in the "Activity sample 1" "activity"
    And "Show activity type" "button" should not exist in the "Activity sample 2" "activity"
