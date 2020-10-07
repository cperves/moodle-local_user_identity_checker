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
 * admin page for jwt keys management
 * @package local_user_identity_checker
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @copyright Université de Strasbourg 2020 {@link http://unistra.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/user_identity_checker/useridentitycheckeradmin.php');
admin_externalpage_setup('local_user_identity_checker_admin_interface', '', array(),
    new moodle_url('/local/user_identity_checker/managment.php', array()));
$PAGE->navbar->add(get_string('pluginname_admin', 'local_user_identity_checker'));
$PAGE->requires->jquery();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_string', 'local_user_identity_checker'));


$perpage = optional_param('perpage', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
if (!$perpage) {
     $perpage = get_user_preferences('tool_my_external_backup_restore_courses_admin_perpage', 10);
} else {
     set_user_preference('tool_my_external_backup_restore_courses_admin_perpage', $perpage);
}
// Action.
$submit = optional_param('submit', null, PARAM_TEXT);
$trigger = optional_param('trigger', 0, PARAM_INT);
if ($submit && $trigger != 0) {
    // GET THE RECORD.
    $scheduledtask = $DB->get_record('user_identity_checker_jwt', array('id' => $trigger));
    if ($scheduledtask) {
        // MODIFY.
        $dashboardurl = optional_param('dashboardurl_'.$trigger, null, PARAM_TEXT);
        $publickey = optional_param('publickey_'.$trigger, null, PARAM_TEXT);
        if ($scheduledtask->dashboardurl != $dashboardurl || $scheduledtask->publickey != $publickey
            && $submit == get_string('edit')) {
            $scheduledtask->dashboardurl = $dashboardurl;
            $scheduledtask->publickey = $publickey;
            $DB->update_record('user_identity_checker_jwt', $scheduledtask);
        }

        // DELETE.
        if ($submit == get_string('delete')) {
            $DB->delete_records('user_identity_checker_jwt', array('id' => $scheduledtask->id));
        }
    }
}
// INSERT.
if ($submit == get_string('create')) {
    $newline = array();
    $newline['dashboardurl'] = null;
    $newline['publickey'] = null;
    $DB->insert_record('user_identity_checker_jwt', $newline);
}
$table = new user_identity_checker_admin($perpage, $page);
$table->is_persistent(true);
echo $table->out($perpage, true);
echo $OUTPUT->footer();