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
 * Private extsearch module utility functions
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/extsearch/lib.php");

/**
 * Print extsearch header.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @return void
 */
function extsearch_print_header($extsearch, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$extsearch->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($extsearch);
    echo $OUTPUT->header();
}

/**
 * Print extsearch heading.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function extsearch_print_heading($extsearch, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($extsearch->displayoptions) ? array() : unserialize($extsearch->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($extsearch->name), 2, 'main', 'extsearchheading');
    }
}

/**
 * Print extsearch introduction.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function extsearch_print_intro($extsearch, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($extsearch->displayoptions) ? array() : unserialize($extsearch->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($extsearch->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'extsearchintro');
            echo format_module_intro('extsearch', $extsearch, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display extsearch frames.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function extsearch_display_frame($extsearch, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        extsearch_print_header($extsearch, $cm, $course);
        extsearch_print_heading($extsearch, $cm, $course);
        extsearch_print_intro($extsearch, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('extsearch');
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $exteurl = $extsearch->externalurl;
        $navurl = "$CFG->wwwroot/mod/extsearch/view.php?id=$cm->id&amp;frameset=top";
        $title = strip_tags(format_string($course->shortname.': '.$extsearch->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','extsearch'));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename"/>
    <frame src="$exteurl" title="$modulename"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print extsearch info and link.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function extsearch_print_workaround($extsearch, $cm, $course) {
    global $OUTPUT;

    extsearch_print_header($extsearch, $cm, $course);
    extsearch_print_heading($extsearch, $cm, $course, true);
    extsearch_print_intro($extsearch, $cm, $course, true);

    $fullurl = $extsearch->externalurl;

    $display = extsearch_get_final_display_type($extsearch);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $options = empty($extsearch->displayoptions) ? array() : unserialize($extsearch->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="urlworkaround">';
    print_string('clicktoopen', 'extsearch', "<a href=\"$fullurl\" $extra>$fullurl</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded extsearch file.
 * @param object $extsearch
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function extsearch_display_embed($extsearch, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_url_mimetype($extsearch->externalurl);
    $fullurl  = $extsearch->externalurl;
    $title    = $extsearch->name;

    $link = html_writer::tag('a', $fullurl, array('href'=>str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'extsearch', $link);

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mimetype == 'audio/mp3') {
        // MP3 audio file
        $code = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/x-flv') {
        // Flash video file
        $code = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'application/x-shockwave-flash') {
        // Flash file
        $code = resourcelib_embed_flash($fullurl, $title, $clicktoopen);

    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {
        // Windows Media Player file
        $code = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/quicktime') {
        // Quicktime file
        $code = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/mpeg') {
        // Mpeg file
        $code = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'audio/x-pn-realaudio-plugin') {
        // RealMedia file
        $code = resourcelib_embed_real($fullurl, $title, $clicktoopen);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    extsearch_print_header($extsearch, $cm, $course);
    extsearch_print_heading($extsearch, $cm, $course);

    echo $code;

    extsearch_print_intro($extsearch, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best diaply format.
 * @param object $extsearch
 * @return int display type constant
 */
function extsearch_get_final_display_type($extsearch) {
    global $CFG;

    if ($extsearch->display != RESOURCELIB_DISPLAY_AUTO) {
        return $extsearch->display;
    }

    // detect links to local moodle pages
    if (strpos($extsearch->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($extsearch->externalurl, 'file.php') === false and strpos($extsearch->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = mimeinfo('type', $extsearch->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Optimised mimetype detection from general URL
 * @param $fullurl
 * @return string mimetype
 */
function extsearch_guess_icon($fullurl) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullurl, '/') < 3 or substr($fullurl, -1) === '/') {
        // most probably default directory - index.php, index.html, etc.
        return 'f/web';
    }

    $icon = mimeinfo('icon', $fullurl);
    $icon = 'f/'.str_replace(array('.gif', '.png'), '', $icon);

    if ($icon === 'f/html' or $icon === 'f/unknown') {
        $icon = 'f/web';
    }

    return $icon;
}
