<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */

if (false) $DB = new moodle_database();

/**
* Extend the base plugin class
*/
class moodlectl_plugin_course extends moodlectl_plugin_base {

    function help() {
        return
        array(
            'create-course' => "  Create a new course:
     moodlectl create-course --categoryid=1 --fullname='Course Fullname 101' --shortname=CF101
     moodlectl create-course --categoryname=Miscellaneous --fullname='Course Fullname 101' --shortname=CF101 --summary='Description for students'
     Please refer to course creation help: http://docs.moodle.org/en/course/edit",
            'list-courses' =>"  List all courses:
     moodlectl list-courses",
            'change-course' =>"  Change a course:
     moodlectl change-course --course-id=1 --name='The course name' --summary='Just a summary'
     Please refer to course amendment help: http://docs.moodle.org/en/course/edit",
            'restore-course' => "  Restore a course over and existing one from a backup:
     moodlectl restore-course --course-id=1 --filename='name/of/file/to/backup/to'
     Please refer to backup/restore help http://docs.moodle.org/en/backup/restore",
            'create-course-from-backup' => "  Create a new course from a backup:
     moodlectl create-course-from-backup --filename='name/of/file/to/backup/to' --name='The course name' --summary='Just a summary'
     Please refer to course creation help: http://docs.moodle.org/en/course/edit and
     backup/restore help http://docs.moodle.org/en/backup/restore",
        'backup-course' => "  Backup an individual course:
     moodlectl backup-course --course-id=1",
            'show-course' => "  Show the details of a course:
     moodlectl show-course --course-id=1
     moodlectl show-course --course-idnumber=COURSE01",
            'list-participants' => "  Show the participants of a course:
     moodlectl list-participants --course-id=1",
            'list-course-modules' => "  Show the modules of a course:
     moodlectl list-course-modules --course-id=1'",
            'delete-course' => "  Delete a course:
     moodlectl delete-course --course-id=1",
            'reset-course' => " Reset a course:
     moodlectl reset--course --course-id=1",
            'create-category' => " Create a course category:
     moodlectl create-category --name=CAT1 --description='category description' --parent=1",
            'change-category' => " Change a course category:
     moodlectl change-category --categoryid=2 --name=CAT1 --description='category description' --parent=1",
            'show-category' => " Show a course category:
     moodlectl show-category --categoryid=2",
            'list-categories' => " List course categories:
     moodlectl list-categories",
        );
    }

    function command_line_options() {
        global $CFG;

        return array(
            'create-course' =>
                array(
                        array('long' => 'categoryid',        'short' => 'c', 'required' => false),
                        array('long' => 'categoryname',                      'required' => false),
                        array('long' => 'fullname',          'short' => 'n', 'required' => true),
                        array('long' => 'shortname',         'short' => 's', 'required' => true),
                        array('long' => 'summary',           'short' => 'a', 'required' => false),
                        array('long' => 'format',            'short' => 'f', 'required' => false, 'default' => 'weeks'),
                        array('long' => 'idnumber',          'short' => 'i', 'required' => false, 'default' => ''),
                        array('long' => 'numsections',       'short' => 'b', 'required' => false, 'default' => 10, 'type' => 'int'),
                        array('long' => 'startdate',         'short' => 'z', 'required' => false, 'default' => '+0'), // value must be compatible with strtodate()
                        array('long' => 'hiddensections',    'short' => 'h', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'newsitems',         'short' => 'n', 'required' => false, 'default' => 5, 'type' => 'int'),
                        array('long' => 'showgrades',        'short' => 'g', 'required' => false, 'default' => 1, 'type' => 'boolean'),
                        array('long' => 'showreports',       'short' => 'r', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'maxbytes',          'short' => 'm', 'required' => false, 'default' => 2097152 , 'type' => 'int'),
                        array('long' => 'metacourse',        'short' => 'd', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'enrol',             'short' => 'e', 'required' => false, 'default' => ''),
                        array('long' => 'defaultrole',                       'required' => false, 'default' => 0, 'type' => 'int'),
                        array('long' => 'enrollable',        'short' => 'j', 'required' => false, 'default' => 1, 'type' => 'int'),
                        // enrolstartdisabled is 1 if enrolstartdate  is 0
                        array('long' => 'enrolstartdate',    'short' => 'k', 'required' => false, 'default' => 0),
                        // enrolenddisabled is 1 if enrolenddate  is 0
                        array('long' => 'enrolenddate',      'short' => 'l', 'required' => false, 'default' => 0), // value must be compatible with strtodate()
                        array('long' => 'enrolperiod',       'short' => 'o', 'required' => false, 'default' => 0, 'type' => 'int'),
                        array('long' => 'expirynotify',      'short' => 'p', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'notifystudents',    'short' => 'q', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'expirythreshold',   'short' => 't', 'required' => false, 'default' => 864000, 'type' => 'int'),
                        array('long' => 'groupmode',         'short' => 'u', 'required' => false, 'default' => 0, 'type' => 'int'),
                        array('long' => 'groupmodeforce',    'short' => 'w', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'visible',           'short' => 'v', 'required' => false, 'default' => 1, 'type' => 'boolean'),
                        array('long' => 'enrolpassword',     'short' => 'x', 'required' => false, 'default' => NULL),
                        array('long' => 'guest',             'short' => 'y', 'required' => false, 'default' => 0, 'type' => 'boolean'),
                        array('long' => 'lang',                              'required' => false, 'default' => ''), // NULL will be en
                        array('long' => 'restrictmodules',                   'required' => false, 'default' => 0, 'type' => 'boolean'),
                    ),
            'list-courses' =>
                array( ),
            'change-course' =>
                array(
                        array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                        array('long' => 'category',          'short' => 'c', 'required' => false, 'default' => NULL),
                        array('long' => 'fullname',          'short' => 'n', 'required' => false, 'default' => NULL),
                        array('long' => 'shortname',         'short' => 's', 'required' => false, 'default' => NULL),
                        array('long' => 'summary',           'short' => 'a', 'required' => false, 'default' => NULL),
                        array('long' => 'format',            'short' => 'f', 'required' => false, 'default' => NULL),
                        array('long' => 'idnumber',          'short' => 'i', 'required' => false, 'default' => NULL),
                        array('long' => 'numsections',       'short' => 'b', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'startdate',         'short' => 'z', 'required' => false, 'default' => NULL), // value must be compatible with strtodate()
                        array('long' => 'hiddensections',    'short' => 'h', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'newsitems',         'short' => 'n', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'showgrades',        'short' => 'g', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'showreports',       'short' => 'r', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'maxbytes',          'short' => 'm', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'metacourse',        'short' => 'd', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'enrol',             'short' => 'e', 'required' => false, 'default' => NULL),
                        array('long' => 'defaultrole',                       'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'enrollable',        'short' => 'j', 'required' => false, 'type' => 'int', 'default' => NULL),
                        // enrolstartdisabled is 1 if enrolstartdate  is 0
                        array('long' => 'enrolstartdate',    'short' => 'k', 'required' => false, 'default' => NULL),
                        // enrolenddisabled is 1 if enrolenddate  is 0
                        array('long' => 'enrolenddate',      'short' => 'l', 'required' => false, 'default' => NULL), // value must be compatible with strtodate()
                        array('long' => 'enrolperiod',       'short' => 'o', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'expirynotify',      'short' => 'p', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'notifystudents',    'short' => 'q', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'expirythreshold',   'short' => 't', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'groupmode',         'short' => 'u', 'required' => false, 'type' => 'int', 'default' => NULL),
                        array('long' => 'groupmodeforce',    'short' => 'w', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'visible',           'short' => 'v', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'enrolpassword',     'short' => 'x', 'required' => false, 'default' => NULL),
                        array('long' => 'guest',             'short' => 'y', 'required' => false, 'type' => 'boolean', 'default' => NULL),
                        array('long' => 'lang',                              'required' => false, 'default' => NULL), // NULL will be en
                        array('long' => 'restrictmodules',                   'required' => false, 'type' => 'boolean', 'default' => NULL),
                    ),
            'backup-course' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true,  'type' => 'int'),
                    array('long' => 'metacourse',        'short' => 'm', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'users',             'short' => 'u', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'logs',              'short' => 'l', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'user-files',        'short' => 'f', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'course-files',      'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'site-files',        'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'messages',          'short' => 'e', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'gradebook',         'short' => 'g', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'blogs',             'short' => 'b', 'required' => false, 'type' => 'boolean', 'default' => false),
                    ),
            'restore-course' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                    array('long' => 'from-file',         'short' => 'f', 'required' => true),
                    array('long' => 'delete-first',      'short' => 'd', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'metacourse',        'short' => 'm', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'users',             'short' => 'u', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'groups',            'short' => 'g', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'logs',              'short' => 'l', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'user-files',        'short' => 'f', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'messages',          'short' => 'e', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'blogs',             'short' => 'b', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'course-files',      'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'site-files',        'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => false),
                    ),
            'create-course-from-backup' =>
                array(
                    array('long' => 'from-file',         'short' => 'f', 'required' => true),
                    array('long' => 'delete-first',      'short' => 'd', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'metacourse',        'short' => 'm', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'users',             'short' => 'u', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'groups',            'short' => 'g', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'logs',              'short' => 'l', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'user-files',        'short' => 'f', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'messages',          'short' => 'e', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'blogs',             'short' => 'b', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'course-files',      'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'site-files',        'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => false),
                    array('long' => 'categoryid',        'short' => 'c', 'required' => false),
                    array('long' => 'categoryname',                      'required' => false),
                    array('long' => 'fullname',          'short' => 'n', 'required' => true),
                    array('long' => 'shortname',                         'required' => true),
                    array('long' => 'summary',                           'required' => false),
                    array('long' => 'format',                            'required' => false, 'default' => 'weeks'),
                    array('long' => 'idnumber',                          'required' => false, 'default' => ''),
                    array('long' => 'numsections',                       'required' => false, 'default' => 10, 'type' => 'int'),
                    array('long' => 'startdate',                         'required' => false, 'default' => '+0'), // value must be compatible with strtodate()
                    array('long' => 'hiddensections',                    'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'newsitems',                         'required' => false, 'default' => 5, 'type' => 'int'),
                    array('long' => 'showgrades',                        'required' => false, 'default' => 1, 'type' => 'boolean'),
                    array('long' => 'showreports',                       'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'maxbytes',                          'required' => false, 'default' => 2097152 , 'type' => 'int'),
                    array('long' => 'metacourse',                        'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'enrol',                             'required' => false, 'default' => ''),
                    array('long' => 'defaultrole',                       'required' => false, 'default' => 0, 'type' => 'int'),
                    array('long' => 'enrollable',                        'required' => false, 'default' => 1, 'type' => 'int'),
                    // enrolstartdisabled is 1 if enrolstartdate  is 0
                    array('long' => 'enrolstartdate',                    'required' => false, 'default' => 0),
                    // enrolenddisabled is 1 if enrolenddate  is 0
                    array('long' => 'enrolenddate',                      'required' => false, 'default' => 0), // value must be compatible with strtodate()
                    array('long' => 'enrolperiod',                       'required' => false, 'default' => 0, 'type' => 'int'),
                    array('long' => 'expirynotify',                      'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'notifystudents',                    'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'expirythreshold',                   'required' => false, 'default' => 864000, 'type' => 'int'),
                    array('long' => 'groupmode',                         'required' => false, 'default' => 0, 'type' => 'int'),
                    array('long' => 'groupmodeforce',                    'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'visible',                           'required' => false, 'default' => 1, 'type' => 'boolean'),
                    array('long' => 'enrolpassword',                     'required' => false, 'default' => NULL),
                    array('long' => 'guest',                             'required' => false, 'default' => 0, 'type' => 'boolean'),
                    array('long' => 'lang',                              'required' => false, 'default' => ''), // NULL will be en
                    array('long' => 'restrictmodules',                   'required' => false, 'default' => 0, 'type' => 'boolean'),
                    ),
            'show-course' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => false, 'type' => 'int', 'default' => NULL),
                    array('long' => 'idnumber',          'short' => 'i', 'required' => false, 'default' => NULL),
                    ),
            'list-participants' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                    array('long' => 'group-id',          'short' => 'g', 'required' => false,'type' => 'int', 'default' => NULL),
                    ),
            'list-course-modules' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                    ),
            'delete-course' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                    ),
            'reset-course' =>
                array(
                    array('long' => 'course-id',                    'short' => 'c', 'required' => true,  'type' => 'int'),
                    array('long' => 'reset_events',                 'short' => 'e', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_logs',                   'short' => 'l', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_notes',                  'short' => 'n', 'required' => false, 'type' => 'boolean', 'default' => true),
                    // multiple, send in comma separated integer values
                    array('long' => 'reset_roles',                  'short' => 'r', 'required' => false),
                    array('long' => 'reset_role_overrides',        'short' => 'o', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_role_local',            'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_gradebook_items',        'short' => 'i', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_gradebook_grades',       'short' => 'g', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_groups_remove',          'short' => 'p', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_groups_members',         'short' => 'm', 'required' => false, 'type' => 'boolean', 'default' => true),
                    // module specific stuff. it's a bit hard to pass stuff in here since they're pluggable but at least do core modules.
                    array('long' => 'reset_assignment_submissions', 'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_forum_all',              'short' => 'f', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_forum_subscriptions',    'short' => 'b', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_forum_track_prefs',      'short' => 't', 'required' => false, 'type' => 'boolean', 'default' => true),
                    array('long' => 'reset_forum_ratings',          'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => true),
                ),
            'create-category' =>
                array(
                        array('long' => 'name',                     'short' => 'n', 'required' => true),
                        array('long' => 'description',              'short' => 'd', 'required' => true),
                        array('long' => 'parent',                   'short' => 'p', 'required' => false, 'type' => 'int', 'default' => 0),
                        ),
            'change-category' =>
                array(
                        array('long' => 'categoryid',               'short' => 'c', 'required' => true,  'type' => 'int'),
                        array('long' => 'name',                     'short' => 'n', 'required' => false),
                        array('long' => 'description',              'short' => 'd', 'required' => false),
                        array('long' => 'parent',                   'short' => 'p', 'required' => false, 'type' => 'int', 'default' => NULL),
                        ),
            'show-category' =>
                array(
                        array('long' => 'categoryid',               'short' => 'c', 'required' => true,  'type' => 'int'),
                        ),
            'list-categories' =>
                array( ),
            );
    }

    
    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'create-course':
                // create a course
                return moodlectl_plugin_course::create_course($options, $format);
                break;
            case 'restore-course':
                // restore a backup over a course
                return moodlectl_plugin_course::course_from_backup($options, $format);
                break;
            case 'create-course-from-backup':
                // create a course from a backup
                return moodlectl_plugin_course::create_course_from_backup($options, $format);
                break;
            case 'backup-course':
                // create a course backup
                return moodlectl_plugin_course::course_backup($options, $format);
                break;
            case 'list-courses':
                // list all courses
                return moodlectl_plugin_course::list_all_courses($format);
                break;
            case 'change-course':
                // update a course
                return moodlectl_plugin_course::change_course($options, $format);
                break;
            case 'delete-course':
                // create a course
                return moodlectl_plugin_course::delete_course($options['course-id']);
                break;
            case 'show-course':
                // show all the details of a course
                $id = empty($options['course-id']) ? false : $options['course-id'];
                $idnumber = empty($options['idnumber']) ? false : $options['idnumber'];
                return moodlectl_plugin_course::show_course($id, $idnumber, $format);
                break;
            case 'list-course-modules':
                // show all the details of a course
                return moodlectl_plugin_course::course_modules($options['course-id']);
                break;
            case 'list-participants':
                // show all the details of a course
                return moodlectl_plugin_course::participants($options['course-id'], $options['group-id']);
                break;
            case 'reset-course':
                // reset the course
                return moodlectl_plugin_course::reset_course($options);
           case 'create-category':
                // create a course category
                return moodlectl_plugin_course::create_category($options, $format);
                break;
           case 'change-category':
                // change a course category
                return moodlectl_plugin_course::change_category($options, $format);
                break;
           case 'show-category':
                // show all the details of a course category
                return moodlectl_plugin_course::show_category($options['categoryid'], $format);
                break;
           case 'list-categories':
                // list course categories
                return moodlectl_plugin_course::list_categories($format);
                break;
           default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* retrieve course details, including modules attached
*
* @param int $id the id for the course
* @param int $idnumber the idnumber for the course
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function show_course($id, $idnumber, $format) {
        global $CFG, $DB;
        if (empty($id) && empty($idnumber)) {
            return new Exception(get_string('argerror', MOODLECTL_LANG, 'id/idnumber'));
        }
        if (!empty($id)) {
            if (! $course = $DB->get_record('course', array('id'=>$id))) {
                return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $id));
            }
        }
        else {
            if (! $course = $DB->get_record('course', array('idnumber'=>$idnumber))) {
                return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $idnumber));
            }
        }
        // remove unwanted elements
        unset($course->modinfo);
        $course->startdate_fmt = (0 == $course->startdate)  ? 'Never' : userdate($course->startdate);
        $course->timecreated_fmt = (0 == $course->timecreated)  ? 'Never' : userdate($course->timecreated);
        $course->timemodified_fmt = (0 == $course->timemodified)  ? 'Never' : userdate($course->timemodified);
        $course->url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
        if ($format != 'opts') {
            $course->modules = moodlectl_plugin_course::course_modules($course->id);
            $course->participants = moodlectl_plugin_course::participants($course->id);
        }
        return $course;
    }

/**
* retrieve course modules attached
*
* @param int $id the id for the course
* @return array list of all the course details | or Exception()
* */
    static function course_modules($id) {
        global $CFG, $DB;
        if (! $course = $DB->get_record('course', array('id'=>$id))) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $id));
        }
        $mods = get_course_mods($id);
        // remove unwanted fields
        // add in the summary description
        // format dates
        if ($mods) {
            foreach ($mods as $k => $mod) {
                unset($mod->visibleold);
                $module = $DB->get_record($mod->modname, array('id'=>$mod->instance));
                if ($module) {
                    $mod->name = $module->name;
                    // sort out naming inconsistencies amoungst modules
                    if($mod->modname == 'workshop') {
                        $module->summary = $module->description;
                    }
                    if (!isset($module->summary)) {
                        // degrade through the possible summary field names
                        if (isset($module->description)) {
                            $module->summary = $module->description;
                        } else if (isset($module->text)) {
                            $module->summary = $module->text;
                        }
                        else {
                            $module->summary = $module->name;
                        }
                    }
                    $mod->summary = $module->summary;
                    $mod->added_fmt = (0 == $mod->added) ? 'Never' : userdate($mod->added);
                    $mod->url = $CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id;
                }
                $mods[$k] = (array)$mod;
            }
        }
        return $mods;
    }

/**
* retrieve course participants (the enroled)
*
* @param int $id the id for the course
* @return array list of all the course details | or Exception()
* */
    static function participants($id, $group=NULL) {
        global $CFG, $DB;
        if (! $course = $DB->get_record('course', array('id'=>$id))) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $id));
        }
        $users = search_users($id, $group, '');
        if ($users) {
            foreach ($users as $k => $user) {
                $user= $DB->get_record('user', array('id'=>$user->id));
                $user->firstaccess_fmt  = (0 == $user->firstaccess)  ? 'Never' : userdate($user->firstaccess);
                $user->lastaccess_fmt   = (0 == $user->lastaccess)   ? 'Never' : userdate($user->lastaccess);
                $user->lastlogin_fmt    = (0 == $user->lastlogin)    ? 'Never' : userdate($user->lastlogin);
                $user->currentlogin_fmt = (0 == $user->currentlogin) ? 'Never' : userdate($user->currentlogin);
                $user->timemodified_fmt = (0 == $user->timemodified) ? 'Never' : userdate($user->timemodified);
                $countries = get_string_manager()->get_list_of_countries();
                $user->country_fmt      = (isset($user->country) && isset($countries[$user->country])) ? $countries[$user->country] : $user->country;
                $timezones = get_list_of_timezones();
                $user->timezone_fmt     = (99 == $user->timezone) ? get_string('serverlocaltime') : $timezones[$user->timezone];
                unset($user->password); // password hash
                unset($user->secret); // one-time password reset string
                unset($user->mnethostid);
                $user->url = $CFG->wwwroot.'/user/view.php?id='.$user->id;
                $users[$k] = (array)$user;
            }
        }
        return $users;
    }

/**
* retrieve course details, including modules attached
*
* @param array $data the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function create_course($data, $format) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot."/course/lib.php");

        // Extract the category id from the parameters
        if (!empty($data['categoryid'])){
            if (!$category = $DB->get_record('course_categories', array('id'=>$data['categoryid']))) {
                return new Exception(get_string('courseinvalidcategory', MOODLECTL_LANG, 'id='.$data['categoryid']));
            }
            $data['category'] = $category->id;
            unset($data['categoryid']);
        }
        elseif (!empty($data['categoryname'])){
            if (!$category = $DB->get_record('course_categories', array('name'=>$data['categoryname']))) {
                return new Exception(get_string('courseinvalidcategory', MOODLECTL_LANG, 'name='.$data['categoryname']));
            }
            $data['category'] = $category->id;
            unset($data['categoryname']);
        }
        else {
            return new Exception(get_string('courseinvalidcategory', MOODLECTL_LANG, get_string('none')));
        }

        // make sure that the shortname doesnt allready exist
        if ($DB->get_record('course', array('shortname'=>$data['shortname']))) {
            return new Exception(get_string('courseshortexists', MOODLECTL_LANG, $data['shortname']));
        }

        // generic checks
        $result = moodlectl_plugin_course::check_course($data);
        if (true !== $result) {
            return $result;
        }

        if (!$course = create_course((object)$data)) {
            return new Exception(get_string('coursecreationfailed', MOODLECTL_LANG));
        }

        $context = get_context_instance(CONTEXT_COURSE, $course->id);

        // assign default role to creator if not already having permission to manage course assignments
        if (!has_capability('moodle/course:view', $context) or !has_capability('moodle/role:assign', $context)) {
            role_assign($CFG->creatornewroleid, $USER->id, 0, $context->id);
        }

        // ensure we can use the course right after creating it
        // this means trigger a reload of accessinfo...
        mark_context_dirty($context->path);

        return moodlectl_plugin_course::show_course($course->id, false, $format);
    }


/**
* create or overload a course from a backup
*
* @param array $data the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function course_from_backup($options, $format) {
        global $CFG, $MOODLECTL_NO_KEY;

        if (! $course = get_record('course', 'id', $options['course-id'])) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $options['course-id']));
        }
        require_once($CFG->dirroot.'/backup/restorelib.php');
        require_once($CFG->dirroot.'/backup/lib.php');
        require_once($CFG->dirroot.'/lib/xmlize.php');

        // setup the preferences
        $prefs = array();
        if ($options['metacourse']) {
            $prefs['restore_metacourse'] = 1;
        }
        if ($options['logs']) {
            $prefs['restore_logs'] = 1;
        }
        if ($options['course-files']) {
            $prefs['restore_course_files'] = 1;
        }
        if ($options['site-files']) {
            $prefs['restore_site_files'] = 1;
        }
        if ($options['messages']) {
            $prefs['restore_messages'] = 1;
        }
        if ($options['groups']) {
            $prefs['restore_groups'] = 1;
        }
        if ($options['blogs']) {
            $prefs['restore_blogs'] = 1;
        }

        // make sure that the restore proceeds silently
        if (!defined('BACKUP_SILENTLY')) { // already defined in batch
            define('BACKUP_SILENTLY', true);
            define('RESTORE_SILENTLY_NOFLUSH', true);
        }
        if (!defined('RESTORE_SILENTLY')) {
            define('RESTORE_SILENTLY', true);
        }
        // capture the the progress dots and discard them
        ob_start();
        $result = import_backup_file_silently($options['from-file'], $options['course-id'], $options['delete-first'], $options['users'], $prefs);
        ob_end_clean();
        if (!$result) {
            $errorstring =  get_string('restorefailed', MOODLECTL_LANG);
            $result = new Exception($errorstring);
        }
        return moodlectl_plugin_course::show_course($options['course-id'], false, $format);
    }


/**
* create or overload a course from a backup
*
* @param array $data the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function create_course_from_backup($options, $format) {
        $course = moodlectl_plugin_course::create_course($options, $format);
        if (is_object($course) && get_class($course) == 'Exception') {
            return $course;
        }
        $options['course-id'] = $course->id;
        return moodlectl_plugin_course::course_from_backup($options, $format);
    }


/**
* create or overload a course from a backup
*
* @param int $id the id for the course
* @return boolean - true success | false failure | or Exception()
* */
    static function course_backup($options, $format) {
        global $CFG, $MOODLECTL_NO_KEY;

        if (! $course = get_record('course', 'id', $options['course-id'])) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $options['course-id']));
        }
        require_once($CFG->dirroot.'/backup/lib.php');
        require_once($CFG->dirroot.'/backup/backuplib.php');
        // make sure that the backup proceeds silently
        if (!defined('BACKUP_SILENTLY')) { // already defined in batch
            define('BACKUP_SILENTLY', 1);
            define('RESTORE_SILENTLY_NOFLUSH', '1');
        }

        // setup the preferences
        $prefs = array();
        if ($options['metacourse']) {
            $prefs['backup_metacourse'] = 1;
        }
        if ($options['users']) {
            $prefs['backup_users'] = 1;
        }
        if ($options['logs']) {
            $prefs['backup_logs'] = 1;
        }
        if ($options['user-files']) {
            $prefs['backup_user_files'] = 1;
        }
        if ($options['course-files']) {
            $prefs['backup_course_files'] = 1;
        }
        if ($options['site-files']) {
            $prefs['backup_site_files'] = 1;
        }
        if ($options['messages']) {
            $prefs['backup_messages'] = 1;
        }
        if ($options['gradebook']) {
            $prefs['backup_gradebook_history'] = 1;
        }
        if ($options['blogs']) {
            $prefs['backup_blogs'] = 1;
        }

        // capture the the progress dots and discard them
        ob_start();
        $result = backup_course_silently($options['course-id'], $prefs, $errorstring);
        ob_end_clean();
        if ($result) {
            if ($format == 'opts') {
                $MOODLECTL_NO_KEY = true;
                $result = array($result);
            }
            else {
                $result = array('file' => $result);
            }
        }
        else {
            $errorstring =  get_string('backupfailed', MOODLECTL_LANG).': '.$errorstring;
            $result = new Exception($errorstring);
        }
        return $result;
    }

/**
* retrieve course details, including modules attached
*
* @param string $format the format of input/output
* @return array list of all the courses | or Exception()
* */
    static function list_all_courses($format) {
        global $CFG, $DB;

        $columns = '*';
        if ('opts' == $format) {
            $columns = 'id';
        }

        if (! $courses = $DB->get_records('course', null, 'id', $columns)) {
            return new Exception(get_string('coursesnotfound', MOODLECTL_LANG));
        }

        // remove unwanted elements
        foreach ($courses as $key => $course) {
            if ('opts' == $format) {
                unset($course->id);
            }
            else {
                unset($course->modinfo);
                $course->startdate_fmt = (0 == $course->startdate)  ? 'Never' : userdate($course->startdate);
                $course->timecreated_fmt = (0 == $course->timecreated)  ? 'Never' : userdate($course->timecreated);
                $course->timemodified_fmt = (0 == $course->timemodified)  ? 'Never' : userdate($course->timemodified);
                $course->url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                $course->modules = moodlectl_plugin_course::course_modules($course->id);
                $course->participants = moodlectl_plugin_course::participants($course->id);
            }
	        $courses[$key] = (array)$course;
        }
        return $courses;
    }


/**
* retrieve course details, including modules attached
*
* @param int $course the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function change_course($data, $format) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot."/course/lib.php");
        $data['id'] = $data['course-id'];
        // make sure that the course allready exist
        if (!$course = $DB->get_record('course', array('id'=>$data['id']))) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $data['id']));
        }

        // make sure that the shortname doesnt allready exist
        if ($data['shortname'] !== NULL && $DB->get_record('course', array('shortname'=>$data['shortname']))) {
            return new Exception(get_string('courseshortexists', MOODLECTL_LANG, $data['shortname']));
        }

        // generic checks
        $result = moodlectl_plugin_course::check_course($data);
        if (true !== $result) {
            return $result;
        }

        // remove empty values, as we don't update them
        foreach ($data as $key => $value) {
        	if ($value === NULL) {
        		unset($data[$key]);
        	}
        }

        // do the actual update
        //if (!$course = update_course((object)$data)) {
		if (!$course = $DB->update_record('course',(object)$data)) {
            return new Exception(get_string('courseupdatefailed', MOODLECTL_LANG));
        }

        return moodlectl_plugin_course::show_course($data['id'], false, $format);
    }


/**
* check course data
*
* @param array $data the course data
* @return boolean - true success | false failure | or Exception()
* */
    static function check_course(&$data) {
        global $CFG;

        // check the date - should be in strtodate() format
        $save_startdate = $data['startdate'];
        if ($data['startdate'] !== NULL && !$data['startdate'] = strtotime($data['startdate'])) {
            return new Exception(get_string('courseinvalidstartdate', MOODLECTL_LANG, $save_startdate));
        }
        // check the weeks per topic
        if ($data['enrollable'] !== NULL && ($data['enrollable'] < 0 || $data['enrollable'] > 2)) {
            return new Exception(get_string('courseinvalidenrollable', MOODLECTL_LANG, $data['enrollable']));
        }
        // check the enrol dates
        if ($data['enrollable'] !== NULL && $data['enrollable'] == 2) {
            $save_enrolstartdate = $data['enrolstartdate'];
        	if (!$data['enrolstartdate'] = strtotime($data['enrolstartdate'])) {
                return new Exception(get_string('courseinvalidenrolstartdate', MOODLECTL_LANG, $save_enrolstartdate));
            }
            $save_enrolenddate = $data['enrolenddate'];
            if (!$data['enrolenddate'] = strtotime($data['enrolenddate'])) {
                return new Exception(get_string('courseinvalidenrolenddate', MOODLECTL_LANG, $save_enrolenddate));
            }
            if ($data['enrolstartdate'] >= $data['enrolenddate']) {
                return new Exception(get_string('courseinvalidenrolstartdateenrolenddate', MOODLECTL_LANG, "$save_enrolstartdate - $save_enrolenddate"));
            }
        }
        // check the format is a valid type
        if ($data['format'] !== NULL && !in_array($data['format'], array('weeks', 'lams', 'topics', 'scorm', 'weekscss', 'social'))) {
            return new Exception(get_string('courseinvalidformat', MOODLECTL_LANG, $data['format']));
        }
        // check the weeks per topic
        if ($data['numsections'] !== NULL && ($data['numsections'] < 1 || $data['numsections'] > 52)) {
            return new Exception(get_string('courseinvalidnumsections', MOODLECTL_LANG, $data['numsections']));
        }
        // check the newsitems
        if ($data['newsitems'] !== NULL && ($data['newsitems'] < 1 || $data['newsitems'] > 10)) {
            return new Exception(get_string('courseinvalidnewsitems', MOODLECTL_LANG, $data['newsitems']));
        }
        // check the groupmode
        if ($data['groupmode'] !== NULL && ($data['groupmode'] < 0 || $data['groupmode'] > 2)) {
            return new Exception(get_string('courseinvalidgroupmode', MOODLECTL_LANG, $data['groupmode']));
        }
        // check enrol - enrolment module
        $enrol_modules = explode(',', $CFG->enrol_plugins_enabled);
         if ($data['enrol'] !== NULL && (!$data['enrol'] == '' && !in_array($data['enrol'], $enrol_modules))) {
            return new Exception(get_string('courseinvalidenrol', MOODLECTL_LANG, $data['enrol']));
         }
        // check defaultrole

        return true;
    }


/**
* Delete a course completely
*
* @param int $id the id for the course
* @return boolean - true success | false failure | or Exception()
* */
    static function delete_course($id) {
        global $CFG, $DB;
        if (! $course = $DB->get_record('course', array('id'=>$id))) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $id));
        }
        if (!$site = get_site()) {
          return new Exception(get_string('coursenotfound', MOODLECTL_LANG, $id));
        }
        if ($site->id == $id) {
          return new Exception(get_string('coursenotdeleted', MOODLECTL_LANG, $id));
        }
        add_to_log(SITEID, "course", "delete", "view.php?id=$course->id", "$course->fullname (ID $course->id)");
        $result = delete_course($course, false);
        fix_course_sortorder(); //update course count in categories

        return $result;
    }

    static function reset_course($options) {
        global $CFG, $DB;
        if (! $course = $DB->get_record('course', array('id'=>$options['course-id']))) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $options['course-id']));
        }
        add_to_log($course->id, "course", "reset", "view.php?id=$course->id", "$course->fullname (ID $course->id)");
        // turn the comma separated role list into an array
        $tmp = array();
        if (array_key_exists('reset_roles', $options)) {
            if ($tmp = explode(',', $options['reset_roles'])) {
                $options['reset_roles'] = array();
                foreach ($tmp as $id) {
                    $options['reset_roles'][] = trim($id);
                }
            }
        }
        // GetOpt can't handle reset_roles and reset_roles_* so rename them
        $options['reset_roles_overrides'] = $options['reset_role_overrides'];
        $options['reset_roles_local']     = $options['reset_role_local'];
        // make sure we have the right values we need
        $options = (object)array_merge($options, array('id' => $options['course-id'], 'courseid' => $options['course-id']));
        $status = reset_course_userdata($options);
        $errors = array();
        foreach ($status as $result) {
            if ($result['error'] !== false) {
                $errors[] = array($result['component'] => $result['error']);
            }
        }
        return $errors;
    }
    
/**
* Create a course category
*
* @param array $data the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course category details | or Exception()
* */
    static function create_category($data, $format) {
		global $DB;
        $newcategory = new stdClass();
        $newcategory->name = $data['name'];
        $newcategory->description = $data['description'];
        $newcategory->parent = $data['parent']; // if $data->parent = 0, the new category will be a top-level category
        if ($newcategory->parent != 0) {
            if (!$parent_cat = $DB->get_record('course_categories', array('id'=>$newcategory->parent))) {
                return new Exception(get_string('createcategoryparenterror', MOODLECTL_LANG, $newcategory->parent));
            }
        }
        // Create a new category.
        $newcategory->sortorder = 999;
        if (!$newcategory->id = $DB->insert_record('course_categories', $newcategory)) {
            return new Exception(get_string('createcategoryerror', MOODLECTL_LANG, $newcategory->name));
        }
        $newcategory->context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
        mark_context_dirty($newcategory->context->path);
        fix_course_sortorder(); // Required to build course_categories.depth and .path.
        return moodlectl_plugin_course::show_category($newcategory->id, $format);
    }
    
/**
* Change a course category
*
* @param array $data the array of the course details to be changed
* @param string $format the format of input/output
* @return array list of all the course category details | or Exception()
* */
    static function change_category($data, $format) {
		global $DB;
        // Update an existing category.
        $newcategory = new stdClass();
        $newcategory->id = $data['categoryid'];
        if ($data['parent'] != 0) {
            $newcategory->parent = $data['parent'];
            if (!$parent_cat = $DB->get_record('course_categories', array('id'=>$newcategory->parent))) {
                return new Exception(get_string('createcategoryparenterror', MOODLECTL_LANG, $newcategory->parent));
            }
        }
        if ($data['name']) {
            $newcategory->name = $data['name'];
        }
        if ($data['description']) {
            $newcategory->description = $data['description'];
        }
        if (!$DB->update_record('course_categories', $newcategory)) {
            return new Exception(get_string('changecategoryparenterror', $newcategory->id));
        }
        fix_course_sortorder();
        return moodlectl_plugin_course::show_category($newcategory->id, $format);
    }
    
    
/**
* Show a course category
*
* @param int $id the id of the course category
* @param string $format the format of input/output
* @return array list of all the course category details | or Exception()
* */
    static function show_category($id, $format) {
		global $DB;
		if (!$category = $DB->get_record('course_categories', array('id'=>$id))) {		
            return false;
        }
        return (array)$category;
    }
    
    
/**
* list a course categories
*
* @param string $format the format of input/output
* @return array list of all the course categories | or Exception()
* */
    static function list_categories($format) {
		global $DB;
        $categories = $DB->get_records('course_categories');
        
        foreach ($categories as $id => $category) {
            $categories[$id] = (array)$category;
        }
        return $categories;
    }
    
}
?>