<?php
/**
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GPLv3+
 * @package local
 *
 */

require_once './MoodlectlTestBase.class.php';

class UserTest extends MoodlectlTestBase
{
    public function testUser1_show_user()
    {
        $this->get_user_cl(1);
    }

    public function testUser2_create_delete_user()
    {
        $user = self::call_moodle('create-user', array('username' => 'myuser',
                                                       'password' => 'password1',
                                                       'emailaddress'  => 'user@example.com',
                                                       'firstname' => 'Test',
                                                       'lastname' => 'User',
                                                       'city' => 'Hamiltron',
                                                       'country' => 'NZ',
                                                       'idnumber' => 'TEST01',
                                                       ), 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');
        $this->get_user_cl($user['id']);
        $this->get_user_by_idnumber_cl($user['idnumber']);
        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'User not deleted: '.$user['id']);
    }

    public function testUser3_list_all_users()
    {
        global $MOODLECTL_RC;
        $users = self::call_moodle('list-users', array(), 'php');
        $this->assertTrue($MOODLECTL_RC, 'Users should be found - there is always 1');
        $this->assertTrue(count($users) > 0);
        foreach ($users as $user) {
            $this->assertTrue(array_key_exists('username', $user), 'Username not found');
        }
    }

    public function testUser4_create_delete_user_full()
    {
        $data = array('username' => 'myuser',
                      'password' => 'password1',
                      'emailaddress'  => 'user@example.com',
                      'firstname' => 'Test',
                      'lastname' => 'User',
                      'city' => 'Hamiltron',
                      'country' => 'NZ',
                      'icq' => '12345',
                      'skype' => 'skypeid',
                      'yahoo' => 'yahooid',
                      'aim' => 'aimid',
                      'msn' => 'msnid',
                      'phone1' => '555 1234',
                      'phone2' => '+64 9 12345',
                      'institution' => 'Waikato University',
                      'department' => 'Computer Science',
                      'address' => '12 Main Street',
                      'url' => 'http://www.example.com',
                      'idnumber' => 'TEST0034',
                      'auth' => 'manual',
                      'lang' => 'is_utf8',
                      'theme' => 'roundcorners',
                      'description' => 'Majic afsatcom AGT. AMME halcon benelux TWA PGP doctrine Juiliett Class Submarine government constitution national information infrastructure keyhole Semtex Consul',
                      'mailformat' => 1,
                      'maildigest' => 1,
                      'maildisplay' => 1,
                      'emailstop' => true,
                      'htmleditor' => false,
                      'ajax' => true,
                      'autosubscribe' => false,
                      'trackforums' => false,
                      'screenreader' => true,
                      );

        $user = self::call_moodle('create-user', $data, 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');

        $userdata = $this->get_user_cl($user['id']);
        foreach ($data as $key => $value) {
            if ('password' == $key) {
                continue;
            }
            elseif ('emailaddress' == $key) {
                $this->assertTrue($data[$key] == $userdata['email']);
            }
            else {
                $this->assertTrue($data[$key] == $userdata[$key], "Failed while checking key=$key: $data[$key] != $userdata[$key]");
            }
        }

        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'User not deleted: '.$user['id']);
    }

    public function testUser5_create_change_delete_user_full()
    {
        $data1 = array('username' => 'myuser',
                      'password' => 'password1',
                      'emailaddress'  => 'user@example.com',
                      'firstname' => 'Test',
                      'lastname' => 'User',
                      'city' => 'Hamiltron',
                      'country' => 'NZ',
                      'icq' => '12345',
                      'skype' => 'skypeid',
                      'yahoo' => 'yahooid',
                      'aim' => 'aimid',
                      'msn' => 'msnid',
                      'phone1' => '555 1234',
                      'phone2' => '+64 9 12345',
                      'institution' => 'Waikato University',
                      'department' => 'Computer Science',
                      'address' => '12 Main Street',
                      'url' => 'http://www.example.com',
                      'idnumber' => 'TEST0034',
                      'auth' => 'manual',
                      'lang' => 'is_utf8',
                      'theme' => 'roundcorners',
                      'description' => 'Majic afsatcom AGT. AMME halcon benelux TWA PGP doctrine Juiliett Class Submarine government constitution national information infrastructure keyhole Semtex Consul',
                      'mailformat' => 1,
                      'maildigest' => 1,
                      'maildisplay' => 1,
                      'emailstop' => true,
                      'htmleditor' => false,
                      'ajax' => true,
                      'autosubscribe' => false,
                      'trackforums' => false,
                      'screenreader' => true,
                      );

        $user = self::call_moodle('create-user', $data1, 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');

        $userdata1 = $this->get_user_cl($user['id']);
        foreach ($data1 as $key => $value) {
            if ('password' == $key) {
                continue;
            }
            elseif ('emailaddress' == $key) {
                $this->assertTrue($data1[$key] == $userdata1['email']);
            }
            else {
                $this->assertTrue($data1[$key] == $userdata1[$key], "Failed while checking key=$key: $data1[$key] != $userdata1[$key]");
            }
        }

        $data2 = array('userid' => $user['id'],
                       'username' => 'myuser2',
                       'password' => 'password2',
                       'emailaddress'  => 'user2@example.com',
                       'firstname' => 'Test2',
                       'lastname' => 'User2',
                       'city' => 'Hamiltron2',
                       'country' => 'CA',
                       'icq' => '123452',
                       'skype' => 'skypeid2',
                       'yahoo' => 'yahooid2',
                       'aim' => 'aimid2',
                       'msn' => 'msnid2',
                       'phone1' => '555 12342',
                       'phone2' => '+64 9 123452',
                       'institution' => 'Waikato University2',
                       'department' => 'Computer Science2',
                       'address' => '12 Main Street2',
                       'url' => 'http://www.example.com/2',
                       'idnumber' => 'TEST00342',
                       'auth' => 'manual',
                       'lang' => 'en_utf8',
                       'theme' => 'roundcorners2',
                       'description' => 'Majic afsatcom AGT. AMME halcon benelux TWA PGP doctrine Juiliett Class Submarine government constitution national information infrastructure keyhole Semtex Consul2',
                       'mailformat' => 0,
                       'maildigest' => 0,
                       'maildisplay' => 0,
                       'emailstop' => false,
                       'htmleditor' => true,
                       'ajax' => false,
                       'autosubscribe' => true,
                       'trackforums' => true,
                       'screenreader' => false,
                       );

        $user = self::call_moodle('change-user', $data2, 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not changed');

        unset($data2['userid']);
        $userdata2 = $this->get_user_cl($user['id']);
        foreach ($data2 as $key => $value) {
            if ('password' == $key) {
                continue;
            }
            elseif ('emailaddress' == $key) {
                $this->assertTrue($data2[$key] == $userdata2['email']);
                $this->assertFalse($data1[$key] == $userdata2['email']);
            }
            elseif ('auth' == $key) {
                $this->assertTrue($data2[$key] == $userdata2[$key]);
                // This field wasn't modified
            }
            else {
                $this->assertTrue($data2[$key] == $userdata2[$key], "Failed while checking key=$key: $data2[$key] != $userdata2[$key]");
                $this->assertFalse($data1[$key] == $userdata2[$key], "Failed while checking key=$key: $data1[$key] == $userdata2[$key]");
            }
        }

        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'User not deleted: '.$user['id']);
    }

    public function testUser6_create_nochange_delete_user_full()
    {
        $data1 = array('username' => 'myuser',
                       'password' => 'password1',
                       'emailaddress'  => 'user@example.com',
                       'firstname' => 'Test',
                       'lastname' => 'User',
                       'city' => 'Hamiltron',
                       'country' => 'NZ',
                       'icq' => '12345',
                       'skype' => 'skypeid',
                       'yahoo' => 'yahooid',
                       'aim' => 'aimid',
                       'msn' => 'msnid',
                       'phone1' => '555 1234',
                       'phone2' => '+64 9 12345',
                       'institution' => 'Waikato University',
                       'department' => 'Computer Science',
                       'address' => '12 Main Street',
                       'url' => 'http://www.example.com',
                       'idnumber' => 'TEST0034',
                       'auth' => 'manual',
                       'lang' => 'is_utf8',
                       'theme' => 'roundcorners',
                       'description' => 'Majic afsatcom AGT. AMME halcon benelux TWA PGP doctrine Juiliett Class Submarine government constitution national information infrastructure keyhole Semtex Consul',
                       'mailformat' => 1,
                       'maildigest' => 1,
                       'maildisplay' => 1,
                       'emailstop' => true,
                       'htmleditor' => false,
                       'ajax' => true,
                       'autosubscribe' => false,
                       'trackforums' => false,
                       'screenreader' => true,
                       );

        $user = self::call_moodle('create-user', $data1, 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not created');

        $userdata = $this->get_user_cl($user['id']);
        foreach ($data1 as $key => $value) {
            if ('password' == $key) {
                continue;
            }
            elseif ('emailaddress' == $key) {
                $this->assertTrue($data1[$key] == $userdata['email']);
            }
            else {
                $this->assertTrue($data1[$key] == $userdata[$key], "Failed while checking key=$key: $data1[$key] != $userdata[$key]");
            }
        }

        $data2 = $data1; // No change in the values
        $data2['userid'] = $user['id'];
        $user = self::call_moodle('change-user', $data2, 'php');
        $this->assertTrue(array_key_exists('id', $user), 'User not changed');

        unset($data2['userid']);
        $userdata2 = $this->get_user_cl($user['id']);
        foreach ($data2 as $key => $value) {
            if ('password' == $key) {
                continue;
            }
            elseif ('emailaddress' == $key) {
                $this->assertTrue($data2[$key] == $userdata2['email']);
                $this->assertTrue($data1[$key] == $userdata2['email']);
            }
            else {
                $this->assertTrue($data2[$key] == $userdata2[$key], "Failed while checking key=$key: $data2[$key] != $userdata2[$key]");
                $this->assertTrue($data1[$key] == $userdata2[$key], "Failed while checking key=$key: $data1[$key] != $userdata2[$key]");
            }
        }

        $result = self::call_moodle('delete-user', array('username' => $user['username']));
        $this->assertTrue($result, 'User not deleted: '.$user['id']);
    }

    private function get_user_cl($id)
    {
        $result = self::call_moodle('show-user', array('userid' => $id));
        $data = explode("\n", $result);
        $user = array();
        foreach ($data as $element) {
            $line = explode("\t", $element);
            $user[$line[0]] = isset($line[1]) ? $line[1] : false;
        }
        $this->assertTrue(array_key_exists('id', $user), 'show user results did not contain id');
        $this->assertTrue($user['id'] == $id);
        $this->assertTrue(array_key_exists('username', $user), 'show user results did not contain a username');
        $this->assertTrue(array_key_exists('email', $user), 'show user results did not contain an email address');
        return $user;
    }

    private function get_user_by_idnumber_cl($idnumber)
    {
        $result = self::call_moodle('show-user', array('idnumber' => $idnumber));
        $data = explode("\n", $result);
        $user = array();
        foreach ($data as $element) {
            $line = explode("\t", $element);
            $user[$line[0]] = isset($line[1]) ? $line[1] : false;
        }
        $this->assertTrue(array_key_exists('id', $user), 'show user results did not contain idnumber');
        $this->assertTrue($user['idnumber'] == $idnumber);
        $this->assertTrue(array_key_exists('username', $user), 'show user results did not contain a username');
        $this->assertTrue(array_key_exists('email', $user), 'show user results did not contain an email address');
        return $user;
    }
}
?>