<?PHP
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */

$string['actionfailed'] = 'Executed action failed.. :(';
$string['argerror'] = 'Invalid arguments supplied: {$a}\n';
$string['backupfailed'] = 'Backup failed';
$string['classmissing'] = 'Class file for plugin {$a} is missing.';
$string['coursecreationfailed'] = 'course creation failed';
$string['courseinvalidstartdate'] = 'invalid startdate specification: {$a}';
$string['courseinvalidenrollable'] = 'invalid enrollable specification: {$a}';
$string['courseinvalidenrolstartdate'] = 'invalid enrolstartdate specification: {$a}';
$string['courseinvalidenrolenddate'] = 'invalid enrolenddate specification: {$a}';
$string['courseinvalidenrolstartdateenrolenddate'] = 'invalid enrolstartdate and enrolenddate specification: {$a} ';
$string['courseinvalidformat'] = 'invalid format specification: {$a}';
$string['courseinvalidnumsections'] = 'invalid numsections specification: {$a}';
$string['courseinvalidnewsitems'] = 'invalid newsitems specification: {$a}';
$string['courseinvalidgroupmode'] = 'invalid groupmode specification: {$a}';
$string['courseinvalidenrol'] = 'invalid enrol specification: {$a}';
$string['courseinvalidcategory'] = 'invalid category specification: {$a}';
$string['coursenotdeleted'] = 'Cannot delete the site course';
$string['coursenotexists'] = 'course does not exist: {$a}';
$string['coursenotfound'] = 'Site not found!';
$string['courseshortexists'] = 'course shortname allready exists: {$a}';
$string['coursesnotfound'] = 'No courses found';
$string['coursenopermission'] = 'Warning: command line user has no rwx permission to {$a}';
$string['courseupdatefailed'] = 'course update failed';
$string['ctlhelp'] = "Usage: moodlectl <action name> [arguments]
    <action name>: is either --help or an action such  as 'create-wiki'
    eg: moodlectl create-wiki
       or
       moodlectl --help
       moodlectl --help create-wiki

    <arguments>: this can be:
       the specific arguments for the action
       or
       a special processor such as --yaml, --php, --json.
       (necessary arguments to be piped through STDIN/STDOUT foreach action)
       NOTE: order of these parameters does matter!

    Options specified on the command line have precedence over STDIN values.

    There is a special 'action name' called batch which enables a series of
    actions to be passed to STDIN in json/yaml/php format. Further details
    of how to access this functionality can be found in moodlectl.php and
    the test suite examples.";
$string['createcategoryerror'] = 'Could not create the new category {$a}';
$string['createcategoryparenterror'] = 'Parent category {$a} does not exist';
$string['changecategoryparenterror'] = 'Could not update the category {$a}';
$string['deserialisefailed'] = 'parsing input on stdin failed for mode: {$a}';
$string['enrolfailed'] = 'Role assignment failed';
$string['filedeletefailed'] = 'File delete failed: {$a}';
$string['fatalerror'] = 'FATAL error occured in moodlectl processing';
$string['forumsnotfound'] = 'No forums found';
$string['forumnotfound'] = 'Forum not found';
$string['forumbaddiscussion'] = 'Disucssion not found';
$string['forumbadmodule'] = 'Module not found';
$string['gradecommitfailed'] = 'Grade commit failed for {$a}';
$string['invalidaction'] = 'Invalid action supplied: {$a}.';
$string['invalidbatch'] = 'Invalid batch file supplied - check structure of file, and matching file type processing mode ({$a}).';
$string['invalidbatchitem'] = 'Invalid batch file supplied - check structure of file especially action number: {$a}.';
$string['missingrequired'] = 'Missing required argument: {$a}';
$string['missingaction'] = 'Action ({$a}) does not exist for this plugin';
$string['noargs'] = 'NO arguments supplied - must at least give action argument eg. moodlectl --help';
$string['noimportgrades'] = 'No import grades to commit for code {$a}';
$string['notcli'] = 'This script is not accessible from the webserver';
$string['pluginsyntaxerror'] = 'Syntax errors in plugin {$a}';
$string['pluginoptsbroken'] = 'The argnument list definition for the chosen plugin is broken.  \nAll options must have attributes: \"long\", and \"required\". \nIf option is not required then it can optionally have the attribute \"default\".';
$string['restorefailed'] = 'Restore failed';
$string['scormcreationfailed'] = 'scorm creation failed';
$string['scormdeletefailed'] = 'Deletion failed: {$a}';
$string['scormnotexists'] = 'scorm activity does not exist: {$a}';
$string['scormsnotfound'] = 'No scorm activities found';
$string['usersnotfound'] = 'No users found';
$string['unenrolfailed'] = 'Role unassignment failed';
$string['wikibadmodule'] = 'Invalid course module id for wiki: {$a}';
$string['wikideletefailed'] = 'Deletion failed: {$a}';
?>
