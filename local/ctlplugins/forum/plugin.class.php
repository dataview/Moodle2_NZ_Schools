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
class moodlectl_plugin_forum extends moodlectl_plugin_base {

    function help() {
        return
        array(
        'create-forum' => "  Create a new forum, within a given course:
    moodlectl create-forum --course-id=1 --name='The forum name' --intro='Just a summary'
    --separate-groups if specified will create separate groups - default is false",
        'change-forum' => "  Change a forum:
    moodlectl change-forum --module-id=999 --name='The forum name' --intro='Just a summary'",
        'delete-forum' => "  Delete a forum instance:
    moodlectl delete-forum --module-id=999",
        'create-discussion' => "  Post to a forum instance:
    moodlectl create-discussion --module-id=999 --subject=<subject text> --message=<some text to post>",
        'post-forum' => "  Post to a forum instance:
    moodlectl post-forum --discussion-id=<discussion id> --parent=<parent id> --message=<some text to post>",
        'list-discussions' => "  list posts of a forum instance:
    moodlectl list-discussions --module-id=999",
        'discussion-posts' => "  list posts of a forum instance:
    moodlectl discussion-posts --discussion-id=999",
        'list-forums' => "  list forums of a course:
    moodlectl list-forum --course-id=999",
        );
    }

    function command_line_options() {
        return
        array(
        'create-forum' => array(
            array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int'),
            array('long' => 'name', 'short' => 'n', 'required' => true),
            array('long' => 'separate-groups', 'short' => 's', 'required' => false, 'type' => 'boolean', 'default' => false),
            array('long' => 'intro', 'short' => 't', 'required' => true),
            array('long' => 'assessed', 'short' => 'a', 'required' => false, 'type' => 'boolean', 'default' => false),
            array('long' => 'scale', 'short' => 'l', 'required' => false, 'type' => 'int', 'default' => 0),
            ),
        'change-forum' => array(
            array('long' => 'module-id', 'short' => 'm', 'required' => true, 'type' => 'int'),
            array('long' => 'name', 'short' => 'n', 'required' => false, 'default' => NULL),
            array('long' => 'intro', 'short' => 't', 'required' => false, 'default' => NULL),
            ),
        'delete-forum' => array(
            array('long' => 'module-id', 'short' => 'i', 'required' => true, 'type' => 'int')
            ),
        'create-discussion' => array(
            array('long' => 'module-id', 'short' => 'm', 'required' => true, 'type' => 'int'),
            array('long' => 'subject', 'short' => 's', 'required' => true),
            array('long' => 'message', 'short' => 't', 'required' => true),
            ),
        'post-forum' => array(
            array('long' => 'discussion-id', 'short' => 'd', 'required' => true, 'type' => 'int'),
            array('long' => 'parent', 'short' => 'p', 'required' => false, 'type' => 'int', 'default' => 0),
            array('long' => 'message', 'short' => 't', 'required' => true),
            ),
        'list-discussions' => array(
            array('long' => 'module-id', 'short' => 'm', 'required' => true, 'type' => 'int')
            ),
        'discussion-posts' => array(
            array('long' => 'discussion-id', 'short' => 'd', 'required' => true, 'type' => 'int')
            ),
        'list-forums' => array(
            array('long' => 'course-id', 'short' => 'c', 'required' => true, 'type' => 'int')
            ),
        );
    }

    function execute($action, $options, $mode, $format) {

        switch ($action) {
            case 'create-forum':
                // create Course forum
                return moodlectl_plugin_forum::create_forum($options['course-id'], $options['separate-groups'], $options['name'], $options['intro'], $options);
                break;
            case 'change-forum':
                // change Course forum
                return moodlectl_plugin_forum::change_forum($options);
                break;
            case 'delete-forum':
                // delete a forum
                return moodlectl_plugin_forum::delete_forum($options['module-id']);
                break;
            case 'create-discussion':
                // create a new discussion
                return moodlectl_plugin_forum::forum_discussion($options['module-id'], $options['subject'], $options['message']);
                break;
            case 'post-forum':
                // Post to a Course forum
                return moodlectl_plugin_forum::post_forum($options['discussion-id'], $options['parent'], $options['message']);
                break;
            case 'list-discussions':
                // list forum discussions of a forum
                return moodlectl_plugin_forum::list_discussions($options['module-id']);
                break;
            case 'discussion-posts':
                // list discussion posts of a forum
                return moodlectl_plugin_forum::discussion_posts($options['discussion-id']);
                break;
            case 'list-forums':
                // list forums of a course
                return moodlectl_plugin_forum::list_forums($options['course-id']);
                break;
            default:
                return new Exception(get_string('missingaction', MOODLECTL_LANG, $action));
        }
    }

/**
* basic encapsulation of creating a forum instance - as would be done in modedit.php
* with forum_add_instance();
*
* @param int $courseid the id of the course to add forum to
* @param int $groupmode the groupmode to use eg. separate groups
* @param string $forum_name  name of the forum - title
* @param string $intro  summary descrition of the forum
* */

    static function create_forum($courseid, $groupmode, $forum_name, $intro, $options) {
        global $CFG;
        require_once($CFG->dirroot."/mod/forum/lib.php");
        require_once($CFG->dirroot."/lib/grouplib.php");
        if (! $course = get_record("course", "id", $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }

        $forum = new object();
    
        $forum->course = $courseid;
        $forum->cmidnumber = 1;
        $forum->name = $forum_name;
        $forum->intro = $intro;
        $forum->assessed = $options['assessed'];
        $forum->scale = $options['scale'];
        $forum->type = 'general';
        $forum->forcesubscribe = false;
        $forum->trackingtype = FORUM_TRACKING_OPTIONAL;
        if ($CFG->enablerssfeeds && isset($CFG->forum_enablerssfeeds) && $CFG->forum_enablerssfeeds) {
          $forum->rsstype = 2;
          $forum->rssarticles = 10;
        }
        $forum->groupmode = $groupmode;
        $forum->visible = '1';
        $forum->module = get_field('modules', 'id', 'name', 'forum');
        $forum->id = forum_add_instance($forum);
        $forum->instance = $forum->id;
        $forum->section = 0; // default to first level section
        $forum->coursemodule = add_course_module($forum);
        $sectionid = add_mod_to_section($forum);
        set_field("course_modules", "section", $sectionid, "id", $forum->coursemodule);
        set_coursemodule_visible($forum->coursemodule, $forum->visible);
        set_coursemodule_idnumber($forum->coursemodule, $forum->cmidnumber);
        rebuild_course_cache($forum->course);
        return $forum;
    }

/**
* basic encapsulation of creating a forum instance - as would be done in modedit.php
* with forum_add_instance();
*
* @param array $options the options for changing a forum
* @return array the amended forum
* */

    static function change_forum($options) {
        global $CFG;
        require_once($CFG->dirroot."/mod/forum/lib.php");
        if (! $cm = get_coursemodule_from_id('forum', $options['module-id'])) {
            return new Exception(get_string('forumbadmodule', MOODLECTL_LANG, $options['module-id']));
        }
        $forum = get_record('forum', 'id', $cm->instance);
        $forum->instance = $forum->id;
        $forum->cmidnumber = $options['module-id'];
        
        foreach ($options as $key => $value) {
            if ($value !== NULL) {
                $forum->$key = $value;
            }
        }
    
        if (forum_update_instance($forum)) {
            return $forum;
        }
        return false;
    }
    

/**
* basic encapsulation deleting a forum instance - as would be done in modedit.php
* with forum_add_instance();
*
* @param int $id the id of the forum to delete
* @return boolean success/failure of delete
* */
    static function delete_forum($cm_id) {
        global $CFG;
        require_once($CFG->dirroot."/mod/forum/lib.php");
        if (! $cm = get_coursemodule_from_id('forum', $cm_id)) {
            return new Exception(get_string('forumbadmodule', MOODLECTL_LANG, $cm_id));
        }
        forum_delete_instance($cm->instance);
        delete_course_module($cm->id);
        delete_mod_from_section($cm->id, "$cm->section");
        rebuild_course_cache($cm->course);
        if ($forum = get_record("forum", "id", $cm->instance)) {
            return new Exception(get_string('forumdeletefailed', MOODLECTL_LANG, $cm_id));
        }
        else {
            return true;
        }
    }
    
/**
* list forums for a given course
*
* @param int $courseid the id of the course the forums belong to
* @return  array list of forums for a given course
* */

    static function list_forums($courseid) {
        global $CFG;
        // make sure that the course allready exist
        if (!$course = get_record('course', 'id', $courseid)) {
            return new Exception(get_string('coursenotexists', MOODLECTL_LANG, $courseid));
        }
        if (! $forums = get_records('forum', 'course', $courseid)) {
            return new Exception(get_string('forumsnotfound', MOODLECTL_LANG));
        }
        foreach ($forums as $key => $forum) {
            $forum->timemodified_fmt = (0 == $forum->timemodified)  ? 'Never' : userdate($forum->timemodified);
            $forum->assesstimestart_fmt = (0 == $forum->assesstimestart)  ? 'Never' : userdate($forum->assesstimestart);
            $forum->assesstimefinish_fmt = (0 == $forum->assesstimefinish)  ? 'Never' : userdate($forum->assesstimefinish);
            $forum->url = $CFG->wwwroot.'/mod/forum/view.php?id='.$forum->id;
            $forums[$key] = (array)$forum;
        }
        return $forums;
        
    }
    
/**
* list forums for a given course
*
* @param int $cm_id the id of the course module for the forum
* @return  array list of forum discussions
* */

    static function list_discussions($cm_id) {
        global $CFG;
        if (! $cm = get_coursemodule_from_id('forum', $cm_id)) {
            return new Exception(get_string('forumbadmodule', MOODLECTL_LANG, $cm_id));
        }
        require_once($CFG->dirroot."/mod/forum/lib.php");
        $discussions = forum_get_discussions($cm);
//        forum_get_discussions()
//        forum_get_discussion_posts()
//        forum_get_discussion_posts();
        
        foreach ($discussions as $key => $discussion) {
            $discussion->timemodified_fmt = (0 == $discussion->timemodified)  ? 'Never' : userdate($discussion->timemodified);
            $discussion->url = $CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion;
            $discussions[$key] = (array)$discussion;
        }
        return $discussions;
        
    }
    
/**
* list forums for a given course
*
* @param int $did the discussion id
* @return  array list of forum postings
* */

    static function discussion_posts($did) {
        global $CFG;
        require_once($CFG->dirroot."/mod/forum/lib.php");
        if (! $posts = forum_get_all_discussion_posts($did, "created ASC")) {
            return new Exception(get_string('forumbaddiscussion', MOODLECTL_LANG, $did));
        }

        foreach ($posts as $key => $post) {
            $post->modified_fmt = (0 == $post->modified)  ? 'Never' : userdate($post->modified);
            $post->created_fmt = (0 == $post->created)  ? 'Never' : userdate($post->created);
            $post->url = $CFG->wwwroot.'/mod/forum/post.php?reply='.$key;
            $posts[$key] = (array)$post;
        }
        return $posts;
    }
    
 /**
* create a new forum discussion
*
* @param int $cm_id the id of the course module for the forum
* @param string $subject discussion subject
* @param string $message message to post to forum
* @return  array list of forum postings
* */

    static function forum_discussion($cm_id, $subject, $message) {
        global $CFG;
        if (! $cm = get_coursemodule_from_id('forum', $cm_id)) {
            return new Exception(get_string('forumbadmodule', MOODLECTL_LANG, $cm_id));
        }
        if (! $forum = get_record('forum', 'id', $cm->instance)) {
            return new Exception(get_string('forumnotfound', MOODLECTL_LANG));
        }
        require_once($CFG->dirroot."/mod/forum/lib.php");
        $discussion = new object();
        $discussion->course   = $forum->course;
        $discussion->forum    = $forum->id;
        $discussion->name     = $subject;
        $discussion->intro    = $message;
        $discussion->assessed = $forum->assessed;
        $discussion->format   = $forum->type;
        $discussion->mailnow  = false;
        $discussion->groupid  = -1;
        $discussion->id = forum_add_discussion($discussion, $discussion->intro);        
        add_to_log($cm->course, "forum", "add discussion",
                "discuss.php?d=$discussion->id", "$discussion->id", $cm->id);
        forum_post_subscription($discussion, $forum);
        return $discussion;        
    }
    
 /**
* create a forum post
*
* @param int $did the discussion id
* @param int $parent the parent id
* @param string $message message to post to forum
* @return  array list of forum postings
* */

    static function post_forum($did, $parent, $message) {
        global $CFG;
        if (! $fd = get_record('forum_discussions', 'id', $did)) {
            return new Exception(get_string('forumbaddiscussion', MOODLECTL_LANG, $did));
        }
        if (! $forum = get_record('forum', 'id', $fd->forum)) {
            return new Exception(get_string('forumnotfound', MOODLECTL_LANG));
        }
        require_once($CFG->dirroot."/mod/forum/lib.php");
        
        $post = new object();
        $post->parent = $parent;
        $post->discussion = $did;
        $post->subject = $fd->name;
        $post->totalscore = 0;
        $post->mailnow = 0;
        $post->format = 1;
        $post->message = $message;
        $post->forum = $forum->id;
        $message = '';
        $post->id = forum_add_new_post($post, $message);
        forum_post_subscription($post, $forum);
        return $post;
    }
}
?>