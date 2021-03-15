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

/**
 * Prints an instance of mod_scoring.
 *
 * For students, it provide the ability to upload the assignment file .
 *
 * @package     mod_scoring
 * @copyright  2020 Jun Deng
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once ('./locallib.php');
require_once(__DIR__.'/classes/submit_assignment.php');
require_once(__DIR__.'/classes/submit_answer.php');

global  $renderer, $USER, $DB;
$id = required_param('id', PARAM_INT);  // Course Module ID.

$urlparams = array('id' => $id);

$url = new moodle_url('/mod/php/view.php', $urlparams);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'scoring');

$scoringid = $cm->instance;

$fs = get_file_storage();

$modulecontext = context_module::instance($cm->id);

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_heading('Automatic Scoring');
$PAGE->set_context($modulecontext);
$PAGE->set_title('Automatic Scoring');

// 检查用户权限
if (has_capability('mod/scoring:getresults', $modulecontext)) { // 教师角色
    redirect(new moodle_url('score.php', $urlparams));
} else {// 学生角色
    redirect(new moodle_url('submit.php', $urlparams));
}

