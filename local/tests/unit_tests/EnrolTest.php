<?php
/**
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPLv3+
 * @package local
 *
 */

require_once './MoodlectlTestBase.class.php';

class EnrolTest extends MoodlectlTestBase
{
    public function testEnrol1_enrol_unenrol_student()
    {
        $user = $this->create_test_user();
        $course = $this->create_test_course();

        self::call_moodle('enrol-student', array('userid' => $user['id'],
                                                 'courseid' => $course['id'],
                                                 ), 'php');
        $result = self::call_moodle('user-has-role', array('userid' => $user['id'],
                                                 'courseid' => $course['id'],
                                                 'rolename' => 'student',
                                                 ), 'php');
        $this->assertTrue($result == 1, 'User is NOT enroled as a student');                                                 
        self::call_moodle('unenrol-student', array('userid' => $user['id'],
                                                   'courseid' => $course['id'],
                                                   ), 'php');
        $result = self::call_moodle('user-has-role', array('userid' => $user['id'],
                                                 'courseid' => $course['id'],
                                                 'rolename' => 'student',
                                                 ), 'php');
        $this->assertTrue($result == 0, 'User IS enroled as a student');                                                 
        self::call_moodle('enrol-user', array('userid' => $user['id'],
                                              'courseid' => $course['id'],
                                              'rolename' => 'student',
                                              ), 'php');
        self::call_moodle('unenrol-user', array('userid' => $user['id'],
                                                'courseid' => $course['id'],
                                                'rolename' => 'student',
                                                ), 'php');

        $this->delete_course($course);
        $this->delete_user($user);
    }

    public function testEnrol2_enrol_unenrol_teacher()
    {
        $user = $this->create_test_user();
        $course = $this->create_test_course();

        self::call_moodle('enrol-teacher', array('userid' => $user['id'],
                                                 'courseid' => $course['id'],
                                                 ), 'php');
        self::call_moodle('unenrol-teacher', array('userid' => $user['id'],
                                                   'courseid' => $course['id'],
                                                   ), 'php');

        self::call_moodle('enrol-user', array('userid' => $user['id'],
                                              'courseid' => $course['id'],
                                              'rolename' => 'teacher',
                                              ), 'php');
        self::call_moodle('unenrol-user', array('userid' => $user['id'],
                                                'courseid' => $course['id'],
                                                'rolename' => 'teacher',
                                                ), 'php');

        $this->delete_course($course);
        $this->delete_user($user);
    }

    private function create_test_course()
    {
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           ), 'php');
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        return $course;
    }

    private function delete_course($course)
    {
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
    }

    private function create_test_user()
    {
        $user = self::call_moodle('create-user', array('username' => 'myuser',
                                                       'password' => 'password1',
                                                       'emailaddress'  => 'user@example.com',
                                                       'firstname' => 'Test',
                                                       'lastname' => 'Enrol',
                                                       'city' => 'Hamiltron',
                                                       'country' => 'NZ',
                                                       ), 'php');
        $this->assertTrue(array_key_exists('id', $user), 'Enrolment not created');
        return $user;
    }

    private function delete_user($user)
    {
        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'User not deleted: '.$user['id']);
    }
}
?>