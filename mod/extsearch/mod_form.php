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
 * extsearch configuration form
 *
 * @package    mod
 * @subpackage extsearch
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @copyright 2011 Aaron Wells {@link http://www.catalyst.net.nz}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/extsearch/locallib.php');

class mod_extsearch_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB, $COURSE;
        $mform = $this->_form;

        $config = get_config('extsearch');

        // this hack is needed for different settings of each subtype
        if (!empty($this->_instance)) {
            if($rec = $DB->get_record('extsearch', array('id'=>$this->_instance))) {
                $type = $rec->searchprovider;
            } else {
                print_error('invalidsearchprovider', 'extsearch');
            }
        } else {
            $type = required_param('type', PARAM_ALPHA);
        }
        $mform->addElement('hidden', 'type', $type);

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'extsearch'));
//        $mform->addElement('url', 'externalurl', get_string('externalurl', 'extsearch'), array('size'=>'60'), array('usefilepicker'=>true));
//        $mform->addElement('url', 'externalurl', get_string('externalurl', 'extsearch'), array('size'=>'60'));
        $courseid = $COURSE->id;

        // Resource URL picker
        if (file_exists("$CFG->dirroot/blocks/extsearch/search.php")) {
            // Lookup the ID of the first Digital NZ search block in this course
            $pickeroptions = array('url' => "/blocks/extsearch/search.php?type={$type}&courseid={$courseid}&choose=",
                               'buttoncaption' => get_string("search{$type}", 'extsearch'));

            MoodleQuickForm::registerElementType('externalpicker',
                                                 "$CFG->dirroot/mod/extsearch/externalpicker.php",
                                                 'MoodleQuickForm_externalpicker');

            $mform->addElement('externalpicker', 'externalurl', get_string("{$type}url", 'extsearch'), $pickeroptions);
        } else {
            // The External Search block is not installed, cannot enable the picker
            $mform->addElement('text', 'externalurl', get_string("{$type}url", 'extsearch'), array('size' => 48));
        }
//        $mform->setHelpButton('reference', array('sourceurl', get_string('digitalnz', 'extsearch'), 'extsearch'));

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'extsearch'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'extsearch'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
            $mform->addHelpButton('display', 'displayselect', 'extsearch');
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'extsearch'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'extsearch'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printheading', get_string('printheading', 'extsearch'));
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printheading', $config->printheading);
            $mform->setAdvanced('printheading', $config->printheading_adv);

            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'extsearch'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
            $mform->setAdvanced('printintro', $config->printintro_adv);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }

}
