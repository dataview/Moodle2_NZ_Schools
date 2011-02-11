<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
/**
* Extend the base plugin class
* This class contains the actions for testing moodlectl - you should not be able to access these
*/
class moodlectl_plugin_test extends moodlectl_plugin_base {

    function help() {
        return
            array(
            'test-basic' => "    This is the help for the test plugin
             Please do not try and use this as it will do nothing useful
             ",
            'test-required' => "    This is the help for the test plugin
             Please do not try and use this as it will do nothing useful
             ",
            'test-simple-fail' => "    This is the help for the test plugin
             Please do not try and use this as it will do nothing useful
             ",
            'test-complex-fail' => "    This is the help for the test plugin
             Please do not try and use this as it will do nothing useful
             "
        );
    }

    function command_line_options() {
        return
        array(
        'test-basic' => array(
            array('long' => 'teststring', 'short' => 's', 'type' => 'string', 'required' => false, 'default' => 'string'),
            array('long' => 'testint', 'short' => 'i', 'type' => 'int', 'required' => false, 'default' => 5),
            array('long' => 'testboolean', 'short' => 'b', 'type' => 'boolean', 'required' => false, 'default' => false),
            ),
        'test-required' => array(
            array('long' => 'teststring', 'short' => 's', 'required' => true),
            array('long' => 'testint', 'short' => 'i', 'type' => 'int', 'required' => true),
            array('long' => 'testboolean', 'short' => 'b', 'type' => 'boolean', 'required' => true),
            ),
        'test-simple-fail' => array(
            array('long' => 'fail', 'short' => 'b', 'type' => 'boolean', 'required' => false, 'default' => false),
            ),
        'test-complex-fail' => array(
            array('long' => 'fail', 'short' => 'b', 'type' => 'boolean', 'required' => false, 'default' => false),
            ),
        );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'test-basic':
                return $options;
                break;
            case 'test-required':
                // set a config value
                return $options;
                break;
            case 'test-batch-unlimited':
                // delete a config value
                return $options;
                break;
            case 'test-simple-fail':
                // delete a config value
                return $options['fail'] ? false : true;
                break;
            case 'test-complex-fail':
                // delete a config value
                return $options['fail'] ? new Exception("died a controlled death") : $options;
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }
}
?>