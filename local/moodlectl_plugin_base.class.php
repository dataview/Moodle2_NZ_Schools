<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */


if (false) $DB = new moodle_database();


class moodlectl_plugin_base {

    /**
    * Constructor for the base moodlectl plugin class
    *
    * This is a stub providing hooks into the commandline options,
    * help and execution events for the processing of commandline
    *  Moodle processes.
    */
    function moodlectl_plugin_base() {

    }

    /**
     * Entry point to specify the help text to be offered up to users
     *
     * help is an array() of text strings for each action that a plugin can perform
     * eg:
     *  return array(
     *     'action1' => 'some help text',
     *     'action2' => 'some other help text',
     *    );
     *
     * @return string of command line help for this plugin
     */
    function help() {

        throw new Exception('HELP! I have no help!');
        return array( 'help' => 'HELP! I have no help!');
    }

    /**
     * Entry point to specify the command line options to be handled
     *
     * NOTE: you cannot use -h and --help as these are handled via
     * the help callback
     *
     * Be wary of plugin names clashing with the command line option names eg: don't use 'course' as this is
     * a plugin name.
     *
     * example:
     *  $opts = array(
     *      array(
     *       'create-wiki' => array(
     *           array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int'),
     *           array('long' => 'name', 'short' => 'n', 'required' => true),
     *           array('long' => 'separate-groups', 'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => true),
     *           array('long' => 'summary', 'short' => 't', 'required' => true),
     *          ),
     *       'change-wiki' => array(
     *           array('long' => 'wiki-id', 'short' => 'w', 'required' => true, 'type' => 'int'),
     *           array('long' => 'name', 'short' => 'n', 'required' => true),
     *           array('long' => 'separate-groups', 'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => true),
     *           array('long' => 'summary', 'short' => 't', 'required' => true),
     *          ),
     *       'delete-wiki' => array(
     *           array('long' => 'wiki-id', 'short' => 'w', 'required' => true, 'type' => 'int'),
     *          )
     *       );
     *
     * The outer array is an array of actions that are available to the user at the command line.
     * Within this is an array containing the command line options for the given action.
     * Each command line options is describe by an array of attributes
     * The default type is 'string'.
     *
     * Default does not need to be specified for 'required' options.
     *
     * Must supply values for 'long', 'required'.
     * 'long' options must not be substring of one another (see http://pear.php.net/bugs/bug.php?id=4475)
     * 'short' option id is optional, as we may run out of letters/numbers for some of the more complicated actions.
     *
     * Supported types are: 'int', 'boolean', 'html', 'string' - these values will be automatically cleaned on input.
     * note that default for boolean types should always be false.
     *
     * @return array of command line options exepected for this plugin
     */
    function command_line_options() {

        throw new Exception('plugin command_line_options not implemented');
        return array();
    }

    /**
     * Entry point to plugin execution.
     *
     * if global $MOODLECTL_NO_KEY is true then the printing out of the  key of
     * a simple key/value array is suppressed.  This might be usefull if the
     * result returned is just a list of lines to be printed when $format is
     * 'opts' (ie. not yaml/json/php).
     *
     * @param action  string, name of the action invoked at the command line
     * @param options  object, reference to command line options populated
     *        from cleaned values.
     * @param mode  string, mode of execution - single/batch - batch gets called
     *        multiple times, but may wish to vary output accordingly
     * @param format string, the format of the results - opts/json/yaml/php
     * @return bool|Exception object, return true to confirm success
     *        false to indicate failure.
     */
    function execute($action, $options, $mode, $format) {

        throw new Exception('plugin execute not implemented');
        return true;
    }

/**
* copy a given file to the correct moodledata location
*
* @param string $file the file to upload
* @param object $course the course object
* @param string $destination the target directory
* @return string full target filename | or Exception()
* */
    static function upload_file($file, $course, $destination) {
        global $CFG, $MOODLECTL_FILESYS_PERMS;
        
        // reinforce the filesystem permissions checking here
        if ($MOODLECTL_FILESYS_PERMS == false) {
            moodlectl_console_write(get_string('coursenopermission', MOODLECTL_LANG, $CFG->dataroot), false, false);
            exit(1);
        }
        
        if (!is_file($file)) {
            echo "file: $file\n";
            return new Exception("The source file must be a file ($file)");
        }
        // immitates files/index.php for uploading files
        if (! $basedir = make_upload_directory("$course->id")) {
            return new Exception("The site administrator needs to fix the file permissions");
        }
        if ($destination == '') {
            $destination = "/";
        }
        if ($destination == "/backupdata") {
            if (! make_upload_directory("$course->id/backupdata")) {   // Backup folder
                return new Exception("Could not create backupdata folder.  The site administrator needs to fix the file permissions");
            }
        }
        if (!(strpos($destination, $CFG->dataroot) === false)) {
            // take it out for giving to make_upload_directory
            $destination = substr($destination, strlen($CFG->dataroot)+1);
        }
        if ($destination{strlen($destination)-1} == '/') { // strip off a trailing / if we have one
            $destination = substr($destination, 0, -1);
        }
        if (!make_upload_directory("$course->id".'/'.$destination, true)) { //TODO maybe put this function here instead of moodlelib.php now.
            return new Exception("Could not create $destination folder.");
        }
        if (!is_dir($basedir.'/'.$destination)) {
            return new Exception("Requested directory does not exist ($basedir/$destination).");
        }
        $files = explode('/', $file);
        $file_name = array_pop($files);
        $target = $basedir.'/'.$destination.'/'.$file_name;
        $destination = $CFG->dataroot .'/'. $destination; // now add it back in so we have a full path
        if (copy($file, $target)) {
            chmod($target, $CFG->directorypermissions);
            self::clam_log_upload($target, $course);
            return $target;
        }
        else {
            return new Exception("failed to move file to $destination/$file_name");
        }
    }
    
/**
 * Adds a file upload to the log table so that clam can resolve the filename to the user later if necessary
 *
 * @uses $CFG
 * @uses $USER
 * @param string $newfilepath ?
 * @param course $course {@link $COURSE}
 * @param boolean $nourl ?
 * @todo Finish documenting this function
 */
    static function clam_log_upload($newfilepath, $course=null, $nourl=false) {
        global $CFG, $USER;
        // get rid of any double // that might have appeared
        $newfilepath = preg_replace('/\/\//', '/', $newfilepath);
        if (strpos($newfilepath, $CFG->dataroot) === false) {
            $newfilepath = $CFG->dataroot .'/'. $newfilepath;
        }
        $courseid = 0;
        if ($course) {
            $courseid = $course->id;
        }
        add_to_log($courseid, 'upload', 'upload', '', $newfilepath);
    }
    
}
?>