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
 * Lets an administrator choose a permission for each capability in delegated sessions.
 *
 * The value is stored as JSON that maps a capability name to its permission. Setting a capability to
 * "Not set" removes it, and any capability can be added from the list below the table.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_sessioncapabilities extends \admin_setting {
    /**
     * Creates the setting.
     */
    public function __construct() {
        parent::__construct(
            'local_delegateaccount/' . session_capabilities::SETTING,
            get_string('sessioncapabilities', 'local_delegateaccount'),
            get_string('sessioncapabilities_desc', 'local_delegateaccount'),
            json_encode(session_capabilities::DEFAULT)
        );
    }

    /**
     * Returns the saved permissions, or the default ones while nothing has been saved.
     *
     * @return string JSON that maps capability names to permissions.
     */
    public function get_setting() {
        return json_encode(session_capabilities::get_permissions());
    }

    /**
     * Saves the permissions posted by the form.
     *
     * @param array|string $data Posted values: permissions by capability, plus the capabilities to add, each with its
     *                           permission. Moodle passes the default as JSON when it applies default settings.
     * @return string Empty when saved, otherwise an error message.
     */
    public function write_setting($data) {
        global $DB;

        if (is_string($data)) {
            $data = ['permissions' => json_decode($data, true)];
        }

        $permissions = [];
        foreach ((array)($data['permissions'] ?? []) as $capability => $permission) {
            $permissions[(string)$capability] = (int)$permission;
        }

        foreach ((array)($data['new'] ?? []) as $new) {
            $new = clean_param($new, PARAM_RAW_TRIMMED);
            if ($new === '') {
                continue;
            }
            if (!$DB->record_exists('capabilities', ['name' => $new])) {
                return get_string('error_unknowncapability', 'local_delegateaccount', s($new));
            }
            $permissions[$new] = (int)($data['newpermissions'][$new] ?? CAP_PROHIBIT);
        }

        $saved = $this->config_write($this->name, json_encode(session_capabilities::sanitise($permissions)));

        return $saved ? '' : get_string('errorsetting', 'admin');
    }

    /**
     * Shows one row per configured capability and a search field to add several more. Capabilities picked in the
     * search field join the same list, so each capability appears once.
     *
     * @param string $data Current value, as returned by get_setting().
     * @param string $query Admin search text.
     * @return string HTML.
     */
    public function output_html($data, $query = '') {
        global $DB, $PAGE;

        $name = $this->get_full_name();
        $id = $this->get_id();
        $configured = json_decode($data, true) ?: [];

        $available = $DB->get_fieldset_select('capabilities', 'name', '1 = 1 ORDER BY name');
        $labels = $this->get_english_labels(array_unique(array_merge(array_keys($configured), $available)));

        $rows = '';
        foreach ($configured as $capability => $permission) {
            $label = $labels[$capability];
            $rows .= \html_writer::tag(
                'li',
                \html_writer::div(
                    \html_writer::span(s($label), 'font-weight-bold fw-bold') .
                    \html_writer::tag('code', s($capability), ['class' => 'small text-muted']),
                    'local-delegateaccount-capability-name'
                ) .
                \html_writer::select(
                    $this->get_permission_options(true),
                    $name . '[permissions][' . $capability . ']',
                    $permission,
                    false,
                    [
                        'id' => $id . '_' . clean_param($capability, PARAM_ALPHANUMEXT),
                        'class' => 'custom-select form-select',
                        'aria-label' => $label,
                    ]
                ),
                ['class' => 'list-group-item local-delegateaccount-capability-row']
            );
        }
        $empty = \html_writer::tag(
            'li',
            get_string('nocapabilitiesconfigured', 'local_delegateaccount'),
            [
                'class' => 'list-group-item text-muted local-delegateaccount-capabilities-empty',
                'hidden' => $rows === '' ? null : 'hidden',
            ]
        );
        $listid = $id . '_list';
        $list = \html_writer::tag(
            'ul',
            $empty . $rows,
            ['id' => $listid, 'class' => 'list-group mb-3 local-delegateaccount-capabilities']
        );

        $choices = [];
        foreach ($available as $capability) {
            if (!isset($configured[$capability])) {
                $choices[$capability] = $labels[$capability] . ' (' . $capability . ')';
            }
        }
        $addlabel = get_string('addcapability', 'local_delegateaccount');
        $searchid = $id . '_new';
        // Moodle's autocomplete filters the capabilities while the administrator types.
        $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', [
            '#' . $searchid, false, false, $addlabel, false, true,
            get_string('nocapabilityselected', 'local_delegateaccount'),
        ]);
        $PAGE->requires->js_call_amd('local_delegateaccount/capability_picker', 'init', [
            $searchid,
            $listid,
            $name . '[newpermissions]',
            array_map(
                fn($value, $label) => ['value' => (string)$value, 'label' => $label],
                array_keys($this->get_permission_options(false)),
                $this->get_permission_options(false)
            ),
            (string)CAP_PROHIBIT,
            get_string('unsavedbadge', 'local_delegateaccount'),
            get_string('remove'),
        ]);
        $add = \html_writer::div(
            \html_writer::label(
                get_string('addcapabilities', 'local_delegateaccount'),
                $searchid,
                true,
                ['class' => 'font-weight-bold fw-bold']
            ) .
            \html_writer::select($choices, $name . '[new][]', [], false, [
                'id' => $searchid,
                'multiple' => 'multiple',
                'class' => 'custom-select form-select',
            ]),
            'local-delegateaccount-capability-add'
        );

        return format_admin_setting($this, $this->visiblename, $list . $add, $this->description, false, '', null, $query);
    }

    /**
     * Returns the capability names in English, whatever the language of the person using the page.
     *
     * @param string[] $capabilities Capability names.
     * @return string[] Names by capability.
     */
    private function get_english_labels(array $capabilities): array {
        $previous = force_current_language('en');
        $labels = [];
        foreach ($capabilities as $capability) {
            $labels[$capability] = get_capability_string($capability);
        }
        force_current_language($previous);

        return $labels;
    }

    /**
     * Returns the permissions an administrator can pick.
     *
     * @param bool $withremove Whether removing the capability from the list is offered.
     * @return string[] Names by permission.
     */
    private function get_permission_options(bool $withremove): array {
        $options = [
            CAP_ALLOW => get_string('permissionallow', 'local_delegateaccount'),
            CAP_PROHIBIT => get_string('permissionprohibit', 'local_delegateaccount'),
        ];

        return $withremove ? $options + [CAP_INHERIT => get_string('removefromlist', 'local_delegateaccount')] : $options;
    }
}
