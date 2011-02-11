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
 * List of extsearchs in course
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 onwards Martin Dougiamas (http://dougiamas.com)
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'extsearch', 'view all', "index.php?id=$course->id", '');

$strextsearch       = get_string('modulename', 'extsearch');
$strextsearches      = get_string('modulenameplural', 'extsearch');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/extsearch/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strextsearches);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strextsearches);
echo $OUTPUT->header();

if (!$extsearchs = get_all_instances_in_course('extsearch', $course)) {
    notice(get_string('thereareno', 'moodle', $strextsearches), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($extsearchs as $extsearch) {
    $cm = $modinfo->cms[$extsearch->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($extsearch->section !== $currentsection) {
            if ($extsearch->section) {
                $printsection = get_section_name($course, $sections[$extsearch->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $extsearch->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($extsearch->timemodified)."</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // each extsearch has an icon in 2.0
        $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
    }

    $class = $extsearch->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    $table->data[] = array (
        $printsection,
        "<a $class $extra href=\"view.php?id=$cm->id\">".$icon.format_string($extsearch->name)."</a>",
        format_module_intro('extsearch', $extsearch, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
