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
 *
 * Enabling teacher upload answer to start auto scoring.
 * @package    mod_scoring
 * @copyright  2020 Jun Deng
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once ('./locallib.php');

global  $renderer, $USER;
$id = required_param('id', PARAM_INT);  // Course Module ID.

$urlparams = array('id' => $id);

$url = new moodle_url('/mod/php/score.php', $urlparams);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'scoring');

$scoringid = $cm->instance;

$modulecontext = context_module::instance($cm->id);

require_login($course, true, $cm);

$PAGE->set_url($url);
$PAGE->set_heading('Automatic Scoring');
$PAGE->set_context($modulecontext);
$PAGE->set_title('Automatic Scoring');


echo $OUTPUT->header();

if (has_capability('mod/scoring:getresults', $modulecontext)) {
    $mform = new mod_scoring_submit_answer();
    $submissionanswer = mod_scoring_get_answer($scoringid);

    // 检测表是否被提交
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('score.php', $urlparams));
        return;
    } else if (($data = $mform->get_data())) {
        // 获取当前scoring实例id
        $data->scoringid = $scoringid;

        // 保存答案文本提交记录
        $itemid = mod_scoring_save_answers($data);

        // 储存答案文本
        $draftitemid = file_get_submitted_draft_itemid('upload_answer');
        file_save_draft_area_files($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_answers', $itemid);
    } else {
        $mform->set_data(array('id' => $id));

        // 是否已经上传
        if ($submissionanswer) {
            $draftitemid = 0;            // 通过传引用获取$draftitemid的值
            file_prepare_draft_area($draftitemid, $cm->context->id, 'mod_scoring', 'scoring_answers', $submissionanswer->id);
            $mform->set_data(array('upload_answer' => $draftitemid));
        }
        $mform->display();
    }

    $scoringurl = new moodle_url('/mod/scoring/getresult.php', $urlparams);
    echo $OUTPUT->single_button($scoringurl, 'Turn on auto-scoring');
}

echo $OUTPUT->footer();



