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


/*
 * 返回评分结果页面链接
 */
function scroing_show_result($allresponses, $cm) {

}

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

    return $DB->insert_record('scoring_submissions', $submission);
}

/**
 * Persist question submission record in scoring_questions table
 * @param $submission
 * @return bool|int
 * @throws dml_exception
 */
function mod_scoring_save_questions($submission) {
    global $DB, $USER;

    $submission->userid = $USER->id;
    $submission->timecreated = time();

    return $DB->insert_record('scoring_questions', $submission);
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

    return $DB->insert_record('scoring_answers', $submission);
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
function mod_scoring_get_question($scoringid) {
    global $DB, $USER;

    $submission = $DB->get_record('scoring_questions', array('scoringid' => $scoringid, 'userid' => $USER->id));

    return $submission;
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