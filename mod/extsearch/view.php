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
 * extsearch module main user interface
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/extsearch/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // extsearch instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $extsearch = $DB->get_record('extsearch', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('extsearch', $extsearch->id, $extsearch->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('extsearch', $id, 0, false, MUST_EXIST);
    $extsearch = $DB->get_record('extsearch', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/extsearch:view', $context);

add_to_log($course->id, 'extsearch', 'view', 'view.php?id='.$cm->id, $extsearch->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/extsearch/view.php', array('id' => $cm->id));

if ($redirect) {
    // coming from course page or extsearch index page,
    // the redirection is needed for completion tracking and logging
    $fullurl = $extsearch->externalurl;
    redirect(str_replace('&amp;', '&', $fullurl));
}

switch (extsearch_get_final_display_type($extsearch)) {
    case RESOURCELIB_DISPLAY_EMBED:
        extsearch_display_embed($extsearch, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        extsearch_display_frame($extsearch, $cm, $course);
        break;
    default:
        extsearch_print_workaround($extsearch, $cm, $course);
        break;
}
