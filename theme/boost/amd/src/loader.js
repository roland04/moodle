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
 * Template renderer for Moodle. Load and render Moodle templates with Mustache.
 *
 * @module     theme_boost/loader
 * @copyright  2015 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      2.9
 */

import * as Aria from './aria';
import * as Bootstrap from './index';
import Pending from 'core/pending';
import {DefaultAllowlist} from './bootstrap/util/sanitizer';
import setupBootstrapPendingChecks from './pending';

/**
 * Rember the last visited tabs.
 */
const rememberTabs = () => {
    const tabTriggerList = document.querySelectorAll('a[data-bs-toggle="tab"]');
    [...tabTriggerList].map(tabTriggerEl => tabTriggerEl.addEventListener('shown.bs.tab', (e) => {
        var hash = e.target.getAttribute('href');
        if (history.replaceState) {
            history.replaceState(null, null, hash);
        } else {
            location.hash = hash;
        }
    }));
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector('[role="tablist"] [href="' + hash + '"]');
        if (tab) {
            tab.click();
        }
    }
};

/**
 * Enable all popovers
 *
 */
const enablePopovers = () => {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverConfig = {
        container: 'body',
        trigger: 'focus',
        allowList: Object.assign(DefaultAllowlist, {table: [], thead: [], tbody: [], tr: [], th: [], td: []}),
    };
    [...popoverTriggerList].map(popoverTriggerEl => new Bootstrap.Popover(popoverTriggerEl, popoverConfig));

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && e.target.closest('[data-bs-toggle="popover"]')) {
            Bootstrap.Popover.getInstance(e.target).hide();
        }
        if (e.key === 'Enter' && e.target.closest('[data-bs-toggle="popover"]')) {
            Bootstrap.Popover.getInstance(e.target).show();
        }
    });
};

/**
 * Enable tooltips
 *
 */
const enableTooltips = () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new Bootstrap.Tooltip(tooltipTriggerEl));
};

const pendingPromise = new Pending('theme_boost/loader:init');

// Add pending promise event listeners to relevant Bootstrap custom events.
setupBootstrapPendingChecks();

// Setup Aria helpers for Bootstrap features.
Aria.init();

// Remember the last visited tabs.
rememberTabs();

// Enable all popovers.
enablePopovers();

// Enable all tooltips.
enableTooltips();

// TODO: Refactor this on Boostrap 5.

// Disables flipping the dropdowns up or dynamically repositioning them along the Y-axis (based on the viewport)
// to prevent the dropdowns getting hidden behind the navbar or them covering the trigger element.
// $.fn.dropdown.Constructor.Default.popperConfig = {
//     modifiers: {
//         flip: {
//             enabled: false,
//         },
//         storeTopPosition: {
//             enabled: true,
//             // eslint-disable-next-line no-unused-vars
//             fn(data, options) {
//                 data.storedTop = data.offsets.popper.top;
//                 return data;
//             },
//             order: 299
//         },
//         restoreTopPosition: {
//             enabled: true,
//             // eslint-disable-next-line no-unused-vars
//             fn(data, options) {
//                 data.offsets.popper.top = data.storedTop;
//                 return data;
//             },
//             order: 301
//         }
//     },
// };

pendingPromise.resolve();

export {
    Bootstrap,
};
