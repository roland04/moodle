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
 * Test page for dropdown dialog output component.
 *
 * @copyright 2023 Mikel Martín <mikel@moodle.com>
 * @package   core
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

global $CFG, $PAGE, $OUTPUT;
$PAGE->set_url('/lib/tests/fixtures/mdl-78290.php');
$PAGE->add_body_class('limitedwidth');
require_login();
$PAGE->set_context(core\context\system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading('Test page for dropdown dialog output component');

// Basic example.
echo $OUTPUT->render_from_template('core/mdl-78290', []);

echo $OUTPUT->footer();