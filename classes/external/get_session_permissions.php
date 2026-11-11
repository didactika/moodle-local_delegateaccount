<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_delegateaccount\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\permission;
use local_delegateaccount\session_capabilities;

/**
 * The capabilities of the site and the permission overrides of the site or of one delegation, for the permission editor.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_session_permissions extends list_service {
    /** Risks a capability can carry, by the bit Moodle gives each one. */
    private const RISKS = [
        RISK_MANAGETRUST => 'managetrust',
        RISK_CONFIG => 'config',
        RISK_XSS => 'xss',
        RISK_PERSONAL => 'personal',
        RISK_SPAM => 'spam',
        RISK_DATALOSS => 'dataloss',
    ];

    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationid' => new external_value(PARAM_INT, 'Delegation identifier, or zero for the site.'),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param int $delegationid Delegation identifier, or zero for the site.
     * @return array The capabilities and the overrides.
     */
    public static function execute(int $delegationid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), compact('delegationid'));
        $delegationid = $params['delegationid'];
        $context = \context_system::instance();
        if ($delegationid === session_capabilities::SITE) {
            self::validate_context($context);
            require_capability('moodle/site:config', $context);
        } else {
            self::require_permission(permission::VIEW);
            $DB->get_record('local_delegateaccount', ['id' => $delegationid], 'id', MUST_EXIST);
        }

        $capabilities = [];
        foreach (get_all_capabilities() as $capability) {
            $risks = [];
            foreach (self::RISKS as $bit => $name) {
                if ((int)$capability['riskbitmask'] & $bit) {
                    $risks[] = $name;
                }
            }
            $capabilities[] = [
                'name' => $capability['name'],
                'label' => get_capability_string($capability['name']),
                'component' => $capability['component'],
                'componentlabel' => get_component_string($capability['component'], (int)$capability['contextlevel']),
                'risks' => $risks,
                'canallow' => is_siteadmin(),
            ];
        }

        $export = fn(array $overrides): array => array_map(
            fn(string $capability, int $permission): array => ['capability' => $capability, 'permission' => $permission],
            array_keys($overrides),
            array_values($overrides)
        );

        return [
            'capabilities' => $capabilities,
            'overrides' => $export(session_capabilities::get_overrides($delegationid)),
            'site' => $delegationid === session_capabilities::SITE ? [] : $export(session_capabilities::get_overrides(0)),
            'canedit' => has_capability(self::MANAGE_PERMISSIONS, $context) &&
                ($delegationid !== session_capabilities::SITE || has_capability('moodle/site:config', $context)),
        ];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        $override = new external_single_structure([
            'capability' => new external_value(PARAM_CAPABILITY, 'Capability name.'),
            'permission' => new external_value(PARAM_INT, 'CAP_ALLOW, CAP_PREVENT or CAP_PROHIBIT.'),
        ]);

        return new external_single_structure([
            'capabilities' => new external_multiple_structure(new external_single_structure([
                'name' => new external_value(PARAM_CAPABILITY, 'Capability name.'),
                'label' => new external_value(PARAM_TEXT, 'What the capability lets a person do.'),
                'component' => new external_value(PARAM_COMPONENT, 'Component that defines it.'),
                'componentlabel' => new external_value(PARAM_TEXT, 'Name of that component.'),
                'risks' => new external_multiple_structure(new external_value(PARAM_ALPHA, 'Risk.')),
                'canallow' => new external_value(PARAM_BOOL, 'Whether the current person may allow it.'),
            ])),
            'overrides' => new external_multiple_structure($override),
            'site' => new external_multiple_structure($override),
            'canedit' => new external_value(PARAM_BOOL, 'Whether the current person may change the overrides.'),
        ]);
    }
}
