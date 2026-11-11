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

/**
 * Fingerprints of the texts the guided tours had when they were fixed English text, from version 1.1.0.
 *
 * A tour still has exactly these texts when nobody edited it, which is what allows the upgrade to move it to language strings.
 *
 * @return array<string, string> Fingerprint of each tour, indexed by the identifier of the string that names it.
 */
function local_delegateaccount_legacy_tour_fingerprints(): array {
    return [
        'tour_use_name' => '95460f0d87e609d404310ef5918e2b3f',
        'tour_my_name' => '22c8e577b0b3ed512752b5e3931585e9',
    ];
}

/**
 * Fingerprint of the texts of a tour, used to tell whether an administrator edited it.
 *
 * @param string $name Name of the tour.
 * @param string $description Description of the tour.
 * @param array $steps Title and content of each step, in the order of the tour, as [title, content].
 * @return string Fingerprint.
 */
function local_delegateaccount_tour_fingerprint(string $name, string $description, array $steps): string {
    return md5(json_encode([$name, $description, array_values($steps)]));
}

/**
 * Moves the guided tours that earlier versions imported as fixed English text to language strings.
 *
 * Only a tour whose name and texts are still the ones shipped is changed, and it is changed where it is, so its identifier,
 * whether it is enabled and what each person has already seen of it are kept. A tour that an administrator edited, or
 * that was deleted, is left as it is, and no tour is created.
 *
 * @return int Number of tours moved to language strings.
 */
function local_delegateaccount_update_tours(): int {
    global $DB;

    if (!class_exists(\tool_usertours\manager::class)) {
        return 0;
    }

    $legacy = local_delegateaccount_legacy_tour_fingerprints();
    $updated = 0;
    foreach (glob(__DIR__ . '/tours/*.json') as $file) {
        $tour = json_decode(file_get_contents($file));
        if (
            !is_object($tour) || empty($tour->steps)
            || !preg_match('/^([a-zA-Z][a-zA-Z0-9_]*),local_delegateaccount$/', (string)($tour->name ?? ''), $matches)
            || !isset($legacy[$matches[1]])
        ) {
            continue;
        }
        $english = get_string_manager()->get_string($matches[1], 'local_delegateaccount', null, 'en');
        $steps = $tour->steps;
        usort($steps, fn(\stdClass $a, \stdClass $b): int => (int)$a->sortorder <=> (int)$b->sortorder);

        foreach ($DB->get_records('tool_usertours_tours', ['name' => $english]) as $record) {
            $stored = array_values($DB->get_records('tool_usertours_steps', ['tourid' => $record->id], 'sortorder ASC, id ASC'));
            $fingerprint = local_delegateaccount_tour_fingerprint(
                $record->name,
                $record->description,
                array_map(fn(\stdClass $step): array => [$step->title, $step->content], $stored)
            );
            if (count($stored) !== count($steps) || $fingerprint !== $legacy[$matches[1]]) {
                continue;
            }

            $current = \tool_usertours\tour::instance($record->id);
            $current->set_name($tour->name)->set_description($tour->description)->persist();
            foreach ($stored as $index => $step) {
                \tool_usertours\step::instance($step->id)
                    ->set_title($steps[$index]->title)
                    ->set_content($steps[$index]->content, (int)$steps[$index]->contentformat)
                    ->persist();
            }
            $updated++;
        }
    }

    return $updated;
}
