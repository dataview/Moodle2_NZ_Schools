<?php

/////////////////////////////////////////////////////////////////////////////
//                                                                         //
// NOTICE OF COPYRIGHT                                                     //
//                                                                         //
// Moodle - Calendar extension                                             //
//                                                                         //
// Copyright (C) 2003-2004  Greek School Network            www.sch.gr     //
//                                                                         //
// Designed by:                                                            //
//     Avgoustos Tsinakos (tsinakos@teikav.edu.gr)                         //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// Programming and development:                                            //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// For bugs, suggestions, etc contact:                                     //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// The current module was developed at the University of Macedonia         //
// (www.uom.gr) under the funding of the Greek School Network (www.sch.gr) //
// The aim of this project is to provide additional and improved           //
// functionality to the Asynchronous Distance Education service that the   //
// Greek School Network deploys.                                           //
//                                                                         //
// This program is free software; you can redistribute it and/or modify    //
// it under the terms of the GNU General Public License as published by    //
// the Free Software Foundation; either version 2 of the License, or       //
// (at your option) any later version.                                     //
//                                                                         //
// This program is distributed in the hope that it will be useful,         //
// but WITHOUT ANY WARRANTY; without even the implied warranty of          //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           //
// GNU General Public License for more details:                            //
//                                                                         //
//          http://www.gnu.org/copyleft/gpl.html                           //
//                                                                         //
/////////////////////////////////////////////////////////////////////////////

require_once('../config.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$from = required_param('from', PARAM_ALPHA);
$var = required_param('var', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$cal_d = optional_param('cal_d', 0, PARAM_INT);
$cal_m = optional_param('cal_m', 0, PARAM_INT);
$cal_y = optional_param('cal_y', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$type = optional_param('type', '', PARAM_ALPHA);

$url = new moodle_url('/calendar/set.php', array('from'=>$from,'var'=>$var));
if ($id !== 0) {
    $url->param('id', $id);
}
if ($cal_d !== 0) {
    $url->param('cal_d', $cal_d);
}
if ($cal_m !== 0) {
    $url->param('cal_m', $cal_m);
}
if ($cal_y !== 0) {
    $url->param('cal_y', $cal_y);
}
if ($action !== 0) {
    $url->param('action', $action);
}
if ($type !== 0) {
    $url->param('type', $type);
}
$PAGE->set_url($url);
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM)); //TODO: wrong

// Initialize the session variables
calendar_session_vars();

// Ensure course id passed if relevant
// Required due to changes in view/lib.php mainly (calendar_session_vars())
$courseid = '';
if (!empty($id)) {
    $courseid = '&course='.$id;
}

switch($var) {
    case 'setuser':
        // Not implemented yet (or possibly at all)
    break;
    case 'setcourse':
        $id = intval($id);
        if($id == 0) {
            $SESSION->cal_courses_shown = array();
            calendar_set_referring_course(0);
        }
        else if($id == 1) {
            $SESSION->cal_courses_shown = calendar_get_default_courses(true);
            calendar_set_referring_course(0);
        }
        else {
            if($DB->get_record('course', array('id'=>$id)) === false) {
                // There is no such course
                $SESSION->cal_courses_shown = array();
                calendar_set_referring_course(0);
            }
            else {
                calendar_set_referring_course($id);
                $SESSION->cal_courses_shown = $id;
            }
        }
    break;
    case 'showgroups':
        $SESSION->cal_show_groups = !$SESSION->cal_show_groups;
        set_user_preference('calendar_savedflt', calendar_get_filters_status());
    break;
    case 'showcourses':
        $SESSION->cal_show_course = !$SESSION->cal_show_course;
        set_user_preference('calendar_savedflt', calendar_get_filters_status());
    break;
    case 'showglobal':
        $SESSION->cal_show_global = !$SESSION->cal_show_global;
        set_user_preference('calendar_savedflt', calendar_get_filters_status());
    break;
    case 'showuser':
        $SESSION->cal_show_user = !$SESSION->cal_show_user;
        set_user_preference('calendar_savedflt', calendar_get_filters_status());
    break;
}

switch($from) {
    case 'event':
        redirect(CALENDAR_URL.'event.php?action='.$action.'&amp;type='.$type.'&amp;id='.intval($id));
    break;
    case 'month':
        redirect(CALENDAR_URL.'view.php?view=month'.$courseid.'&cal_d='.$cal_d.'&cal_m='.$cal_m.'&cal_y='.$cal_y);
    break;
    case 'upcoming':
        redirect(CALENDAR_URL.'view.php?view=upcoming'.$courseid);
    break;
    case 'day':
        redirect(CALENDAR_URL.'view.php?view=day'.$courseid.'&cal_d='.$cal_d.'&cal_m='.$cal_m.'&cal_y='.$cal_y);
    break;
    case 'course':
        redirect($CFG->wwwroot.'/course/view.php?id='.intval($id));
    break;
    default:

}