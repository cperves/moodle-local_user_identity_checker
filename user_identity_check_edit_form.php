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
 * local_user_identity_checker edit form class
 *
 * @package    local_user_identity_checker
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @copyright Université de Strasbourg 2020 {@link http://unistra.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once('lib.php');

class user_identity_check_edit_form extends moodleform {

    protected function definition() {
        global $CFG, $PAGE;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('form_header', 'local_user_identity_checker'));
        $mform->addElement('static', 'desctext', get_string('form_description', 'local_user_identity_checker'));

        $mform->addElement('hidden', 'jti');
        $mform->setDefault('jti', $this->_customdata['jti']);
        $mform->setType('jti', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'validation_url');
        $mform->setDefault('validation_url', $this->_customdata['validation_url']);
        $mform->setType('validation_url', PARAM_RAW);

        $mform->addElement('hidden', 'dashboard_url');
        $mform->setDefault('dashboard_url', $this->_customdata['dashboard_url']);
        $mform->setType('dashboard_url', PARAM_RAW);

        $mform->addElement('hidden', 'redirect_url');
        $mform->setDefault('redirect_url', $this->_customdata['redirect_url']);
        $mform->setType('redirect_url', PARAM_RAW);

        $mform->addElement(
            'advcheckbox',
            'authorize',
            get_string('form_field_authorize_name', 'local_user_identity_checker'),
            get_string('form_field_authorize_label', 'local_user_identity_checker')
        );

        $this->add_action_buttons();

        $mform->addHelpButton('authorize', 'autorizehelp', 'local_user_identity_checker');
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if ($data['authorize'] == 0) {
            $errors['authorize'] = get_string('form_error_authorization_not_given', 'local_user_identity_checker');
        }
        return $errors;
    }
}
