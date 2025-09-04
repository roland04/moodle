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

namespace core\external;

use core\output\pix_icon;
use core\output\icon_system;

/**
 * Class pix_icon_exporter
 *
 * @package    core
 * @copyright  2025 Ferran Recio <ferran@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pix_icon_exporter extends exporter {
    /**
     * Constructor with parameter type hints.
     *
     * @param pix_icon $data The pix_icon data to export.
     * @param array $related Related data for the exporter.
     */
    public function __construct(
        pix_icon $data,
        array $related = [],
    ) {
        parent::__construct($data, $related);
    }

    #[\Override]
    protected static function define_properties(): array {
        return [
            'pix' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'description' => 'The pix icon.',
            ],
            'component' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'description' => 'The component the icon belongs to.',
            ],
        ];
    }

    #[\Override]
    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    #[\Override]
    protected static function define_other_properties() {
        return [
            // We cannot use the 'attributes' property directly because it is an associative array
            // and it is already defined in the pix_icon class and exporters cannot reuse the
            // an object attribute with a different value.
            'extras' => [
                'type' => [
                    'name' => [
                        'type' => PARAM_TEXT,
                        'null' => NULL_NOT_ALLOWED,
                        'description' => 'The name of the attribute.',
                    ],
                    'value' => [
                        'type' => PARAM_TEXT,
                        'null' => NULL_NOT_ALLOWED,
                        'description' => 'The value of the attribute.',
                    ],
                ],
                'null' => NULL_ALLOWED,
                'description' => 'The attributes of the icon.',
            ],
            'iconsystem' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'The icon system for the icon.',
            ],
            'iconoutput' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'The icon output for the icon.',
            ],
            'iconmap' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'description' => 'The icon map for the icon.',
            ],
        ];
    }

    #[\Override]
    protected function get_other_values(\renderer_base $output) {
        /** @var pix_icon $source */
        $source = $this->data;

        // Normalize the associative array attributes as an array of objects with name and value.
        $attributes = [];
        foreach ($source->attributes as $name => $value) {
            $attributes[] = ['name' => $name, 'value' => $value];
        }

        $iconsystem = icon_system::instance();

        return [
            'extras' => $attributes,
            'iconsystem' => $iconsystem::class,
            'iconoutput' => $iconsystem->get_icon_output_class(),
            'iconmap' => $this->get_icon_map($iconsystem, $source),
        ];
    }

    /**
     * Get the icon map for the given icon if any.
     *
     * @param icon_system $iconsystem
     * @param pix_icon $icon
     * @return string|null
     */
    private function get_icon_map(icon_system $iconsystem, pix_icon $icon): ?string {
        // Normalize component name.
        $component = $icon->component;
        if ($component == null || $component == 'moodle') {
            $component = 'core';
        }

        $iconkey = $component . ':' . $icon->pix;
        return $iconsystem->get_icon_name_map()[$iconkey] ?? null;
    }
}
