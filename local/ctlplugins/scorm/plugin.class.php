<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
error_reporting(E_ALL & !E_NOTICE & ~E_DEPRECATED); // get rid of notices as these muck up the result
ini_set('display_errors', '1');
global $CFG;
require_once($CFG->dirroot."/mod/scorm/lib.php");
require_once($CFG->dirroot."/lib/grouplib.php");
/**
* Extend the base plugin class
*/
class moodlectl_plugin_scorm extends moodlectl_plugin_base {

    function help() {
        return
        array(
            'create-scorm' => "  Create a new course:
     moodlectl create-scorm --course-id=1 --name='The course name' --summary='Just a summary'
     Please refer to course creation help: http://docs.moodle.org/en/course/edit",
            'list-scorms' =>"  List all courses:
     moodlectl list-scorms",
            'change-course' =>"  Change a course:
     moodlectl change-scorm --course-id=1 --name='The course name' --summary='Just a summary'
     Please refer to course amendment help: http://docs.moodle.org/en/course/edit",
            'show-scorm' => "  Show the details of a course:
     moodlectl show-scorm --course-id=1",
            'delete-scorm' => "  Delete a course:
     moodlectl delete-scorm --course-id=1",
            );
    }

    function command_line_options() {
        global $CFG;

        return array(
            'list-scorms' =>
                array( 
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                ),
            'change-scorm' =>
                array(
                    array('long' => 'scorm-id',          'short' => 'i', 'required' => true, 'type' => 'int'),
                    array('long' => 'name',              'short' => 'n', 'required' => false, 'default' => NULL),
                    array('long' => 'reference',         'short' => 'f', 'required' => false),
                    array('long' => 'summary',           'short' => 's', 'required' => false),
                    array('long' => 'maxgrade',                          'required' => false, 'default' => NULL, 'type' => 'double'),
                    array('long' => 'grademethod',                       'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'maxattempt',                        'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'updatefreq',                        'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'skipview',                          'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'hidebrowse',                        'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'hidetoc',                           'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'hidenav',                           'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'auto',                              'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'popup',                             'required' => false, 'default' => NULL, 'type' => 'boolean'),
                    array('long' => 'options',                           'required' => false, 'default' => NULL),
                    array('long' => 'width',                             'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'height',                            'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'whatgrade',                         'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'section',                           'required' => false, 'default' => NULL, 'type' => 'int'),
                    array('long' => 'visible',                           'required' => false, 'default' => NULL, 'type' => 'int'),
                    ),
            'create-scorm' =>
                array(
                    array('long' => 'course-id',         'short' => 'c', 'required' => true, 'type' => 'int'),
                    array('long' => 'name',              'short' => 'n', 'required' => true),
                    array('long' => 'reference',         'short' => 'f', 'required' => true),
                    array('long' => 'summary',           'short' => 's', 'required' => true),
                    array('long' => 'maxgrade',                          'required' => false, 'default' => $CFG->scorm_maxgrade, 'type' => 'double'),
                    array('long' => 'grademethod',                       'required' => false, 'default' => $CFG->scorm_grademethod, 'type' => 'int'),
                    array('long' => 'maxattempt',                        'required' => false, 'default' => $CFG->scorm_maxattempts, 'type' => 'int'),
                    array('long' => 'updatefreq',                        'required' => false, 'default' => $CFG->scorm_updatefreq, 'type' => 'int'),
                    array('long' => 'skipview',                          'required' => false, 'default' => $CFG->scorm_skipview, 'type' => 'boolean'),
                    array('long' => 'hidebrowse',                        'required' => false, 'default' => $CFG->scorm_hidebrowse, 'type' => 'boolean'),
                    array('long' => 'hidetoc',                           'required' => false, 'default' => $CFG->scorm_hidetoc, 'type' => 'boolean'),
                    array('long' => 'hidenav',                           'required' => false, 'default' => $CFG->scorm_hidenav, 'type' => 'boolean'),
                    array('long' => 'auto',                              'required' => false, 'default' => $CFG->scorm_auto, 'type' => 'boolean'),
                    array('long' => 'popup',                             'required' => false, 'default' => $CFG->scorm_popup, 'type' => 'boolean'),
                    array('long' => 'options',                           'required' => false, 'default' => ''),
                    array('long' => 'width',                             'required' => false, 'default' => $CFG->scorm_framewidth, 'type' => 'int'),
                    array('long' => 'height',                            'required' => false, 'default' => $CFG->scorm_frameheight, 'type' => 'int'),
                    array('long' => 'whatgrade',                         'required' => false, 'default' => $CFG->scorm_whatgrade, 'type' => 'int'),
                    array('long' => 'section',                           'required' => false, 'default' => 0, 'type' => 'int'),
                    array('long' => 'visible',                           'required' => false, 'default' => 1, 'type' => 'int'),
                    ),
            'show-scorm' =>
                array(
                    array('long' => 'scorm-id',         'short' => 'i', 'required' => true, 'type' => 'int'),
                    ),
            'delete-scorm' =>
                array(
                    array('long' => 'scorm-id',         'short' => 'i', 'required' => true, 'type' => 'int'),
                    ),
            );
    }


    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'change-scorm':
                // restore a backup over a course
                return moodlectl_plugin_scorm::change_scorm($options, $format);
                break;
            case 'create-scorm':
                // create a course from a backup
                return moodlectl_plugin_scorm::create_scorm($options, $format);
                break;
            case 'list-scorms':
                // list all courses
                return moodlectl_plugin_scorm::list_scorms($options['course-id'], $format);
                break;
            case 'delete-scorm':
                // create a course
                return moodlectl_plugin_scorm::delete_scorm($options['scorm-id']);
                break;
            case 'show-scorm':
                // show all the details of a course
                return moodlectl_plugin_scorm::show_scorm($options['scorm-id'], $format);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* retrieve course details, including modules attached
*
* @param int $id the id for the scorm activity
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function show_scorm($id, $format) {
        global $CFG;
        if (! $scorm = get_record('scorm', 'id', $id)) {
            return new Exception(get_string('scormnotexists', MOODLECTL_LANG, $id));
        }
        $scorm->timemodified_fmt = (0 == $scorm->timemodified)  ? 'Never' : userdate($scorm->timemodified);
        $scorm->url = $CFG->wwwroot.'/mod/scorm/view.php?id='.$scorm->id;
        return $scorm;
    }

/**
* create or overload a course from a backup
*
* @param array $options the array of the scorm details to be created
* @param string $format the format of input/output
* @return array list of all the scorm details | or Exception()
* */
    static function create_scorm($options, $format) {
        if (! $course = get_record("course", "id", $options['course-id'])) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $options['course-id']));
        }
        
        // generic checks
        $options['datadir']      = NULL;
        $options['pkgtype']      = NULL;
        $options['launch']       = NULL;
        $options['redirect']     = NULL;
        $options['redirecturl']  = NULL;
        $options['coursemodule'] = NULL;
        $options['instance']     = NULL;
        $options['strictmode']   = 0;
        $options['course']       = $options['course-id'];
        $result = moodlectl_plugin_scorm::check_scorm($course, $options);
        if (true !== $result) {
            return $result;
        }
        
        $scorm = new object();
        $scorm->course      = $options['course-id'];
        $scorm->cmidnumber  = 1;
        $scorm->name        = $options['name'];
        $scorm->summary     = $options['summary'];
        $scorm->reference   = $options['reference'];
        $scorm->maxgrade    = $options['maxgrade'];
        $scorm->grademethod = $options['grademethod'];
        $scorm->maxattempt  = $options['maxattempt'];
        $scorm->updatefreq  = $options['updatefreq'];
        $scorm->datadir     = $options['datadir'];
        $scorm->pkgtype     = $options['pkgtype'];
        $scorm->launch      = $options['launch'];
        $scorm->redirect    = $options['redirect'];
        $scorm->redirecturl = $options['redirecturl'];
        $scorm->skipview    = $options['skipview'];
        $scorm->hidebrowse  = $options['hidebrowse'];
        $scorm->hidetoc     = $options['hidetoc'];
        $scorm->hidenav     = $options['hidenav'];
        $scorm->auto        = $options['auto'];
        $scorm->popup       = $options['popup'];
        $scorm->options     = $options['options'];
        $scorm->width       = $options['width'];
        $scorm->height      = $options['height'];
        $scorm->whatgrade   = $options['whatgrade'];
        $scorm->strictmode  = $options['strictmode'];
        $scorm->visible     = $options['visible'];

        $scorm->groupingid       = $course->defaultgroupingid;
        $scorm->instance         = NULL;
        $scorm->groupmembersonly = 0;
        $scorm->groupmode        = 0;
        $scorm->gradecat         = 1;
        $scorm->module = get_field('modules', 'id', 'name', 'scorm');
        
        $scorm->id = scorm_add_instance($scorm);
        if (!$scorm->id) {
            return new Exception(get_string('scormcreationfailed', MOODLECTL_LANG));
        }
        $scormid = $scorm->id;
        $scorm->modulename = 'scorm';
        $scorm->instance = $scorm->id;
        $scorm->section = $options['section']; // default to first level section
        $scorm->coursemodule = add_course_module($scorm);
        $sectionid = add_mod_to_section($scorm);
        set_field("course_modules", "section", $sectionid, "id", $scorm->coursemodule);
        set_coursemodule_visible($scorm->coursemodule, $scorm->visible);
        set_coursemodule_idnumber($scorm->coursemodule, $scorm->cmidnumber);
        rebuild_course_cache($scorm->course);
        return moodlectl_plugin_scorm::show_scorm($scormid, $format);
    }


/**
* retrieve course details, including modules attached
*
* @param string $format the format of input/output
* @return array list of all the courses | or Exception()
* */
    static function list_scorms($courseid, $format) {
        global $CFG;
        // make sure that the course allready exist
        if (!$course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        if (! $scorms = get_records('scorm', 'course', $courseid)) {
            return new Exception(get_string('scormsnotfound', MOODLECTL_LANG));
        }
        foreach ($scorms as $key => $scorm) {
            $scorm->timemodified_fmt = (0 == $scorm->timemodified)  ? 'Never' : userdate($scorm->timemodified);
            $scorm->url = $CFG->wwwroot.'/mod/scorm/view.php?id='.$scorm->id;
	        $scorms[$key] = (array)$scorm;
        }
        return $scorms;
    }


/**
* retrieve course details, including modules attached
*
* @param int $course the array of the course details to be created
* @param string $format the format of input/output
* @return array list of all the course details | or Exception()
* */
    static function change_scorm($data, $format) {
        global $CFG;
        if (! $scorm = get_record('scorm', 'id', $data['scorm-id'])) {
            return new Exception(get_string('scormnotexists', MOODLECTL_LANG, $data['scorm-id']));
        }
        $course = get_record("course", "id", $scorm->course);
        $update_data = (array)$scorm;
        $data['id']       = $data['scorm-id'];
        $data['instance'] = $data['scorm-id'];
        foreach ($data as $key => $value) {
            if ($value !== NULL) {
                $update_data[$key] = $value;
            }
        }

        // generic checks
        $result = moodlectl_plugin_scorm::check_scorm($course, $update_data);
        if (true !== $result) {
            return $result;
        }
        
        // update the modified values
        foreach ($data as $key => $value) {
        	if ($value !== NULL) {
        	    $scorm->$key = $value;
        	}
        }
        if (!scorm_update_instance($scorm)) {
            return new Exception(get_string('scormcreationfailed', MOODLECTL_LANG));
        }

        return moodlectl_plugin_scorm::show_scorm($data['scorm-id'], $format);
    }


/**
* check course data
*
* @param array $data the course data
* @param array $data the course data
* @return boolean - true success | false failure | or Exception()
* */
    static function check_scorm($course, &$data) {
        global $CFG;

        require_once($CFG->dirroot.'/mod/scorm/locallib.php');
        require_once($CFG->dirroot.'/mod/scorm/mod_form.php');
        //$instance, $section, $cm
        $cm = NULL;
        $instance = NULL;
        if (isset($data['id'])) {
            $instance = $data['scorm-id'];
            $cm = get_coursemodule_from_instance("scorm", $data['scorm-id'], $course->id);
            $data['section'] = $cm->section;
            $data['coursemodule'] = $cm->module;
        }
        $cw = get_course_section($data['section'], $course->id);
        $mform = new mod_scorm_mod_form($instance, $cw->section, $cm);
        $mform->data_preprocessing($data);
        $validate = scorm_validate($data);
        if (!$validate->result) {
            return new Exception(implode(' ', $validate->errors));
        }
        
        return true;
    }


/**
* Delete a course completely
*
* @param int $id the id for the course
* @return boolean - true success | false failure | or Exception()
* */
    static function delete_scorm($id) {
        global $CFG;
        if (! $scorm = get_record('scorm', 'id', $id)) {
            return new Exception(get_string('scormnotexists', MOODLECTL_LANG, $id));
        }
        $course = get_record("course", "id", $scorm->course);
        $cm = get_coursemodule_from_instance("scorm", $scorm->id, $course->id);
        $result = scorm_delete_instance($id);
        delete_course_module($cm->id);
        delete_mod_from_section($cm->id, "$cm->section");
        add_to_log(SITEID, "scorm", "delete", "mod/scorm/view.php?id=$scorm->id", $scorm->name);
        if ($scorm = get_record('scorm', 'id', $id)) {
            return new Exception(get_string('scormdeletefailed', MOODLECTL_LANG, $id));
        }
        else {
            return true;
        }
    }
}
?>