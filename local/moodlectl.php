#!/usr/bin/env php
<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 *
 * moodlectl is a command line tool for accessing Moodle functions in various ways.
 * All the accessable functions (actions) are made available through plugins published in the
 * ctlplugins directory.
 *
 * These actions can be executed by either passing all the necessary command line options, or by
 * serialising the appropriate options in json/yaml/php and passing them in via stdin.
 *
 * Additionally, actions can be batched together, for performance/convenience as an array of sets
 * of options, also passed in via stdin (yaml/json/php).
 *
 * examples:
 * Command line only
 * ./moodlectl.php create-wiki --course-id=1 --name='The new wiki' --summary='Just a summary'
 *
 * JSON
 * cat json.txt | ./moodlectl.php create-wiki --json --name='overriding: json'
 * json.txt looks like:
 * {"course-id":1,"name":"The new wiki - json","summary":"this is overriding","separate-groups":false}
 *
 * Inspecting this data structure, it is made up of:
 * $action_options = array(  // an array of action options - just like you would pass on the command line
 *                         "course-id" => 1,
 *                         "name" => "The new wiki - json - 1",
 *                         "summary" => "this is overriding",
 *                         "separate-groups" => false
 *                        );
 *
 * Batched actions:
 * cat batch_json.txt | ./moodlectl.php --batch --json
 * batch_json.txt looks like:
 * [
 *  {"create-wiki":{"course-id":1,"name":"The new wiki - json - 1","summary":"this is overriding","separate-groups":false}},
 *  {"create-wiki":{"course-id":1,"name":"The new wiki - json - 2","summary":"this is overriding","separate-groups":false}},
 *  {"create-wiki":{"course-id":1,"name":"The new wiki - json - 3","summary":"this is overriding","separate-groups":false}},
 * ]
 *
 * This shows that a batch is a sequential array of a keyed array containing one action (key is action name), that contains
 * the array of options - lets break that down:
 * $batch_data = array( // an array of actions
 *                     array( // an array that describes a single action ie. action name and the action options
 *                           "create-wiki" => array( // an array of action options
 *                                                  "course-id" => 1,
 *                                                  "name" => "The new wiki - json - 1",
 *                                                  "summary" => "this is overriding",
 *                                                  "separate-groups" => false
 *                                                  )
 *                           ),
 *                     // repeat for each action
 *                    );
 */

// set error reporting for debugging
error_reporting(E_ALL & !E_NOTICE & ~E_DEPRECATED); 
ini_set('display_errors', '1');

// specifies which directory moodlectl lives in, in relation to dirroot
define('MOODLECTL_BASE', 'local');
// the language text group - often the same as above but not necessarily ...
define('MOODLECTL_LANG', 'local_ctlplugins');
//mtrace($string, $eol="\n", $sleep=0)
//========================================================================================//

// PHP 5.3 seems to require
define('CLI_SCRIPT', true);

//add dirname to path for finding config.php
require(dirname(__FILE__).'/../config.php');

// setup the environment
moodlectl_setup_env();

// check for write permissions to the dataroot
global $MOODLECTL_FILESYS_PERMS;
$MOODLECTL_FILESYS_PERMS = true;
if (!moodlectl_permissions_test()) {
    moodlectl_console_write(get_string('coursenopermission', MOODLECTL_LANG, $CFG->dataroot), false, false);
    $MOODLECTL_FILESYS_PERMS = false;
}

//retrieve the plugins and start the command line options processing
$plugins = moodlectl_get_plugins();

//fetch arguments
$args = Console_Getopt::readPHPArgv();

//checking errors for argument fetching
if (PEAR::isError($args)) {
    moodlectl_console_write('argerror');
    exit(1);
}

// remove stderr/stdout redirection args
$args = preg_grep('/2>&1/', $args, PREG_GREP_INVERT);

// must supply at least one arg for the action to perform
if (count($args) <= 1) {
    moodlectl_console_write('noargs');
    exit(1);
}

// get the first option - this must always be a plugin name or help
$action = trim($args[1], '- ');
array_shift($args);
if ($action == 'help') {
    if (isset($args[1]) && $action_help = trim($args[1], '- ')) {
        if (isset($plugins[$action_help])) {
            moodlectl_console_write(moodlectl_help_text(array($action_help => $plugins[$action_help])), false, false);
        }
        else {
            // action does not exist
            $text = get_string('invalidaction', MOODLECTL_LANG, $action)."\n".moodlectl_help_text($plugins);
            moodlectl_console_write($text, false, false);
            exit(1);
        }
    }
    else {
        moodlectl_console_write(moodlectl_help_text($plugins), false, false);
    }
    exit(1);
}

// we now test to see if this is in batch mode
// ./moodlectl.php --batch --[php/yaml/json]
// the second argument must be a file type for standard in
// the structure returned by the deserialisation is an arry of arrays containing sets of instructions
if ($action == 'batch') {
    // which mode?
    $mode = moodlectl_determine_mode($args);

    // check and parse stdin for piped in values
    $batch = moodlectl_parse_stdin($action, $plugins, $args);
    if (is_array($batch)) {
        $actions = count($batch);
        $c = 0;
        foreach ($batch as $item) {
            // check each action - there should be only one per batch item
            $c++;
            if (count($item) > 1) {
                $text = get_string('invalidbatchitem', MOODLECTL_LANG, $c)."\n".moodlectl_help_text($plugins);
                moodlectl_console_write($text, false, false);
                exit(1);
            }
            foreach ($item as $item_action => $item_values) {
                $item_values = (array)$item_values;
                // is it a valid action
                if (!array_key_exists($item_action, $plugins)) {
                    $text = get_string('invalidaction', MOODLECTL_LANG, $item_action)."\n".moodlectl_help_text($plugins);
                    moodlectl_console_write($text, false, false);
                    exit(1);
                }
                // check options and set defaults
                moodlectl_check_options($item_action, $plugins, $item_values);
                // execute and handle output for action
                moodlectl_execute($item_action, $plugins, $mode, $item_values, 'batch');
                // output breaker between results
                if ($c < $actions) {
                    fwrite(STDOUT, "<<<<<BATCH>>>>>\n");
                }
            }
        }
    }
    else {
        $text = get_string('invalidbatch', MOODLECTL_LANG, $mode)."\n".moodlectl_help_text($plugins);
        moodlectl_console_write($text, false, false);
        exit(1);
    }
}
else {
    // check for single Invalid action supplied
    if (!array_key_exists($action, $plugins)) {
        $text = get_string('invalidaction', MOODLECTL_LANG, $action)."\n".moodlectl_help_text($plugins);
        moodlectl_console_write($text, false, false);
        exit(1);
    }
    $mode = moodlectl_determine_mode($args);

    // check and parse stdin for piped in values
    $values = moodlectl_parse_commandline($action, $plugins, $args);

    // check options and set defaults
    moodlectl_check_options($action, $plugins, $values);

    // execute and handle output for action
    moodlectl_execute($action, $plugins, $mode, $values);
}

exit(0);



//=========================================================================//
/**
 * Get the list of plugins
 *
 * use the base directory to find a list of directories that contain the
 * plugins.
 * load each plugin and then create an instance.
 *
 * @return array the list of plugin objects available
 */
function moodlectl_get_plugins() {
    global $CFG;

    $plugins = array();
    $ctlplugins = get_list_of_plugins(MOODLECTL_BASE.'/ctlplugins');
	
	// hack: to hide the lang directory, as it seems to be needed in this location for the lang file to work: 'lang' is not a plugin directory, so hide it..
	
    foreach ($ctlplugins as $ctlplugin) {
        if (!empty($CFG->{'moodlectl_plugin_hide_'.$ctlplugin})) {  // Not wanted
            continue;
        }
        // ignore the test plugin unless we are testing
        if (!getenv('MOODLECTL_TEST') && $ctlplugin == 'test') {
            continue;
        }
        // check class file exists for plugin
        $file = $CFG->dirroot.'/'.MOODLECTL_BASE."/ctlplugins/$ctlplugin/plugin.class.php";
        if (!file_exists($file)) {
            moodlectl_console_write('classmissing', MOODLECTL_LANG, true, $ctlplugin);
            exit(1);
        }

        // load the plugin
        require($file);

        // create the object
        $ctlclass = "moodlectl_plugin_$ctlplugin";
        $plugin = new $ctlclass();
        $actions = $plugin->command_line_options();
        foreach ($actions as $action => $options) {
            // fix up default type of string
            foreach ($actions[$action] as &$opt) {
                if (!array_key_exists('type', $opt)) {
                    $opt['type'] = 'string';
                }
            }
            $plugins[$action] = array('plugin' => $plugin, 'pluginname' => $ctlplugin, 'options' => $actions[$action]);
        }
    }
    return $plugins;
}


/**
 * generate the moodlctl help text for a given list of plugins.
 *
 * @param array a list of plugin objects
 * @return string of help text
 */
function moodlectl_help_text($plugins) {
    $text = '';
    // get help text from plugins
    if (count($plugins) == 1) { // do detailed help for chosen plugin
        foreach ($plugins as $key => $plugin) { // there is only one
            $action_help = $plugin['plugin']->help();
            $action_options = $plugin['plugin']->command_line_options();
            foreach ($action_options as $action_name => $options) {
                if ($action_name != $key) { // we only want the one
                    continue;
                }
                $text .= "Action: $action_name \n";
                if (isset($action_help[$key])) {
                    $text .= $action_help[$key]."\n";
                }
                if ($options and count($options) > 0) {
                    $text .= "\n  Complete parameter specifications:\n";
                    foreach ($options as $option) {
                        $required = $option['required'] ? "required" : "optional";
                        $type = sprintf("%-10s", (isset($option['type']) ? $option['type'] : 'string'));
                        $name = sprintf("%-20s", $option['long']);
                        $text .= "    $name $type $required \n";
                    }
                    $text .= "\n";
                }
            }
        }
    }
    else {
        $text = get_string('ctlhelp', MOODLECTL_LANG)."\n\n";
        $pluginlist = array();
        $actionlist = array();
        foreach ($plugins as $key => $plugin) {
            if (isset($actionlist[$plugin['pluginname']])) {
                $actionlist[$plugin['pluginname']] .= ', '. $key;
            }
            else {
                $actionlist[$plugin['pluginname']] = $key;
            }
            $pluginlist[$plugin['pluginname']] = $plugin['plugin'];
        }
        foreach ($actionlist as $key => $actions) {
            $text .= "Plugin: $key\nActions: $actions\n\n";
        }
    }
    return $text;
}


/**
 * Get the option that matches this either long or short op
 *
 * @param array $options list of options definition arrays
 * @param string $option an option name - long or short
 * @return array the definition of a particular option - type, default etc.
 */
function moodlectl_get_option($options, $option) {
    foreach ($options as $opt) {
        if ($opt['long'] == $option || (isset($opt['short']) && $opt['short'] == $option)) {
            return $opt;
        }
    }
    return false;
}


/**
 * Use the option definition to determine how to clean the value
 *
 * @param array $option options definition
 * @param mixed the option value
 * @return mixed clean option value.
 */
function moodlectl_clean_parms($option, $unclean) {
    // preserve NULLs, as they will come from defaults
    if ($unclean === NULL) {
        return $unclean;
    }
    $value = '';
    if ($option) {
        switch ($option['type']) {
            case 'boolean':
                $value = clean_param($unclean, PARAM_BOOL);
                break;
            case 'int':
                $value = clean_param($unclean, PARAM_INT);
                break;
            case 'html':
                $value = clean_param($unclean, PARAM_CLEANHTML);
                break;
            case 'double':
                $value = clean_param($unclean, PARAM_NUMBER);
                break;
            default:
                // treat as text/string
                $value = clean_param($unclean, PARAM_TEXT);
                break;
        }
    }
    return $value;
}


/**
 * Write to standard out and error with exit in error.
 *
 * @param string  $identifier
 * @param string $module name of module $module
 * @param boolean $use_string_lib flag to decide whether to do get_string() lookup or not
 * @param string $a value that get substituted into get_string() entry (normal $a processing)
 * @return nothing
 */
function moodlectl_console_write($identifier, $module=MOODLECTL_LANG, $use_string_lib=true, $a=NULL) {
    // emulated cli script - something like cron
    if ($use_string_lib) {
        fwrite(STDOUT, get_string($identifier, $module, $a)."\n");
    } else {
        fwrite(STDOUT, $identifier."\n");
    }
    // clear all output
    fflush(STDOUT);
}


/**
 * parse stdin to determine option values passed in
 *
 * @param string $action chosen user action
 * @param array $plugins array of plugin objects
 * @param array $args command line arguments
 * @param array $long_opts array of options that the command line will be scanned for
 * @return array option values.
 */
function moodlectl_parse_stdin($action, $plugins, $args, &$long_opts = false) {
    // get the second option - this might be help - arg[2] might also be a yaml/json/php directive
    $values = array();
    $mode = moodlectl_determine_mode($args);
    if ($mode != 'opts') {
        if ($long_opts) {
            $long_opts[]= $mode;
        }
        // explicitly state that there is no input file - all options handled on the ommand line
        $is_there_input=stream_select($read=array(STDIN), $write=NULL, $except=NULL, 0);
        if ($is_there_input < 1) {
            return $values;
        }
        else {
            switch ($mode) {
                case 'yaml':
                    $values = syck_load(file_get_contents('php://stdin'));
                    break;
                case 'json':
                    $values = json_decode(file_get_contents('php://stdin'));
                    break;
                case 'php':
                    // suppress error reporting and then check for NULL value on unserialize()
                    error_reporting(E_ALL & !E_NOTICE);
                    $values = unserialize(file_get_contents('php://stdin'));
                    error_reporting(E_ALL);
                    break;
                default:
                    break;
            }
        }
    }

    // loading from file can destroy $values if it's empty
    if ($values === false || $values === NULL) {
        moodlectl_console_write('deserialisefailed', MOODLECTL_LANG, true, $mode);
        moodlectl_console_write(moodlectl_help_text(array($action => $plugins[$action])), false, false);
        exit(1);
    }
    else {
        $values = (array)$values;
    }
    return $values;
}


/**
 * parse commandline to determine option values passed in
 *
 * @param string $action chosen user action
 * @param array $plugins array of plugin objects
 * @param array $args command line arguments
 * @return array option values.
 */
function moodlectl_parse_commandline($action, $plugins, $args) {
    // construct the options for the selected plugin as well as validate their structure
    $options = $plugins[$action]['options'];

    // get short and long options from plugins
    $short_opts = '';
    $short_opts_b = '';
    $long_opts = array();
    $long_opts_b = array();
    foreach ($options as $option) {
        // ensure mandatory plugin option descriptors exist
        if (!array_key_exists('long', $option) || !array_key_exists('required', $option)) {
            moodlectl_console_write('pluginoptsbroken');
            exit(1);
         }
         // short options may not exist as we run out of letters/numbers for the bigger ones
         if ($option['type'] == 'boolean') {
            if (isset($option['short'])) {
                $short_opts_b .= $option['short'];
            }
            $long_opts_b[]= $option['long'];
         }
         else {
            if (isset($option['short'])) {
                $short_opts .= $option['short'].':';
            }
            $long_opts[]= $option['long'].'==';
         }
    }
    $short_opts .= $short_opts_b;
    $long_opts = array_merge($long_opts, $long_opts_b);

    // add on the boolean opt for the action name
    $long_opts []= $action;

    // check and parse stdin for piped in values
    $values = moodlectl_parse_stdin($action, $plugins, $args, $long_opts);

    // still parse/check rest of options
    // override the values with the command line opts now - take precedence over stdin values
    // parse the command line options
    $console_opt = Console_Getopt::getOpt($args, $short_opts, $long_opts);

    //detect errors in the options such as invalid opt
    if (PEAR::isError($console_opt)) {
        $errormsg = str_replace('Console_Getopt: ', '', $console_opt->message);
        moodlectl_console_write('argerror', MOODLECTL_LANG, true, $errormsg);
        moodlectl_console_write(moodlectl_help_text(array($action => $plugins[$action])), false, false);
        exit(1);
    }

    // stash the values from the command line
    $opts = $console_opt[0];
    if (sizeof($opts) > 0) {
        foreach ($opts as $o) {
            $opt = moodlectl_get_option($options, trim($o[0], '- '));
            if ($opt) {
                if ($opt['type'] == 'boolean') {
                    $values[$opt['long']] = true;
                } else if ($o[1] !== NULL) {
                    $values[$opt['long']] = $o[1];
                }
            }
        }
    }
    return $values;
}


/**
 * check options, and add defaults
 *
 * @param string $action chosen user action
 * @param array $plugins array of plugin objects
 * @param array $values to be checked and defaults set
 * @return array option values.
 */
function moodlectl_check_options($action, $plugins, &$values) {
    // check required options, and set defaults
    $options = $plugins[$action]['options'];
    foreach ($options as $option) {
         if ($option['required'] && !array_key_exists($option['long'], $values)) {
            moodlectl_console_write('missingrequired', MOODLECTL_LANG, true, $option['long']);
            moodlectl_console_write("\n".moodlectl_help_text(array($action => $plugins[$action])), false, false);
            exit(1);
         }
         // set the default
         if (!array_key_exists($option['long'], $values)) {
             if (array_key_exists('default', $option)) {
                $values[$option['long']] = $option['default'];
             }
             else {
                 // must have some sort of default set in the database otherwise
             }
         }
         else {
            // further validate the options for correct type
            $values[$option['long']] = moodlectl_clean_parms($option, $values[$option['long']]);
         }
    }
}


/**
 * execute action and output result
 *
 * @param string $action chosen user action
 * @param array $plugins array of plugin objects
 * @param string $mode mode of options passing
 * @param array $values for execution
 * @param style $style of execution - single or batch
 * @return array option values.
 */
function moodlectl_execute($action, $plugins, $mode, $values, $style='single') {
    global $MOODLECTL_NO_KEY;
    $MOODLECTL_NO_KEY = false;

    // we now have all the values - pass them into the plugin to execute
    $result = $plugins[$action]['plugin']->execute($action, $values, $style, $mode);

    // test the result - this can be true/false or an array or an object
    // Exception objects are a special case as this has a message that can be pulled out
    // lets do the bad ones first
    if ($result === false) {
        if ($mode) {
         $result = new Exception(get_string_manager()->get_string('actionfailed', MOODLECTL_LANG));
        }
        else {
            moodlectl_console_write('actionfailed');
            exit(1);
        }
    }

    // reformat Exception so it can be serialised
    if (is_object($result) && get_class($result) == 'Exception') {
        $exception = array('message' => $result->getMessage()
                           //'code' => $result->getCode(),
                           //'file' => $result->getFile(),
                           //'line' => $result->getLine()
                           //'trace' => $result->getTraceAsString()
						   );
        switch ($mode) {
            case 'yaml':
                fwrite(STDOUT, syck_dump($exception)."\n");
            break;
            case 'json':
                fwrite(STDOUT, json_encode($exception)."\n");
                break;
            case 'php':
                fwrite(STDOUT, serialize($exception)."\n");
                break;
            default:
                moodlectl_console_write('actionfailed');
                moodlectl_console_write(get_string('message', 'message').': '.$result->getMessage(),false,false);
                break;
        }
        exit(1);
    }

    // now the good ones
    if (is_array($result) || is_object($result)) {
        switch ($mode) {
            case 'yaml':
                fwrite(STDOUT, syck_dump((array)$result)."\n");	// the syck_dump don't work :(
            break;
            case 'json':
                fwrite(STDOUT, json_encode($result)."\n");
                break;
            case 'php':
                fwrite(STDOUT, serialize($result)."\n");
                break;
            default:
                foreach ($result as $key => $data) {
                    if (is_array($data)) {
                        $values = array();
                        foreach ($data as $k => $v) {
                            $values[]= "$k\t$v";
                        }
                        $value = implode("\t", $values);
                    }
                    else {
                        $value = $data;
                    }
                    // can suppress printing the key out for simple values
                    if ($MOODLECTL_NO_KEY) {
                        fwrite(STDOUT, "$value\n");
                    }
                    else {
                        fwrite(STDOUT, "$key\t$value\n");
                    }
                }
                break;
        }
        // clear all output
        fflush(STDOUT);
    }
}


/**
 * calculate mode
 *
 * @param array $args command line arguments
 * @return string mode - opts/json/yaml/php
 */
function moodlectl_determine_mode($args) {
    $mode = 'opts';
    if (isset($args[1])) {
        $mode = trim($args[1], '- ');
        switch ($mode) {
            case 'yaml':
            case 'json':
            case 'php':
                break;
            default:
                // could be a legitimate other argument
                $mode = 'opts';
                break;
        }
    }
    return $mode;
}


/**
 * setup the environment so that there is an active user, and any other requirements
 * that Moodle has - this is almost the same as what cron.php does.
 *
 * @return boolean true
 */
function moodlectl_setup_env() {
    global $CFG, $USER, $SESSION;

    include ("Console/Getopt.php");
    require_once($CFG->dirroot.'/'.MOODLECTL_BASE.'/moodlectl_plugin_base.class.php');

    // increase error reporting
    error_reporting(E_ALL);

    // make sure that it is only run from the command line
    if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])){
        moodlectl_console_write('notcli');
        exit(-1);
    }

    // allow unlimited execution time
    set_time_limit(0);
    // this allows moodlectl to have the same access/privelledges as cron
    define('FULLME', 'cron');

/// Do not set moodle cookie because we do not need it here, it is better to emulate session
    $nomoodlecookie = true;

/// The current directory in PHP version 4.3.0 and above isn't necessarily the
/// directory of the script when run from the command line. The require_once()
/// would fail, so we'll have to chdir()

    if (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['argv'][0])) {
        chdir(dirname($_SERVER['argv'][0]));
    }

    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/gradelib.php');

/// Extra debugging (set in config.php)
    if (!empty($CFG->showcronsql)) {
        $db->debug = true;
    }
    if (!empty($CFG->showcrondebugging)) {
        $CFG->debug = DEBUG_DEVELOPER;
        $CFG->debugdisplay = true;
    }

/// emulate normal session
    $SESSION = new object();
    $USER = get_admin();      /// Temporarily, to provide environment for this script

/// ignore admins timezone, language and locale - use site deafult instead!
    $USER->timezone = $CFG->timezone;
    $USER->lang = '';
    $USER->theme = '';
    $USER->sesskey = sesskey();
    //course_setup(SITEID); // Moodle 1.9
	require_login(SITEID); // Moodle 2.0
/// increase memory limit (PHP 5.2 does different calculation, we need more memory now)
    @raise_memory_limit('128M');
}


/**
* test  permissions this user has in the $CFG->dataroot directory
*
* @return boolean true succeeded | false
* */
function moodlectl_permissions_test() {
    global $CFG;
    $perms = fileperms($CFG->dataroot);
    // Owner
    $owner  = (($perms & 0x0100) ? 'r' : '-');
    $owner .= (($perms & 0x0080) ? 'w' : '-');
    $owner .= (($perms & 0x0040) ?
                (($perms & 0x0800) ? 's' : 'x' ) :
                (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $group  = (($perms & 0x0020) ? 'r' : '-');
    $group .= (($perms & 0x0010) ? 'w' : '-');
    $group .= (($perms & 0x0008) ?
                (($perms & 0x0400) ? 's' : 'x' ) :
                (($perms & 0x0400) ? 'S' : '-'));

    // World
    $user  = (($perms & 0x0004) ? 'r' : '-');
    $user .= (($perms & 0x0002) ? 'w' : '-');
    $user .= (($perms & 0x0001) ?
                (($perms & 0x0200) ? 't' : 'x' ) :
                (($perms & 0x0200) ? 'T' : '-'));

    // world writeable - bleh
    if ($user == 'rwx') {
        return true;
    }
    // am I the owner
    if (fileowner($CFG->dataroot) == posix_getuid()) {
        return true;
    }

    // do I have group access
    $gid = filegroup($CFG->dataroot);
    $group_data = posix_getgrgid($gid);
    if ($group == 'rwx' && ($gid == posix_getgid() || in_array(posix_getlogin(), $group_data['members']))) {
        return true;
    }
    return false;
}
?>
