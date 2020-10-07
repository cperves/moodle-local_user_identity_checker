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
 * This file contains the definition for course table which subclassses easy_table
 *
 * @package   local_user_identity_checker
 * @copyright  2020 unistra  {@link http://unistra.fr}
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/local/user_identity_checker/locallib.php');

/**
 * Extends table_sql to provide a table of course to import from external platform
 *
 * @package   tool_assignmentupgrade
 */
class user_identity_checker_admin extends table_sql implements renderable {
    /** @var int $perpage */
    private $perpage = 10;
    /** @var int $rownum (global index of current row in table) */
    private $rownum = -1;
    /** @var renderer_base for getting output */
    private $output = null;
    /** @var boolean $any - True if there is one or more entries*/
    public $anyentry = false;

    /**
     * This table loads the list of all course programmed to be restored from external p)lf to current plf
     *
     * @param int $perpage How many per page
     * @param int $rowoffset The starting row for pagination
     */
    public function __construct($perpage=null, $page=null, $rowoffset=0) {
        global $PAGE, $CFG;
        parent::__construct('user_identity_checker_any_entries');
        if (isset($perpage)) {
             $this->perpage = $perpage;
        }
        if (isset($page)) {
             $this->currpage = $page;
        }

        $this->define_baseurl(new moodle_url('/local/user_identity_checker/managment.php'));

        $this->anyentries = user_identity_checker_any_entries();

        // Do some business - then set the sql.
        if ($rowoffset) {
            $this->rownum = $rowoffset - 1;
        }
        $fields = '*';
        $from = '{user_identity_checker_jwt}';
        $where = 'true';

        $this->set_sql($fields, $from, $where);
        $this->set_count_sql('select count(*) from {user_identity_checker_jwt}');

        $columns = array();
        $headers = array();

        $columns[] = 'modify';
        $columns[] = 'delete';
        $headers[] = '';
        $headers[] = '';
        $columns[] = 'dashboardurl';
        $headers[] = get_string('dashboardurl', 'local_user_identity_checker');
        $columns[] = 'publickey';
        $headers[] = get_string('publickey', 'local_user_identity_checker');

        // Set the columns.
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->no_sorting('action');
        $this->use_pages = true;
        $this->collapsible(false);
    }

    protected function col_modify(stdClass $row) {
        $out = html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $this->currpage, 'name' => 'page'));
        $out .= html_writer::empty_tag('input',
            array('type' => 'submit', 'value' => get_string('edit'),
                'name' => 'submit', 'onclick' => '$(\'#trigger\').val('.$row->id.')'));
        return $out;
    }

    protected function col_delete(stdClass $row) {
        $out = html_writer::empty_tag('input', array('type' => 'hidden', 'value' => $this->currpage, 'name' => 'page'));
        $out .= html_writer::empty_tag('input',
            array('type' => 'submit', 'value' => get_string('delete'), 'name' => 'submit',
                'onclick' => '$(\'#trigger\').val('.$row->id.')'));
        return $out;
    }
    /**
     * Return the number of rows to display on a single page
     *
     * @return int The number of rows per page
     */
    protected function get_rows_per_page() {
        return $this->perpage;
    }

    /**
     * @param stdClass $row
     * @return string
     */
    protected function col_dashboardurl(stdClass $row) {
        return html_writer::tag('textarea', $row->dashboardurl, array('name' => 'dashboardurl_'.$row->id));

    }
    /**
     * @param stdClass $row
     * @return string
     */
    protected function col_publickey(stdClass $row) {
        return html_writer::tag('textarea', $row->publickey, array('name' => 'publickey_'.$row->id));

    }
    // Override fonctions to include form.
    public function start_html() {

         parent::start_html();
         echo html_writer::start_tag('form', array('action' => $this->baseurl->out()));
         echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'trigger', 'id' => 'trigger'));
    }

    public function finish_html() {
        echo html_writer::end_tag('form');
        echo html_writer::start_tag('form', array('action' => $this->baseurl->out()));
        echo html_writer::empty_tag('input',
            array('type' => 'submit', 'value' => get_string('create'), 'name' => 'submit'));
        echo html_writer::end_tag('form');
        parent::finish_html();
    }
}
