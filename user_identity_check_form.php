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
 * local_user_identity_checker user form
 *
 * @package    local_user_identity_checker
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @copyright Université de Strasbourg 2020 {@link http://unistra.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use unistra\user_identity_checker\jwt as jwt_lib;

require_once('../../config.php');
require('vendor/autoload.php');
require_once('lib.php');
require_once('user_identity_check_edit_form.php');

$rawtoken = optional_param('t', null, PARAM_RAW_TRIMMED);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/user_identity_checker/user_identity_check_form.php', ['t' => $rawtoken]);
$PAGE->set_title(get_string('authorization_form_title', 'local_user_identity_checker'));
$PAGE->set_heading(get_string('authorization_form_heading','local_user_identity_checker'));

// Trigger moodle authentication.
// Autologinguest set to false to be sure to have to authenticate.
require_login(null, false);
// echo header because of redicrect with notification message
echo $OUTPUT->header();



if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $token = jwt_lib\get_token($rawtoken);
        $tokendata = jwt_lib\get_token_data($token);
        $publickkey = jwt_lib\get_dashboard_public_key($tokendata['dashboard_url']);
        jwt_lib\validate_token($token, $publickkey);
    } catch (jwt_lib\InvalidTokenException $exception) {
        $tokendata = [
            'jti' => '',
            'validation_url' => '',
            'redirect_url' => '',
            'dashboard_url' => '',
        ];
        print_error($exception->getMessage());
    }
    $editform = new user_identity_check_edit_form(null, $tokendata);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editform = new user_identity_check_edit_form(null);

    if ($editform->is_cancelled()) {
        $dashboardurl = $editform->get_submitted_data()->dashboard_url;
        redirect($dashboardurl, '', null);
    } else if ($formdata = $editform->get_data()) {
        if ((bool)$formdata->authorize) {
            $privatekey = get_config('local_user_identity_checker', 'privatekey');
            $dashboardurl = $formdata->dashboard_url;
            $validationurl = $dashboardurl . $formdata->validation_url;
            $issuer = $CFG->wwwroot;
            if (jwt_lib\register($USER->username, $formdata->jti, $privatekey, $validationurl, $issuer)) {
                $message = get_string('success_redirect_message', 'local_user_identity_checker');
                redirect($dashboardurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                print_error('registration_error', 'local_user_identity_checker');
            }
        }
    }
}

$editform->display();

echo $OUTPUT->footer();
