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
 * Functions related to course enrolment
 */
class moodlectl_plugin_enrol extends moodlectl_plugin_base {

    function help() {
        return
        array(
            'user-has-role' => "  Check a user has a given role in a course:
     moodlectl user-has-role --userid=4 --courseid=100 --roleid=2
     moodlectl user-has-role --username=bob --coursename=CF101 --rolename=teacher",
        'enrol-user' => "  Enrol a user into a course with a given role:
     moodlectl enrol-user --userid=4 --courseid=100 --roleid=2
     moodlectl enrol-user --username=bob --coursename=CF101 --rolename=teacher",
            'unenrol-user' => "  Remove a user role in a course:
     moodlectl unenrol-user --userid=4 --courseid=100 --roleid=2
     moodlectl unenrol-user --username=bob --coursename=CF101 --rolename=student",
            'enrol-student' => "  Enrol a student into a course:
     moodlectl enrol-student --userid=4 --courseid=100
     moodlectl enrol-student --username=bob --coursename=CF101",
            'unenrol-student' => "  Remove a student from a course:
     moodlectl unenrol-student --userid=4 --courseid=100
     moodlectl unenrol-student --username=bob --coursename=CF101",
            'enrol-teacher' => "  Enrol a teacher into a course:
     moodlectl enrol-teacher --userid=4 --courseid=100
     moodlectl enrol-teacher --username=bob --coursename=CF101",
            'unenrol-teacher' => "  Remove a teacher from a course:
     moodlectl unenrol-teacher --userid=4 --courseid=100
     moodlectl unenrol-teacher --username=bob --coursename=CF101",
            );
    }

    function command_line_options() {
        global $CFG;

        return array(
                     'user-has-role' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),

                           // Used to lookup the role
                           array('long' => 'roleid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'rolename', 'required' => false, 'default' => ''),
                           ),

                    'enrol-user' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),

                           // Used to lookup the role
                           array('long' => 'roleid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'rolename', 'required' => false, 'default' => ''),
                           ),

                     'unenrol-user' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),

                           // Used to lookup the role
                           array('long' => 'roleid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'rolename', 'required' => false, 'default' => ''),
                           ),

                     'enrol-student' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),
                           ),

                     'unenrol-student' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),
                           ),

                     'enrol-teacher' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),
                           ),

                     'unenrol-teacher' =>
                     array(
                           // Used to lookup the user
                           array('long' => 'userid',       'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'username',     'required' => false, 'default' => ''),
                           array('long' => 'emailaddress', 'required' => false, 'default' => ''),

                           // Used to lookup the course
                           array('long' => 'courseid',   'required' => false, 'default' => 0, 'type' => 'int'),
                           array('long' => 'coursename', 'required' => false, 'default' => ''),
                           ),

                     );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'user-has-role':
                return moodlectl_plugin_enrol::user_has_role($options, false);
                break;
            case 'enrol-user':
                return moodlectl_plugin_enrol::enrol_user($options, $format, false);
                break;
            case 'unenrol-user':
                return moodlectl_plugin_enrol::enrol_user($options, $format, true);
                break;
            case 'enrol-student':
                return moodlectl_plugin_enrol::enrol_user($options, $format, false, 'student');
                break;
            case 'unenrol-student':
                return moodlectl_plugin_enrol::enrol_user($options, $format, true, 'student');
                break;
            case 'enrol-teacher':
                return moodlectl_plugin_enrol::enrol_user($options, $format, false, 'teacher');
                break;
            case 'unenrol-teacher':
                return moodlectl_plugin_enrol::enrol_user($options, $format, true, 'teacher');
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

    /**
     * Assign/unassign the a role to a user in a course
     *
     * @param array   $options  One of these options will allow us to match user, course and role records
     * @param string  $format   Format of input/output
     * @param boolean $unenrol  True to unassign (unenrol) the user, False (the default) otherwise
     * @param string  $rolename Shortname of the role to assign if not available in $options
     * @return boolean - true success | false failure | or Exception()
     */
    static function enrol_user($options, $format, $unenrol=false, $rolename='') {
		global $DB;
        $userparams = array('userid' => 'id', 'username' => 'username', 'emailaddress' => 'email');
        $user = find_matching_record('user', $userparams, $options);
        if (is_object($user) && get_class($user) == 'Exception') {
            return $user;
        }

        $courseparams = array('courseid' => 'id', 'coursename' => 'shortname');
        $course = find_matching_record('course', $courseparams, $options);
        if (is_object($course) && get_class($course) == 'Exception') {
            return $course;
        }

        if (empty($rolename)) {
            $roleparams = array('roleid' => 'id', 'rolename' => 'shortname');
            $role = find_matching_record('role', $roleparams, $options);
            if (is_object($role) && get_class($role) == 'Exception') {
                return $role;
            }
        }
        else {
            $role = $DB->get_record('role', array('shortname'=>$rolename));
        }

        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        if (!$unenrol) {
			if (!role_assign($role->id, $user->id, $coursecontext->id)) {
                return new Exception(get_string('enrolfailed', MOODLECTL_LANG, $action));
            }
        }
        else {
            if (!role_unassign($role->id, $user->id, $coursecontext->id)) {
                return new Exception(get_string('unenrolfailed', MOODLECTL_LANG, $action));
            }
        }
		
		# hackety hack, based on enrol/manual/manage.php code..
		
		if (!$enrol_manual = enrol_get_plugin('manual')) {
			throw new coding_exception('Can not instantiate enrol_manual');
		}
		
		$instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'), '*', MUST_EXIST); //slightly modified to use courseid
		$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
		$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);
		
		$enrol_manual->enrol_user($instance, $user->id, $role->id);
    }


    /**
     * check that a user has a role in a course
     *
     * @param array   $options  One of these options will allow us to match user, course and role records
     * @param string  $rolename Shortname of the role to assign if not available in $options
     * @return boolean - true success | false failure | or Exception()
     */
    static function user_has_role($options, $rolename='') {

        $userparams = array('userid' => 'id', 'username' => 'username', 'emailaddress' => 'email');
        $user = find_matching_record('user', $userparams, $options);
        if (is_object($user) && get_class($user) == 'Exception') {
            return $user;
        }

        $courseparams = array('courseid' => 'id', 'coursename' => 'shortname');
        $course = find_matching_record('course', $courseparams, $options);
        if (is_object($course) && get_class($course) == 'Exception') {
            return $course;
        }

        if (empty($rolename)) {
            $roleparams = array('roleid' => 'id', 'rolename' => 'shortname');
            $role = find_matching_record('role', $roleparams, $options);
            if (is_object($role) && get_class($role) == 'Exception') {
                return $role;
            }
        }
        else {
            $role = get_record('role', 'shortname', $rolename);
        }

        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $result = user_has_role_assignment( $user->id, $role->id, $coursecontext->id);
        return $result;
    }
}


?>
