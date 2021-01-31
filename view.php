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
 * @package     mod_scoring
 * @copyright   2020 Jun Deng <1013991382@qq.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/submit_assignment.php');
require_once(__DIR__.'/classes/submit_question.php');

global  $renderer, $USER;
// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// module instance id.
$s  = optional_param('s', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('scoring', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('scoring', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($s) {
    $moduleinstance = $DB->get_record('scoring', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('scoring', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_scoring'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/scoring/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading('Automatic Scoring');
$PAGE->set_context($modulecontext);
$PAGE->set_title('Automatic Scoring');


echo $OUTPUT->header();

// 检查访客身份
if (has_capability('mod/scoring:getresult', $modulecontext)) {
//    // 查看提交情况
//    $suburl = new moodle_url('/mod/scoring/view_submission.php', array('id'=>$id));
//    echo $OUTPUT->single_button($suburl, 'Viewing of submissions');
//
//    // 获取评分结果
//    $scoringurl = new moodle_url('/mod/scoring/result.php', array('id'=>$id));
//    echo $OUTPUT->single_button($scoringurl, 'Turn on auto-scoring');

    $mform = new mod_scoring_submit_question();

    // 检测表是否被提交
    if ($data = $mform->get_data()) {
        // 储存题目文本
        $draftitemid = file_get_submitted_draft_itemid('uploadquestion');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'question', $USER->id);

        // 储存答案文本
        $draftitemid = file_get_submitted_draft_itemid('uploadanswer');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'answer', $USER->id);
        redirect(new moodle_url("www.baidu.com"));

    } else if (($data = $mform->get_data())) {
        // Form has been submitted.
        $draftitemid = file_get_submitted_draft_itemid('attachment_filemanager');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_learn', 'submission', 0);
        $data->phpid = $phpid;
        $data->code = $data->content_editor;
        mod_php_save_submission($data);
    } else {
        // Form has not been submitted or there was an error
        // Just display the form
        $mform->set_data(array('id' => $id));
        $mform->display();
    }

} else {
    // 输出题目文本信息
    $fs = get_file_storage();

    // Prepare file record object
    $fileinfo = array(
        'component' => 'mod_php',     // usually = table name
        'filearea' => 'submission',     // usually = table name
        'itemid' => 0,               // usually = ID of row in table
        'contextid' => 69, // ID of context
        'filepath' => '/',           // any path beginning and ending in /
        'filename' => 'acwing学习目录.txt'); // any filename

    // Get file
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    // Read contents
    if ($file) {
        echo '<pre>';
        $contents = $file->get_content();
        echo $contents;
        echo '</pre>';
        echo '<HR style="border:3 double #987cb9" width="80%" color=#987cb9 SIZE=3>';
    } else {
        // file doesn't exist - do something
    }
    // 建立答案提交对象
    $mform = new submit_assignment();
    $mform->display();
}

echo $OUTPUT->footer();
