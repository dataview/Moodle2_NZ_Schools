<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db001';
$CFG->dbname    = 'mdl2_papatoetoehigh';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'twB1aQNZ';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);

$CFG->wwwroot   = 'http://papatoetoehigh.moodle2.net.nz';
$CFG->dataroot  = '/data/u00/www/temp_moodledata/papatoetoehigh.moodle2.net.nz';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

$CFG->passwordsaltmain = '*6f;U}HVgp>!hQhq3bv_1YtuIGMII';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

