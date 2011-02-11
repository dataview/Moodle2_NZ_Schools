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

class CourseTest extends MoodlectlTestBase {

    public function testCourse1_show_course() {
        $this->get_course_json(1);
    }

    public function testCourse2_show_course() {
        $course = $this->get_course_json(1);
        $this->assertTrue(count($course['modules']) > 0);
        if ($course['modules']) {
            foreach ($course['modules'] as $module) {
                $module = (array)$module;
                $this->assertTrue(array_key_exists('url', $module), 'module does not contain URL');
                $this->assertTrue(!empty($module['url']), 'module does not contain URL');
            }
        }
        $this->assertTrue(count($course['participants']) > 0);
        if ($course['participants']) {
            foreach ($course['participants'] as $participant) {
                $participant = (array)$participant;
                $this->assertTrue(array_key_exists('url', $participant), 'participants does not contain URL');
                $this->assertTrue(!empty($participant['url']), 'participants does not contain URL');
            }
        }
    }

    public function testCourse3_show_course() {
        $this->get_course_cl(1);
    }

    public function testCourse4_create_delete_course() {
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'idnumber' => 'COURSE01',
                                                            ), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $this->get_course_cl($course['id']);
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
    }


    public function testCourse5_create_delete_ex1_course() {
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'scorm',
                                                           'idnumber' => 'COURSE01',
                                                        ), 'json');

        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $this->assertTrue($course['summary'] == 'This is the test summary for the course Test 101', 'Course summary not set correctly');
        $this->assertTrue($course['format'] == 'scorm', 'Course format not set to scorm');
        $this->get_course_cl($course['id']);
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
    }


    public function testCourse6_create_with_bad_data_course() {
        global $MOODLECTL_RC;
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid format specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid startdate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '3',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrollable specification: 3/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolstartdate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolenddate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+2 days',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolstartdate and enrolenddate specification: \+2 days - \+2 days/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '53',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid numsections specification: 53/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '11',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid newsitems specification: 11/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '3',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid groupmode specification: 3/', self::last_error_message());
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '0',
                                                           'enrol' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrol specification: xxxx/', self::last_error_message());

        // create a real course this time
        $then = strtotime('+2 days');
        $course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '0',
                                                           'idnumber' => 'COURSE01',
                                                           'enrol' => 'manual',
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been created');

        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $this->assertTrue($course['summary'] == 'This is the test summary for the course Test 101', 'Course summary not set correctly');
        $this->assertTrue($course['shortname'] == 'TEST101', 'Course shortname not set to TEST101');
        $this->assertTrue($course['category'] == 1, 'Course category not set to 1');
        $this->assertTrue($course['format'] == 'weeks', 'Course format not set to weeks');
        $this->assertTrue($course['enrollable'] == 2, 'Course enrollable not set to 2');
        $this->assertTrue($course['startdate'] >= $then, "Course startdate(".$course['startdate'].") not set to >= $then");
        $this->assertTrue($course['enrolstartdate'] >= $then, "Course enrolstartdate(".$course['enrolstartdate'].") not set to >= $then");
        $this->assertTrue($course['enrolenddate'] >= $then, 'Course enrolenddate('.$course['enrolenddate'].') not set to >= '.$then);
        $this->assertTrue($course['numsections'] == 52, 'Course numsections not set to 52');
        $this->assertTrue($course['newsitems'] == 10, 'Course newsitems not set to 10');
        $this->assertTrue($course['groupmode'] == 0, 'Course groupmode not set to 0');
        $this->assertTrue($course['enrol'] == 'manual', 'Course enrol not set to manual');

        // tidy up the course
        $result = self::call_moodle('delete-course', array('course-id' => $course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$course['id']);
    }

    public function testCourse7_list_all_courses() {
        global $MOODLECTL_RC;
        $courses = self::call_moodle('list-courses', array(), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Courses should be found - there is always 1');
        $this->assertTrue(count($courses) > 0);
        foreach ($courses as $course) {
            $this->assertTrue(array_key_exists('id', $course), 'Course id not found');
        }
    }


    public function testCourse8_change_with_bad_data_course() {
        global $MOODLECTL_RC;
        $new_course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST102',
                                                           'fullname'  => 'Test 102 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 102',
                                                           'format' => 'weeks',
                                                           'startdate' => '+0',
                                                           'enrollable' => '1',
                                                           'numsections' => '50',
                                                           'newsitems' => '5',
                                                           'groupmode' => '1',
                                                           'enrol' => 'manual',
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been created');

        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been changed');
        $this->assertRegExp('/invalid format specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid startdate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '3',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrollable specification: 3/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolstartdate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolenddate specification: xxxx/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+2 days',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrolstartdate and enrolenddate specification: \+2 days - \+2 days/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '53',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid numsections specification: 53/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '11',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid newsitems specification: 11/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '3',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid groupmode specification: 3/', self::last_error_message());
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '0',
                                                           'enrol' => 'xxxx',
                                                            ), 'json');
        $this->assertFalse($MOODLECTL_RC, 'Course should NOT have been created');
        $this->assertRegExp('/invalid enrol specification: xxxx/', self::last_error_message());

        // create a real course this time
        $then = strtotime('+2 days');
        $course = self::call_moodle('change-course', array('course-id' => $new_course['id'],
                                                           'category' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101',
                                                           'format' => 'weeks',
                                                           'startdate' => '+2 days',
                                                           'enrollable' => '2',
                                                           'enrolstartdate' => '+2 days',
                                                           'enrolenddate' => '+3 days',
                                                           'numsections' => '52',
                                                           'newsitems' => '10',
                                                           'groupmode' => '0',
                                                           'enrol' => 'manual',
                                                           'idnumber' => 'COURSE01',
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been created');

        // get course by idnumber
        $copy_of_course = $this->get_course_idnumber_json('COURSE01');
        
        $this->assertTrue(array_key_exists('id', $course), 'Course not created');
        $this->assertTrue($course['summary'] == 'This is the test summary for the course Test 101', 'Course summary not set correctly');
        $this->assertTrue($course['shortname'] == 'TEST101', 'Course shortname not set to TEST101');
        $this->assertTrue($course['category'] == 1, 'Course category not set to 1');
        $this->assertTrue($course['format'] == 'weeks', 'Course format not set to weeks');
        $this->assertTrue($course['enrollable'] == 2, 'Course enrollable not set to 2');
        $this->assertTrue($course['startdate'] >= $then, "Course startdate(".$course['startdate'].") not set to >= $then");
        $this->assertTrue($course['enrolstartdate'] >= $then, "Course enrolstartdate(".$course['enrolstartdate'].") not set to >= $then");
        $this->assertTrue($course['enrolenddate'] >= $then, 'Course enrolenddate('.$course['enrolenddate'].') not set to >= '.$then);
        $this->assertTrue($course['numsections'] == 52, 'Course numsections not set to 52');
        $this->assertTrue($course['newsitems'] == 10, 'Course newsitems not set to 10');
        $this->assertTrue($course['groupmode'] == 0, 'Course groupmode not set to 0');
        $this->assertTrue($course['enrol'] == 'manual', 'Course enrol not set to manual');

        // get the course back by idnumber
        
        // tidy up the course
        $result = self::call_moodle('delete-course', array('course-id' => $new_course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$new_course['id']);
    }


    public function testCourse9_backup_restore_course() {
        global $MOODLECTL_RC;
        $backup = self::call_moodle('backup-course', array('course-id' => 1,
                                                           'user-files' => false,
                                                           'course-files' => false,
                                                           'site-files' => false,
                                                           'messages' => false,
                                                           'gradebook' => false,
                                                           'blogs' => false,
                                                           'logs' => false,
                                                           'users' => false,
                                                             ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been backedup');

        $new_course = self::call_moodle('create-course-from-backup', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101 '.$backup['file'],
                                                           'format' => 'weeks',
                                                           'startdate' => '+0',
                                                           'enrollable' => '1',
                                                           'numsections' => '50',
                                                           'newsitems' => '5',
                                                           'groupmode' => '1',
                                                           'idnumber' => 'COURSE01',
                                                           'enrol' => 'manual',
                                                            'from-file' => $backup['file'],
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been created');

        // tidy up the course
        $result = self::call_moodle('delete-course', array('course-id' => $new_course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$new_course['id']);
    }


    public function testCourse10_backup_restore_course() {
        global $MOODLECTL_RC;
        $backup = self::call_moodle('backup-course', array('course-id' => 1,
                                                           'user-files' => false,
                                                           'course-files' => false,
                                                           'site-files' => false,
                                                           'messages' => false,
                                                           'gradebook' => false,
                                                           'blogs' => false,
                                                           'logs' => false,
                                                           'users' => false,
                                                             ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been backedup');

        $new_course = self::call_moodle('create-course', array('categoryid' => 1,
                                                           'shortname' => 'TEST101',
                                                           'fullname'  => 'Test 101 fullname',
                                                           'summary'   => 'This is the test summary for the course Test 101 '.$backup['file'],
                                                           'format' => 'weeks',
                                                           'startdate' => '+0',
                                                           'enrollable' => '1',
                                                           'numsections' => '50',
                                                           'newsitems' => '5',
                                                           'idnumber' => 'COURSE01',
                                                           'groupmode' => '1',
                                                           'enrol' => 'manual',
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been created');

        $course = self::call_moodle('restore-course', array('course-id' => $new_course['id'],
                                                            'from-file' => $backup['file'],
                                                            ), 'json');
        $this->assertTrue($MOODLECTL_RC, 'Course should have been restored');

        // tidy up the course
        $result = self::call_moodle('delete-course', array('course-id' => $new_course['id']));
        $this->assertTrue($result, 'Course not deleted: '.$new_course['id']);
    }



    private function get_course_cl($id) {
        $result = self::call_moodle('show-course', array('course-id' => $id));
        $data = explode("\n", $result);
        $course = array();
        foreach ($data as $element) {
            $line = explode("\t", $element);
            $course[$line[0]] = isset($line[1]) ? $line[1] : false;
        }
        $this->assertTrue(array_key_exists('id', $course), 'show course results did not contain id');
        $this->assertTrue($course['id'] == $id);
        $this->assertFalse(array_key_exists('modules', $course), 'show course results did not contain modules list');
        $this->assertFalse(array_key_exists('participants', $course), 'show course results did not contain participants list');
        return $course;
    }

    private function get_course_json($id) {
        $course = self::call_moodle('show-course', array('course-id' => $id), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'show course results did not contain id');
        $this->assertTrue($course['id'] == $id);
        $this->assertTrue(array_key_exists('modules', $course), 'show course results did not contain modules list');
        $this->assertTrue(array_key_exists('participants', $course), 'show course results did not contain participants list');
        return $course;
    }

    private function get_course_idnumber_json($idnumber) {
        $course = self::call_moodle('show-course', array('idnumber' => $idnumber), 'json');
        $this->assertTrue(array_key_exists('id', $course), 'show course results did not contain id');
        $this->assertTrue($course['idnumber'] == $idnumber);
        $this->assertTrue(array_key_exists('modules', $course), 'show course results did not contain modules list');
        $this->assertTrue(array_key_exists('participants', $course), 'show course results did not contain participants list');
        return $course;
    }
}
?>