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

defined('MOODLE_INTERNAL') || die();

function xmldb_scoring_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020100505) {

        $table = new xmldb_table('scoring_submissions');
        $key = new xmldb_key('unique', XMLDB_KEY_UNIQUE, ['scoringid']);

        // Launch add key unique.
        $dbman->add_key($table, $key);

        $table = new xmldb_table('scoring_questions');
        $key = new xmldb_key('unique', XMLDB_KEY_UNIQUE, ['scoringid']);

        // Launch add key unique.
        $dbman->add_key($table, $key);

        $table = new xmldb_table('scoring_answers');
        $key = new xmldb_key('unique', XMLDB_KEY_UNIQUE, ['scoringid']);

        // Launch add key unique.
        $dbman->add_key($table, $key);

        // Scoring savepoint reached.
        upgrade_mod_savepoint(true, 2020100505, 'scoring');
    }
}
