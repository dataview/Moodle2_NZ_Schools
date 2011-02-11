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
 * True/false
 *
 * @package    mod
 * @subpackage lesson
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();

/** True/False question type */
define("LESSON_PAGE_TRUEFALSE",     "2");

class lesson_page_type_truefalse extends lesson_page {

    protected $type = lesson_page::TYPE_QUESTION;
    protected $typeidstring = 'truefalse';
    protected $typeid = LESSON_PAGE_TRUEFALSE;
    protected $string = null;

    public function get_typeid() {
        return $this->typeid;
    }
    public function get_typestring() {
        if ($this->string===null) {
            $this->string = get_string($this->typeidstring, 'lesson');
        }
        return $this->string;
    }
    public function get_idstring() {
        return $this->typeidstring;
    }
    public function display($renderer, $attempt) {
        global $USER, $CFG, $PAGE;
        $answers = $this->get_answers();
        shuffle($answers);

        $params = array('answers'=>$answers, 'lessonid'=>$this->lesson->id, 'contents'=>$this->get_contents(), 'attempt'=>$attempt);
        $mform = new lesson_display_answer_form_truefalse($CFG->wwwroot.'/mod/lesson/continue.php', $params);
        $data = new stdClass;
        $data->id = $PAGE->cm->id;
        $data->pageid = $this->properties->id;
        $mform->set_data($data);
        return $mform->display();
    }
    public function check_answer() {
        global $DB, $CFG;
        $formattextdefoptions = new stdClass();
        $formattextdefoptions->noclean = true;
        $formattextdefoptions->para = false;

        $answers = $this->get_answers();
        shuffle($answers);
        $params = array('answers'=>$answers, 'lessonid'=>$this->lesson->id, 'contents'=>$this->get_contents());
        $mform = new lesson_display_answer_form_truefalse($CFG->wwwroot.'/mod/lesson/continue.php', $params);
        $data = $mform->get_data();
        require_sesskey();

        $result = parent::check_answer();

        $answerid = $data->answerid;
        if ($answerid === false) {
            $result->noanswer = true;
            return $result;
        }
        $result->answerid = $answerid;
        $answer = $DB->get_record("lesson_answers", array("id" => $result->answerid), '*', MUST_EXIST);
        if ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto)) {
            $result->correctanswer = true;
        }
        if ($this->lesson->custom) {
            if ($answer->score > 0) {
                $result->correctanswer = true;
            } else {
                $result->correctanswer = false;
            }
        }
        $result->newpageid = $answer->jumpto;
        $result->response  = format_text($answer->response, $answer->responseformat, $formattextdefoptions);
        $result->studentanswer = $result->userresponse = $result->response;
        return $result;
    }

    public function display_answers(html_table $table) {
        $answers = $this->get_answers();
        $options = new stdClass();
        $options->noclean = true;
        $options->para = false;
        $i = 1;
        foreach ($answers as $answer) {
            $cells = array();
            if ($this->lesson->custom && $answer->score > 0) {
                // if the score is > 0, then it is correct
                $cells[] = '<span class="labelcorrect">'.get_string("answer", "lesson")." $i</span>: \n";
            } else if ($this->lesson->custom) {
                $cells[] = '<span class="label">'.get_string("answer", "lesson")." $i</span>: \n";
            } else if ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto)) {
                // underline correct answers
                $cells[] = '<span class="correct">'.get_string("answer", "lesson")." $i</span>: \n";
            } else {
                $cells[] = '<span class="labelcorrect">'.get_string("answer", "lesson")." $i</span>: \n";
            }
            $cells[] = format_text($answer->answer, $answer->answerformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("response", "lesson")." $i</span>";
            $cells[] = format_text($answer->response, $answer->responseformat, $options);
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("score", "lesson").'</span>';
            $cells[] = $answer->score;
            $table->data[] = new html_table_row($cells);

            $cells = array();
            $cells[] = "<span class=\"label\">".get_string("jump", "lesson").'</span>';
            $cells[] = $this->get_jump_name($answer->jumpto);
            $table->data[] = new html_table_row($cells);

            if ($i === 1){
                $table->data[count($table->data)-1]->cells[0]->style = 'width:20%;';
            }

            $i++;
        }
        return $table;
    }
    public function stats(array &$pagestats, $tries) {
        if(count($tries) > $this->lesson->maxattempts) { // if there are more tries than the max that is allowed, grab the last "legal" attempt
            $temp = $tries[$this->lesson->maxattempts - 1];
        } else {
            // else, user attempted the question less than the max, so grab the last one
            $temp = end($tries);
        }
        if ($this->properties->qoption) {
            $userresponse = explode(",", $temp->useranswer);
            foreach ($userresponse as $response) {
                if (isset($pagestats[$temp->pageid][$response])) {
                    $pagestats[$temp->pageid][$response]++;
                } else {
                    $pagestats[$temp->pageid][$response] = 1;
                }
            }
        } else {
            if (isset($pagestats[$temp->pageid][$temp->answerid])) {
                $pagestats[$temp->pageid][$temp->answerid]++;
            } else {
                $pagestats[$temp->pageid][$temp->answerid] = 1;
            }
        }
        if (isset($pagestats[$temp->pageid]["total"])) {
            $pagestats[$temp->pageid]["total"]++;
        } else {
            $pagestats[$temp->pageid]["total"] = 1;
        }
        return true;
    }

    public function report_answers($answerpage, $answerdata, $useranswer, $pagestats, &$i, &$n) {
        $answers = $this->get_answers();
        $formattextdefoptions = new stdClass(); //I'll use it widely in this page
        $formattextdefoptions->para = false;
        $formattextdefoptions->noclean = true;
        foreach ($answers as $answer) {
            if ($this->properties->qoption) {
                if ($useranswer == NULL) {
                    $userresponse = array();
                } else {
                    $userresponse = explode(",", $useranswer->useranswer);
                }
                if (in_array($answer->id, $userresponse)) {
                    // make checked
                    $data = "<input  readonly=\"readonly\" disabled=\"disabled\" name=\"answer[$i]\" checked=\"checked\" type=\"checkbox\" value=\"1\" />";
                    if (!isset($answerdata->response)) {
                        if ($answer->response == NULL) {
                            if ($useranswer->correct) {
                                $answerdata->response = get_string("thatsthecorrectanswer", "lesson");
                            } else {
                                $answerdata->response = get_string("thatsthewronganswer", "lesson");
                            }
                        } else {
                            $answerdata->response = format_text($answer->response, $answer->responseformat, $formattextdefoptions);
                        }
                    }
                    if (!isset($answerdata->score)) {
                        if ($this->lesson->custom) {
                            $answerdata->score = get_string("pointsearned", "lesson").": ".$answer->score;
                        } elseif ($useranswer->correct) {
                            $answerdata->score = get_string("receivedcredit", "lesson");
                        } else {
                            $answerdata->score = get_string("didnotreceivecredit", "lesson");
                        }
                    }
                } else {
                    // unchecked
                    $data = "<input type=\"checkbox\" readonly=\"readonly\" name=\"answer[$i]\" value=\"0\" disabled=\"disabled\" />";
                }
                if (($answer->score > 0 && $this->lesson->custom) || ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto) && !$this->lesson->custom)) {
                    $data .= "<div class=highlight>".format_text($answer->answer, $answer->answerformat, $formattextdefoptions)."</div>";
                } else {
                    $data .= format_text($answer->answer, $answer->answerformat, $formattextdefoptions);
                }
            } else {
                if ($useranswer != NULL and $answer->id == $useranswer->answerid) {
                    // make checked
                    $data = "<input  readonly=\"readonly\" disabled=\"disabled\" name=\"answer[$i]\" checked=\"checked\" type=\"checkbox\" value=\"1\" />";
                    if ($answer->response == NULL) {
                        if ($useranswer->correct) {
                            $answerdata->response = get_string("thatsthecorrectanswer", "lesson");
                        } else {
                            $answerdata->response = get_string("thatsthewronganswer", "lesson");
                        }
                    } else {
                        $answerdata->response = format_text($answer->response, $answer->responseformat, $formattextdefoptions);
                    }
                    if ($this->lesson->custom) {
                        $answerdata->score = get_string("pointsearned", "lesson").": ".$answer->score;
                    } elseif ($useranswer->correct) {
                        $answerdata->score = get_string("receivedcredit", "lesson");
                    } else {
                        $answerdata->score = get_string("didnotreceivecredit", "lesson");
                    }
                } else {
                    // unchecked
                    $data = "<input type=\"checkbox\" readonly=\"readonly\" name=\"answer[$i]\" value=\"0\" disabled=\"disabled\" />";
                }
                if (($answer->score > 0 && $this->lesson->custom) || ($this->lesson->jumpto_is_correct($this->properties->id, $answer->jumpto) && !$this->lesson->custom)) {
                    $data .= "<div class=\"highlight\">".format_text($answer->answer, $answer->answerformat, $formattextdefoptions)."</div>";
                } else {
                    $data .= format_text($answer->answer, $answer->answerformat, $formattextdefoptions);
                }
            }
            if (isset($pagestats[$this->properties->id][$answer->id])) {
                $percent = $pagestats[$this->properties->id][$answer->id] / $pagestats[$this->properties->id]["total"] * 100;
                $percent = round($percent, 2);
                $percent .= "% ".get_string("checkedthisone", "lesson");
            } else {
                $percent = get_string("noonecheckedthis", "lesson");
            }

            $answerdata->answers[] = array($data, $percent);
            $answerpage->answerdata = $answerdata;
        }
        return $answerpage;
    }
}

class lesson_add_page_form_truefalse extends lesson_add_page_form_base {

    public $qtype = 'truefalse';
    public $qtypestring = 'truefalse';

    public function custom_definition() {
        $this->_form->addElement('header', 'answertitle0', get_string('correctresponse', 'lesson'));
        $this->add_answer(0, NULL, true);
        $this->add_response(0);
        $this->add_jumpto(0, get_string('correctanswerjump', 'lesson'), LESSON_NEXTPAGE);
        $this->add_score(0, get_string('correctanswerscore', 'lesson'), 1);

        $this->_form->addElement('header', 'answertitle1', get_string('wrongresponse', 'lesson'));
        $this->add_answer(1, NULL, true);
        $this->add_response(1);
        $this->add_jumpto(1, get_string('wronganswerjump', 'lesson'), LESSON_THISPAGE);
        $this->add_score(1, get_string('wronganswerscore', 'lesson'), 0);
    }
}

class lesson_display_answer_form_truefalse extends moodleform {

    public function definition() {
        global $USER, $OUTPUT;
        $mform = $this->_form;
        $answers = $this->_customdata['answers'];
        $lessonid = $this->_customdata['lessonid'];
        $contents = $this->_customdata['contents'];
        if (array_key_exists('attempt', $this->_customdata)) {
            $attempt = $this->_customdata['attempt'];
        } else {
            $attempt = new stdClass();
            $attempt->answerid = null;
        }

        $mform->addElement('header', 'pageheader', $OUTPUT->box($contents, 'contents'));

        $options = new stdClass();
        $options->para = false;
        $options->noclean = true;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        $i = 0;
        foreach ($answers as $answer) {
            $mform->addElement('html', '<div class="answeroption">');
            $mform->addElement('radio', 'answerid', null, format_text($answer->answer, $answer->answerformat, $options), $answer->id);
            $mform->setType('answerid', PARAM_INT);
            if (isset($USER->modattempts[$lessonid]) && $answer->id == $attempt->answerid) {
                $mform->setDefault('answerid', true);
            }
            $mform->addElement('html', '</div>');
            $i++;
        }

        $this->add_action_buttons(null, get_string("pleasecheckoneanswer", "lesson"));
    }

}