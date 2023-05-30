@core @core_course @javascript
Feature: Activities group mode icons behavior in course page

  Scenario Outline: Teachers should see group mode icons in both view and edit mode
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format         |
      | Course 1 | C1        | <courseformat> |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name                       | intro                         | course | idnumber | groupmode |
      | forum    | No groups forum            | Forum with groupmode = 0      | C1     | forum1   | 0         |
      | data     | Visible groups database    | Database with groupmode = 2   | C1     | data1    | 2         |
      | assign   | Separate groups assignment | Assignment with groupmode = 1 | C1     | assign1  | 1         |
    And I log in as "teacher1"
    When I am on "Course 1" course homepage with editing mode <editmode>
    Then "Separate groups" "icon" should not exist in the "No groups forum" "activity"
    And "Visible groups" "icon" should not exist in the "No groups forum" "activity"
    And "Separate groups" "icon" should not exist in the "Visible groups database" "activity"
    And "Visible groups" "icon" should exist in the "Visible groups database" "activity"
    And "Separate groups" "icon" should exist in the "Separate groups assignment" "activity"
    And "Visible groups" "icon" should not exist in the "Separate groups assignment" "activity"

    Examples:
      | editmode | courseformat |
      | off      | topics       |
      | on       | topics       |
      | off      | weeks        |
      | on       | weeks        |

  Scenario Outline: Students should not see group mode icons in both view and edit mode
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format         |
      | Course 1 | C1        | <courseformat> |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name                       | intro                         | course | idnumber | groupmode |
      | forum    | No groups forum            | Forum with groupmode = 0      | C1     | forum1   | 0         |
      | data     | Visible groups database    | Database with groupmode = 2   | C1     | data1    | 2         |
      | assign   | Separate groups assignment | Assignment with groupmode = 1 | C1     | assign1  | 1         |
    When I am on the "C1" "Course" page logged in as "student1"
    Then "Separate groups" "icon" should not exist in the "No groups forum" "activity"
    And "Visible groups" "icon" should not exist in the "No groups forum" "activity"
    And "Separate groups" "icon" should not exist in the "Visible groups database" "activity"
    And "Visible groups" "icon" should not exist in the "Visible groups database" "activity"
    And "Separate groups" "icon" should not exist in the "Separate groups assignment" "activity"
    And "Visible groups" "icon" should not exist in the "Separate groups assignment" "activity"

    Examples:
      | courseformat |
      | topics       |
      | weeks        |
