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

namespace local_delegateaccount;

/**
 * Settings page whose groups of settings are shown as tabs.
 *
 * Moodle core has no tabbed settings page, and the one Boost uses belongs to that theme, so the plugin
 * keeps its own. Every tab is a regular settings page; this page saves and searches them as one.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_settingpage_tabs extends \admin_settingpage {
    /** @var \admin_settingpage[] The tabs, in display order. */
    private array $tabs = [];

    /**
     * Adds a tab. Add its settings first, as they are collected here.
     *
     * @param \admin_settingpage $tab Tab with its settings already added.
     */
    public function add_tab(\admin_settingpage $tab): void {
        foreach ($tab->settings as $name => $setting) {
            $this->settings->{$name} = $setting;
        }
        $this->tabs[] = $tab;
    }

    /**
     * Whether any tab shows or hides settings depending on other settings.
     *
     * @return bool
     */
    public function has_dependencies() {
        return (bool)$this->get_dependencies_for_javascript();
    }

    /**
     * Collects the show and hide conditions of every tab.
     *
     * @return array
     */
    public function get_dependencies_for_javascript() {
        return array_merge([], ...array_map(
            fn(\admin_settingpage $tab) => $tab->get_dependencies_for_javascript(),
            $this->tabs
        ));
    }

    /**
     * Renders the tabs.
     *
     * @return string HTML.
     */
    public function output_html() {
        global $OUTPUT;

        $activetab = optional_param('activetab', '', PARAM_TEXT);
        $tabs = [];
        foreach ($this->tabs as $index => $tab) {
            $tabs[] = [
                'name' => $tab->name,
                'displayname' => $tab->visiblename,
                'html' => $tab->output_html(),
                'active' => $activetab === '' ? $index === 0 : $activetab === $tab->name,
            ];
        }

        return $tabs ? $OUTPUT->render_from_template('local_delegateaccount/admin/setting_tabs', ['tabs' => $tabs]) : '';
    }
}
