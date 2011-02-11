<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 * @subpackage test
 *
 */

// make sure that it is only used from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])){
    echo "command line access only.";
    exit(-1);
}

// constant for path to moodlectl executable
define('MOODLECTL_PATH', '../../moodlectl.php');
global $MOODLECTL_PATH;


?>