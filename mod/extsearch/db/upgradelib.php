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
 * extsearch module upgrade related helper functions
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Migrate extsearch module data from 1.9 resource_old table to new extsearch table
 * @return void
 */
function extsearch_20_migrate() {
    global $CFG, $DB;

    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->libdir/resourcelib.php");
    require_once("$CFG->dirroot/course/lib.php");

    if (!file_exists("$CFG->dirroot/mod/resource/db/upgradelib.php")) {
        // bad luck, somebody deleted resource module
        return;
    }

    require_once("$CFG->dirroot/mod/resource/db/upgradelib.php");

    // create resource_old table and copy resource table there if needed
    if (!resource_20_prepare_migration()) {
        // no modules or fresh install
        return;
    }

    if (!$candidates = $DB->get_recordset('resource_old', array('type'=>'digitalnz', 'migrated'=>0))) {
        return;
    }

    foreach ($candidates as $candidate) {
        $externalurl = $candidate->reference;
        $siteid = get_site()->id;

        // Figure out if it's a DigitalNZ or EDNA result, based on the URL
        if (substr($externalurl, 0, 30) == 'http://api.digitalnz.org/edna/'){
            $type = 'edna';
        } elseif (substr($externalurl, 0, 33) == 'http://api.digitalnz.org/records/'){
            $type = 'digitalnz';
        } else {
            $type = 'google';
        }

        upgrade_set_timeout();

        if ($CFG->texteditors !== 'textarea') {
            $intro       = text_to_html($candidate->intro, false, false, true);
            $introformat = FORMAT_HTML;
        } else {
            $intro       = $candidate->intro;
            $introformat = FORMAT_MOODLE;
        }

        $extsearch = new stdClass();
        $extsearch->course       = $candidate->course;
        $extsearch->name         = $candidate->name;
        $extsearch->intro        = $intro;
        $extsearch->introformat  = $introformat;
        $extsearch->externalurl  = $externalurl;
        $extsearch->searchprovider = $type;
        $extsearch->timemodified = time();

        $options    = array('printheading'=>0, 'printintro'=>1);
        $parameters = array();
        if ($candidate->options == 'frame') {
            $extsearch->display = RESOURCELIB_DISPLAY_FRAME;

        } else if ($candidate->options == 'objectframe') {
            $extsearch->display = RESOURCELIB_DISPLAY_EMBED;

        } else if ($candidate->popup) {
            $extsearch->display = RESOURCELIB_DISPLAY_POPUP;
            if ($candidate->popup) {
                $rawoptions = explode(',', $candidate->popup);
                foreach ($rawoptions as $rawoption) {
                    list($name, $value) = explode('=', trim($rawoption), 2);
                    if ($value > 0 and ($name == 'width' or $name == 'height')) {
                        $options['popup'.$name] = $value;
                        continue;
                    }
                }
            }

        } else {
            $extsearch->display = RESOURCELIB_DISPLAY_AUTO;
        }
        $extsearch->displayoptions = serialize($options);

        if (!$extsearch = resource_migrate_to_module('extsearch', $candidate, $extsearch)) {
            continue;
        }
    }

    $candidates->close();

    // clear all course modinfo caches
    rebuild_course_cache(0, true);
}