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
 * Executes the login-as functionality for delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_delegateaccount\manager;

$targetuserid = required_param('id', PARAM_INT);

require_login();
require_sesskey();

$accountsurl = new moodle_url('/local/delegateaccount/pages/accounts.php');
if (\core\session\manager::is_loggedinas()) {
    redirect(
        $accountsurl,
        get_string('error_alreadyloggedinas', 'local_delegateaccount'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$syscontext = context_system::instance();
require_capability('local/delegateaccount:use', $syscontext);

$error = manager::get_delegated_access_error((int)$USER->id, $targetuserid);
if ($error !== null) {
    redirect($accountsurl, get_string($error, 'local_delegateaccount'), null, \core\output\notification::NOTIFY_ERROR);
}

manager::start_delegated_session($targetuserid);
redirect(new moodle_url('/my/'));
