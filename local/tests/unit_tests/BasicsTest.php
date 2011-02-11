<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
global $BATCH;
$BATCH = 25;

require_once './MoodlectlTestBase.class.php';

class BasicsTest extends MoodlectlTestBase {

    public function testBasics1_basic_opts() {
        $result = self::call_moodle('test-basic', array('teststring' => 1));
        $this->assertRegExp('/teststring/', $result);
        $teststring = split("\t", implode('', preg_grep('/teststring/', explode("\n", $result))));
        $this->assertTrue($teststring[1] == 1);
        $result = self::call_moodle('test-basic', array('teststring' => 'the quick brown fox'));
        $this->assertRegExp('/teststring/', $result);
        $teststring = split("\t", implode('', preg_grep('/teststring/', explode("\n", $result))));
        $this->assertTrue($teststring[1] == 'the quick brown fox');
        $result = self::call_moodle('test-basic', array('testint' => 1));
        $this->assertRegExp('/testint/', $result);
        $testint = split("\t", implode('', preg_grep('/testint/', explode("\n", $result))));
        $this->assertTrue($testint[1] == 1);
        $result = self::call_moodle('test-basic', array('testint' => 'x'));
        $this->assertRegExp('/testint/', $result);
        $testint = split("\t", implode('', preg_grep('/testint/', explode("\n", $result))));
        $this->assertFalse($testint[1] == 1);
        $result = self::call_moodle('test-basic', array('testint' => '1'));
        $this->assertRegExp('/testint/', $result);
        $testint = split("\t", implode('', preg_grep('/testint/', explode("\n", $result))));
        $this->assertTrue($testint[1] == 1);
        $result = self::call_moodle('test-basic', array('testboolean' => false));
        $this->assertRegExp('/testboolean/', $result);
        $testboolean = split("\t", implode('', preg_grep('/testboolean/', explode("\n", $result))));
        $this->assertTrue(1 == $testboolean[1]);
        $result = self::call_moodle('test-basic', array());
        $this->assertRegExp('/testboolean/', $result);
        $testboolean = split("\t", implode('', preg_grep('/testboolean/', explode("\n", $result))));
        $this->assertFalse(isset($testboolean[1]));
        $result = self::call_moodle('test-simple-fail', array());
        $this->assertTrue($result == 1);
        $result = self::call_moodle('test-simple-fail', array('fail' => false));
        $this->assertTrue($result == 0);
    }

    public function testBasics2_basic_in_formats() {
        $this->check_basics_by_format('json');
        $this->check_basics_by_format('yaml');
        $this->check_basics_by_format('php');
    }

    protected function check_basics_by_format($format) {
        $result = self::call_moodle('test-basic', array('teststring' => 1), $format);
        $this->assertTrue($result['teststring'] == 1);
        $result = self::call_moodle('test-basic', array('teststring' => 'the quick brown fox'), $format);
        $this->assertTrue($result['teststring'] == 'the quick brown fox');
        $result = self::call_moodle('test-basic', array('testint' => 1), $format);
        $this->assertTrue($result['testint'] == 1);
        $result = self::call_moodle('test-basic', array('testint' => 'x'), $format);
        $this->assertFalse($result['testint'] == 1);
        $result = self::call_moodle('test-basic', array('testint' => '1'), $format);
        $this->assertTrue($result['testint'] == 1);
        $result = self::call_moodle('test-basic', array('testboolean' => true), $format);
        $this->assertTrue($result['testboolean'] == true);
        $this->assertTrue($result['testboolean'] == 1);
        $result = self::call_moodle('test-basic', array(), $format);
        $this->assertTrue($result['testboolean'] == false);
        $this->assertTrue($result['testboolean'] == 0);
        $result = self::call_moodle('test-simple-fail', array(), $format);  // pass fale for a boolean
        $this->assertTrue($result == 1);
        $this->assertFalse(self::last_error());
        $result = self::call_moodle('test-simple-fail', array('fail' => true), $format);
        $this->assertTrue($result == 0);
        $this->assertArrayHasKey('message', self::last_error());
        $error = self::last_error();
        $this->assertTrue('Executed action failed' == $error['message']);
    }

    public function testBasics3_required_opts() {
        $result = self::call_moodle('test-required', array());
        $this->assertRegExp('/Missing required argument: teststring/', $result);
        $result = self::call_moodle('test-required', array('teststring' => 1));
        $this->assertRegExp('/Missing required argument: testint/', $result);
        $result = self::call_moodle('test-required', array('teststring' => 'x', 'testint' => 2));
        $this->assertRegExp('/Missing required argument: testboolean/', $result);
        $result = self::call_moodle('test-required', array('teststring' => 'x', 'testint' => 2, 'testboolean' => false));
        $teststring = split("\t", implode('', preg_grep('/teststring/', explode("\n", $result))));
        $this->assertTrue($teststring[1] == 'x');
        $testint = split("\t", implode('', preg_grep('/testint/', explode("\n", $result))));
        $this->assertTrue($testint[1] == 2);
        $testboolean = split("\t", implode('', preg_grep('/testboolean/', explode("\n", $result))));
        $this->assertTrue(1 == $testboolean[1]);
    }

    public function testBasics4_defaults() {
        $result = self::call_moodle('test-basic', array(), 'json');
        $this->assertTrue($result['teststring'] == 'string');
        $this->assertTrue($result['testint'] == 5);
        $this->assertTrue($result['testboolean'] == false);
        $result = self::call_moodle('test-basic', array(), 'yaml');
        $this->assertTrue($result['teststring'] == 'string');
        $this->assertTrue($result['testint'] == 5);
        $this->assertTrue($result['testboolean'] == false);
        $result = self::call_moodle('test-basic', array(), 'php');
        $this->assertTrue($result['teststring'] == 'string');
        $this->assertTrue($result['testint'] == 5);
        $this->assertTrue($result['testboolean'] == false);
    }

    public function testBasics5_batch_no_errors() {
        $this->basic_batch_no_errors('json');
        $this->basic_batch_no_errors('yaml');
        $this->basic_batch_no_errors('php');
    }

    protected function basic_batch_no_errors($format) {
        $this->assertTrue(self::add_to_batch('test-basic', array()));
        $this->assertTrue(self::add_to_batch('test-basic', array('teststring' => 'the quick brown fox')));
        $this->assertTrue(self::add_to_batch('test-required', array('teststring' => 'x', 'testint' => 2, 'testboolean' => true)));
        $result = self::process_batch($format);
        $this->assertTrue(count($result) == 3);
        $this->assertArrayHasKey('rc', $first = array_shift($result));
        $this->assertTrue($first['rc'] == true);
        $first = $first['result'];
        $this->assertTrue($first['teststring'] == 'string');
        $this->assertTrue($first['testint'] == 5);
        $this->assertTrue($first['testboolean'] == false);
        $this->assertArrayHasKey('rc', $second = array_shift($result));
        $this->assertTrue($second['rc'] == true);
        $second = $second['result'];
        $this->assertTrue($second['teststring'] == 'the quick brown fox');
        $this->assertTrue($second['testint'] == 5);
        $this->assertTrue($second['testboolean'] == false);
        $this->assertArrayHasKey('rc', $third = array_shift($result));
        $this->assertTrue($third['rc'] == true);
        $third = $third['result'];
        $this->assertTrue($third['teststring'] == 'x');
        $this->assertTrue($third['testint'] == 2);
        $this->assertTrue($third['testboolean'] == true);
        $this->assertTrue(count($result) == 0);
    }

    public function testBasics6_batch_simple_errors() {
        $this->basic_batch_simple_errors('json');
        $this->basic_batch_simple_errors('yaml');
        $this->basic_batch_simple_errors('php');
    }

    protected function basic_batch_simple_errors($format) {
        $this->assertTrue(self::add_to_batch('test-required', array('teststring' => 'x', 'testint' => 2)));
        $result = self::process_batch($format);
        $this->assertTrue(count($result) == 1);
        $this->assertArrayHasKey('rc', $result = array_shift($result));
        $this->assertTrue($result['rc'] == false);
        $this->assertRegExp('/Missing required argument: testboolean/', $result['error']);
    }

    public function testBasics7_batch_complex_errors() {
        $this->basic_batch_complex_errors('json');
        $this->basic_batch_complex_errors('yaml');
        $this->basic_batch_complex_errors('php');
    }

    protected function basic_batch_complex_errors($format) {
        $this->assertTrue(self::add_to_batch('test-basic', array()));
        $this->assertTrue(self::add_to_batch('test-basic', array('teststring' => 'the quick brown fox')));
        $this->assertTrue(self::add_to_batch('test-required', array('teststring' => 'x', 'testint' => 2))); // the error
        $this->assertTrue(self::add_to_batch('test-required', array('teststring' => 'x', 'testint' => 2, 'testboolean' => true)));
        $result = self::process_batch($format);
        $this->assertTrue(count($result) == 3);  // expect 1 less than we sent
        $this->assertArrayHasKey('rc', $first = array_shift($result));
        $this->assertTrue($first['rc'] == true);
        $first = $first['result'];
        $this->assertTrue($first['teststring'] == 'string');
        $this->assertTrue($first['testint'] == 5);
        $this->assertTrue($first['testboolean'] == false);
        $this->assertArrayHasKey('rc', $second = array_shift($result));
        $this->assertTrue($second['rc'] == true);
        $second = $second['result'];
        $this->assertTrue($second['teststring'] == 'the quick brown fox');
        $this->assertTrue($second['testint'] == 5);
        $this->assertTrue($second['testboolean'] == false);
        $this->assertTrue(count($result) == 1);
        $this->assertArrayHasKey('rc', $result = array_shift($result));
        $this->assertTrue($result['rc'] == false);
        $this->assertRegExp('/Missing required argument: testboolean/', $result['error']);
    }

    public function testBasics8_batch_volume() {
        $this->basic_batch_volume('json');
        $this->basic_batch_volume('yaml');
        $this->basic_batch_volume('php');
     }

    public function basic_batch_volume($format) {
        global $BATCH;
        $time_start = microtime(true);
        for ($i=0; $i < $BATCH; $i++) {
            $this->assertTrue(self::add_to_batch('test-required', array('teststring' => 'x', 'testint' => 2, 'testboolean' => true)));
        }
        $result = self::process_batch($format);
        $this->assertTrue(count($result) == $BATCH);
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo "\nRun time for batch size ($BATCH) iterations for format: $format: $time\n";
    }

    public function testBasics9_volume() {
        $this->basic_volume('json');
        $this->basic_volume('yaml');
        $this->basic_volume('php');
        $this->basic_volume(false);
    }

    public function basic_volume($format) {
        global $BATCH, $MOODLECTL_RC;
        $time_start = microtime(true);
        for ($i=0; $i < $BATCH; $i++) {
            if ($format) {
                $result = self::call_moodle('test-required', array('teststring' => 'x', 'testint' => 2, 'testboolean' => true), $format);
            }
            else {
                $result = self::call_moodle('test-required --testboolean ', array('teststring' => 'x', 'testint' => 2), $format);
            }
            if (!$MOODLECTL_RC) {
                echo $result;
            }
            $this->assertTrue($MOODLECTL_RC);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if (!$format) {
            $format = 'opts';
        }
        echo "\nRun time for volume testing size ($BATCH) iterations for format: $format: $time\n";
    }


}
?>