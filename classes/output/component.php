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

namespace local_delegateaccount\output;

use core\output\react_component_renderable;

/**
 * One React component of the plugin, with what it starts from: the texts of the page, the icons it draws and its own
 * properties. Moodle's core/react_autoinit mounts it from the element the renderer writes.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class component implements react_component_renderable, \renderable {
    /** Icons of Moodle the components draw, by their id. */
    private const ICONS = ['a/search', 'i/previous', 'i/next', 't/edit', 't/add', 't/delete', 'i/permissions', 'i/log',
        'i/users', 'i/switch', 'e/cancel', 't/copy'];

    /** Core texts the components use. */
    private const CORE = [
        'actions' => 'moodle', 'cancel' => 'moodle', 'savechanges' => 'moodle', 'time' => 'moodle', 'filters' => 'moodle',
        'riskmanagetrustshort' => 'admin', 'riskconfigshort' => 'admin', 'riskxssshort' => 'admin',
        'riskpersonalshort' => 'admin', 'riskspamshort' => 'admin', 'riskdatalossshort' => 'admin',
    ];

    /** @var array|null Texts and icons, read once for every page. */
    private static ?array $strings = null;

    /**
     * Creates the component.
     *
     * @param string $name Name of the component inside the plugin, for example DelegationList.
     * @param array $props Its own properties.
     * @param bool $withstrings Whether it gets the texts and icons of the plugin.
     */
    public function __construct(
        /** @var string Name of the component inside the plugin. */
        private readonly string $name,
        /** @var array Its own properties. */
        private readonly array $props = [],
        /** @var bool Whether it gets the texts and icons of the plugin. */
        private readonly bool $withstrings = true,
    ) {
    }

    /**
     * The React component that draws it.
     *
     * @return string Name of the component.
     */
    #[\Override]
    public function get_react_component_name(): string {
        return 'local_delegateaccount/' . $this->name;
    }

    /**
     * What the component starts from.
     *
     * @param \renderer_base $renderer Renderer.
     * @return \stdClass Properties of the component.
     */
    #[\Override]
    public function get_react_component_props(\renderer_base $renderer): \stdClass {
        return (object)($this->withstrings ? $this->props + ['strings' => self::strings($renderer)] : $this->props);
    }

    /**
     * Every text of the plugin in the language of the person, with the places of the values written the way the script
     * fills them, the core texts as core:name and the icons as pix:id.
     *
     * @param \renderer_base $renderer Renderer that draws the icons.
     * @return string[] Texts by name.
     */
    public static function strings(\renderer_base $renderer): array {
        if (self::$strings === null) {
            $strings = [];
            foreach (get_string_manager()->load_component_strings('local_delegateaccount', current_language()) as $name => $value) {
                $strings[$name] = preg_replace(['/\{\$a->([a-z]+)\}/', '/\{\$a\}/'], ['{$1}', '{n}'], $value);
            }
            foreach (self::CORE as $name => $component) {
                $strings['core:' . $name] = get_string($name, $component);
            }
            foreach (self::ICONS as $icon) {
                $strings['pix:' . $icon] = $renderer->pix_icon($icon, '', 'core');
            }
            self::$strings = $strings;
        }

        return self::$strings;
    }
}
