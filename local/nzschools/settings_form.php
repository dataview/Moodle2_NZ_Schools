<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class nzschoolssettings_form extends moodleform {

    function definition() {
        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('header', 'schooldetails', get_string('schooldetails', 'local_nzschools'));

        $mform->addElement('text', 'sitename', get_string('sitename', 'local_nzschools'), array('size'=>'48', 'onblur'=>'updateShortName(); '));
        $mform->setType('sitename', PARAM_TEXT);
        $mform->addRule('sitename', get_string('required'), 'required', null, 'client');
        $mform->addRule('sitename', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'shortname', get_string('shortname', 'local_nzschools'), 'size="10"');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->addRule('shortname', get_string('maximumchars', '', 20), 'maxlength', 20, 'client');

        $nzschoolsprofile = array('primary'      => get_string('primary', 'local_nzschools'),
                                 'secondary'    => get_string('secondary', 'local_nzschools'));

        $mform->addElement('select', 'nzschoolsprofile', get_string('nzschoolsprofile', 'local_nzschools'), $nzschoolsprofile);
        $mform->addRule('nzschoolsprofile', get_string('required'), 'required', null, 'client');

        $years = range(1, 13);
        $yeargroup = array();
        $yeargroup[] =& $mform->createElement('select', 'fromyear', null, $years);
        $yeargroup[] =& $mform->createElement('static', 'to', null, get_string('to', 'local_nzschools'));
        $yeargroup[] =& $mform->createElement('select', 'toyear', null, $years);
        $mform->addGroup($yeargroup, 'yeargroup', get_string('years', 'local_nzschools'), '', false);
        $mform->setDefault('toyear', 12);
        $mform->addRule('yeargroup', get_string('required'), 'required', null, 'client');

        // Logo customisation
        $mform->addElement('header', 'logoheader', get_string('logocustomisation', 'local_nzschools'));

        $mform->addElement('static', 'currentpicture', get_string('currentlogo','local_nzschools'));
        $mform->addElement('checkbox', 'deletepicture', get_string('removelogo','local_nzschools'), get_string('removelogohelp', 'local_nzschools'));
        $mform->setDefault('deletepicture', 0);

        $filemanager_options = array();
        // 3 == FILE_EXTERNAL & FILE_INTERNAL
        // These two constant names are defined in repository/lib.php
        $filemanager_options['return_types'] = 3;
        $filemanager_options['accepted_types'] = 'web_image';
        $filemanager_options['maxbytes'] = get_max_upload_file_size($CFG->maxbytes);
        $mform->addElement('filepicker', 'logo', get_string('logo', 'local_nzschools'), null, $filemanager_options);
//        $mform->addElement('checkbox', 'plainbg', get_string('themeplainbg', 'local_nzschools'), get_string('themeplainbghelp', 'local_nzschools'), array('id' => 'plainbg'));

        // Theme customisation
        $mform->addElement('header', 'themeheader', get_string('themecustomisation', 'local_nzschools'));
        $themelink = new moodle_url('/admin/settings.php', array('section'=>'themesettingnz_schools'));
        $purgelink = new moodle_url('/admin/purgecaches.php');
        $a = new stdClass();
        $a->themeurl = $themelink->out();
        $a->purgeurl = $purgelink->out();
        $mform->addElement('static', 'themelink', '', get_string('themesettingslink', 'local_nzschools', $a));
        
        // Options
        $mform->addElement('header', 'options', get_string('options', 'local_nzschools'));

        $mform->addElement('checkbox', 'createcats', get_string('createcategories', 'local_nzschools'));
        $mform->setDefault('createcats', true);


        $this->add_action_buttons();
    }

    function definition_after_data(){
		global $CFG;
        $mform =& $this->_form;
		$image_el =& $mform->getElement('currentpicture');
        $context = get_context_instance(CONTEXT_SYSTEM);
        $fs = get_file_storage();
		$files = $fs->get_area_files($context->id, 'local_nzschools', 'logo', 0);
		if (count($files)){
			$image_el->setValue("<img src=\"{$CFG->wwwroot}/local/nzschools/logo.php?force=1\" />");
			$logo_el =& $mform->getElement('logo');
			if (false) $logo_el = new MoodleQuickForm_filepicker();
			$logo_el->setLabel(get_string('replacelogo','local_nzschools'));
		} else {
			$mform->removeElement('currentpicture');
			$mform->removeElement('deletepicture');
		}

		$init = optional_param('init', 0, PARAM_BOOL);
		if ( $init ){
		    $mform->addElement('hidden','init','1');
		}
    }
}
