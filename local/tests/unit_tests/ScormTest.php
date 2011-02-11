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

class ScormTest extends MoodlectlTestBase {

    public function testScorm1_create_delete_course_add_scorm() {
        // create skeleton course
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                            ), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $this->get_course_json($course['id']);
        // upload test scorm package
        $result = self::call_moodle('upload-file', array('course-id' => $course['id'],
                                                         'file' => dirname(__FILE__).'/../test_scorm.zip',
                                                         'destination' => ''), 'json');
        $this->assertTrue(array_key_exists('file', $result), 'did not return the file name');
        // create the scorm activity        
        $scorm = self::call_moodle('create-scorm', array('course-id' => $course['id'],
                                                         'reference' => 'test_scorm.zip',
                                                         'name' => 'Test SCORM package',
                                                         'summary' => 'This is a test scorm package'), 'json');
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
    }

    private function get_course_json($id) {
        $course = self::call_moodle('show-course', array('course-id' => $id), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'show course results did not contain id');
        $this->assertTrue($course['id'] == $id);
        $this->assertTrue(array_key_exists('modules', $course), 'show course results did not contain modules list');
        $this->assertTrue(array_key_exists('participants', $course), 'show course results did not contain participants list');
        return $course;
    }
}
?>