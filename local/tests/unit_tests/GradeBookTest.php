<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */

require_once './MoodlectlTestBase.class.php';

class GradeBookTest extends MoodlectlTestBase {

    public function testGradeBook1_export() {
        // export Grades
        global $MOODLECTL_RC;
        $grades = self::call_moodle('export-gradebook', array('course-id' => 1, ), 'json');
        $this->assertTrue(is_array($grades), 'Grade results was not an array');
        $this->assertTrue($MOODLECTL_RC, 'There nay not be any grades - but atleast it should run');
    }
    
    public function testGradeBook2_list_grade_items() {
        // list grade items
        global $MOODLECTL_RC;
        $gradeitems = self::call_moodle('gradebook-list-items', array('course-id' => 1, ), 'json');
        $this->assertTrue(is_array($gradeitems), 'Grade items results was not an array');
        $this->assertTrue(count($gradeitems) > 0, 'No item entries found');
        $this->assertTrue($MOODLECTL_RC, 'Users should be found - there is always 1');
    }
    
    public function testGradeBook3_grade_user() {
        // grade user
        global $MOODLECTL_RC;
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                            ), 'php');
        $this->assertTrue($MOODLECTL_RC, 'The course should be created');
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $forum = self::call_moodle('create-forum', array('course-id' => $course['id'], 'name' => 'forum test', 'intro' => 'Just a summary',
                                                         'assessed' => true, 'scale' => 99), 'json');
        $this->assertTrue('Just a summary' == $forum['intro']);
        $gradeitems = self::call_moodle('gradebook-list-items', array('course-id' => $course['id'], ), 'json');
        $this->assertTrue(is_array($gradeitems), 'Grade items results was not an array');
        $this->assertTrue(count($gradeitems) > 0, 'No item entries found');
        $gradeitem = (array) array_pop($gradeitems);
        $user = self::call_moodle('create-user', array('username' => 'myuser',
                                               'password' => 'password1',
                                               'emailaddress'  => 'user@example.com',
                                               'firstname' => 'Test',
                                               'lastname' => 'User',
                                               'city' => 'Hamiltron',
                                               'country' => 'NZ',
                                               ), 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');
        self::call_moodle('enrol-student', array('userid' => $user['id'], 'courseid' => $course['id'], ), 'php');
        $this->assertTrue($MOODLECTL_RC, 'User should be enroled: '.$user['id']);
        $result = self::call_moodle('import-gradebook', array('course-id' => $course['id'],
                                                              'grade-id' => $gradeitem['id'],
                                                              'user-id' =>  $user['id'],
                                                              'score' => '55',
                                                              'feedback' => 'jolly good show', ), 'json');
        $grades = self::call_moodle('export-gradebook', array('course-id' => $course['id'], ), 'php');
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
        $grades = $grades[$user['id']]['grades'];
        $grade = $grades[$gradeitem['id']];
        $this->assertTrue($grade['grade'] == 55.00, 'Grade not correct');
    }
    
    public function testGradeBook4_grade_user() {
        // grade user
        global $MOODLECTL_RC;
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                            ), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $forum = self::call_moodle('create-forum', array('course-id' => $course['id'], 'name' => 'forum test', 'intro' => 'Just a summary',
                                                         'assessed' => true, 'scale' => 99), 'json');
        $this->assertTrue('Just a summary' == $forum['intro']);
        $gradeitems = self::call_moodle('gradebook-list-items', array('course-id' => $course['id'], ), 'json');
        $this->assertTrue(is_array($gradeitems), 'Grade items results was not an array');
        $this->assertTrue(count($gradeitems) > 0, 'No item entries found');
        $gradeitem = (array) array_pop($gradeitems);
        $user = self::call_moodle('create-user', array('username' => 'myuser',
                                               'password' => 'password1',
                                               'emailaddress'  => 'user@example.com',
                                               'firstname' => 'Test',
                                               'lastname' => 'User',
                                               'city' => 'Hamiltron',
                                               'country' => 'NZ',
                                               ), 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');
        self::call_moodle('enrol-student', array('userid' => $user['id'], 'courseid' => $course['id'], ), 'php');
        $this->assertTrue($MOODLECTL_RC, 'User should be enroled: '.$user['id']);
        $result = self::call_moodle('import-gradebook-code', array(), 'json');
        $importcode = $result['importcode'];
        $result = self::call_moodle('import-gradebook-add', array('import-code' => $importcode,
                                                              'course-id' => $course['id'],
                                                              'grade-id' => $gradeitem['id'],
                                                              'user-id' =>  $user['id'],
                                                              'score' => '55',
                                                              'feedback' => 'jolly good show', ), 'json');
        $this->assertTrue($result == 1, 'gradebook add failed');
        $result = self::call_moodle('import-gradebook-commit', array('import-code' => $importcode,
                                                              'course-id' => $course['id'], ), 'json');
        $this->assertTrue($result == 1, 'gradebook commit failed');
        $grades = self::call_moodle('export-gradebook', array('course-id' => $course['id'], ), 'php');
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
        $grades = $grades[$user['id']]['grades'];
        $grade = $grades[$gradeitem['id']];
        $this->assertTrue($grade['grade'] == 55.00, 'Grade not correct');
    }
}
?>