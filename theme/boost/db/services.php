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
 * External functions and service declaration for theme Boost
 *
 * @package    theme_boost
 * @category   webservice
 * @copyright  2025 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'theme_boost_load_bootstrap_icon_system_map' => [
        'classname' => theme_boost\external\bootstrapicons\load_icons_map::class,
        'description' => 'Load the mapping of moodle pix names to Bootstrap icon names',
        'type' => 'read',
        'loginrequired' => false,
        'ajax' => true,
    ],
];
