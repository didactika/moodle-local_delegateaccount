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
 * This file adds the settings pages to the navigation menu
 *
 * @package   local_delegateaccount
 * @copyright 2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig || has_capability('local/delegateaccount:manage', context_system::instance())) {
    $managepage = new admin_externalpage(
        'local_delegateaccount_manage',
        get_string('manage_accounts', 'local_delegateaccount'),
        new moodle_url('/local/delegateaccount/manage.php'),
        'local/delegateaccount:manage'
    );
    $ADMIN->add('accounts', $managepage);
}
