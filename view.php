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
 * For teacher, it provide the ability to uploadtest file and answer file.
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
require_once(__DIR__.'/classes/submit_test.php');

global  $renderer, $USER;
$id = required_param('id', PARAM_INT);  // Course Module ID.

$urlparams = array('id' => $id);

$url = new moodle_url('/mod/php/view.php', $urlparams);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'scoring');

$scoringid = $cm->instance;

$modulecontext = context_module::instance($cm->id);

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_heading('Automatic Scoring');
$PAGE->set_context($modulecontext);
$PAGE->set_title('Automatic Scoring');


echo $OUTPUT->header();

// 检查用户权限
if (has_capability('mod/scoring:getresults', $modulecontext)) { // 教师角色
    $mform = new mod_scoring_submit_test();
    $submissionquestion = mod_scoring_get_question($scoringid);
    $submissionanswer = mod_scoring_get_answer($scoringid);

    // 检测表是否被提交
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('view.php', $urlparams));
        return;
    } else if (($data = $mform->get_data())) {
        // 获取当前scoring实例id
        $data->scoringid = $scoringid;

        // 保存题目文本提交记录
        $itemid = mod_scoring_save_questions($data);

        // 储存题目文本
        $draftitemid = file_get_submitted_draft_itemid('upload_question');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_questions', $itemid);

        // 保存答案文本提交记录
        $itemid = mod_scoring_save_answers($data);

        // 储存答案文本
        $draftitemid = file_get_submitted_draft_itemid('upload_answer');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_answers', $itemid);
    } else {
        $mform->set_data(array('id' => $id));
        // 是否已经上传
        if ($submissionquestion) {
            $draftitemid = 0;            // 通过传引用获取$draftitemid的值
            file_prepare_draft_area($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_questions', $submissionquestion->id);
            $mform->set_data(array('upload_question' => $draftitemid));
        }

        // 是否已经上传
        if ($submissionanswer) {
            $draftitemid = 0;            // 通过传引用获取$draftitemid的值
            file_prepare_draft_area($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_answers', $submissionanswer->id);
            $mform->set_data(array('upload_answer' => $draftitemid));
        }
        $mform->display();
    }
} else { // 学生角色
    $mform = new mod_scoring_submit_assignment();
    $submission = mod_scoring_get_submission($scoringid);

    // 检测表是否被提交
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('view.php', $urlparams));
        return;
    } else if (($data = $mform->get_data())) {
        // 获取当前scoring实例id
        $data->scoringid = $scoringid;

        // 保存作业文本提交记录
        $itemid = mod_scoring_save_submission($data);

        // 储存作业文本
        $draftitemid = file_get_submitted_draft_itemid('upload_assignment');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_submissions', $itemid);
    } else {
        $mform->set_data(array('id' => $id));

        // 是否已经上传
        if ($submission) {
            $draftitemid = 0;            // 通过传引用获取$draftitemid的值
            file_prepare_draft_area($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_submissions', $submission->id);
            $mform->set_data(array('upload_assignment' => $draftitemid));
        }
        $mform->display();
    }
}

echo $OUTPUT->footer();
