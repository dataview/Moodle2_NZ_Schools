<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_courseicon_install() {
    global $DB;
    $dbman = $DB->get_manager();
        
    // Define field icon to be added to course
    $table = new xmldb_table('course');
    $field = new xmldb_field('icon', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

    // Conditionally launch add field icon
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }

    // Define field icon to be added to course_categories
    $table = new xmldb_table('course_categories');
    $field = new xmldb_field('icon', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

    // Conditionally launch add field icon
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
}
