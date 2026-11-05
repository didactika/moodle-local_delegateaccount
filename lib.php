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
 * Library functions for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Imports the guided tours shipped in the plugin folder as `tour_*.json` into Moodle's User Tours.
 *
 * A tour whose name already exists is left untouched, so tours that administrators edited,
 * renamed or disabled are never overwritten and running this twice creates no duplicates.
 *
 * @return int Number of tours imported.
 */
function local_delegateaccount_import_tours(): int {
    global $DB;

    if (!class_exists(\tool_usertours\manager::class)) {
        return 0;
    }

    $imported = 0;
    foreach (glob(__DIR__ . '/tour_*.json') as $file) {
        $json = file_get_contents($file);
        $tour = json_decode($json);
        if (!is_object($tour) || empty($tour->name) || empty($tour->steps)) {
            debugging('Skipping invalid tour file ' . basename($file), DEBUG_DEVELOPER);
            continue;
        }
        if ($DB->record_exists('tool_usertours_tours', ['name' => $tour->name])) {
            continue;
        }

        \tool_usertours\manager::import_tour_from_json($json);
        $imported++;
    }

    return $imported;
}
