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
*/
class moodlectl_plugin_wiki extends moodlectl_plugin_base {

    function help() {
        return
        array(
        'create-wiki' => "  Create a new Wiki, within a given course:
    moodlectl create-wiki --course-id=1 --name='The wiki name' --summary='Just a summary'
    --separate-groups if specified will create separate groups - default is false",
        'delete-wiki' => "  Delete a Wiki instance:
    moodlectl delete-wiki --module-id=999",
        );
    }

    function command_line_options() {
        return
        array(
        'create-wiki' => array(
            array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int'),
            array('long' => 'name', 'short' => 'n', 'required' => true),
            array('long' => 'separate-groups', 'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => false),
            array('long' => 'summary', 'short' => 't', 'required' => true),
            ),
        'delete-wiki' => array(
            array('long' => 'module-id', 'short' => 'i', 'required' => true, 'type' => 'int')
            )
        );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'create-wiki':
                // create default Course Wiki
                return moodlectl_plugin_wiki::create_wiki($options['course-id'], $options['separate-groups'], $options['name'], $options['summary']);
                break;
            case 'delete-wiki':
                // delete a wiki
                return moodlectl_plugin_wiki::delete_wiki($options['module-id']);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* basic encapsulation of creating a wiki instance - as would be done in modedit.php
* with wiki_add_instance();
*
* @param int $courseid the id of the course to add forum to
* @param int $groupmode the groupmode to use eg. separate groups
* @param string $wiki_name  name of the wiki - title
* @param string $summary  summary descrition of the wiki
* */

    static function create_wiki($courseid, $groupmode, $wiki_name, $summary) {
        global $CFG;
        require_once($CFG->dirroot."/mod/wiki/lib.php");
        require_once($CFG->dirroot."/lib/grouplib.php");
        if (! $course = get_record("course", "id", $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }

        $wiki = new object();
        $wiki->course = $courseid;
        $wiki->cmidnumber = 1;
        $wiki->name = $wiki_name;
        $wiki->summary = $summary;
        $wiki->htmlmode = 2;
        $wiki->wtype = 'group';
        $wiki->ewikiacceptbinary = 0;
        $wiki->ewikiprinttitle = 1;
        $wiki->disablecamelcase = 0;
        $wiki->setpageflags = 0;
        $wiki->strippages = 0;
        $wiki->removepages = 0;
        $wiki->revertchanges = 0;
        $wiki->pagename = '';
        $wiki->initialcontent = '';
        $wiki->groupingid = $course->defaultgroupingid;
        $wiki->groupmembersonly = 0;
    //    gradecat    8   - uncategorised
        $wiki->groupmode = $groupmode;
        $wiki->visible = '0';
        $wiki->module = get_field('modules', 'id', 'name', 'wiki');
        $wiki->id = wiki_add_instance($wiki);
        $wiki->modulename = 'wiki';
        $wiki->instance = $wiki->id;
        $wiki->section = 0; // default to first level section
        $wiki->coursemodule = add_course_module($wiki);
        $sectionid = add_mod_to_section($wiki);
        set_field("course_modules", "section", $sectionid, "id", $wiki->coursemodule);
        set_coursemodule_visible($wiki->coursemodule, $wiki->visible);
        set_coursemodule_idnumber($wiki->coursemodule, $wiki->cmidnumber);
        rebuild_course_cache($wiki->course);
        return $wiki;
    }


/**
* basic encapsulation deleting a wiki instance - as would be done in modedit.php
* with wiki_add_instance();
*
* @param int $id the id of the wiki to delete
* @return boolean success/failure of delete
* */

    static function delete_wiki($cm_id) {
        global $CFG;
        require_once($CFG->dirroot."/mod/wiki/lib.php");
        if (! $cm = get_coursemodule_from_id('wiki', $cm_id)) {
            return new Exception(get_string('wikibadmodule', MOODLECTL_LANG, $cm_id));
        }
        wiki_delete_instance($cm->instance);
        delete_course_module($cm->id);
        delete_mod_from_section($cm->id, "$cm->section");
        if ($wiki = get_record("wiki", "id", $cm->instance)) {
            return new Exception(get_string('wikideletefailed', MOODLECTL_LANG, $cm_id));
        }
        else {
            return true;
        }
    }
}
?>