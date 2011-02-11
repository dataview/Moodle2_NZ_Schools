<?php

class block_calendar_upcoming extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_calendar_upcoming');
    }

    function get_content() {
        global $USER, $CFG, $SESSION;
        $cal_m = optional_param( 'cal_m', 0, PARAM_INT );
        $cal_y = optional_param( 'cal_y', 0, PARAM_INT );

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }
        // Reset the session variables
        calendar_session_vars($this->page->course);
        $this->content = new stdClass;
        $this->content->text = '';

        if (empty($this->instance)) { // Overrides: use no course at all
            $courseshown = false;
            $filtercourse = array();
            $this->content->footer = '';

        } else {
            $courseshown = $this->page->course->id;
            $this->content->footer = '<div class="gotocal"><a href="'.$CFG->wwwroot.
                                     '/calendar/view.php?view=upcoming&amp;course='.$courseshown.'">'.
                                      get_string('gotocalendar', 'calendar').'</a>...</div>';
            $context = get_context_instance(CONTEXT_COURSE, $courseshown);
            if (has_capability('moodle/calendar:manageentries', $context) ||
                has_capability('moodle/calendar:manageownentries', $context)) {
                $this->content->footer .= '<div class="newevent"><a href="'.$CFG->wwwroot.
                                          '/calendar/event.php?action=new&amp;course='.$courseshown.'">'.
                                           get_string('newevent', 'calendar').'</a>...</div>';
            }
            if ($courseshown == SITEID) {
                // Being displayed at site level. This will cause the filter to fall back to auto-detecting
                // the list of courses it will be grabbing events from.
                $filtercourse    = NULL;
                $groupeventsfrom = NULL;
                $SESSION->cal_courses_shown = calendar_get_default_courses(true);
                calendar_set_referring_course(0);
            } else {
                // Forcibly filter events to include only those from the particular course we are in.
                $filtercourse    = array($courseshown => $this->page->course);
                $groupeventsfrom = array($courseshown => 1);
            }
        }

        // We 'll need this later
        calendar_set_referring_course($courseshown);

        // Be VERY careful with the format for default courses arguments!
        // Correct formatting is [courseid] => 1 to be concise with moodlelib.php functions.

        calendar_set_filters($courses, $group, $user, $filtercourse, $groupeventsfrom, false);
        $events = calendar_get_upcoming($courses, $group, $user,
                                        get_user_preferences('calendar_lookahead', CALENDAR_UPCOMING_DAYS),
                                        get_user_preferences('calendar_maxevents', CALENDAR_UPCOMING_MAXEVENTS));

        if (!empty($this->instance)) {
            $this->content->text = calendar_get_block_upcoming($events,
                                   'view.php?view=day&amp;course='.$courseshown.'&amp;');
        }

        if (empty($this->content->text)) {
            $this->content->text = '<div class="post">'.
                                   get_string('noupcomingevents', 'calendar').'</div>';
        }

        return $this->content;
    }
}


