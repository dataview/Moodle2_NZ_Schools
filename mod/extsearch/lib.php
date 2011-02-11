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
 * Mandatory public API of extsearch module
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function extsearch_get_types() {
    global $CFG;
    $types = array();

    // The "External Search Result" group header label
    $type = new stdClass();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = "extsearch_group_start";
    $type->typestr = '--'.get_string('modulename', 'extsearch');
    $types[] = $type;

    $type = new stdClass();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = "extsearch&amp;type=google";
    $type->typestr = get_string('google', 'extsearch');
    $types[] = $type;

    if ( get_config(NULL, 'block_extsearch_digitalnz_api_key') ){

        $type = new stdClass();
        $type->modclass = MOD_CLASS_RESOURCE;
        $type->type = "extsearch&amp;type=digitalnz";
        $type->typestr = get_string('digitalnz', 'extsearch');
        $types[] = $type;

        $type = new stdClass();
        $type->modclass = MOD_CLASS_RESOURCE;
        $type->type = "extsearch&amp;type=edna";
        $type->typestr = get_string('edna', 'extsearch');
        $types[] = $type;
    }

    return $types;
}

/**
 * List of features supported in URL module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function extsearch_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function extsearch_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function extsearch_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function extsearch_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function extsearch_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add extsearch instance.
 * @param object $data
 * @param object $mform
 * @return int new extsearch instance id
 */
function extsearch_add_instance($data, $mform) {
    global $DB;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    if (!empty($data->externalurl) && (strpos($data->externalurl, '://') === false) && (strpos($data->externalurl, '/', 0) === false)) {
        $data->externalurl = 'http://'.$data->externalurl;
    }

    $data->timemodified = time();
    $data->searchprovider = $data->type;
    $data->id = $DB->insert_record('extsearch', $data);

    return $data->id;
}

/**
 * Update extsearch instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function extsearch_update_instance($data, $mform) {
    global $CFG, $DB;

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    if (!empty($data->externalurl) && (strpos($data->externalurl, '://') === false) && (strpos($data->externalurl, '/', 0) === false)) {
        $data->externalurl = 'http://'.$data->externalurl;
    }

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->searchprovider = $data->type;

    $DB->update_record('extsearch', $data);

    return true;
}

/**
 * Delete extsearch instance.
 * @param int $id
 * @return bool true
 */
function extsearch_delete_instance($id) {
    global $DB;

    if (!$extsearch = $DB->get_record('extsearch', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('extsearch', array('id'=>$extsearch->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $extsearch
 * @return object|null
 */
function extsearch_user_outline($course, $user, $mod, $extsearch) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'extsearch',
                                              'action'=>'view', 'info'=>$extsearch->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $extsearch
 */
function extsearch_user_complete($course, $user, $mod, $extsearch) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'extsearch',
                                              'action'=>'view', 'info'=>$extsearch->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'extsearch');
    }
}

/**
 * Returns the users with data in one extsearch
 *
 * @param int $extsearchid
 * @return bool false
 */
function extsearch_get_participants($extsearchid) {
    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function extsearch_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/extsearch/locallib.php");

    if (!$extsearch = $DB->get_record('extsearch', array('id'=>$coursemodule->instance), 'id, name, display, displayoptions, externalurl')) {
        return NULL;
    }

    $info = new stdClass();
    $info->name = $extsearch->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = extsearch_guess_icon($extsearch->externalurl);

    $display = extsearch_get_final_display_type($extsearch);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/extsearch/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($extsearch->displayoptions) ? array() : unserialize($extsearch->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/extsearch/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->extra = "onclick=\"window.open('$fullurl'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_OPEN) {
        $fullurl = "$CFG->wwwroot/mod/extsearch/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->extra = "onclick=\"window.location.href ='$fullurl';return false;\"";
    }

    return $info;
}

/**
 * This function extends the global navigation for the site.
 * It is important to note that you should not rely on PAGE objects within this
 * body of code as there is no guarantee that during an AJAX request they are
 * available
 *
 * @param navigation_node $navigation The url node within the global navigation
 * @param stdClass $course The course object returned from the DB
 * @param stdClass $module The module object returned from the DB
 * @param stdClass $cm The course module instance returned from the DB
 */
function extsearch_extend_navigation($navigation, $course, $module, $cm) {
    /**
     * This is currently just a stub so that it can be easily expanded upon.
     * When expanding just remove this comment and the line below and then add
     * you content.
     */
    $navigation->nodetype = navigation_node::NODETYPE_LEAF;
}