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
 * local_user_identity_checker setting file
 *
 * @package    local_user_identity_checker
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @copyright Université de Strasbourg 2020 {@link http://unistra.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_user_identity_checker_folder',
        get_string('pluginname', 'local_user_identity_checker')));
    $settings = new admin_settingpage(
        'local_user_identity_checker',
        get_string('settings')
    );
    $ADMIN->add('local_user_identity_checker_folder', $settings);
    $ADMIN->add('local_user_identity_checker_folder', new admin_externalpage(
        'local_user_identity_checker_admin_interface',
        get_string('pluginname_admin', 'local_user_identity_checker'),
        "$CFG->wwwroot/local/user_identity_checker/managment.php",
        'moodle/site:config')
    );
    $settings->add(
        new admin_setting_configtextarea(
            'local_user_identity_checker/publickey',
            get_string('public_key_name', 'local_user_identity_checker'),
            get_string('public_key_desc', 'local_user_identity_checker'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configtextarea(
            'local_user_identity_checker/privatekey',
            get_string('private_key_name', 'local_user_identity_checker'),
            get_string('private_key_desc', 'local_user_identity_checker'),
            ''
        )
    );
}
