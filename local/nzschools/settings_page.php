<?php
require_once(dirname(__FILE__) . '/../../config.php');
//require_once(dirname(__FILE__).'/settings_form.php');
require_once($CFG->dirroot.'/local/nzschools/settings_form.php');
require_once($CFG->dirroot.'/local/nzschools/lib.php');

// Whether or not this is being called during Moodle's initial setup
// (If it is, then we want to redirect to admin/index.php after submit,
// otherwise, we want to redirect back to this page.)
$init = optional_param('init', false, PARAM_BOOL);
$statusmsg = false;

$site = get_site();
if (!$site) {
    redirect($CFG->wwwroot.'/admin/index.php');
    exit;
}

// Security check
require_login(0, false);
$context = get_context_instance(CONTEXT_SYSTEM);
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/nzschools/settings_page.php');
$mform = new nzschoolssettings_form();

if ($data = $mform->get_data()) {

    // Set site name
    $site->fullname     = $data->sitename;
    $site->shortname    = $data->shortname;

    $DB->update_record('course', $site);

    // Save install profile type
    set_config('nzschoolsprofile', $data->nzschoolsprofile);

    // Save years
    set_config('fromyear', $data->fromyear+1);
    set_config('toyear', $data->toyear+1);

    // Auto create cats
    set_config('createcats', !empty($data->createcats));

    // Handle request to delete existing logo
    if ( $data->deletepicture ){
    	$fs = get_file_storage();
    	$fs->delete_area_files($context->id, 'local_nzschools', 'logo', 0);
    }
    
    // Handle new logo
    $tempfilepath = $mform->save_temp_file('logo');
    if ( $tempfilepath ){
        local_nzschools_process_logo( $tempfilepath );
        @unlink($tempfilepath);
    }

    if ( $init ){
        redirect($CFG->wwwroot.'/admin/index.php');
        die();
    } else {
        $statusmsg = get_string('changessaved');
    }
}

// Display
$strnzschoolssettings = get_string('nzschoolssettings', 'local_nzschools');
//    $navigation  = build_navigation(array(array('name' => $strnzschoolssettings, 'link' => null, 'type' => 'misc')));
//    $PAGE->requires->yui2_lib(array('yui_yahoo',
//                'yui_event',
//                'yui_dom',
//                'yui_connection',
//                'yui_animation',
//                'yui_container',
//                'yui_dragdrop',
//                'yui_slider',
//                'yui_element',
//                'yui_get',
//                'yui_colorpicker'));

//    echo "<script type=\"text/javascript\" src=\"/schools/local/nzschools/settings.js\" />";
$PAGE->requires->js('/local/nzschools/settings.js');

    // Temporarily set CSS files to be loaded for this page
//    $CFG->stylesheets[] = $CFG->wwwroot.'/lib/yui/colorpicker/assets/skins/sam/colorpicker.css';
//    $CFG->stylesheets[] = $CFG->wwwroot.'/lib/yui/slider/assets/skins/sam/slider.css';
//    $CFG->stylesheets[] = $CFG->wwwroot.'/lib/yui/container/assets/skins/sam/container.css';


//    print_header($strnzschoolssettings, $strnzschoolssettings, $navigation, '', '', false, '&nbsp;', '&nbsp;');
require_once($CFG->dirroot.'/lib/adminlib.php');
$adminroot = admin_get_root(); // need all settings
$settingspage = $adminroot->locate('nzschoolssettings', true);
$title = $settingspage->visiblename;
$PAGE->set_title($title);
$PAGE->set_heading($title);
//$PAGE->set_title('NZ Schools Moodle Settings');
//$PAGE->set_heading('NZ Schools Moodle Settings');
echo $OUTPUT->header();
//    print_heading($strnzschoolssettings);
echo $OUTPUT->heading($strnzschoolssettings);
if ($statusmsg) {
    echo $OUTPUT->notification($statusmsg, 'notifysuccess');
}

$mform->set_data(array('colour1'        => @$CFG->theme_colour1,
                       'colour2'        => @$CFG->theme_colour2,
                       'colour3'        => @$CFG->theme_colour3,
                       'plainbg'        => get_config('theme_nz_schools','plainbg'),
                       'sitename'       => $site->fullname,
                       'shortname'      => $site->shortname,
                       'nzschoolsprofile' => @$CFG->nzschoolsprofile,
                       'fromyear'       => empty($CFG->fromyear) ? 0 : $CFG->fromyear-1,
                       'toyear'         => empty($CFG->toyear) ? 12 : $CFG->toyear-1,
                       'createcats'     => isset($CFG->createcats) ? $CFG->createcats : 1
                       ));
// Print path to dynamic css file for use in JS
echo '<script type="text/javascript">var dynamic_css = "'.$CFG->wwwroot.'/theme/'.current_theme().'/dynamic_css.php";</script>';

// Print page body
echo '<table width="100%"><tr><td width="80%">';

$mform->display();

echo '</td><td valign="top">';

// Print a block to assist with styling
//    print_side_block('<h2>'.get_string('themetips', 'local_nzschools').'</h2>', get_string('themetipsdetail', 'local_nzschools'));

echo '</td></tr></table>';

echo $OUTPUT->footer();
//    print_footer('none');

?>