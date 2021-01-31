<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_scoring_upgrade($oldversion = 0)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020100502) {

        // Define table scoring_submissions to be created.
        $table = new xmldb_table('scoring_submissions');

        // Adding fields to table scoring_submissions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('scoringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table scoring_submissions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for scoring_submissions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

/*********************/
        $table1 = new xmldb_table('scoring_result');

        // Adding fields to table scoring_result.
        $table1->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table1->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table1->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table1->add_field('scoringid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table1->add_field('submitid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table1->add_field('score', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table scoring_result.
        $table1->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for scoring_result.
        if (!$dbman->table_exists($table1)) {
            $dbman->create_table($table1);
        }

        // Scoring savepoint reached.
        upgrade_mod_savepoint(true, 2020100502, 'scoring');
    }
}
