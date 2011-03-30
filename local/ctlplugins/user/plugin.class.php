<?php
/**
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPLv3+
 * @package local
 *
 */
if (false) $DB = new moodle_database();

require_once "$CFG->dirroot/local/moodlectl_utils.php";

/**
 * Extend the base plugin class
 */
class moodlectl_plugin_user extends moodlectl_plugin_base {

    function help() {
        return
        array(
            'create-user' => "  Create a new user account:
     moodlectl create-user --username=bob --password=bob123 --emailaddress=bob@example.com --firstname=Bob --lastname=Gratton --city=Quebec --country=CA --duplicate",
            'change-user' => "  Modify one or more fields from an existing user account:
     moodlectl change-user --userid=2 --emailaddress=bob.gratton@example.com
     moodlectl change-user --userid=2 --firstname=Bob --lastname=Gratton --username=bob.gratton",
            'show-user' => "  Show the details of a user account:
     moodlectl --show-user --userid=2
     moodlectl --show-user --idnumber=USER02
     moodlectl --show-user --emailaddress=bob@example.com
     moodlectl --show-user --username=sample.user",
            'delete-user' => "  Delete a user account:
     moodlectl --delete-user --userid=2
     moodlectl --delete-user --emailaddress=bob@example.com
     moodlectl --delete-user --username=sample.user",
            'list-users' =>"  List all (non-deleted) users:
     moodlectl list-users",
            );
    }

    function command_line_options() {
        global $CFG;

        return array(
                     'create-user' =>
                     array(
                           // Required information
                           array('long' => 'username',     'required' => true),
                           array('long' => 'password',     'required' => true),
                           array('long' => 'emailaddress', 'required' => true),
                           array('long' => 'firstname',    'required' => true),
                           array('long' => 'lastname',     'required' => true),
                           array('long' => 'city',         'required' => true),
                           array('long' => 'country',      'required' => true),

                           // Optional user information
                           array('long' => 'icq',         'required' => false),
                           array('long' => 'skype',       'required' => false),
                           array('long' => 'yahoo',       'required' => false),
                           array('long' => 'aim',         'required' => false),
                           array('long' => 'msn',         'required' => false),
                           array('long' => 'phone1',      'required' => false),
                           array('long' => 'phone2',      'required' => false),
                           array('long' => 'institution', 'required' => false),
                           array('long' => 'department',  'required' => false),
                           array('long' => 'address',     'required' => false),
                           array('long' => 'url',         'required' => false),
                           array('long' => 'idnumber',    'required' => false),

                           // Settings
                           array('long' => 'auth',          'required' => false, 'default' => 'manual'),
                           array('long' => 'lang',          'required' => false),
                           array('long' => 'theme',         'required' => false),
                           array('long' => 'timezone',      'required' => false),
                           array('long' => 'description',   'required' => false),
                           array('long' => 'mnethostid',    'required' => false, 'type' => 'int'),
                           array('long' => 'mailformat',    'required' => false, 'type' => 'int'),
                           array('long' => 'maildigest',    'required' => false, 'type' => 'int'),
                           array('long' => 'maildisplay',   'required' => false, 'type' => 'int'),
                           array('long' => 'emailstop',     'required' => false, 'type' => 'boolean'),
                           array('long' => 'htmleditor',    'required' => false, 'type' => 'boolean'),
                           array('long' => 'ajax',          'required' => false, 'type' => 'boolean'),
                           array('long' => 'autosubscribe', 'required' => false, 'type' => 'boolean'),
                           array('long' => 'trackforums',   'required' => false, 'type' => 'boolean'),
                           array('long' => 'screenreader',  'required' => false, 'type' => 'boolean'),
                           array('long' => 'duplicate',     'required' => false, 'type' => 'boolean'),
                           ),

                     'change-user' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid', 'required' => true, 'type' => 'int'),

                           // Required information
                           array('long' => 'username',     'required' => false),
                           array('long' => 'password',     'required' => false),
                           array('long' => 'emailaddress', 'required' => false),
                           array('long' => 'firstname',    'required' => false),
                           array('long' => 'lastname',     'required' => false),
                           array('long' => 'city',         'required' => false),
                           array('long' => 'country',      'required' => false),

                           // Optional user information
                           array('long' => 'icq',         'required' => false),
                           array('long' => 'skype',       'required' => false),
                           array('long' => 'yahoo',       'required' => false),
                           array('long' => 'aim',         'required' => false),
                           array('long' => 'msn',         'required' => false),
                           array('long' => 'phone1',      'required' => false),
                           array('long' => 'phone2',      'required' => false),
                           array('long' => 'institution', 'required' => false),
                           array('long' => 'department',  'required' => false),
                           array('long' => 'address',     'required' => false),
                           array('long' => 'url',         'required' => false),
                           array('long' => 'idnumber',    'required' => false),

                           // Settings
                           array('long' => 'auth',          'required' => false, 'default' => 'manual'),
                           array('long' => 'lang',          'required' => false),
                           array('long' => 'theme',         'required' => false),
                           array('long' => 'timezone',      'required' => false),
                           array('long' => 'description',   'required' => false),
                           array('long' => 'mnethostid',    'required' => false, 'type' => 'int'),
                           array('long' => 'mailformat',    'required' => false, 'type' => 'int'),
                           array('long' => 'maildigest',    'required' => false, 'type' => 'int'),
                           array('long' => 'maildisplay',   'required' => false, 'type' => 'int'),
                           array('long' => 'emailstop',     'required' => false, 'type' => 'boolean'),
                           array('long' => 'htmleditor',    'required' => false, 'type' => 'boolean'),
                           array('long' => 'ajax',          'required' => false, 'type' => 'boolean'),
                           array('long' => 'autosubscribe', 'required' => false, 'type' => 'boolean'),
                           array('long' => 'trackforums',   'required' => false, 'type' => 'boolean'),
                           array('long' => 'screenreader',  'required' => false, 'type' => 'boolean'),
                           ),

                     'show-user' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'idnumber',     'required' => false, 'default' => ''),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),
                           ),

                     'delete-user' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),
                           ),
                     'list-users' =>
                     array(// no parameters
                           ),
                     );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'create-user':
                return moodlectl_plugin_user::create_user($options, $format);
                break;
            case 'change-user':
                return moodlectl_plugin_user::change_user($options, $format);
                break;
            case 'show-user':
                return moodlectl_plugin_user::show_user($options, $format);
                break;
            case 'delete-user':
                return moodlectl_plugin_user::delete_user($options, $format);
                break;
            case 'list-users':
                return moodlectl_plugin_user::list_users($format);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

    /**
     * Create a new user account
     *
     * @param array  $options all of the user profile details passed on the command line
     * @param string $format the format of input/output
     * @return array list of all the user details
     */
    static function create_user($data, $format) {
        global $CFG, $DB;

        // Validate a few fields
        //if (count_records('user', 'username', $data['username']) > 0) { // moodle 1.9
		if ($DB->count_records('user', array("username"=>$data['username'])) > 0) { // moodle 2.0
            return new Exception('Cannot create new user: username='.$data['username'].' is already taken.');
        }
        elseif ($DB->count_records('user', array('email'=>$data['emailaddress'])) > 0 && ! $data['duplicate']) {
            return new Exception('Cannot create new user: emailaddress='.$data['emailaddress'].' is already taken.');
        }
        elseif (!check_password_policy($data['password'], $errmsg)) {
            return new Exception('Invalid password for new user: '.strip_tags($errmsg));
        }

        $usernew = (object)$data;
		//var_dump($usernew);
        rename_object_property($usernew, 'userid', 'id');
        rename_object_property($usernew, 'emailaddress', 'email');
        $usernew->username = trim($usernew->username);
        $usernew->timemodified = time();
        $usernew->mnethostid = $CFG->mnet_localhost_id;
        $usernew->confirmed  = 1;
        $usernew->password = hash_internal_user_password($usernew->password);
		
        // $usernew = addslashes_object($usernew); // addslashes_object is no longer available - NOTE / TODO check if addslashes/ mysqlrealescapestring or such is done elsewhere on the object before insertion into db
		
		// get rid of NULL values in the object data, as they are not able to be inserted into the moodle2 db
		foreach($usernew as $key => $value) {
			if($value === NULL) $usernew->$key = ''; // replace NULL's with empty string
		}
		
        if (!$usernew->id = $DB->insert_record('user', $usernew)) {
            return new Exception('Error creating user record');
        }
        return moodlectl_plugin_user::show_user(array('userid' => $usernew->id), $format);
    }

    /**
     * Edit an existing user account
     *
     * @param array  $options all of the user profile details passed on the command line
     * @param string $format the format of input/output
     * @return array list of all the user details
     */
    static function change_user($data, $format) {
		global $DB;
        // Validate a few fields
        if (!empty($data['password']) and !check_password_policy($data['password'], $errmsg)) {
            return new Exception('Invalid password for new user: '.strip_tags($errmsg));
        }

        $usernew = (object)$data;
        rename_object_property($usernew, 'userid', 'id');
        rename_object_property($usernew, 'emailaddress', 'email');
        $usernew->timemodified = time();

        // Required post-processing on a few fields
        if (!empty($usernew->username)) {
            $usernew->username = trim($usernew->username);
        }
        if (!empty($usernew->password)) {
            $usernew->password = hash_internal_user_password($usernew->password);
        }

        //$usernew = addslashes_object($usernew);
        if (!$DB->update_record('user', $usernew)) {
            return new Exception('Error modifying user record');
        }
        return moodlectl_plugin_user::show_user(array('userid' => $usernew->id), $format);
    }

    /**
     * Retrieve user profile details
     *
     * @param array  $options one of these options will allow us to match a user record
     * @param string $format the format of input/output
     * @return array list of all the user details
     */
    static function show_user($options, $format) {

        $userparams = array('userid' => 'id', 'username' => 'username', 'emailaddress' => 'email', 'idnumber' => 'idnumber');
        $user = find_matching_record('user', $userparams, $options);
        if (is_object($user) && get_class($user) == 'Exception') {
            return $user;
        }
		
		$cleansed_user = new stdClass; // $user has too much cruft - just keep what we want
		// refactor this with array of attributes/fields to loop thru
		$cleansed_user->id = $user->id;
		$cleansed_user->auth = $user->auth;
		$cleansed_user->username = $user->username;
		$cleansed_user->idnumber = $user->idnumber;
		$cleansed_user->firstname= $user->firstname;
		$cleansed_user->lastname= $user->lastname;
		$cleansed_user->email = $user->email;
		$cleansed_user->city = $user->city;
		$cleansed_user->country = $user->country;
		$cleansed_user->phone1 = $user->phone1;
		$cleansed_user->phone2 = $user->phone2;
		$cleansed_user->institution = $user->institution;
		$cleansed_user->confirmed = $user->confirmed;
		$cleansed_user->deleted = $user->deleted;
		$cleansed_user->suspended = $user->suspended;
		
		/*
		// remove unwanted elements
        unset($user->password); // password hash
        unset($user->secret); // one-time password reset string

        // Some values can be formatted nicely
        $user->firstaccess_fmt  = (0 == $user->firstaccess)  ? 'Never' : userdate($user->firstaccess);
        $user->lastaccess_fmt   = (0 == $user->lastaccess)   ? 'Never' : userdate($user->lastaccess);
        $user->lastlogin_fmt    = (0 == $user->lastlogin)    ? 'Never' : userdate($user->lastlogin);
        $user->currentlogin_fmt = (0 == $user->currentlogin) ? 'Never' : userdate($user->currentlogin);
        $user->timemodified_fmt = (0 == $user->timemodified) ? 'Never' : userdate($user->timemodified);
        $countries = get_string_manager()->get_list_of_countries();
        $user->country_fmt      = (isset($user->country) && isset($countries[$user->country])) ? $countries[$user->country] : $user->country;
        $timezones = get_list_of_timezones();
        $user->timezone_fmt     = (99 == $user->timezone) ? get_string('serverlocaltime') : $timezones[$user->timezone];
        return $user;
		*/
		return $cleansed_user;
    }

    /**
     * Delete the given user account
     *
     * @param array  $options one of these options will allow us to match a user record
     * @param string $format the format of input/output
     * @return boolean - true success | false failure | or Exception()
     */
    static function delete_user($options, $format) {

        $userparams = array('userid' => 'id', 'username' => 'username', 'emailaddress' => 'email');
        $user = find_matching_record('user', $userparams, $options);
        if (is_object($user) && get_class($user) == 'Exception') {
            return $user;
        }

        return delete_user($user);
    }

    /**
     * List user accounts
     *
     * @param string $format  Format of input/output
     * @return array list of all users | or Exception()
     */
    static function list_users($format) {
        global $CFG, $DB;

        //$columns = '*';
		$columns = 'id, username, idnumber, firstname, lastname, email';
        if ('opts' == $format) {
            $columns = 'id';
        }

        //if (!$users = $DB->get_records('user', 'deleted', '0', 'id', $columns)) { // moodle 1.9
		if (!$users = $DB->get_records('user', array("deleted"=>'0'), "id",  $columns )) { // moodle 2.0
            return new Exception(get_string('usersnotfound', MOODLECTL_LANG));
        }

        foreach ($users as $key => $user) {
            $users[$key] = (array)$user;
        }
        return $users;
    }
}
//$DB->count_records($table, $conditions);
//$DB->get_records($table, $conditions, $sort, $fields, $limitfrom, $limitnum);

?>
