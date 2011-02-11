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

class SerialisedTest extends MoodlectlTestBase {
	
    // tests:
    //# JSON
    //cat json.txt | ../moodlectl.php --create-wiki --json --name='overriding: json'
    //
    //# PHP
    //cat php.txt | ../moodlectl.php --create-wiki --php --name='overriding: php'
    //
    //# YAML
    //cat yaml.txt | ../moodlectl.php --create-wiki --yaml --name='overriding: yaml'
    
    public function test1_JSON() {
        $json = (array)json_decode(file_get_contents(dirname(__FILE__).'/../json.txt'));
        $result = self::call_moodle('create-wiki', $json, 'json');
        $this->assertTrue(array_key_exists('coursemodule', $result));
        $this->assertTrue($result['coursemodule'] > 1);
        $this->assertEquals($result["name"], "The new wiki - json");
        $this->assertEquals($result["summary"], "this is overriding");
        $result = self::call_moodle('delete-wiki', array('module-id' => $result['coursemodule']));
        $this->assertTrue($result);
    }
    
    public function test2_YAML() {
        $yaml = (array)syck_load(file_get_contents(dirname(__FILE__).'/../yaml.txt'));
        $result = self::call_moodle('create-wiki', $yaml, 'yaml');
        $this->assertTrue(array_key_exists('coursemodule', $result));
        $this->assertTrue($result['coursemodule'] > 1);
        $this->assertEquals($result["name"], "The new wiki - yaml");
        $this->assertEquals($result["summary"], "Just a summary - longer text from yaml");
        $result = self::call_moodle('delete-wiki', array('module-id' => $result['coursemodule']));
        $this->assertTrue($result);
    }
    
    public function test3_PHP() {
        $php = (array)unserialize(file_get_contents(dirname(__FILE__).'/../php.txt'));
        $result = self::call_moodle('create-wiki', $php, 'php');
        $this->assertTrue(array_key_exists('coursemodule', $result));
        $this->assertTrue($result['coursemodule'] > 1);
        $this->assertEquals($result["name"], "The new wiki - php");
        $this->assertEquals($result["summary"], "this is overriding");
        $result = self::call_moodle('delete-wiki', array('module-id' => $result['coursemodule']));
        $this->assertTrue($result);
    }
}
?>