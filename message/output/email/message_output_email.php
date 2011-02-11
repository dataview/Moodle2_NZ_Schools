<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Email message processor - send a given message by email
 *
 * @author Luis Rodrigues
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 */
require_once($CFG->dirroot.'/message/output/lib.php');

class message_output_email extends message_output {
    /**
     * Processes the message (sends by email).
     * @param object $eventdata the event data submitted by the message sender plus $eventdata->savedmessageid
     */
    function send_message($eventdata) {
        global $CFG;

        if (!empty($CFG->noemailever)) {
            // hidden setting for development sites, set in config.php if needed
            debugging('$CFG->noemailever active, no email message sent.', DEBUG_MINIMAL);
            return true;
        }

        //hold onto email preference because /admin/cron.php sends a lot of messages at once
        static $useremailaddresses = array();

        //check user preference for where user wants email sent
        if (!array_key_exists($eventdata->userto->id, $useremailaddresses)) {
            $useremailaddresses[$eventdata->userto->id] = get_user_preferences('message_processor_email_email', $eventdata->userto->email, $eventdata->userto->id);
        }
        $usertoemailaddress = $useremailaddresses[$eventdata->userto->id];

        if ( !empty($usertoemailaddress)) {
            $userto->email = $usertoemailaddress;
        }

        $result = email_to_user($eventdata->userto, $eventdata->userfrom,
            $eventdata->subject, $eventdata->fullmessage, $eventdata->fullmessagehtml);

        return $result;
    }

    /**
     * Creates necessary fields in the messaging config form.
     * @param object $mform preferences form class
     */
    function config_form($preferences){
        global $USER;
        $string = get_string('email','message_email').': <input size="30" name="email_email" value="'.$preferences->email_email.'" />';

        if (empty($preferences->email_email) && !empty($preferences->userdefaultemail)) {
            $string .= ' ('.get_string('default').': '.$preferences->userdefaultemail.')';
        }
        return $string;
    }

    /**
     * Parses the form submitted data and saves it into preferences array.
     * @param object $mform preferences form class
     * @param array $preferences preferences array
     */
    function process_form($form, &$preferences){
        $preferences['message_processor_email_email'] = $form->email_email;
    }

    /**
     * Loads the config data from database to put on the form (initial load)
     * @param array $preferences preferences array
     * @param int $userid the user id
     */
    function load_data(&$preferences, $userid){
        $preferences->email_email = get_user_preferences( 'message_processor_email_email', '', $userid);
    }
}
