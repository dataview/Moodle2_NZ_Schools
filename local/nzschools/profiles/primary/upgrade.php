<?php
require_once($CFG->dirroot.'/local/nzschools/lib.php');
//
// This script is will customise moodle to the primary school moodle profile
//

/**
 * Customise moodle for primary schools
 *
 * @param $oldversion   The current version number of the local customisations
 * @global $CFG
 * @return bool
 */
function nzschoolsprofile_upgrade_primary($oldversion) {
    global $CFG, $USER, $DB;

    $return = true;

    if ($oldversion < 2009072700) {

        $createcats = $CFG->createcats;
        $fromyear   = $CFG->fromyear;
        $toyear     = $CFG->toyear;

        // Setup course categories
        if (!empty($createcats)) {
            local_nzschools_createcats($fromyear, $toyear);
            if ( isset($CFG->templatecat) ){
                local_nzschools_restoretemplates($CFG->dirroot.'/local/nzschools/profiles/primary/templatecourses');
            }
        }

        // Configure Front page
        set_config('frontpage','0'); // News items
        set_config('frontpageloggedin','0,4'); // News items and Combo list
        // The "My Moodle Redirect" feature has been removed in Moodle 2 (see MDL-23024).
        // However, setting the default home page to "My Moodle" has nearly the same effect.
        //set_config('mymoodleredirect', '1'); // Force users to my moodle
        set_config('defaulthomepage',HOMEPAGE_MY);
    }

    if ($oldversion < 2009092103) {

        // Insert Scales
        // NCEA Achievement Standard
        $scale = new stdClass();
        $scale->courseid = 0;
        $scale->name = 'NCEA (Achievement Standard)';
        $scale->scale = 'Did not submit, Re-submit, Not Achieved, Achieved, Merit, Excellence';
        $scale->description = '';
        $scale->userid = $USER->id;
        $scale->timemodified = time();
        $DB->insert_record('scale', $scale);

        // NCEA Unit Standard
        $scale = new stdClass();
        $scale->courseid = 0;
        $scale->name = 'NCEA (Unit Standard)';
        $scale->scale = 'Did not submit, Re-submit, Not Achieved, Achieved';
        $scale->description = '';
        $scale->userid = $USER->id;
        $scale->timemodified = time();
        $DB->insert_record('scale', $scale);

        // Star Rating
        $scale = new stdClass();
        $scale->courseid = 0;
        $scale->name = 'Star Scale';
        $scale->scale = '★,★★,★★★,★★★★,★★★★★';
        $scale->description = '';
        $scale->userid = $USER->id;
        $scale->timemodified = time();
        $DB->insert_record('scale', $scale);

        // Set default country
        set_config('country', 'NZ');

        // Set default course format
        set_config('format', 'simple', 'moodlecourse');

        // Hide guest login button
        set_config('guestloginbutton', '0');

        // Disable grade display to students
        set_config('showgrades', '0', 'moodlecourse');

        // Create the principal role
        if (!$DB->record_exists('role', array('shortname'=>'principal'))) {
            $principalrole = create_role(addslashes(get_string('principal', 'local_nzschools')), 'principal',
                    addslashes(get_string('principaldescription', 'local_nzschools')), 'editingteacher');

            $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));

            if ($principalrole && $teacherrole) {
                role_cap_duplicate($teacherrole, $principalrole);
            }
        }


        // Hide modules
        $DB->set_field('modules', 'visible', 0, array('name'=>'survey'));

        // Hide blocks
        $DB->set_field('block', 'visible', 0, array('name'=>'loancalc'));
        $DB->set_field('block', 'visible', 0, array('name'=>'mentees'));
        $DB->set_field('block', 'visible', 0, array('name'=>'mnet_hosts'));
        $DB->set_field('block', 'visible', 0, array('name'=>'search'));
        $DB->set_field('block', 'visible', 0, array('name'=>'gaccess'));
        $DB->set_field('block', 'visible', 0, array('name'=>'gmail'));
        $DB->set_field('block', 'visible', 0, array('name'=>'gdata'));

        // Enable filters
        filter_set_global_state('mod/glossary',TEXTFILTER_ON);
        filter_set_global_state('filter/mediaplugin',TEXTFILTER_ON);

        // Disable messaging
        set_config('messaging', '0');

        // Prevent users hiding blocks
        set_config('allowuserblockhiding', '0');

        // Weeks start on Monday
        set_config('calendar_startwday', '1');

        // Open docs in a new window
        set_config('doctonewwindow', '1');

    }

    return ($return);
}

?>
