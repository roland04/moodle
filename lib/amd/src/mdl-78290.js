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
 * mdl-78290 Test
 *
 * @module      core/mdl-78290
 * @copyright   2023 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

const Selectors = {
  DROPDOWN: '.dropdown',
  DROPDOWN_SUBMENU: '.dropdown-submenu',
  DROPDOWN_SUBMENU_ITEM: '.dropdown-submenu > .dropdown-item',
  DROPDOWN_SUBMENU_MENU: '.dropdown-submenu > .dropdown-menu',
};

/**
 * Set the visibility of a submenu.
 *
 * @private
 * @param {HTMLElement} dropdownSubmenu The submenu to set the visibility.
 * @param {boolean} visible True to show the submenu, false to hide it.
 */
const _setSubmenuVisibility = (dropdownSubmenu, visible) => {
  const dropdownSubmenuItem = dropdownSubmenu.querySelector(Selectors.DROPDOWN_SUBMENU_ITEM);
  const dropdownSubmenuMenu = dropdownSubmenu.querySelector(Selectors.DROPDOWN_SUBMENU_MENU);
  dropdownSubmenuItem.setAttribute('aria-expanded', visible ? 'true' : 'false');
  dropdownSubmenuMenu.classList.toggle('show', visible);
};

let initialized = false;

/**
 * Initialise module for given report
 *
 * @method
 */
export const init = () => {

    if (initialized) {
        // We already added the event listeners (can be called multiple times by mustache template).
        return;
    }

    // Add event listeners to submenus.
    document.querySelectorAll(Selectors.DROPDOWN).forEach(dropdown => {
      dropdown.addEventListener('click', event => {
        const dropdownSubmenuItem = event.target.closest(Selectors.DROPDOWN_SUBMENU_ITEM);
        if (dropdownSubmenuItem) {
          // Avoid dropdowns being closed after clicking a subemnu.
          // This won't be needed with BS5 (data-bs-auto-close handles it).
          event.stopPropagation();

          // Hide all visible submenus in the same dropdown first.
          dropdown.querySelectorAll(`${Selectors.DROPDOWN_SUBMENU_MENU}.show`).forEach(visibleSubmenuMenu => {
            const dropdownSubmenu = visibleSubmenuMenu.closest(Selectors.DROPDOWN_SUBMENU);
            _setSubmenuVisibility(dropdownSubmenu, false);
          });

          // Show the submenu of the clicked item.
          const dropdownSubmenu = dropdownSubmenuItem.closest(Selectors.DROPDOWN_SUBMENU);
          _setSubmenuVisibility(dropdownSubmenu, true);
        }
      });
    });

    // Hide all submenus when hidind a dropdown.
    // This is using JQuery because of BS4 events. JQuery won't be needed with BS5.
    $('.dropdown').on('hidden.bs.dropdown', event => {
      event.target.querySelectorAll(Selectors.DROPDOWN_SUBMENU).forEach(dropdownSubmenu => {
        _setSubmenuVisibility(dropdownSubmenu, false);
      });
    });

    initialized = true;
};
