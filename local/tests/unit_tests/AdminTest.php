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

class AdminTest extends MoodlectlTestBase {
	
    public function testConfig1_get() {
        $this->get_config_cl(false);
    }
   
    public function testConfig2_set() {
        $result = self::call_moodle('set-config', array('name' => 'moosex_declare', 'value' => 1));
        $this->assertRegExp('/moosex_declare/', $result);
        $config = explode("\t", $result);
        $this->assertTrue(1 == $config[1]);
        $this->get_config_cl(1);
    }
    
    public function testConfig3_unset() {
        $result = self::call_moodle('unset-config', array('name' => 'moosex_declare'));
        $this->assertRegExp('/moosex_declare/', $result);
        $config = explode("\t", $result);
        $value = false;
        if (isset($config[1])) {
            $value = $config[1];
        }
        $this->assertTrue(false == $value);
        $this->get_config_cl(false);
    }

    public function testConfig4_get() {
        $this->get_config_json(false);
    }
    
    public function testConfig5_set() {
        $result = self::call_moodle('set-config', array('name' => 'moosex_declare', 'value' => 1), 'json');
        $this->assertTrue(1 == $result['moosex_declare']);
        $this->get_config_json(1);
    }
    
    public function testConfig6_unset() {
        $result = self::call_moodle('unset-config', array('name' => 'moosex_declare'), 'json');
        
        $this->assertTrue(false == $result['moosex_declare']);
        $this->get_config_json(false);
    }

    public function testConfig7_read_json_nostdin() {
        $result = self::call_moodle('get-config', array('name' => 'moosex_declare'), 'json');
        $this->assertTrue(array_key_exists('moosex_declare', $result), 'config get results did not contain moosex_declare');
        $this->assertTrue($result['moosex_declare'] == false);
    }
    
    public function testAdmin8_files() {
        $result = self::call_moodle('upload-file', array('course-id' => 1, 'file' => dirname(__FILE__).'/../json.txt', 'destination' => ''), 'json');
        $this->assertTrue(array_key_exists('file', $result), 'did not return the file name');
        $result = self::call_moodle('directory-list', array('course-id' => 1, 'directory' => ''), 'json');
        $this->assertTrue(count($result) >= 1);
        // get my file
        $mine = false;
        foreach ($result as $file) {
            if (preg_match('/json.txt/', $file->file)) {
                $mine = $file;
            }
        }
        $this->assertTrue($mine !== false);
        $this->assertTrue($mine->isdir == 0);
        $result = self::call_moodle('delete-file', array('course-id' => 1, 'file' => '/json.txt'), 'json');
        $this->assertTrue($result == 1);
    }
    
    private function get_config_cl($test) {
        $result = self::call_moodle('get-config', array('name' => 'moosex_declare'));
        $this->assertRegExp('/moosex_declare/', $result);
        $config = explode("\t", $result);
        $value = false;
        if (isset($config[1])) {
            $value = $config[1];
        }
        $this->assertTrue($value == $test);
    }

    private function get_config_json($test) {
        $result = self::call_moodle('get-config', array('name' => 'moosex_declare'), 'json');
        $this->assertTrue(count($result) == 1);
        $this->assertTrue(array_key_exists('moosex_declare', $result), 'config get results did not contain moosex_declare');
        $this->assertTrue($result['moosex_declare'] == $test);
    }
}
