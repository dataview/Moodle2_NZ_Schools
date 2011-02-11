<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for lib/navigationlib.php
 *
 * @package   moodlecore
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (5)
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir . '/navigationlib.php');

class navigation_node_test extends UnitTestCase {
    protected $tree;
    public static $includecoverage = array('./lib/navigationlib.php');
    public static $excludecoverage = array();
    protected $fakeproperties = array(
        'text' => 'text',
        'shorttext' => 'A very silly extra long short text string, more than 25 characters',
        'key' => 'key',
        'type' => 'navigation_node::TYPE_COURSE',
        'action' => 'http://www.moodle.org/');
    protected $activeurl = null;
    protected $inactivenode = null;

    /**
     * @var navigation_node
     */
    public $node;

    public function setUp() {
        global $CFG, $PAGE;
        parent::setUp();

        $this->activeurl = $PAGE->url;
        navigation_node::override_active_url($this->activeurl);
        
        $this->inactiveurl = new moodle_url('http://www.moodle.com/');
        $this->fakeproperties['action'] = $this->inactiveurl;

        $this->node = new navigation_node('Test Node');
        $this->node->type = navigation_node::TYPE_SYSTEM;
        $demo1 = $this->node->add('demo1', $this->inactiveurl, navigation_node::TYPE_COURSE, null, 'demo1', new pix_icon('i/course', ''));
        $demo2 = $this->node->add('demo2', $this->inactiveurl, navigation_node::TYPE_COURSE, null, 'demo2', new pix_icon('i/course', ''));
        $demo3 = $this->node->add('demo3', $this->inactiveurl, navigation_node::TYPE_CATEGORY, null, 'demo3',new pix_icon('i/course', ''));
        $demo4 = $demo3->add('demo4', $this->inactiveurl,navigation_node::TYPE_COURSE,  null, 'demo4', new pix_icon('i/course', ''));
        $demo5 = $demo3->add('demo5', $this->activeurl, navigation_node::TYPE_COURSE, null, 'demo5',new pix_icon('i/course', ''));
        $demo5->add('activity1', null, navigation_node::TYPE_ACTIVITY, null, 'activity1')->make_active();
        $hiddendemo1 = $this->node->add('hiddendemo1', $this->inactiveurl, navigation_node::TYPE_CATEGORY, null, 'hiddendemo1', new pix_icon('i/course', ''));
        $hiddendemo1->hidden = true;
        $hiddendemo1->add('hiddendemo2', $this->inactiveurl, navigation_node::TYPE_COURSE, null, 'hiddendemo2', new pix_icon('i/course', ''))->helpbutton = 'Here is a help button';;
        $hiddendemo1->add('hiddendemo3', $this->inactiveurl, navigation_node::TYPE_COURSE,null, 'hiddendemo3', new pix_icon('i/course', ''))->display = false;
    }

    public function test___construct() {
        global $CFG;
        $node = new navigation_node($this->fakeproperties);
        $this->assertEqual($node->text, $this->fakeproperties['text']);
        $this->assertEqual($node->title, $this->fakeproperties['text']);
        $this->assertTrue(strpos($this->fakeproperties['shorttext'], substr($node->shorttext,0, -3))===0);
        $this->assertEqual($node->key, $this->fakeproperties['key']);
        $this->assertEqual($node->type, $this->fakeproperties['type']);
        $this->assertEqual($node->action, $this->fakeproperties['action']);
    }
    public function test_add() {
        global $CFG;
        // Add a node with all args set
        $node1 = $this->node->add('test_add_1','http://www.moodle.org/',navigation_node::TYPE_COURSE,'testadd1','key',new pix_icon('i/course', ''));
        // Add a node with the minimum args required
        $node2 = $this->node->add('test_add_2',null, navigation_node::TYPE_CUSTOM,'testadd2');
        $node3 = $this->node->add(str_repeat('moodle ', 15),str_repeat('moodle', 15));

        $this->assertIsA($node1, 'navigation_node');
        $this->assertIsA($node2, 'navigation_node');
        $this->assertIsA($node3, 'navigation_node');

        $this->assertReference($node1, $this->node->get('key'));
        $this->assertReference($node2, $this->node->get($node2->key));
        $this->assertReference($node2, $this->node->get($node2->key, $node2->type));
        $this->assertReference($node3, $this->node->get($node3->key, $node3->type));
    }

    public function test_add_class() {
        $node = $this->node->get('demo1');
        $this->assertIsA($node, 'navigation_node');
        if ($node !== false) {
            $node->add_class('myclass');
            $classes = $node->classes;
            $this->assertTrue(in_array('myclass', $classes));
        }
    }


    public function test_check_if_active() {
        // First test the string urls
        // demo1 -> action is http://www.moodle.org/, thus should be true
        $demo5 = $this->node->find('demo5', navigation_node::TYPE_COURSE);
        if ($this->assertIsA($demo5, 'navigation_node')) {
            $this->assertTrue($demo5->check_if_active());
        }

        // demo2 -> action is http://www.moodle.com/, thus should be false
        $demo2 = $this->node->get('demo2');
        if ($this->assertIsA($demo2, 'navigation_node')) {
            $this->assertFalse($demo2->check_if_active());
        }
    }

    public function test_contains_active_node() {
        // demo5, and activity1 were set to active during setup
        // Should be true as it contains all nodes
        $this->assertTrue($this->node->contains_active_node());
        // Should be true as demo5 is a child of demo3
        $this->assertTrue($this->node->get('demo3')->contains_active_node());
        // Obviously duff
        $this->assertFalse($this->node->get('demo1')->contains_active_node());
        // Should be true as demo5 contains activity1
        $this->assertTrue($this->node->get('demo3')->get('demo5')->contains_active_node());
        // Should be true activity1 is the active node
        $this->assertTrue($this->node->get('demo3')->get('demo5')->get('activity1')->contains_active_node());
        // Obviously duff
        $this->assertFalse($this->node->get('demo3')->get('demo4')->contains_active_node());
    }

    public function test_find_active_node() {
        $activenode1 = $this->node->find_active_node();
        $activenode2 = $this->node->get('demo1')->find_active_node();
        
        if ($this->assertIsA($activenode1, 'navigation_node')) {
            $this->assertReference($activenode1, $this->node->get('demo3')->get('demo5')->get('activity1'));
        }
        
        $this->assertNotA($activenode2, 'navigation_node');
    }

    public function test_find() {
        $node1 = $this->node->find('demo1', navigation_node::TYPE_COURSE);
        $node2 = $this->node->find('demo5', navigation_node::TYPE_COURSE);
        $node3 = $this->node->find('demo5', navigation_node::TYPE_CATEGORY);
        $node4 = $this->node->find('demo0', navigation_node::TYPE_COURSE);
        $this->assertIsA($node1, 'navigation_node');
        $this->assertIsA($node2, 'navigation_node');
        $this->assertNotA($node3, 'navigation_node');
        $this->assertNotA($node4, 'navigation_node');
    }

    public function test_find_expandable() {
        $expandable = array();
        $this->node->find_expandable($expandable);
        $this->assertEqual(count($expandable), 4);
        if (count($expandable) === 4) {
            $name = $expandable[0]['branchid'];
            $name .= $expandable[1]['branchid'];
            $name .= $expandable[2]['branchid'];
            $name .= $expandable[3]['branchid'];
            $this->assertEqual($name, 'demo1demo2demo4hiddendemo2');
        }
    }

    public function test_get() {
        $node1 = $this->node->get('demo1'); // Exists
        $node2 = $this->node->get('demo4'); // Doesn't exist for this node
        $node3 = $this->node->get('demo0'); // Doesn't exist at all
        $node4 = $this->node->get(false);   // Sometimes occurs in nature code
        $this->assertIsA($node1, 'navigation_node');
        $this->assertFalse($node2);
        $this->assertFalse($node3);
        $this->assertFalse($node4);
    }

    public function test_get_css_type() {
        $csstype1 = $this->node->get('demo3')->get_css_type();
        $csstype2 = $this->node->get('demo3')->get('demo5')->get_css_type();
        $this->node->get('demo3')->get('demo5')->type = 1000;
        $csstype3 = $this->node->get('demo3')->get('demo5')->get_css_type();
        $this->assertEqual($csstype1, 'type_category');
        $this->assertEqual($csstype2, 'type_course');
        $this->assertEqual($csstype3, 'type_unknown');
    }

    public function test_make_active() {
        global $CFG;
        $node1 = $this->node->add('active node 1', null, navigation_node::TYPE_CUSTOM, null, 'anode1');
        $node2 = $this->node->add('active node 2', new moodle_url($CFG->wwwroot), navigation_node::TYPE_COURSE, null, 'anode2');
        $node1->make_active();
        $this->node->get('anode2')->make_active();
        $this->assertTrue($node1->isactive);
        $this->assertTrue($this->node->get('anode2')->isactive);
    }
    public function test_remove() {
        $remove1 = $this->node->add('child to remove 1', null, navigation_node::TYPE_CUSTOM, null, 'remove1');
        $remove2 = $this->node->add('child to remove 2', null, navigation_node::TYPE_CUSTOM, null, 'remove2');
        $remove3 = $remove2->add('child to remove 3', null, navigation_node::TYPE_CUSTOM, null, 'remove3');

        $this->assertIsA($remove1, 'navigation_node');
        $this->assertIsA($remove2, 'navigation_node');
        $this->assertIsA($remove3, 'navigation_node');

        $this->assertIsA($this->node->get('remove1'), 'navigation_node');
        $this->assertIsA($this->node->get('remove2'), 'navigation_node');
        $this->assertIsA($remove2->get('remove3'), 'navigation_node');

        $this->assertTrue($remove1->remove());
        $this->assertTrue($this->node->get('remove2')->remove());
        $this->assertTrue($remove2->get('remove3')->remove());

        $this->assertFalse($this->node->get('remove1'));
        $this->assertFalse($this->node->get('remove2'));
    }
    public function test_remove_class() {
        $this->node->add_class('testclass');
        $this->assertTrue($this->node->remove_class('testclass'));
        $this->assertFalse(in_array('testclass', $this->node->classes));
    }
}

/**
 * This is a dummy object that allows us to call protected methods within the
 * global navigation class by prefixing the methods with `exposed_`
 */
class exposed_global_navigation extends global_navigation {
    protected $exposedkey = 'exposed_';
    public function __construct(moodle_page $page=null) {
        global $PAGE;
        if ($page === null) {
            $page = $PAGE;
        }
        parent::__construct($page);
        $this->cache = new navigation_cache('simpletest_nav');
    }
    public function __call($method, $arguments) {
        if (strpos($method,$this->exposedkey) !== false) {
            $method = substr($method, strlen($this->exposedkey));
        }
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }
        throw new coding_exception('You have attempted to access a method that does not exist for the given object '.$method, DEBUG_DEVELOPER);
    }
    public function set_initialised() {
        $this->initialised = true;
    }
}

class mock_initialise_global_navigation extends global_navigation {

    static $count = 1;

    public function load_for_category() {
        $this->add('load_for_category', null, null, null, 'initcall'.self::$count);
        self::$count++;
        return 0;
    }

    public function load_for_course() {
        $this->add('load_for_course', null, null, null, 'initcall'.self::$count);
        self::$count++;
        return 0;
    }

    public function load_for_activity() {
        $this->add('load_for_activity', null, null, null, 'initcall'.self::$count);
        self::$count++;
        return 0;
    }

    public function load_for_user() {
        $this->add('load_for_user', null, null, null, 'initcall'.self::$count);
        self::$count++;
        return 0;
    }
}

class global_navigation_test extends UnitTestCase {
    /**
     * @var global_navigation
     */
    public $node;
    protected $cache;
    protected $modinfo5 = 'O:6:"object":6:{s:8:"courseid";s:1:"5";s:6:"userid";s:1:"2";s:8:"sections";a:1:{i:0;a:1:{i:0;s:3:"288";}}s:3:"cms";a:1:{i:288;O:6:"object":17:{s:2:"id";s:3:"288";s:8:"instance";s:2:"19";s:6:"course";s:1:"5";s:7:"modname";s:5:"forum";s:4:"name";s:10:"News forum";s:7:"visible";s:1:"1";s:10:"sectionnum";s:1:"0";s:9:"groupmode";s:1:"0";s:10:"groupingid";s:1:"0";s:16:"groupmembersonly";s:1:"0";s:6:"indent";s:1:"0";s:10:"completion";s:1:"0";s:5:"extra";s:0:"";s:4:"icon";s:0:"";s:11:"uservisible";b:1;s:9:"modplural";s:6:"Forums";s:9:"available";b:1;}}s:9:"instances";a:1:{s:5:"forum";a:1:{i:19;R:8;}}s:6:"groups";N;}';
    protected $coursesections5 = 'a:5:{i:0;O:8:"stdClass":6:{s:7:"section";s:1:"0";s:2:"id";s:2:"14";s:6:"course";s:1:"5";s:7:"summary";N;s:8:"sequence";s:3:"288";s:7:"visible";s:1:"1";}i:1;O:8:"stdClass":6:{s:7:"section";s:1:"1";s:2:"id";s:2:"97";s:6:"course";s:1:"5";s:7:"summary";s:0:"";s:8:"sequence";N;s:7:"visible";s:1:"1";}i:2;O:8:"stdClass":6:{s:7:"section";s:1:"2";s:2:"id";s:2:"98";s:6:"course";s:1:"5";s:7:"summary";s:0:"";s:8:"sequence";N;s:7:"visible";s:1:"1";}i:3;O:8:"stdClass":6:{s:7:"section";s:1:"3";s:2:"id";s:2:"99";s:6:"course";s:1:"5";s:7:"summary";s:0:"";s:8:"sequence";N;s:7:"visible";s:1:"1";}i:4;O:8:"stdClass":6:{s:7:"section";s:1:"4";s:2:"id";s:3:"100";s:6:"course";s:1:"5";s:7:"summary";s:0:"";s:8:"sequence";N;s:7:"visible";s:1:"1";}}';
    public static $includecoverage = array('./lib/navigationlib.php');
    public static $excludecoverage = array();

    public function setUp() {
        $this->cache = new navigation_cache('simpletest_nav');
        $this->node = new exposed_global_navigation();
        // Create an initial tree structure to work with
        $cat1 = $this->node->add('category 1', null, navigation_node::TYPE_CATEGORY, null, 'cat1');
        $cat2 = $this->node->add('category 2', null, navigation_node::TYPE_CATEGORY, null, 'cat2');
        $cat3 = $this->node->add('category 3', null, navigation_node::TYPE_CATEGORY, null, 'cat3');
        $sub1 = $cat2->add('sub category 1', null, navigation_node::TYPE_CATEGORY, null, 'sub1');
        $sub2 = $cat2->add('sub category 2', null, navigation_node::TYPE_CATEGORY, null, 'sub2');
        $sub3 = $cat2->add('sub category 3', null, navigation_node::TYPE_CATEGORY, null, 'sub3');
        $course1 = $sub2->add('course 1', null, navigation_node::TYPE_COURSE, null, 'course1');
        $course2 = $sub2->add('course 2', null, navigation_node::TYPE_COURSE, null, 'course2');
        $course3 = $sub2->add('course 3', null, navigation_node::TYPE_COURSE, null, 'course3');
        $section1 = $course2->add('section 1', null, navigation_node::TYPE_COURSE, null, 'sec1');
        $section2 = $course2->add('section 2', null, navigation_node::TYPE_COURSE, null, 'sec2');
        $section3 = $course2->add('section 3', null, navigation_node::TYPE_COURSE, null, 'sec3');
        $act1 = $section2->add('activity 1', null, navigation_node::TYPE_ACTIVITY, null, 'act1');
        $act2 = $section2->add('activity 2', null, navigation_node::TYPE_ACTIVITY, null, 'act2');
        $act3 = $section2->add('activity 3', null, navigation_node::TYPE_ACTIVITY, null, 'act3');
        $res1 = $section2->add('resource 1', null, navigation_node::TYPE_RESOURCE, null, 'res1');
        $res2 = $section2->add('resource 2', null, navigation_node::TYPE_RESOURCE, null, 'res2');
        $res3 = $section2->add('resource 3', null, navigation_node::TYPE_RESOURCE, null, 'res3');

        $this->cache->clear();
        $this->cache->modinfo5 = unserialize($this->modinfo5);
        $this->cache->coursesections5 = unserialize($this->coursesections5);
        $this->cache->canviewhiddenactivities = true;
        $this->cache->canviewhiddensections = true;
        $this->cache->canviewhiddencourses = true;
        $sub2->add('Test Course 5', new moodle_url('http://moodle.org'),navigation_node::TYPE_COURSE,null,'5');
    }
    public function test_load_generic_course_sections() {
        $coursenode = $this->node->find('5', navigation_node::TYPE_COURSE);
        $course = new stdClass;
        $course->id = '5';
        $course->numsections = 10;
        $course->modinfo = $this->modinfo5;
        $this->node->load_generic_course_sections($course, $coursenode, 'topic', 'topic');
        $this->assertEqual($coursenode->children->count(),1);
    }
    public function test_format_display_course_content() {
        $this->assertTrue($this->node->exposed_format_display_course_content('topic'));
        $this->assertFalse($this->node->exposed_format_display_course_content('scorm'));
        $this->assertTrue($this->node->exposed_format_display_course_content('dummy'));
    }
    public function test_load_section_activities() {
        $node = $this->node->find('5', navigation_node::TYPE_COURSE);
        $course = new stdClass;
        $course->id = '5';
        $course->numsections = 10;
        $section = $node->add('Test Section 1', null, navigation_node::TYPE_SECTION, null, $this->cache->coursesections5[1]->id);
        $modinfo = $this->cache->modinfo5;
        $modinfo->sections[1] = array(289, 290);
        $modinfo->cms[289] = clone($modinfo->cms[288]);
        $modinfo->cms[289]->id = 289;
        $modinfo->cms[289]->sectionnum = 1;
        $modinfo->cms[290]->modname = 'forum';
        $modinfo->cms[289]->instance = 20;
        $modinfo->cms[290] = clone($modinfo->cms[288]);
        $modinfo->cms[290]->id = 290;
        $modinfo->cms[290]->modname = 'resource';
        $modinfo->cms[290]->sectionnum = 1;
        $modinfo->cms[290]->instance = 21;
        $modinfo->instances['forum'][20] = clone($modinfo->instances['forum'][19]);
        $modinfo->instances['forum'][20]->id = 20;
        $modinfo->instances['resource'] = array();
        $modinfo->instances['resource'][21] = clone($modinfo->instances['forum'][19]);
        $modinfo->instances['resource'][21]->id = 21;
        $this->cache->modinfo5 = $modinfo;
        $course->modinfo = serialize($modinfo);
        $activities = $this->node->exposed_load_section_activities($section, 1, $modinfo);
        foreach ($activities as $activity) {
            if ($this->assertIsA($activity, 'navigation_node')) {
                $this->assertEqual($activity->type, navigation_node::TYPE_ACTIVITY);
                $this->assertReference($activity, $section->get($activity->key));
            }
        }
    }
    public function test_module_extends_navigation() {
        $this->cache->test1_extends_navigation = true;
        $this->cache->test2_extends_navigation = false;
        $this->assertTrue($this->node->exposed_module_extends_navigation('data'));
        $this->assertTrue($this->node->exposed_module_extends_navigation('test1'));
        $this->assertFalse($this->node->exposed_module_extends_navigation('test2'));
        $this->assertFalse($this->node->exposed_module_extends_navigation('test3'));
    }
}

/**
 * This is a dummy object that allows us to call protected methods within the
 * global navigation class by prefixing the methods with `exposed_`
 */
class exposed_navbar extends navbar {
    protected $exposedkey = 'exposed_';
    public function __construct(moodle_page $page) {
        parent::__construct($page);
        $this->cache = new navigation_cache('simpletest_nav');
    }
    function __call($method, $arguments) {
        if (strpos($method,$this->exposedkey) !== false) {
            $method = substr($method, strlen($this->exposedkey));
        }
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }
        throw new coding_exception('You have attempted to access a method that does not exist for the given object '.$method, DEBUG_DEVELOPER);
    }
}

class navigation_exposed_moodle_page extends moodle_page {
    public function set_navigation(navigation_node $node) {
        $this->_navigation = $node;
    }
}

class navbar_test extends UnitTestCase {
    protected $node;
    protected $oldnav;

    public static $includecoverage = array('./lib/navigationlib.php');
    public static $excludecoverage = array();

    public function setUp() {
        global $PAGE;

        $temptree = new global_navigation_test();
        $temptree->setUp();
        $temptree->node->find('course2', navigation_node::TYPE_COURSE)->make_active();

        $page = new navigation_exposed_moodle_page();
        $page->set_url($PAGE->url);
        $page->set_context($PAGE->context);

        $navigation = new exposed_global_navigation($page);
        $navigation->children = $temptree->node->children;
        $navigation->set_initialised();
        $page->set_navigation($navigation);

        $this->cache = new navigation_cache('simpletest_nav');
        $this->node = new exposed_navbar($page);
    }
    public function test_add() {
        // Add a node with all args set
        $this->node->add('test_add_1','http://www.moodle.org/',navigation_node::TYPE_COURSE,'testadd1','testadd1',new pix_icon('i/course', ''));
        // Add a node with the minimum args required
        $this->node->add('test_add_2','http://www.moodle.org/',navigation_node::TYPE_COURSE,'testadd2','testadd2',new pix_icon('i/course', ''));
        $this->assertIsA($this->node->get('testadd1'), 'navigation_node');
        $this->assertIsA($this->node->get('testadd2'), 'navigation_node');
    }
    public function test_has_items() {
        $this->assertTrue($this->node->has_items());
    }
}

class navigation_cache_test extends UnitTestCase {
    protected $cache;

    public static $includecoverage = array('./lib/navigationlib.php');
    public static $excludecoverage = array();

    public function setUp() {
        $this->cache = new navigation_cache('simpletest_nav');
        $this->cache->anysetvariable = true;
    }
    public function test___get() {
        $this->assertTrue($this->cache->anysetvariable);
        $this->assertEqual($this->cache->notasetvariable, null);
    }
    public function test___set() {
        $this->cache->myname = 'Sam Hemelryk';
        $this->assertTrue($this->cache->cached('myname'));
        $this->assertEqual($this->cache->myname, 'Sam Hemelryk');
    }
    public function test_cached() {
        $this->assertTrue($this->cache->cached('anysetvariable'));
        $this->assertFalse($this->cache->cached('notasetvariable'));
    }
    public function test_clear() {
        $cache = clone($this->cache);
        $this->assertTrue($cache->cached('anysetvariable'));
        $cache->clear();
        $this->assertFalse($cache->cached('anysetvariable'));
    }
    public function test_set() {
        $this->cache->set('software', 'Moodle');
        $this->assertTrue($this->cache->cached('software'));
        $this->assertEqual($this->cache->software, 'Moodle');
    }
}

/**
 * This is a dummy object that allows us to call protected methods within the
 * global navigation class by prefixing the methods with `exposed_`
 */
class exposed_settings_navigation extends settings_navigation {
    protected $exposedkey = 'exposed_';
    function __construct() {
        global $PAGE;
        parent::__construct($PAGE);
        $this->cache = new navigation_cache('simpletest_nav');
    }
    function __call($method, $arguments) {
        if (strpos($method,$this->exposedkey) !== false) {
            $method = substr($method, strlen($this->exposedkey));
        }
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }
        throw new coding_exception('You have attempted to access a method that does not exist for the given object '.$method, DEBUG_DEVELOPER);
    }
}

class settings_navigation_test extends UnitTestCase {
    protected $node;
    protected $cache;

    public static $includecoverage = array('./lib/navigationlib.php');
    public static $excludecoverage = array();

    public function setUp() {
        global $PAGE;
        $this->cache = new navigation_cache('simpletest_nav');
        $this->node = new exposed_settings_navigation();
    }
    public function test___construct() {
        $this->node = new exposed_settings_navigation();
    }
    public function test___initialise() {
        $this->node->initialise();
        $this->assertEqual($this->node->id, 'settingsnav');
    }
    public function test_in_alternative_role() {
        $this->assertFalse($this->node->exposed_in_alternative_role());
    }
}
