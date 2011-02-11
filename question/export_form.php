<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class question_export_form extends moodleform {

    function definition() {
        $mform    =& $this->_form;

        $defaultcategory   = $this->_customdata['defaultcategory'];
        $contexts   = $this->_customdata['contexts'];
//--------------------------------------------------------------------------------
        $mform->addElement('header','fileformat',get_string('fileformat','quiz'));
        $fileformatnames = get_import_export_formats('export');
        $radioarray = array();
        $i = 0 ;
        foreach ($fileformatnames as $shortname => $fileformatname) {
            $currentgrp1 = array();
            $currentgrp1[] = &$mform->createElement('radio','format','',$fileformatname,$shortname);
            $mform->addGroup($currentgrp1,"formathelp[$i]",'',array('<br />'),false);
            $mform->addHelpButton("formathelp[$i]", $shortname, 'qformat_'.$shortname);
            $i++ ;
        }
        $mform->addRule("formathelp[0]",null,'required',null,'client');
//--------------------------------------------------------------------------------
        $mform->addElement('header','general', get_string('general', 'form'));

        $mform->addElement('questioncategory', 'category', get_string('exportcategory', 'question'), compact('contexts'));
        $mform->setDefault('category', $defaultcategory);
        $mform->addHelpButton('category', 'exportcategory', 'question');

        $categorygroup = array();
        $categorygroup[] =& $mform->createElement('checkbox', 'cattofile', '', get_string('tofilecategory', 'question'));
        $categorygroup[] =& $mform->createElement('checkbox', 'contexttofile', '', get_string('tofilecontext', 'question'));
        $mform->addGroup($categorygroup, 'categorygroup', '', '', false);
        $mform->disabledIf('categorygroup', 'cattofile', 'notchecked');
        $mform->setDefault('cattofile', 1);
        $mform->setDefault('contexttofile', 1);

//        $fileformatnames = get_import_export_formats('export');
//        $mform->addElement('select', 'format', get_string('fileformat','quiz'), $fileformatnames);
//        $mform->setDefault('format', 'gift');

        // set a template for the format select elements
        $renderer =& $mform->defaultRenderer();
        $template = "{help} {element}\n";
        $renderer->setGroupElementTemplate($template, 'format');

//--------------------------------------------------------------------------------
        $this->add_action_buttons(false, get_string('exportquestions', 'quiz'));
//--------------------------------------------------------------------------------
    }
}
