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
 * Validates the placeholders allowed in a delegated account notification template.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_notificationtemplate extends \admin_setting_confightmleditor {
    /** @var string[] Template placeholders that may be expanded. */
    private const PLACEHOLDERS = [
        'authoriseduser',
        'delegateduser',
        'actor',
        'timestart',
        'timeend',
        'sitefullname',
    ];

    /**
     * Validates that a template contains only the documented placeholders.
     *
     * @param string $data Proposed template.
     * @return bool|string True when valid, otherwise an error message.
     */
    public function validate($data) {
        $validation = parent::validate($data);
        if ($validation !== true) {
            return $validation;
        }

        preg_match_all('/\{\$a->([^}]+)\}/', $data, $matches);
        $unsupported = array_diff($matches[1], self::PLACEHOLDERS);
        if (!empty($unsupported)) {
            return get_string(
                'error_invalidtemplateplaceholder',
                'local_delegateaccount',
                implode(', ', $unsupported)
            );
        }

        return true;
    }
}
