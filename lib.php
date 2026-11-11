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

/**
 * Imports the guided tours shipped in the plugin's tours folder into Moodle's User Tours.
 *
 * The texts of the tours are references to this plugin's language strings (`identifier,component`), which User Tours
 * resolves in the language of each user. A tour is left untouched when one with the same name already exists, either as
 * the reference or as the English text that earlier versions stored, so tours that administrators edited, renamed or
 * disabled are never overwritten and running this twice creates no duplicates.
 *
 * @return int Number of tours imported.
 */
function local_delegateaccount_import_tours(): int {
    global $DB;

    if (!class_exists(\tool_usertours\manager::class)) {
        return 0;
    }

    $imported = 0;
    foreach (glob(__DIR__ . '/tours/*.json') as $file) {
        $json = file_get_contents($file);
        $tour = json_decode($json);
        if (!is_object($tour) || empty($tour->name) || empty($tour->steps)) {
            debugging('Skipping invalid tour file ' . basename($file), DEBUG_DEVELOPER);
            continue;
        }

        $names = [$tour->name];
        if (
            preg_match('/^([a-zA-Z][a-zA-Z0-9_]*),local_delegateaccount$/', $tour->name, $matches)
            && get_string_manager()->string_exists($matches[1], 'local_delegateaccount')
        ) {
            $names[] = get_string_manager()->get_string($matches[1], 'local_delegateaccount', null, 'en');
        }
        [$insql, $params] = $DB->get_in_or_equal($names);
        if ($DB->record_exists_select('tool_usertours_tours', 'name ' . $insql, $params)) {
            continue;
        }

        \tool_usertours\manager::import_tour_from_json($json);
        $imported++;
    }

    return $imported;
}
