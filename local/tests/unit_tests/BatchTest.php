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

class BatchTest extends MoodlectlTestBase {
	
    // tests:
    //# JSON
    //cat batchjson.txt | ../moodlectl.php --batch --json
    //
    //# PHP
    //cat batchphp.txt | ../moodlectl.php --batch --php
    //
    //# YAML
    //cat batchyaml.txt | ../moodlectl.php --batch --yaml
      
    public function test1_BatchJSON() {
        $json = (array)json_decode(file_get_contents(dirname(__FILE__).'/../batch_json.txt'));
        $results = self::call_moodle('batch', $json, 'json');
        $this->assertEquals(count($results), 10);
        foreach ($results as $result) {
            $this->assertTrue($result['rc'], 'wiki-create failed: '.(isset($result['error']) ? $result['error']:""));
            $wiki = $result['result'];
            $this->assertTrue(array_key_exists('coursemodule', $wiki));
            $this->assertTrue($wiki['coursemodule'] >= 1);
            $this->assertRegExp('/The new wiki - json/', $wiki["name"]);
            $this->assertEquals($wiki["summary"], "this is overriding");
            $delete = self::call_moodle('delete-wiki', array('module-id' => $wiki['coursemodule']));
            $this->assertTrue($delete);
        }
    }
    
    public function test2_BatchYAML() {
        $yaml = (array)syck_load(file_get_contents(dirname(__FILE__).'/../batch_yaml.txt'));
        $results = self::call_moodle('batch', $yaml, 'yaml');
        $this->assertEquals(count($results), 10);
        foreach ($results as $result) {
            $this->assertTrue($result['rc'], 'wiki-create failed: '.(isset($result['error']) ? $result['error']:""));
            $wiki = $result['result'];
            $this->assertTrue(array_key_exists('coursemodule', $wiki));
            $this->assertTrue($wiki['coursemodule'] > 1);
            $this->assertRegExp('/The new wiki - yaml/', $wiki["name"]);
            $this->assertEquals($wiki["summary"], "Just a summary - longer text from yaml");
            $delete = self::call_moodle('delete-wiki', array('module-id' => $wiki['coursemodule']));
            $this->assertTrue($delete);
        }
    }
    
    public function test3_BatchPHP() {
        $php = (array)unserialize(file_get_contents(dirname(__FILE__).'/../batch_php.txt'));
        $results = self::call_moodle('batch', $php, 'php');
        $this->assertEquals(count($results), 10);
        foreach ($results as $result) {
            $this->assertTrue($result['rc'], 'wiki-create failed: '.(isset($result['error']) ? $result['error']:""));
            $wiki = $result['result'];
            $this->assertTrue(array_key_exists('coursemodule', $wiki));
            $this->assertTrue($wiki['coursemodule'] > 1);
            $this->assertRegExp('/The new wiki - php/', $wiki["name"]);
            $this->assertEquals($wiki["summary"], "this is overriding");
            $delete = self::call_moodle('delete-wiki', array('module-id' => $wiki['coursemodule']));
            $this->assertTrue($delete);
        }
    }
}
