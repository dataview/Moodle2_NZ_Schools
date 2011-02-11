<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_courseicon_uninstall() {
    global $DB;
    $dbman = $DB->get_manager();

    // Define field icon to be dropped from course
    $table = new xmldb_table('course');
    $field = new xmldb_field('icon');

    // Conditionally launch drop field icon
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    // Define field icon to be dropped from course
    $table = new xmldb_table('course_categories');
    $field = new xmldb_field('icon');

    // Conditionally launch drop field icon
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }

    return true;
}
