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

/**
 * Contains class mod_feedback_templates_table
 *
 * @package   mod_feedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Class mod_feedback_templates_table
 *
 * @package   mod_feedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_feedback_templates_table extends flexible_table {
    /** @var string|null Indicate whether we are managing template or not. */
    private $mode;

    /** @var int|null The module id. */
    private $cmid;

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     * @param moodle_url $baseurl
     * @param string $mode Indicate whether we are managing templates
     */
    public function __construct($uniqueid, $baseurl, ?string $mode = null) {
        parent::__construct($uniqueid);
        $this->mode = $mode;
        $this->cmid = $baseurl->param('id');
        $tablecolumns = [
            'template' => get_string('template', 'feedback'),
            'public' => get_string('public', 'feedback'),
        ];
        if ($this->mode) {
            $tablecolumns['actions'] = '';
        }

        $this->set_attribute('class', 'templateslist');

        $this->define_columns(array_keys($tablecolumns));
        $this->define_headers(array_values($tablecolumns));
        $this->define_baseurl($baseurl);
        $this->column_class('template', 'template');
        $this->column_class('actions', 'text-right');
        $this->sortable(false);
    }

    /**
     * Displays the table with the given set of templates
     * @param array $templates
     */
    public function display($templates) {
        global $OUTPUT;
        if (empty($templates)) {
            echo $OUTPUT->box(get_string('no_templates_available_yet', 'feedback'),
                             'generalbox boxaligncenter');
            return;
        }

        $this->setup();

        foreach ($templates as $template) {
            $data = [];
            $url = new moodle_url($this->baseurl, array('templateid' => $template->id, 'sesskey' => sesskey()));
            $data[] = $OUTPUT->action_link($url, format_string($template->name));
            $data[] = $template->ispublic ? get_string('yes') : get_string('no');

            // Only show the actions if we are managing templates.
            if ($this->mode && has_capability('mod/feedback:deletetemplate', $this->get_context())) {
                $actions = $this->get_row_actions($template);
                $data[] = $OUTPUT->render($actions);
            }

            $this->add_data($data);
        }
        $this->finish_output();
    }

    /**
     * Get the row actions for the given template
     *
     * @param stdClass $template
     * @return action_menu
     */
    protected function get_row_actions($template) {
        global $PAGE, $OUTPUT;

        $url = new moodle_url($this->baseurl, array('templateid' => $template->id, 'sesskey' => sesskey()));
        $strdeletefeedback = get_string('delete_template', 'feedback');
        $actions = new action_menu();
        $actions->set_menu_trigger($OUTPUT->pix_icon('a/setting', get_string('actions')));

        // Preview.
        $actions->add(new action_menu_link(
            new moodle_url($this->baseurl, array('templateid' => $template->id, 'sesskey' => sesskey())),
            new pix_icon('t/preview', get_string('preview')),
            get_string('preview'),
            false,
        ));

        // Use template.
        if (has_capability('mod/feedback:edititems', context_module::instance($this->cmid))) {
            $PAGE->requires->js_call_amd('mod_feedback/usetemplate', 'init');
            $actions->add(new action_menu_link(
                new moodle_url('#'),
                new pix_icon('i/files', get_string('preview')),
                get_string('use_this_template', 'mod_feedback'),
                false,
                ['data-action' => 'usetemplate', 'data-dataid' => $this->cmid, 'data-templateid' => $template->id],
            ));
        }

        // Delete.
        $candeletepublictemplate = has_all_capabilities(
            ['mod/feedback:createpublictemplate', 'mod/feedback:deletetemplate'],
            context_system::instance()
        );
        if (!$template->ispublic || $candeletepublictemplate) {
            $exporturl = new moodle_url(
                '/mod/feedback/manage_templates.php',
                $url->params() + ['deletetemplate' => $template->id]
            );
            $deleteaction = new action_link(
                $exporturl,
                get_string('delete'),
                new confirm_action(get_string('confirmdeletetemplate', 'feedback')),
                ['class' => 'text-danger'],
                new pix_icon('t/delete', $strdeletefeedback),
            );
            // TODO: Ugly hack! This workarounds probable bug in lib/outputcomponents.php:4500.
            $deleteaction->primary = false;

            $actions->add($deleteaction);
        }

        return $actions;
    }
}
