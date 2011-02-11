<?php

// try to suppress error notices (since they'll break the image)
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
require_once($CFG->libdir.'/filelib.php');

// try to suppress error notices (since they'll break the image)
$CFG->debudisplay = 0;

$force = optional_param('force',false,PARAM_BOOL);
$fs = get_file_storage();
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$files = $fs->get_area_files($context->id, 'local_nzschools', 'logo', 0);
if ( count($files) ){
	$file = array_pop($files);
	send_stored_file($file);
} elseif ($force){
	die();
} elseif (is_file($CFG->dirroot.'/theme/'.current_theme().'/pix/logo.png')) {
    send_file($CFG->dirroot.'/theme/'.current_theme().'/pix/logo.png', 'logo.png', 525600);
} else {
    send_file($CFG->dirroot.'/pix/moodlelogo-med-white.gif', 'moodlelogo-med-white.gif', 525600);
}
