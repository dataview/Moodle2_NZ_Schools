<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
/**
* Extend the base plugin class
* This class contains the actions for exporting and importing gradebook values
*/
class moodlectl_plugin_gradebook extends moodlectl_plugin_base {

    function help() {
        return
            array(
            'export-gradebook' => "  export the gradebook for a given course:
    moodlectl export-gradebook  --course-id=<id> ",
            'gradebook-list-items' => "  list the items of a gradebook:
    moodlectl gradebook-list-items  --course-id=<id> 
    See addtional help at http://docs.moodle.org/en/grade/import/xml/index    ",
            'import-gradebook-code' => "  obtain a new import code for building a batch of grades to import:
    moodlectl import-gradebook-code
    See addtional help at http://docs.moodle.org/en/grade/import/xml/index    ",
            'import-gradebook-add' => "  add a grade to the import stack:
    moodlectl import-gradebook-add  --import-code=<code> --course-id=<id> --grade-id=<id> --user-id=<id> --score=<score> --feedback=<feedback>
    See addtional help at http://docs.moodle.org/en/grade/import/xml/index",
            'import-gradebook-commit' => "  commit the grade import stack:
    moodlectl import-gradebook-commit --import-code=<code>
    See addtional help at http://docs.moodle.org/en/grade/import/xml/index",
            'import-gradebook' => "  import single entry into the gradebook:
    moodlectl import-gradebook --course-id=<id> --grade-id=<id> --user-id=<id> --score=<score> --feedback=<feedback>
    See addtional help at http://docs.moodle.org/en/grade/import/xml/index    ",
            );
    }

    function command_line_options() {
        return
        array(
        'export-gradebook' => array(
            array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int'),
            ),
        'gradebook-list-items' => array(
            array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int'),
            ),
        'import-gradebook-code' => array(
            ),
        'import-gradebook-add' => array(
            array('long' => 'import-code', 'short' => 'i', 'required' => true,  'type' => 'int'),
            array('long' => 'course-id',   'short' => 'c', 'required' => true,  'type' => 'int'),
            array('long' => 'grade-id',    'short' => 'g', 'required' => true,  'type' => 'int'),
            array('long' => 'user-id',     'short' => 'u', 'required' => true,  'type' => 'int'),
            array('long' => 'score',       'short' => 's', 'required' => false, 'type' => 'double', 'default' => NULL),
            array('long' => 'feedback',    'short' => 'f', 'required' => false                    , 'default' => NULL),
            ),
        'import-gradebook-commit' => array(
            array('long' => 'course-id',   'short' => 'c', 'required' => true,  'type' => 'int'),
            array('long' => 'import-code', 'short' => 'i', 'required' => true,  'type' => 'int'),
            ),
        'import-gradebook' => array(
            array('long' => 'course-id',   'short' => 'c', 'required' => true,  'type' => 'int'),
            array('long' => 'grade-id',    'short' => 'g', 'required' => true,  'type' => 'int'),
            array('long' => 'user-id',     'short' => 'u', 'required' => true,  'type' => 'int'),
            array('long' => 'score',       'short' => 's', 'required' => false, 'type' => 'double', 'default' => NULL),
            array('long' => 'feedback',    'short' => 'f', 'required' => false                    , 'default' => NULL),
            ),
        );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'export-gradebook':
                // list out the gradebook values
                return moodlectl_plugin_gradebook::export_gradebook($options['course-id'], $format);
                break;
            case 'gradebook-list-items':
                // list the grade items for a gradebook
                return moodlectl_plugin_gradebook::list_grade_items($options['course-id']);
                break;
            case 'import-gradebook-code':
                // obtain a new import code for batching grades together
                return moodlectl_plugin_gradebook::import_code($format);
                break;
            case 'import-gradebook-add':
                // add a grade to the import stack
                return moodlectl_plugin_gradebook::add_grade($options['import-code'],
                                                             $options['course-id'],
                                                             $options['grade-id'],
                                                             $options['user-id'],
                                                             $options['score'],
                                                             $options['feedback']);
                break;
            case 'import-gradebook-commit':
                // commit the current stack of grades against an import code
                return moodlectl_plugin_gradebook::commit_grades($options['course-id'], $options['import-code']);
                break;
            case 'import-gradebook':
                // import and commit one grade
                return moodlectl_plugin_gradebook::import_gradebook($format,
                                                             $options['course-id'],
                                                             $options['grade-id'],
                                                             $options['user-id'],
                                                             $options['score'],
                                                             $options['feedback']);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* export the gradebook values
*
* @param int $courseid the course id
* @return array list of gradebook values
* */
    static function export_gradebook($courseid, $format) {
        global $CFG;
        if (! $course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        require_once $CFG->dirroot.'/grade/export/lib.php';
        require_once 'grade_export_array.php';

        // Grab the grade_seq for this course
        $switch = grade_get_setting($courseid, 'aggregationposition', $CFG->grade_aggregationposition);
        $gseq = new grade_seq($courseid, $switch);
        $items = array();
        if ($grade_items = $gseq->items) {
            foreach ($grade_items as $grade_item) {
                $items[]= $grade_item->id;
            }
        }
        $itemids = implode(',', $items);
        $groupid = 0;
        $export_feedback = 0;
        $updatedgradesonly = false;
        $displaytype = $CFG->grade_export_displaytype;
        $decimalpoints = $CFG->grade_export_decimalpoints;
        $export = new grade_export_array($course, $groupid, $itemids, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints);
        $results = $export->print_grades($format);
        if ($format == 'opts') {
            return true;
        }
        else {
            return $results;
        }
    }
    
/**
* list all grade items 
*
* @param int $courseid the course id
* @return array grade_items | boolean false
* */
    static function list_grade_items($courseid) {
        global $CFG;
        if (! $course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        require_once $CFG->dirroot.'/lib/grade/grade_item.php';
        if ($grade_items = grade_item::fetch_all(array('courseid'=>$course->id))) {
            $items = array();
            foreach ($grade_items as $grade_item) {
                if ($grade_item->hidden) {
                    continue;
                }
                $items[$grade_item->id]= array('id' => $grade_item->id,
                                'courseid'        => $grade_item->courseid,
                                'categoryid'      => $grade_item->categoryid,
                                'itemname'        => $grade_item->itemname,
                                'itemtype'        => $grade_item->itemtype,
                                'itemmodule'      => $grade_item->itemmodule,
                                'iteminstance'    => $grade_item->iteminstance,
                                'itemnumber'      => $grade_item->itemnumber,
                                'iteminfo'        => $grade_item->iteminfo,
                                'idnumber'        => $grade_item->idnumber,
                                'calculation'     => $grade_item->calculation,
                                'gradetype'       => $grade_item->gradetype,
                                'grademax'        => $grade_item->grademax,
                                'grademin'        => $grade_item->grademin,
                                'scaleid'         => $grade_item->scaleid,
                                'outcomeid'       => $grade_item->outcomeid,
                                'gradepass'       => $grade_item->gradepass,
                                'multfactor'      => $grade_item->multfactor,
                                'plusfactor'      => $grade_item->plusfactor,
                                'aggregationcoef' => $grade_item->aggregationcoef,
                                'display'         => $grade_item->display,
                                'decimals'        => $grade_item->decimals,
                                'locked'          => $grade_item->locked,
                                'needsupdate'     => $grade_item->needsupdate,
                );
            }
            return $items;
        }
        else {
            return false;
        }
    }
    
/**
* generate a new grade import code
*
* @return int grade import code
* */
    static function import_code($format) {
        global $CFG, $MOODLECTL_NO_KEY;
        require_once $CFG->dirroot.'/grade/import/lib.php';
        if ($format == 'opts') {
            $MOODLECTL_NO_KEY = true;
        }
        return array('importcode' => get_new_importcode());
    }
    
/**
* add gradebook values to import stack
*
* @param int $importcode the grade import code
* @param int $courseid the course id
* @param int $gradeidnumber grade id number
* @param int $useridnumber the user id
* @param number $score the score
* @param string $feedback the feedback
* @return boolean success or failure of uploading gradebook entires
* */
    static function add_grade($importcode, $courseid, $gradeidnumber, $useridnumber, $score, $feedback) {
        global $CFG, $USER;
        if (! $course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        require_once $CFG->libdir.'/gradelib.php';
        require_once $CFG->dirroot.'/grade/lib.php';
        require_once $CFG->dirroot.'/grade/import/lib.php';
        
//        if (!$grade_items = grade_item::fetch_all(array('idnumber'=>$gradeidnumber, 'courseid'=>$course->id))) {
        if (!$grade_items = grade_item::fetch_all(array('id'=>$gradeidnumber, 'courseid'=>$course->id))) {
            // gradeitem does not exist
            return new Exception(get_string('errincorrectgradeidnumber', 'gradeimport_xml', $gradeidnumber));
        } else if (count($grade_items) != 1) {
            return new Exception(get_string('errduplicategradeidnumber', 'gradeimport_xml', $gradeidnumber));
        } else {
            $grade_item = reset($grade_items);
        }

        // grade item locked, abort
        if ($grade_item->is_locked()) {
            $status = false;
            $error  = get_string('gradeitemlocked', 'grades');
            break;
        }

        // check if user exist and convert idnumber to user id
        if (!$user = get_record('user', 'id', addslashes($useridnumber))) {
            // no user found, abort
            return new Exception(get_string('errincorrectuseridnumber', 'gradeimport_xml', $useridnumber));
        }

        // check if grade_grade is locked and if so, abort
        if ($grade_grade = new grade_grade(array('itemid'=>$grade_item->id, 'userid'=>$user->id))) {
            $grade_grade->grade_item =& $grade_item;
            if ($grade_grade->is_locked()) {
                // individual grade locked, abort
                return new Exception(get_string('gradelocked', 'grades'));
            }
        }

        $newgrade = new object();
        $newgrade->itemid     = $grade_item->id;
        $newgrade->userid     = $user->id;
        $newgrade->importcode = $importcode;
        $newgrade->importer   = $USER->id;
        $newgrade->finalgrade = $score;
        $newgrade->feedback   = $feedback;

        // insert this grade into a temp table
        if (!insert_record('grade_import_values', addslashes_recursive($newgrade))) {
            // could not insert into temp table
            return new Exception(get_string('importfailed', 'grades'));
        }
        return true;
    }
    
/**
* commit gradebook entries in the import stack
*
* @param int $importcode the grade import code
* @return boolean success or failure of commiting gradebook entires
* */
    static function commit_grades($courseid, $importcode) {
        global $CFG;
        if (! $course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        if (count_records('grade_import_values', 'importcode', $importcode) == 0) {
            return new Exception(get_string('noimportgrades', MOODLECTL_LANG, $importcode));
        }
        require_once $CFG->libdir.'/gradelib.php';
        require_once $CFG->dirroot.'/grade/lib.php';
        require_once $CFG->dirroot.'/grade/import/lib.php';
        if (grade_import_commit($courseid, $importcode, true, false)) {
            return true;
        }
        else {
            return new Exception(get_string('gradecommitfailed', MOODLECTL_LANG, $importcode));
        }
    }
    
/**
* import gradebook value
*
* @param int $courseid the course id
* @param int $gradeidnumber grade id number
* @param int $useridnumber the user id
* @param number $score the score
* @param string $feedback the feedback
* @return boolean success or failure of uploading gradebook entires
* */
    static function import_gradebook($format, $courseid, $gradeidnumber, $useridnumber, $score, $feedback) {
        global $CFG;
        $importcode = self::import_code($format);
        $result = self::add_grade($importcode['importcode'], $courseid, $gradeidnumber, $useridnumber, $score, $feedback);
        if (true !== $result) {
            return $result;
        }
        return self::commit_grades($courseid, $importcode['importcode']);
    }
}
?>