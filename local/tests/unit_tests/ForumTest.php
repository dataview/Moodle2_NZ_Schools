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

class ForumTest extends MoodlectlTestBase {
	
    public function testForum1_create() {
        $forum = $this->createForum();
        $this->deleteForum($forum);
    }
    
    private function createForum() {
        $forum = self::call_moodle('create-forum', array('course-id' => 1, 'name' => 'forum test', 'intro' => 'Just a summary'), 'json');
        $this->assertTrue('Just a summary' == $forum['intro']);
        return $forum;
    }
    
    private function deleteForum($forum) {
        $result = self::call_moodle('delete-forum', array('module-id' => $forum['coursemodule']), 'json');
        $this->assertTrue($result == 1);
    }
    
    public function testForum2_list_forums() {
        $forum = $this->createForum();
        $result = self::call_moodle('list-forums', array('course-id' => 1), 'json');
        echo "Forums: ".count($result)."\n";
        $this->assertTrue(count($result) >= 1);
        $this->deleteForum($forum);
    }
    
    public function testForum3_create_discussion() {
        $forum = $this->createForum();
        $discussion = $this->createDiscussion($forum);
         $results = self::call_moodle('list-discussions', array('module-id' => $forum['coursemodule']), 'json');
        $this->assertTrue(count($results) >= 1);
        $this->deleteForum($forum);
    }
    
    private function createDiscussion($forum) {
        echo "module: ".$forum['coursemodule']."\n";
        $discussion = self::call_moodle('create-discussion', array('module-id' => $forum['coursemodule'], 'subject' => 'just another subject', 'message' => 'just another message'), 'json');
        echo "Discussion: ".$discussion['id']."\n";
        $this->assertTrue(isset($discussion['id']));
        return $discussion;
    }
    
    public function testForum4_post_forum() {
        $forum = $this->createForum();
        $discussion = $this->createDiscussion($forum);
        $post = self::call_moodle('post-forum', array('discussion-id' => $discussion['id'], 'message' => 'just another message'), 'json');
        $this->assertTrue(isset($post['id']));
        $this->deleteForum($forum);
    }
}
