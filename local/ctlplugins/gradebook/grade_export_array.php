<?php

// copied from grade_export_txt.php

/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local
 *
 */
require_once($CFG->dirroot.'/grade/export/lib.php');

class grade_export_array extends grade_export {

    var $plugin = 'array';

    var $separator; // default separator

    function grade_export_array($course, $groupid=0, $itemlist='', $export_feedback=false, $updatedgradesonly = false, $displaytype = GRADE_DISPLAY_TYPE_REAL, $decimalpoints = 2) {
        $this->grade_export($course, $groupid, $itemlist, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints);
        $this->separator = 'tab';
    }

    function print_grades($mode) {
        global $CFG;

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        switch ($this->separator) {
            case 'comma':
                $separator = ",";
                break;
            case 'tab':
            default:
                $separator = "\t";
        }

/// Print names of all the fields
        if ($mode == 'opts') {
            echo get_string("firstname").$separator.
                 get_string("lastname").$separator.
                 get_string("idnumber").$separator.
                 get_string("institution").$separator.
                 get_string("department").$separator.
                 get_string("email");
        }
        $grade_item_details = array();
        foreach ($this->columns as $grade_item) {
            if ($mode == 'opts') {
                echo $separator.$this->format_column_name($grade_item);
                /// add a feedback column
                if ($this->export_feedback) {
                    echo $separator.$this->format_column_name($grade_item, true);
                }
            }
            else {
                if ($grade_item->itemtype == 'mod') {
                    $name = get_string('modulename', $grade_item->itemmodule).': '.$grade_item->get_name();
                } else {
                    $name = $grade_item->get_name();
                }
                $name = strip_tags($name);
                $grade_item_details[$grade_item->id] = array('name' => $name,
                                                             'id' => $grade_item->id, 
                                                             'type' => $grade_item->itemtype, 
                                                             'grademax' => $grade_item->grademax, 
                                                             'grademin' => $grade_item->grademin);
            }            
        }
        if ($mode == 'opts') {
            echo "\n";
        }
        
        $all_grades = array();

/// Print all the lines of data.
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->init();
        while ($userdata = $gui->next_user()) {

            $user = $userdata->user;
            
            if ($mode == 'opts') {
                echo $user->firstname.$separator.$user->lastname.$separator.$user->idnumber.$separator.$user->institution.$separator.$user->department.$separator.$user->email;
            }
            else {
                $user_grades = array('lastname' => $user->lastname,
                                     'firstname' => $user->firstname,
                                     'idunmber' => $user->idnumber,
                                     'institution' => $user->institution,
                                     'department' => $user->department,
                                     'email' => $user->email,
                                     'id' => $user->id,
                                     'username' => $user->username,
                                     'grades' => array());
            }

            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                if ($mode == 'opts') {
                    echo $separator.$this->format_grade($grade);
                    if ($this->export_feedback) {
                        echo $separator.$this->format_feedback($userdata->feedbacks[$itemid]);
                    }
                }
                else {
                    $details = $grade_item_details[$itemid];
                    $user_grades['grades'][$itemid] = array('id' => $itemid, 
                                                            'grade' => $this->format_grade($grade), 
                                                            'name' => $details['name'],
                                                            'type' => $details['type'], 
                                                            'grademax' => $details['grademax'], 
                                                            'grademin' => $details['grademin']);
                    $all_grades[$user->id] = $user_grades;
                }
            }
            if ($mode == 'opts') {
                echo "\n";
            }
        }
        $gui->close();
        $geub->close();
        return $all_grades;
    }
}
?>