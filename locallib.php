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
 *
 * This class provides all the functionality for the new scoring module.
 *
 * @package     mod_scoring
 * @copyright   2020 Jun Deng
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Persist student's assignment submission record in scoring_submissions table
 * @param $submission
 * @return bool|int
 * @throws dml_exception
 */
function mod_scoring_save_submission($submission) {
    global $DB, $USER;

    $submission->userid = $USER->id;
    $submission->timecreated = time();

    $record =  array('scoringid' => $submission->scoringid, 'userid' => $USER->id);
    $exists = $DB->get_record('scoring_submissions', $record);
    if ($exists) {
        $DB->delete_records('scoring_submissions', $record);
        $DB->delete_records('files', array('userid' => $USER->id, 'component' => 'mod_scoring',
            'filearea' => 'scoring_submissions', 'itemid' => $exists->id));
    }

    return $DB->insert_record('scoring_submissions', $submission);
}


/**
 * Persist answer submission record in scoring_questions table
 * @param $submission
 * @return bool|int
 * @throws dml_exception
 */
function mod_scoring_save_answers($submission) {
    global $DB, $USER;

    $submission->userid = $USER->id;
    $submission->timecreated = time();

    $record =  array('scoringid' => $submission->scoringid, 'userid' => $USER->id);
    $exists = $DB->get_record('scoring_answers', $record);
    if ($exists) {
        $DB->delete_records('scoring_answers', $record);
        $DB->delete_records('files', array('userid' => $USER->id, 'component' => 'mod_scoring',
            'filearea' => 'scoring_answers', 'itemid' => $exists->id));
    }

    return $DB->insert_record('scoring_answers', $submission);
}

/**
 * Persist scoring results record in scoring_questions table
 * @param $submission
 * @return bool|int
 * @throws dml_exception
 */
function mod_scoring_save_results($result) {
    global $DB, $USER;

    $record =  array('scoringid' => $result->scoringid, 'userid' => $result->userid, 'tid' => $result->tid);
    $exists = $DB->get_record('scoring_results', $record);
    if(!$exists) {
        $DB->insert_record('scoring_results', $result);
    }
}

/**
 * Get submission for the current user (if exists).
 * @param $scoringid
 * @return mixed
 * @throws dml_exception
 */
function mod_scoring_get_submission($scoringid) {
    global $DB, $USER;

    $submission = $DB->get_record('scoring_submissions', array('scoringid' => $scoringid, 'userid' => $USER->id));

    return $submission;
}

/**
 * @param $scoringid
 * @return mixed
 * @throws dml_exception
 */
function mod_scoring_show_question($scoringid) {

}

/**
 * @param $scoringid
 * @return mixed
 * @throws dml_exception
 */
function mod_scoring_get_answer($scoringid) {
    global $DB, $USER;

    $submission = $DB->get_record('scoring_answers', array('scoringid' => $scoringid, 'userid' => $USER->id));

    return $submission;
}

/**
 * @param $s
 * @return string
 */
function mod_scoring_txt_to_csv($s) {
    $str = str_replace(PHP_EOL, '', $s);
    $str = str_replace('"', '"""', $str);
    $str = '"'.$str.'"';
    return $str;
}