<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
global $CFG;

define('TEST_LOG_FILE', '/tmp/moodlectl_trace.log');

require_once(dirname(__FILE__).'/../test_config.php');

require_once 'PHPUnit/Framework.php';

class MoodlectlTestBase extends PHPUnit_Framework_TestCase {

    public function setUp() {
        global $MOODLECTL_PATH, $TEST_INIT;
        if ($TEST_INIT != get_class($this)) {
            echo "\nRunning ". get_class($this)."\n";
            $TEST_INIT = get_class($this);
        }
        $MOODLECTL_PATH = getenv('MOODLECTL_PATH');
        if (empty($MOODLECTL_PATH)) {
            $MOODLECTL_PATH = MOODLECTL_PATH;
        }
        if (!file_exists($MOODLECTL_PATH)) {
            throw new Exception('Cannot find MOODLECTL_PATH: '.$MOODLECTL_PATH);
        }
        // enable the test plugin
        putenv("MOODLECTL_TEST=1");
    }

    // Global batch container
    static protected $moodlectl_batch = array();

    // Global error container
    static protected $last_error = false;

    /**
     * return the last error
     *
     * @return string last error message || false
     */
    static function last_error_message() {
        if (is_array(self::$last_error) && isset(self::$last_error['message'])) {
            return self::$last_error['message'];
        }
        return false;
    }

    /**
     * return the last error message
     *
     * @return array last error values
     */
    static function last_error() {
        return self::$last_error;
    }

   /**
     * output logging messages for the moodlectl layer
     *
     * @param string $msg message to log
     * @return boolean true
     */
    static function add_to_log($msg) {
        if (getenv('MOODLECTL_LOG')) {
            $fp = fopen(TEST_LOG_FILE, 'a');
            fwrite($fp, $msg."\n");
            fflush($fp);
            fclose($fp);
        }
        return true;
    }
    
    /**
     * reset the global batch container
     *
     * @return boolean true batch reset
     */
    static function reset_batch() {
        self::$moodlectl_batch = array();
        return true;
    }

    /**
     * add an action to the batch container
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @return boolean true batch reset
     */
    static function add_to_batch($action, $options) {
        self::$moodlectl_batch[]= array($action => $options);
        return true;
    }

    /**
     * execute all actions held in the batch container
     *
     * @param string $format for the call - yaml/json/php or false for command line options only
     * @return string of result
     */
    static function process_batch($format='json') {
        $result = self::call_moodle('batch', self::$moodlectl_batch, $format);
        self::reset_batch();
        return $result;
    }


    /**
     * call moodle
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @param string $format for the call - yaml/json/php or false for command line options only
     * @return string of result
     */
    static function call_moodle ($action, $options, $format=false) {
        self::$last_error = false;
        if ($format) {
            return self::call_moodle_pipe ($action, $options, $format);
        }
        else {
            return self::call_moodle_exec ($action, $options);
        }
    }

    /**
     * call moodlectl in command line options mode
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @return string of result
     */
    static function call_moodle_exec ($action, $options) {
        global $MOODLECTL_RC;
        $cmd = self::format_cmd($action, $options);
        self::add_to_log('EXEC: ' . $cmd);
        exec($cmd, $output, $rc);
        $rc = $rc == 0 ? true : false;
        self::add_to_log('RC: ' . $rc . "\nRESULT: ".implode("\n", $output));
        $MOODLECTL_RC = $rc;
        return count($output) ? implode("\n", $output) : $rc;
    }


    /**
     * serialise the values to pass in
     *
     * @param array $options a list of options/parameters for the action
     * @param string $format for the call - yaml/json/php
     * @return string serialised values
     */
    static function call_serialise ($options, $format) {
        switch ($format) {
            case 'yaml':
                return syck_dump($options);
                break;
            case 'json':
                return json_encode($options);
                break;
            case 'php':
                return serialize($options);
                break;
            default:
                // unknown exchange format
                throw new Exception('unknown exchange format - must be json/yaml/php');
                break;
        }
    }


    /**
     * deserialise the values passed in
     *
     * @param string $results the results from a call
     * @param string $format for the call - yaml/json/php
     * @return array of result values
     */
    static function call_deserialise ($results, $format) {
        if ($results == "1" || $results == "0") {
            return (int)$results;
        }
        switch ($format) {
            case 'yaml':
                $values = syck_load($results);
                break;
            case 'json':
                $values = json_decode($results);
                break;
            case 'php':
                // suppress error reporting and then check for NULL value on unserialize()
                error_reporting(E_ALL & !E_NOTICE);
                $values = unserialize($results);
                error_reporting(E_ALL);
                break;
            default:
                // unknown exchange format
                throw new Exception('unknown exchange format - must be json/yaml/php');
                break;
        }
        if ($values === false || $values === NULL) {
            throw new Exception("de-serialization of results failed: $results");
        }
        return (array)$values;
    }


    /**
     * call moodlectl in piped fill format mode
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @param string $format for the call - yaml/json/php
     * @return array of result values
     */
    static function call_moodle_pipe ($action, $options, $format) {
        global $MOODLECTL_PATH, $MOODLECTL_RC;
        $cmd = self::format_cmd($action, array());
        $cmd = escapeshellcmd($MOODLECTL_PATH." --$action --$format 2>&1");
        if ($action == 'batch') {
            $results = explode("<<<<<BATCH>>>>>\n", self::execute_pipe($cmd, self::call_serialise($options, $format)));
            $values = array();
            // check for errors
            $error = NULL;
            if (!$MOODLECTL_RC) {
                $error = array_pop($results);
            }
            foreach ($results as $result) {
                if (!empty($result)) {
                    $values[]= array('result' => self::call_deserialise($result, $format), 'rc' => true);
                }
            }
            if ($error) {
                $values[]= array('error' => $error, 'rc' => false);
            }
            return $values;
        }
        else {
            $result = self::call_deserialise(self::execute_pipe($cmd, self::call_serialise($options, $format)), $format);
            if (!$MOODLECTL_RC) {
                if (isset($result['message'])) {
                    self::$last_error = $result;
                }
                else {
                    self::$last_error = array_pop($result);
                }
                return false;
            }
            else {
                return $result;
            }
        }
    }


    /**
     * execute the command opening in and out pipes
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @return string of result
     */
    static function execute_pipe ($command, $stdin) {
        global $MOODLECTL_RC;
        $result = '';
        $MOODLECTL_RC = true;
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
            );

        self::add_to_log('PIPE: ' . $command . "\nINPUT: ".$stdin);
        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            // pass in the serialised data on stdin
            fwrite($pipes[0], $stdin);
            fflush($pipes[0]);
            fclose($pipes[0]);
            // retrieve the results
            $result = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $MOODLECTL_RC = proc_close($process) == 0 ? true : false;
        }
        else {
            // failed miserably
            self::add_to_log("THROWING A WOBBLY - PIPE FAILED");
            throw new Exception('failed to open pipe to moodlectl cmd: '.$command);
        }
        self::add_to_log("RC: $MOODLECTL_RC \nRESULT: " . $result);
        return $result ? $result : $MOODLECTL_RC;
    }


    /**
     * format the command line parameters for a moodlectl call
     *
     * @param string $action the corresponding moodlectl action
     * @param array $options a list of options/parameters for the action
     * @return string of the command line call
     */
    static function format_cmd ($action, $options) {
        global $MOODLECTL_PATH;
        $opts = array();
        if ($options) {
            foreach ($options as $key => $value) {
                $opts[]= "--$key".($value ? '=\''.addslashes($value)."'" : '');
            }
        }
//        echo escapeshellcmd($MOODLECTL_PATH.($action ? " $action " : ''). implode($opts, ' ') . ' 2>&1')."\n";
        return escapeshellcmd($MOODLECTL_PATH.($action ? " $action " : ''). implode($opts, ' ') . ' 2>&1');
    }
}
