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

if (\core\session\manager::is_loggedinas()) {
    $errurl = new moodle_url('/local/delegateaccount/pages/accounts.php');
    redirect(
        $errurl,
        get_string('error_alreadyloggedinas', 'local_delegateaccount'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

$syscontext = context_system::instance();
require_capability('local/delegateaccount:use', $syscontext);

$realuserid = $USER->id;

if (!manager::delegation_exists($realuserid, $targetuserid)) {
    $errurl = new moodle_url('/local/delegateaccount/pages/accounts.php');
    redirect($errurl, get_string('error_unauthorized', 'local_delegateaccount'), null, \core\output\notification::NOTIFY_ERROR);
}

\core\session\manager::loginas($targetuserid, $syscontext);
redirect(new moodle_url('/my/'));
