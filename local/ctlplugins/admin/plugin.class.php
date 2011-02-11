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

/**
* Extend the base plugin class
* This class contains the actions for managing Moodle config values, either core or plugin
* ... and other admin funcitons
*/
class moodlectl_plugin_admin extends moodlectl_plugin_base {

    function help() {
        return
            array(
            'get-config' => "  Get a config entry:
    moodlectl get-config  --name=<an entry> --module=<module/plugin name>",
            'set-config' => "  Set a config value:
    moodlectl set-config --name=<an entry> --module=<module/plugin name> --value=<the value of the config entry>",
            'unset-config' => "  Delete a config entry:
    moodlectl unset-config  --name=<an entry> --module=<module/plugin name>",
            'upload-file' => "  Upload a file to a given destination:
    moodlectl upload-file  --file=<path to file> --course-id=<course id> --destination=<destination directory relative to course>",
            'delete-file' => "  delete a file from a course specific directory:
    moodlectl delete-file  --file=<path to file> --course-id=<course id>",
            'directory-list' => "  list the contents of a directory:
    moodlectl directory-list  --directory=<path to directory> --course-id=<course id>",
            'maintenance-on' => "  Switch maintenance mode on:
    moodlectl maintenance-on  --message='some html or text describing the maintenance mode'",
            'maintenance-off' => "  Switch maintenance mode off:
    moodlectl maintenance-off",
            'list-filters' => "  list the installed filter paths:
    moodlectl list-filters",
            'is-filter-enabled' => "  Check if a given filter is enabled:
    moodlectl is-filter-enabled --filter=<filterpath>",
            'enable-filter' => "  Enable a given filter:
    moodlectl enable-filter --filter=<filterpath>",
            'disable-filter' => "  Disable a given filter:
    moodlectl disable-filter --filter=<filterpath>",
            'enable-module' => "  Show an activity module:
    moodlectl enable-module --module=<module shortname>",
            'disable-module' => "  Hide an activity module:
    moodlectl disable-module --module=<module shortname>",
            'list-modules' => " List installed modules:
    moodlectl list-modules",
            'is-module-enabled' => "  Check if a given module is enabled:
    moodlectl is-module-enabled --module=<module shortname>",
            'enable-block' => "  Show a block:
    moodlectl enable-block --block=<block shortname>",
            'disable-block' => "  Hide a block:
    moodlectl disable-block --block=<block shortname>",
            'list-blocks' => "  List installed blocks:
    moodlectl list-blocks",
            'is-block-enabled' => "  Check if a given block is enabled:
    moodlectl is-block-enabled --block=<block shortname>",
            );
    }

    function command_line_options() {
        return
        array(
        'get-config' => array(
            array('long' => 'name', 'short' => 'c', 'required' => false, 'default' => NULL),
            array('long' => 'module', 'short' => 'm', 'required' => false, 'default' => NULL),
            ),
        'set-config' => array(
            array('long' => 'name', 'short' => 'c', 'required' => true),
            array('long' => 'value', 'short' => 'v', 'required' => true),
            array('long' => 'module', 'short' => 'm', 'required' => false, 'default' => NULL),
            ),
        'unset-config' => array(
            array('long' => 'name', 'short' => 'c', 'required' => true),
            array('long' => 'module', 'short' => 'm', 'required' => false, 'default' => NULL),
            ),
        'upload-file' => array(
            array('long' => 'file',        'short' => 'f', 'required' => true),
            array('long' => 'course-id',   'short' => 'c', 'required' => true, 'type' => 'int'),
            array('long' => 'destination', 'short' => 'd', 'required' => true),
            ),
        'delete-file' => array(
            array('long' => 'file',        'short' => 'f', 'required' => true),
            array('long' => 'course-id',   'short' => 'c', 'required' => true, 'type' => 'int'),
            ),
        'directory-list' => array(
            array('long' => 'directory',   'short' => 'd', 'required' => false, 'default' => ''),
            array('long' => 'course-id',   'short' => 'c', 'required' => true, 'type' => 'int'),
            ),
        'maintenance-on' => array(
            array('long' => 'message', 'short' => 'm', 'required' => true, 'type' => 'html'),
            ),
        'maintenance-off' => array(
            ),
        'list-filters' => array(
            ),
        'is-filter-enabled' => array(
            array('long' => 'filter', 'short' => 'f', 'required' => true),
            ),
        'enable-filter' => array(
            array('long' => 'filter', 'short' => 'f', 'required' => true),
            ),
        'disable-filter' => array(
            array('long' => 'filter', 'short' => 'f', 'required' => true),
            ),
        'enable-module' => array(
            array('long' => 'module',   'short' => 'm', 'required' => true, 'default' => NULL),
            ),
        'disable-module' => array(
            array('long' => 'module',   'short' => 'm', 'required' => true, 'default' => NULL),
            ),
        'is-module-enabled' => array(
            array('long' => 'module',   'short' => 'm', 'required' => true, 'default' => NULL),
            ),
        'list-modules' => array(
            ),
        'enable-block' => array(
            array('long' => 'block',   'short' => 'b', 'required' => true, 'default' => NULL),
            ),
        'disable-block' => array(
            array('long' => 'block',   'short' => 'b', 'required' => true, 'default' => NULL),
            ),
        'list-blocks' => array(
            ),
        'is-block-enabled' => array(
            array('long' => 'block',   'short' => 'b', 'required' => true, 'default' => NULL),
            ),
        );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'get-config':
                // list out the config values
                return moodlectl_plugin_admin::get_config($options['name'], $options['module']);
                break;
            case 'set-config':
                // set a config value
                return moodlectl_plugin_admin::set_config($options['name'], $options['value'], $options['module']);
                break;
            case 'unset-config':
                // delete a config value
                return moodlectl_plugin_admin::unset_config($options['name'], $options['module']);
                break;
            case 'upload-file':
                // upload a file to a course directory
                return moodlectl_plugin_admin::upload_file_action($options['file'], $options['course-id'], $options['destination'], $format);
                break;
            case 'delete-file':
                // delete a file from a course directory
                return moodlectl_plugin_admin::file_delete_action($options['file'], $options['course-id'], $format);
                break;
            case 'directory-list':
                // list the contents of a directory
                return moodlectl_plugin_admin::directory_list_action($options['directory'], $options['course-id'], $format);
                break;
            case 'maintenance-on':
                // switch the moodle instance into maintenance mode
                return moodlectl_plugin_admin::maintenance_on($options['message']);
                break;
            case 'maintenance-off':
                // switch off maintenance mode
                return moodlectl_plugin_admin::maintenance_off();
                break;
            case 'list-filters':
                // list the available filter paths
                return moodlectl_plugin_admin::list_filters();
                break;
            case 'is-filter-enabled':
                // check if a filter is enabled already
                return moodlectl_plugin_admin::is_filter_enabled($options['filter']);
                break;
            case 'enable-filter':
                // check if a filter is enabled already
                return moodlectl_plugin_admin::enable_filter($options['filter']);
                break;
            case 'disable-filter':
                // check if a filter is enabled already
                return moodlectl_plugin_admin::disable_filter($options['filter']);
                break;
            case 'enable-module':
                // show an activity module
                return moodlectl_plugin_admin::enable_module($options['module']);
                break;
            case 'disable-module':
                // show an activity module
                return moodlectl_plugin_admin::disable_module($options['module']);
                break;
            case 'is-module-enabled':
                // show an activity module
                return moodlectl_plugin_admin::is_module_enabled($options['module']);
                break;
            case 'list-modules':
                // List installed modules
                return moodlectl_plugin_admin::list_modules();
                break;
            case 'enable-block':
                // show an activity module
                return moodlectl_plugin_admin::block_enable($options['block']);
                break;
            case 'disable-block':
                // show an activity module
                return moodlectl_plugin_admin::block_disable($options['block']);
                break;
            case 'list-blocks':
                // list installed blocks
                return moodlectl_plugin_admin::list_blocks();
                break;
            case 'is-block-enabled':
                // show an activity module
                return moodlectl_plugin_admin::is_block_enabled($options['block']);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* retrieve selected (or not) confiiguration values
*
* @param string $name the name of a config value
* @param string $module the name of a module of the associated config entry
* @return array list of config values
* */
    static function get_config($name, $module) {
        global $CFG;
        if (isset($name)) {
            return array($name => get_config($module, $name));
        }
        return get_config($module, $name);
    }

/**
* set a config entry optionally specifying the associated module/plugin
*
* @param string $name the name of a config value
* @param string $value the value of a config entry
* @param string $module the name of a module of the associated config entry
* @return boolean success or failure of setting config value
* */
    static function set_config($name, $value, $module) {
        global $CFG;
        set_config($name, $value, $module);
        return moodlectl_plugin_admin::get_config($name, $module);
    }

/**
* delete a specified config entry optinally specifying the assoicated plugin
*
* @param string $name the name of a config value
* @param string $module the name of a module of the associated config entry
* @return boolean success or failure of deleting config value
* */
    static function unset_config($name, $module) {
        global $CFG;
        unset_config($name, $module);
        return moodlectl_plugin_admin::get_config($name, $module);
    }
    
/**
* upload a file to a course specific location
*
* @param string $file file to upload
* @param int $courseid target course id
* @param string $destination target upload directory relative to course
* @return boolean success or failure of uploading file
* */
    static function upload_file_action($file, $courseid, $destination, $format) {
        global $CFG, $MOODLECTL_NO_KEY;
        require_once($CFG->dirroot.'/lib/uploadlib.php');
        if (! $course = get_record("course", "id", $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        $result = self::upload_file($file, $course, $destination);
        if (is_object($result) && get_class($result) == 'Exception') {
           return $result;
        }
        if ($format == 'opts') {
            $MOODLECTL_NO_KEY = true;
        }
        return array('file' => $result);
    }

    
/**
* delete a file from a course specific location
*
* @param string $file file to upload
* @param int $courseid target course id
* @return boolean success or failure of deletion
* */
    static function file_delete_action($file, $courseid, $format) {
        global $CFG, $MOODLECTL_NO_KEY;
        require_once($CFG->dirroot.'/lib/filelib.php');
        if (! $course = get_record("course", "id", $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        $basedir = make_upload_directory("$course->id");
        $fullfile = $basedir.'/'.$file;
        if (! fulldelete($fullfile)) {
            return new Exception(get_string('filedeletefailed', MOODLECTL_LANG, $fullfile));
        }
        return true;
    }
    
    
/**
* list files at a location
*
* @param string $dir directory to list
* @param int $courseid target course id
* @return array list of files |  boolean false
* */
    static function directory_list_action($dir, $courseid, $format) {
        global $CFG, $MOODLECTL_NO_KEY;
        require_once($CFG->dirroot.'/lib/filelib.php');
        if (! $course = get_record("course", "id", $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        $subfilelist = array();
        $basedir = make_upload_directory("$course->id");
        $fulldir = $basedir.'/'.$dir;
        if (!is_dir($fulldir)) {
            return false;
        }
        $currdir = opendir($fulldir);
        while (false !== ($subfile = readdir($currdir))) {
            if ($subfile <> ".." && $subfile <> ".") {
                $subfilelist[] = array('file' => $dir."/".$subfile, 'isdir' => (is_dir($fulldir.'/'.$subfile) ? 1 : 0));
            }
        }
        $MOODLECTL_NO_KEY = true;
        return $subfilelist;
    }
    
/**
* switch the moodle instance into maintenance mode
*
* @param string $message maintenance mode message to be displayed
* @return boolean true/false - success/failure
* */
    static function maintenance_on($message) {
        global $CFG;
        $filename = $CFG->dataroot.'/'.SITEID.'/maintenance.html';
        $file = fopen($filename, 'w');
        fwrite($file, stripslashes($message));
        fclose($file);
        return true;
    }
/**
* switch off maintenance mode
*
* @return boolean true/false - success/failure
* */
    static function maintenance_off() {
        global $CFG;
        $filename = $CFG->dataroot.'/'.SITEID.'/maintenance.html';
        unlink($filename);
        return true;
    }
/**
* list available filters
*
* @return array list of filter paths | boolean false
* */
    static function list_filters() {
        global $CFG;

        // get a list of installed filters
        $installedfilters = array();
        $filterlocations = array('mod','filter');
        foreach ($filterlocations as $filterlocation) {
            $plugins = get_list_of_plugins($filterlocation);
            foreach ($plugins as $plugin) {
                $pluginpath = "$CFG->dirroot/$filterlocation/$plugin/filter.php";
                if (is_readable($pluginpath)) {
                    $installedfilters[] = "$filterlocation/$plugin";
                }
            }
        }

        if (empty($installedfilters)) {
            return false;
        }

        return $installedfilters;
    }
/**
* check if a filter is enabled
*
* @return boolean true/false - enabled/disabled
* */
    static function is_filter_enabled($filterpath) {
        global $CFG;

        // get all the currently selected filters
        if (!empty($CFG->textfilters)) {
            $activefilters = explode(',', $CFG->textfilters);
        } else {
            // no filters currently enabled
            return array('No filters currently enabled');
        }

        $enabled = array_search($filterpath,$activefilters);
        if ($enabled===false) {
            return array($filterpath => 'disabled');
        }

        return array($filterpath => 'enabled');
    }
/**
* show/enable a filter
*
* @return boolean true/false - success/failure
* */
    static function enable_filter($filterpath){
        global $CFG;

        // get a list of installed filters
        $installedfilters = array();
        $filterlocations = array('mod','filter');
        foreach ($filterlocations as $filterlocation) {
            $plugins = get_list_of_plugins($filterlocation);
            foreach ($plugins as $plugin) {
                $pluginpath = "$CFG->dirroot/$filterlocation/$plugin/filter.php";
                if (is_readable($pluginpath)) {
                    $installedfilters["$filterlocation/$plugin"] = "$filterlocation/$plugin";
                }
            }
        }

        // get all the currently selected filters
        if (!empty($CFG->textfilters)) {
            $activefilters = explode(',', $CFG->textfilters);
        } else {
            $activefilters = array();
        }

        // check filterpath is valid
        if (!array_key_exists($filterpath, $installedfilters)) {
            return false;
        } else {
            // add it to installed filters
            $activefilters[] = $filterpath;
            $activefilters = array_unique($activefilters);
        }

        set_config('textfilters', implode(',', $activefilters));
        reset_text_filters_cache();
        return true;
    }
/**
* hide/disable a filter
*
* @return boolean true/false - success/failure
* */
    static function disable_filter($filterpath) {
        global $CFG;

        // get all the currently selected filters
        if (!empty($CFG->textfilters)) {
            $activefilters = explode(',', $CFG->textfilters);
        } else {
            $activefilters = array();
        }
        $key=array_search($filterpath, $activefilters);
        // check filterpath is valid
        if ($key===false) {
            return false;
        }
        // just delete it
        unset($activefilters[$key]);

        set_config('textfilters', implode(',', $activefilters));
        reset_text_filters_cache();
        return true;
    }
/**
* show/enable a module
*
* @return boolean true/false - success/failure
* */
    static function enable_module($modulename) {
        global $CFG;

        if (!$module = get_record("modules", "name", $modulename)) {
            return false;
        }
        set_field("modules", "visible", "1", "id", $module->id); // Show main module
        set_field('course_modules', 'visible', '1', 'visibleold',
                  '1', 'module', $module->id); // Get the previous saved visible state for the course module.
        // clear the course modinfo cache for courses
        // where we just made something visible
        $sql = "UPDATE {$CFG->prefix}course
                SET modinfo=''
                WHERE id IN (SELECT DISTINCT course
                             FROM {$CFG->prefix}course_modules
                             WHERE visible=1 AND module={$module->id})";
        execute_sql($sql, false);
        return true;
    }
/**
* hide/disable a module
*
* @return boolean true/false - success/failure
* */
    static function disable_module($modulename) {
        global $CFG;

        if (!$module = get_record("modules", "name", $modulename)) {
            return false;
        }
        set_field("modules", "visible", "0", "id", $module->id); // Hide main module
        // Remember the visibility status in visibleold
        // and hide...
        $sql = "UPDATE {$CFG->prefix}course_modules
                SET visibleold=visible,
                    visible=0
                WHERE  module={$module->id}";
        execute_sql($sql, false);
        // clear the course modinfo cache for courses
        // where we just deleted something
        $sql = "UPDATE {$CFG->prefix}course
                SET modinfo=''
                WHERE id IN (SELECT DISTINCT course
                             FROM {$CFG->prefix}course_modules
                             WHERE visibleold=1 AND module={$module->id})";
        execute_sql($sql, false);
        return true;
    }
/**
* list installed modules
*
* @return array installed modules  | boolean false
* */
    static function list_modules() {

        if (!$mods = get_list_of_plugins('mod')) {
            return false;
        }
        return $mods;
    }
/**
* Check if a given module is enabled
*
* @return boolean true/false - success/failure
* */
    static function is_module_enabled($modulename) {

        if (!$module = get_record('modules','name',$modulename)) {
            return false;
        }

        $enabled = $module->visible ? 'enabled' : 'disabled';
        return array($modulename => $enabled);
    }
/**
* show/enable a block
*
* @return boolean true/false - success/failure
* */
    static function block_enable($blockname) {

        if (!$block = get_record('block', 'name', $blockname)) {
            return false;
        }
        set_field('block', 'visible', '1', 'id', $block->id);      // Show block
        return true;
    }
/**
* hide/disable a block
*
* @return boolean true/false - success/failure
* */
    static function block_disable($blockname) {
        if (!$block = get_record('block', 'name', $blockname)) {
            return false;
        }
        set_field('block', 'visible', '0', 'id', $block->id);      // Hide block
        return true;
    }
/**
* list installed blocks
*
* @return array installed blocks  | boolean false
* */
    static function list_blocks() {

        if (!$blocks = get_list_of_plugins('blocks')) {
            return false;
        }
        return $blocks;
    }
/**
* Check if a given block is enabled
*
* @return boolean true/false - success/failure
* */
    static function is_block_enabled($blockname) {

        if (!$block = get_record('block','name',$blockname)) {
            return false;
        }

        $enabled = $block->visible ? 'enabled' : 'disabled';
        return array($blockname => $enabled);
    }
}
?>
