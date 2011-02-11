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

class OptTest extends MoodlectlTestBase {

   // tests:
   // nothing
   // --help
   // --create-wiki --help
   // ../moodlectl.php --create-wiki --course-id=1 --name='The new wiki' --summary='Just a summary'
   // ../moodlectl.php --delete-wiki --module-id=<previous id>
   
    public function testBasic1_nothing() {
        $result = self::call_moodle('', false);
        $this->assertRegExp('/NO arguments supplied/', $result);
    }
   
    public function testBasic2_help() {
        $result = self::call_moodle('--help', false);
        $this->assertRegExp('/^Usage\: moodlectl \<action name\> \[arguments\]/', $result);
    }
   
    public function testBasic3_creatw_wiki_help() {
        $result = self::call_moodle('--help create-wiki', array());
        $this->assertRegExp('/Action: create-wiki/', $result);
        $this->assertRegExp('/Create a new Wiki, within a given course:/', $result);
    }
    
    public function testBasic4_create_delete_wiki() {
        $result = self::call_moodle('create-wiki', array('course-id' => 1, 'name' => 'The new wiki', 'summary' => 'Just a summary'));
        $coursemodule = split("\t", implode('', preg_grep('/coursemodule/', explode("\n", $result))));
        $this->assertRegExp('/coursemodule/', $result);
        $this->assertTrue($coursemodule[1] > 1);
        $result = self::call_moodle('delete-wiki', array('module-id' => $coursemodule[1]));
        $this->assertTrue($result);
    }
    
}
