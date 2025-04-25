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

namespace theme_boost\output;

use core\output\icon_system_font;
use renderer_base;
use pix_icon;

/**
 * Bootstrap icon system class.
 *
 * @package    theme_boost
 * @category   output
 * @copyright  2025 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon_system_bootstrap extends icon_system_font {
    /**
     * @var array $map Cached map of moodle icon names to font awesome icon names.
     */
    private $map = [];

    public function get_core_icon_map() {
        // TODO: Add all the mappings here. Use https://icons.getbootstrap.com/ for reference.
        return [
            'core:a/search' => 'bi-search',
            'core:e/question' => 'bi-question-lg',
            'core:t/message' => 'bi-chat-square fs-5',
            'core:i/notifications' => 'bi-bell fs-5',
            'core:t/editstring' => 'bi-pencil',
            'core:i/settings' => 'bi-gear',
            'core:i/bulk_edit' => 'bi-pencil-square',
            'core:i/menu' => 'bi-three-dots-vertical',
            'core:t/delete' => 'bi-trash3',
            'core:i/dragdrop' => 'bi-arrows-move',
            'core:t/right' => 'bi-arrow-right',
            'core:t/copy' => 'bi-copy',
            'core:t/assignroles' => 'bi-person-gear',
            'core:t/groupv' => 'bi-person-circle',
        ];
    }

    #[\Override]
    public function get_deprecated_icons(): array {
        // Add deprecated core icons to parent deprecated icons.
        return array_merge(
            parent::get_deprecated_icons(),
            [],
        );
    }

    /**
     * Overridable function to get a mapping of all icons.
     * Default is to do no mapping.
     */
    public function get_icon_name_map() {
        if ($this->map === []) {
            $cache = \cache::make('theme_boost', 'bootstrap_icon_map');

            // Create different mapping keys for different icon system classes, there may be several different
            // themes on the same site.
            $mapkey = 'mapping_' . preg_replace('/[^a-zA-Z0-9_]/', '_', get_class($this));
            $this->map = $cache->get($mapkey);

            if (empty($this->map)) {
                $this->map = $this->get_core_icon_map();

                // TODO: Move this to hook.
                $callback = 'get_bootstrap_icon_map';
                if ($pluginsfunction = get_plugins_with_function($callback)) {
                    foreach ($pluginsfunction as $plugintype => $plugins) {
                        foreach ($plugins as $pluginfunction) {
                            $pluginmap = $pluginfunction();
                            $this->map += $pluginmap;
                        }
                    }
                }

                $deprecated = $this->get_deprecated_icons();
                foreach ($this->map as $from => $to) {
                    // Add the deprecated class to all deprecated icons.
                    if (in_array($from, $deprecated)) {
                        $this->map[$from] .= ' deprecated deprecated-'.$from;
                    }
                }

                $cache->set($mapkey, $this->map);
            }
        }
        return $this->map;
    }

    #[\Override]
    public function get_amd_name() {
        return 'theme_boost/bootstrapicons/icon_system_bootstrap';
    }

    #[\Override]
    public function render_pix_icon(renderer_base $output, pix_icon $icon) {
        $subtype = 'theme_boost\output\pix_icon_bootstrap';
        $subpix = new $subtype($icon);

        $data = $subpix->export_for_template($output);

        if (!$subpix->is_mapped()) {
            $data['unmappedIcon'] = $icon->export_for_template($output);
            // If the icon is not mapped, we need to check if it is deprecated.
            $component = $icon->component;
            if (empty($component) || $component === 'moodle' || $component === 'core') {
                $component = 'core';
            }
            $iconname = $component . ':' . $icon->pix;
            if (in_array($iconname, $this->get_deprecated_icons())) {
                $data['unmappedIcon']['extraclasses'] .= ' deprecated deprecated-'.$iconname;
            }
        }
        if (isset($icon->attributes['aria-hidden'])) {
            $data['aria-hidden'] = $icon->attributes['aria-hidden'];
        }

        return $output->render_from_template('theme_boost/local/bootstrapicons/pix_icon_bootstrap', $data);
    }
}
