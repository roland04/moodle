<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace mod_feedback\reportbuilder\local\systemreports;

// use core\context\{course, system};
use core_group\reportbuilder\local\entities\group;
use mod_feedback\reportbuilder\local\entities\response;
use mod_feedback\reportbuilder\local\entities\question;
use mod_feedback\reportbuilder\local\entities\question_value;
use core_reportbuilder\local\entities\{course, user};
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\local\report\{action, column};
use core_reportbuilder\system_report;
use html_writer;
use lang_string;
use moodle_url;
use pix_icon;
use stdClass;

defined('MOODLE_INTERNAL') || die;

global $CFG;

/**
 * Feedback responses system report class implementation
 *
 * @package    mod_feedback
 * @copyright  2024 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responses extends system_report {

    /** @var array $items Array of items to be displayed in the report */
    private array $items;

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        global $USER;

        [$course, $cm] = get_course_and_cm_from_cmid($this->get_context()->instanceid, 'feedback');
        $feebackstructure = new \mod_feedback_structure(null, $cm, $course->id);
        $this->items = $feebackstructure->get_items(true);
        $feebackstructure->shuffle_anonym_responses();

        $responseentity = new response();
        $responsealias = $responseentity->get_table_alias('feedback_completed');

        $this->set_main_table('feedback_completed', $responsealias);
        $this->add_entity($responseentity);
        $paramfeedback = database::generate_param_name();
        $this->add_base_condition_sql(
            "{$responsealias}.feedback = :$paramfeedback",
            [$paramfeedback => $cm->instance]
        );

        // Join user entity.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity($userentity->add_join(
            "LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$responsealias}.userid"
        ));

        // Any columns required by actions should be defined here to ensure they're always available.
        $this->add_base_fields("{$responsealias}.id");

        $this->add_columns();
        $this->add_filters();
        $this->add_actions();

        // TODO: Set the initial sort column.
        $this->set_initial_sort_column('user:fullnamewithpicturelink', SORT_ASC);
        $this->set_downloadable(true);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('mod/feedback:viewreports', $this->get_context());
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     *
     * @param string $entityuseralias
     * @param string $entityservicealias
     */
    public function add_columns(): void {
        global $DB;

        $responsealias = $this->get_entity('response')->get_table_alias('feedback_completed');

        $this->add_columns_from_entities([
            'user:fullnamewithpicturelink',
            'response:timemodified',
        ]);

        $this->get_column('user:fullnamewithpicturelink')
            ->add_fields("{$responsealias}.anonymous_response, {$responsealias}.random_response")
            ->set_callback(static function(string $value, stdClass $row): string {
                if ($row->anonymous_response == FEEDBACK_ANONYMOUS_YES) {
                    $value = get_string('anonymous_nr', 'mod_feedback', $row->random_response);
                    $row->firstname = $row->lastname = get_string('anonymous', 'mod_feedback');
                }
                return $value;
            });

        // TODO: Add the rest of the columns, including the ones from the question entity.
        $questionentity = new question();
        $this->add_entity($questionentity);
        foreach ($this->items as $key => $item) {
            $alias = database::generate_alias();
            $this->add_column((new column(
                "item{$key}",
                $this->get_item_name($item),
                'question',
            ))
                ->add_join("LEFT JOIN {feedback_value} {$alias} ON {$alias}.completed = {$responsealias}.id
                    AND {$alias}.item = {$item->id}")
                ->add_field($DB->sql_cast_to_char("{$alias}.value"), 'value')
                ->set_is_sortable(true)
            );
        }
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [
            'user:fullname',
        ];

        $this->add_filters_from_entities($filters);

        $this->get_filter('user:fullname')
            ->set_header(new lang_string('user'));
    }

    /**
     * Add the system report actions. An extra column will be appended to each row, containing all actions added here
     *
     * Note the use of ":id" placeholder which will be substituted according to actual values in the row
     */
    protected function add_actions(): void {

        // Action to preview response.
        $this->add_action((new action(
            new moodle_url('#'),
            new pix_icon('t/preview', '', 'core'),
            ['data-action' => 'response-view', 'data-response-id' => ':id'],
            false,
            new lang_string('preview', 'core')
        )));
        // TODO: Add JS and WS to perform the preview action.

        // Action to delete response.
        $this->add_action((new action(
            new moodle_url('#'),
            new pix_icon('t/delete', '', 'core'),
            ['class' => 'text-danger', 'data-action' => 'response-delete', 'data-response-id' => ':id'],
            false,
            new lang_string('delete', 'core')
        )));
        // TODO: Add JS and WS to perform the delete action.
    }

    /**
     * Returns the name of the item
     *
     * @param stdClass $item
     * @return lang_string
     */
    private function get_item_name(stdClass $item): lang_string {
        if (strval($item->label) !== '') {
            return new lang_string(
                'nameandlabelformat',
                'mod_feedback',
                (object)['label' => $item->label, 'name' => $item->name]
            );
        }
        else {
            return new lang_string('nameformat', 'mod_feedback', $item->name);
        }
    }
}
