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
 * Contains the default section selector.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_courseformat\output\local\content;

use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use core_courseformat\sectiondelegate;
use renderable;
use stdClass;
use url_select;

/**
 * Represents the section selector.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectionselector implements named_templatable, renderable {

    use courseformat_named_templatable;

    /** @var course_format the course format class */
    protected $format;

    /** @var sectionnavigation the main section navigation class */
    protected $navigation;

    /**
     * Constructor.
     *
     * In the current imeplementaiton the seciton selector is almost a variation of the section navigator
     * but in the 4.0 this selector will be a kind of dropdown menu. When this happens the construct params
     * will change.
     *
     * @param course_format $format the course format
     * @param sectionnavigation $navigation the current section navigation
     */
    public function __construct(course_format $format, sectionnavigation $navigation) {
        $this->format = $format;
        $this->navigation = $navigation;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $format = $this->format;
        $course = $format->get_course();

        $modinfo = $this->format->get_modinfo();

        $data = $this->navigation->export_for_template($output);

        // Add the section selector.
        $sectionmenu = [];
        $disabledoptions = [];
        $sectionmenu[course_get_url($course)->out(false)] = get_string('maincoursepage');
        $allsections = $modinfo->get_section_info_all();
        $sectionwithchildren = [];
        // First get any section with chidren (easier to process later in a regular loop).
        foreach ($allsections as $section) {
            if (!$section->uservisible) {
                continue;
            }
            if ($section->is_delegated()) {
                $sectiondelegated = sectiondelegate::instance($section);
                $parentsection = $sectiondelegated->get_parent_section();
                // If the section is delegated we need to get the parent section and add the section to the parent section array.
                if ($parentsection) {
                    $sectionwithchildren[$parentsection->sectionnum][] = $section;
                }
            }
        }

        foreach ($allsections as $section) {
            // Ignore delegated section as they are inserted after the parent section in the menu.
            if (!$section->uservisible || $section->is_delegated()) {
                continue;
            }
            $this->add_section_menu($section, $data->currentsection, $sectionmenu, $disabledoptions);
            if (isset($sectionwithchildren[$section->sectionnum])) {
                foreach ($sectionwithchildren[$section->sectionnum] as $section) {
                    if ($section->uservisible) {
                        $this->add_section_menu($section, $data->currentsection, $sectionmenu, $disabledoptions, true);
                    }
                }
            }
        }

        $select = new url_select($sectionmenu, '', ['' => get_string('jumpto')], null, null, $disabledoptions);
        $select->class = 'jumpmenu';
        $select->formid = 'sectionmenu';

        $data->selector = $output->render($select);
        return $data;
    }

    /**
     * Create a new section menu
     *
     * @param \section_info $section
     * @param int $currentsectionnum
     * @param array $sectionmenu
     * @param array $disabledoptions
     * @param bool $shouldindent should indent the section (this is used for delegated sections)
     * @return void
     */
    private function add_section_menu(
        \section_info $section,
        int $currentsectionnum,
        array &$sectionmenu,
        array &$disabledoptions,
        bool $shouldindent = false,
    ) {
        $indenter = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $format = $this->format;
        $course = $format->get_course();
        $url = course_get_url($course, $section, ['navigation' => true]);
        $urlkey = $url->out(false);
        $sectionmenu[$urlkey] = ($shouldindent ? $indenter : '') . $format->get_section_name($section);
        if ($section->sectionnum == $currentsectionnum) {
            $disabledoptions[] = $urlkey;
        }
    }
}
