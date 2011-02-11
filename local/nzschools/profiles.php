<?php
/**
 * profiles.php - Post install customisation profiles
 *
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package profiles
 */

/**
 * Install profile upgrade
 * 
 * @return bool
 */
function nzschoolsprofile_upgrade() {
    global $CFG;

    if (empty($CFG->nzschoolsprofile)) {
        print_error('errornoprofileselected','local_nzschools');
    }
    if (!is_file($CFG->dirroot.'/local/nzschools/profiles/'.$CFG->nzschoolsprofile.'/version.php')) {
        print_error('errorprofileversionmissing','local_nzschools');
    }
    if (!is_file($CFG->dirroot.'/local/nzschools/profiles/'.$CFG->nzschoolsprofile.'/upgrade.php')) {
        print_error('errorupgradeprofilemissing','local_nzschools');
    }
    require($CFG->dirroot.'/local/nzschools/profiles/'.$CFG->nzschoolsprofile.'/version.php');
    require($CFG->dirroot.'/local/nzschools/profiles/'.$CFG->nzschoolsprofile.'/upgrade.php');

    $upgradefunc = 'nzschoolsprofile_upgrade_'.$CFG->nzschoolsprofile;
    
    $oldversion = isset($CFG->nzschoolsprofile_version) ? $CFG->nzschoolsprofile_version : 0;


    if ($upgradefunc($oldversion)) {
        set_config('nzschoolsprofile_version', $nzschoolsprofile_version);
    }
    return(true);
}


?>
