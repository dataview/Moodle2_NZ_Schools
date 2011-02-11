<?php

/**
* Copyright (C) 2009  Moodlerooms Inc.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
* 
* @copyright  Copyright (c) 2009 Moodlerooms Inc. (http://www.moodlerooms.com)
* @license    http://opensource.org/licenses/gpl-3.0.html     GNU Public License
* @author Chris Stones
*/

/**
 * Google Services Access
 *
 * Development plans:
 * All services we support will have links and icons
 * Optional Google Icon Set
 * 
 * @author Chris Stones 
 * @version $Id$
 * @package block_gaccess
 **/
class block_gaccess extends block_list {


    function init() {
        $this->title   = get_string('pluginname', 'block_gaccess');
    }

    /**
     * Default case: the block can be used in all course types
     * @return array
     * @todo finish documenting this function
     */
    function applicable_formats() {
        // Default case: the block can be used in courses and site index, but not in activities
        return array('all' => true, 'site' => true);
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $COURSE, $OUTPUT;


        // quick and simple way to prevent block from showing up on front page
        if (!isloggedin()) {
            $this->content = NULL;
            return $this->content;
        }

        // quick and simple way to prevent block from showing up on users My Moodle if their email does not match the Google registered domain
        $domainname = get_config('auth/gsaml','domainname');
        if (!preg_match("/^[a-z0-9&\'\.\-\+]+@$domainname/",$USER->email)) {
            $this->content = NULL;
            return $this->content;
        }

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $domain = get_config('auth/gsaml','domainname');
        if (empty($domain)) {
            $this->content->items[] = get_string('nodomainyet', 'block_gaccess');
            return $this->content;
        }

        // USE the icons from this page
        // https://www.google.com/a/cpanel/mroomsdev.com/Dashboard
        // Google won't mind ;) (I hope)
        $google_services = array(
            /*
            array(
                    'service'   => 'Gmail',
                    'relayurl'  => 'http://mail.google.com/a/'.$domain, 
                    'icon_name' => 'gmail'
            ),
            array(
                    'service'   => 'Start Page',
                    'relayurl'  => 'http://partnerpage.google.com/'.$domain, 
                    'icon_name' => 'startpage'
            ),
            */
            array(
                    'service'   => 'Calendar',
                    'relayurl'  => 'http://www.google.com/calendar/a/'.$domain, 
                    'icon_name' => 'calendar'
            ),
            array(
                    'service'   => 'Docs',
                    'relayurl'  => 'http://docs.google.com/a/'.$domain, 
                    'icon_name' => 'gdocs'
            ),
        );

        $newwinlnk = get_config('blocks/gaccess','newwinlink');
        if ($newwinlnk) { 
            $target = 'target=\"_new\"';
        } else {
            $target = '';
        }

        foreach ($google_services as $gs) {
            
            if (!empty($gs['icon_name'])) {
                $icon = "<img src=\"".$OUTPUT->pix_url($gs['icon_name'], 'block_gaccess')."\" alt=\"".$gs['service']."\" />";
            } else {
                // Default to a check graphic
                $icon = "<img src=\"".$OUTPUT->pix_url('i/tick_green_small')."\" alt=\"$service\" />";
            }
            $this->content->items[] = "<a ".$target.". title=\"".$gs['service']."\"  href=\"".$gs['relayurl']."\">".$icon . '&nbsp;' . $gs['service']."</a>";
        }

        return $this->content;
    }
}

