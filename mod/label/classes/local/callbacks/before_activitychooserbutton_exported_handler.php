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

namespace mod_label\local\callbacks;

use core_course\hook\before_activitychooserbutton_exported;
use action_link;
use moodle_url;
use section_info;

/**
 * Class before_activitychooserbutton_exported_handler
 *
 * @package    mod_label
 * @copyright  2024 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_activitychooserbutton_exported_handler {

    /**
    * Callback for before_activitychooserbutton_exported
    *
    * @param before_activitychooserbutton_exported $hook
    */
    public static function callback(before_activitychooserbutton_exported $hook): void {
        /** @var section_info $section */
        $section = $hook->get_section();

        $attributes = [
            'class' => 'dropdown-item',
            'data-action' => 'addModule',
            'data-modname' => 'label',
            'data-sectionnum' => $section->sectionnum,
        ];

        $hook->get_activitychooserbutton()->add_action_link(new action_link(
            new moodle_url('#'),
            get_string('modulename', 'mod_label'),
            null,
            $attributes,
            null,
        ));
    }
}
