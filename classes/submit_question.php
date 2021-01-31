<?php


/**
 * The question submitted event
 *
 * @package     mod_scoring
 * @copyright   2020 Jun Deng <1013991382@qq.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class mod_scoring_submit_question extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        // upload question
        $mform->addElement('filemanager', 'uploadquestion', 'upload question');

        // upload answer
        $mform->addElement('filemanager', 'uploadanswer', 'upload answer');

        $this->add_action_buttons();
    }

    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}