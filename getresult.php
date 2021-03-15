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
 * Get scoring result.
 *
 * @package     mod_scoring
 * @copyright   2020 Jun Deng
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once ('./locallib.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/../../lib/filelib.php');

global  $CFG,$renderer, $USER;
$id = required_param('id', PARAM_INT);  // Course Module ID.

$urlparams = array('id' => $id);

$url = new moodle_url('/mod/php/getresult.php', $urlparams);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'scoring');

$fs = get_file_storage();

$scoringid = $cm->instance;

$modulecontext = context_module::instance($cm->id);

$cmid = $modulecontext->id;

require_login($course, true, $cm);

// 获取scoring_submissios table中获取当前实例下所有记录，去到files表中查询得到文件并得到文件内容
$records = $DB->get_records('scoring_submissions', array('scoringid' => $scoringid));
$csv_body = null;
$i = 0;

foreach($records as $record) {
    $fileinfo = array(
        'component'  =>  'mod_scoring',
        'filearea'  =>  'scoring_submissions',
        'itemid'  =>  $record->id,
        'contextid' => $cmid,
        'mimetype' => 'text/plain',
    );

    $fileinfo = $DB->get_record('files', $fileinfo);

    // get file content
    $file = $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea,
        $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);

    $str = $file->get_content();
    $str = mod_scoring_txt_to_csv($str);

    $csv_body[$i++] = array(
        'userid' => $record->userid,
        'itemid' => $record->id,
        'scoringid' => $record->scoringid,
        'tid' => 1, // 每个文本文件只有一题
        'content' => $str,
    );
}

// 生成评待分的csv文件供算法调用
$csv_header = ['userid','itemid', 'scoringid', 'tid', 'content'];
$fp = fopen('D:\\wampserver\\moodledata\\temp\\temp_input.csv','w'); // 需根据Linux路径更换
$header = implode(',', $csv_header) . PHP_EOL;
$content = '';
foreach ($csv_body as $k => $v) {
    $content .= implode(',', $v) . PHP_EOL;
}

$csv = $header.$content;     // 拼接
fwrite($fp,  chr(0xEF).chr(0xBB).chr(0xBF));    // 写入并关闭资源
fwrite($fp, $csv);
fclose($fp);

// 生成标准答案的csv文件供算法调用
$header = 'ans,' . PHP_EOL;
$record = $DB->get_record('scoring_answers', array('scoringid' => $scoringid));
$fileinfo = array(
    'component'  =>  'mod_scoring',
    'filearea'  =>  'scoring_answers',
    'itemid'  =>  $record->id,
    'contextid' => $cmid,
    'mimetype' => 'text/plain',
);

$fileinfo= $DB->get_record('files', $fileinfo);  // 获取标准答案文件
$file = $fs->get_file($fileinfo->contextid, $fileinfo->component, $fileinfo->filearea,
    $fileinfo->itemid, $fileinfo->filepath, $fileinfo->filename);

$str = $file->get_content();
$content = mod_scoring_txt_to_csv($str);
$csv = $header.$content;

$fp = fopen('D:\\wampserver\\moodledata\\temp\\ans.csv','w'); // 写入
fwrite($fp,  chr(0xEF).chr(0xBB).chr(0xBF));
fwrite($fp, $csv);
fclose($fp);


// 调用算法文件
$output = null;
exec('python D:\\wampserver\\moodledata\\temp\\testInputOutput.py', $output);

// 将评分结果存入数据库
$fp = fopen("D:\\wampserver\\moodledata\\temp\\score.csv", 'r');
$data = fgetcsv($fp);
while(!feof($fp) && $data = fgetcsv($fp)) {
    $result = null;
    $result->userid = $data[0];
    $result->itemid = $data[1];
    $result->scoringid= $data[2];
    $result->tid = $data[3];
    $result->score = $data[5];

    mod_scoring_save_results($result);
    var_dump($result);
}
