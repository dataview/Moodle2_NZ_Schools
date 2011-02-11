<?php

// This script uses installed report plugins to print quiz reports

    require_once("../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->dirroot.'/mod/scorm/locallib.php');
    require_once($CFG->dirroot.'/mod/scorm/reportsettings_form.php');
    require_once($CFG->libdir.'/formslib.php');
    define('SCORM_REPORT_DEFAULT_PAGE_SIZE', 20);
    define('SCORM_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
    define('SCORM_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
    define('SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);

    $id = optional_param('id', '', PARAM_INT);    // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);     // SCORM ID
    $b = optional_param('b', '', PARAM_INT);     // SCO ID
    $user = optional_param('user', '', PARAM_INT);  // User ID
    $attempt = optional_param('attempt', '1', PARAM_INT);  // attempt number
    $action     = optional_param('action', '', PARAM_ALPHA);
    $attemptids = optional_param('attemptid', array(), PARAM_RAW);
    $download = optional_param('download', '', PARAM_RAW);

    $url = new moodle_url('/mod/scorm/report.php');
    if ($user !== '') {
        $url->param('user', $user);
    }
    if ($attempt !== '1') {
        $url->param('attempt', $attempt);
    }
    if ($action !== '') {
        $url->param('action', $action);
    }

    if (!empty($id)) {
        $url->param('id', $id);
        if (! $cm = get_coursemodule_from_id('scorm', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
            print_error('coursemisconf');
        }
        if (! $scorm = $DB->get_record('scorm', array('id'=>$cm->instance))) {
            print_error('invalidcoursemodule');
        }
    } else {
        if (!empty($b)) {
            $url->param('b', $b);
            if (! $sco = $DB->get_record('scorm_scoes', array('id'=>$b))) {
                print_error('invalidactivity', 'scorm');
            }
            $a = $sco->scorm;
        }
        if (!empty($a)) {
            $url->param('a', $a);
            if (! $scorm = $DB->get_record('scorm', array('id'=>$a))) {
                print_error('invalidcoursemodule');
            }
            if (! $course = $DB->get_record('course', array('id'=>$scorm->course))) {
                print_error('coursemisconf');
            }
            if (! $cm = get_coursemodule_from_instance('scorm', $scorm->id, $course->id)) {
                print_error('invalidcoursemodule');
            }
        }
    }
    $PAGE->set_url($url);

    require_login($course->id, false, $cm);

    $contextmodule = get_context_instance(CONTEXT_MODULE,$cm->id);

    require_capability('mod/scorm:viewreport', $contextmodule);

    add_to_log($course->id, 'scorm', 'report', 'report.php?id='.$cm->id, $scorm->id, $cm->id);

    if (!empty($user)) {
        $userdata = scorm_get_user_data($user);
    } else {
        $userdata = null;
    }
    if (!empty($download)) {
        $noheader = true;
    }
/// Print the page header
    if (empty($noheader)) {

        $strscorms = get_string('modulenameplural', 'scorm');
        $strscorm  = get_string('modulename', 'scorm');
        $strreport  = get_string('report', 'scorm');
        $strattempt  = get_string('attempt', 'scorm');
        $strname  = get_string('name');

        $PAGE->set_title("$course->shortname: ".format_string($scorm->name));
        $PAGE->set_heading($course->fullname);
        $PAGE->navbar->add($strreport, new moodle_url('/mod/scorm/report.php', array('id'=>$cm->id)));

        if (empty($b)) {
            if (!empty($a)) {
                $PAGE->navbar->add("$strattempt $attempt - ".fullname($userdata));
            }
        } else {
            $PAGE->navbar->add("$strattempt $attempt - ".fullname($userdata), new moodle_url('/mod/scorm/report.php', array('a'=>$a, 'user'=>$user, 'attempt'=>$attempt)));
            $PAGE->navbar->add($sco->title);
        }
        echo $OUTPUT->header();
        $currenttab = 'reports';
        require($CFG->dirroot . '/mod/scorm/tabs.php');
        echo $OUTPUT->heading(format_string($scorm->name));
    }

    if ($action == 'delete' && has_capability('mod/scorm:deleteresponses',$contextmodule) && confirm_sesskey()) {
        if (scorm_delete_responses($attemptids, $scorm)) { //delete responses.
            add_to_log($course->id, 'scorm', 'delete attempts', 'report.php?id=' . $cm->id, implode(",", $attemptids), $cm->id);
            echo $OUTPUT->notification(get_string('scormresponsedeleted', 'scorm'), 'notifysuccess');
        }
    }

    if (empty($b)) {
        if (empty($a)) {
            // No options, show the global scorm report
                $pageoptions = array();
                $pageoptions['id'] = $cm->id;
                $reporturl = new moodle_url($CFG->wwwroot.'/mod/scorm/report.php', $pageoptions);

                // find out current groups mode
                $currentgroup = groups_get_activity_group($cm, true);

                // detailed report
                $mform = new mod_scorm_report_settings( $reporturl, compact('currentgroup') );
                if ($fromform = $mform->get_data()) {
                    $detailedrep = $fromform->detailedrep;
                    $pagesize = $fromform->pagesize;
                    $attemptsmode = $fromform->attemptsmode;
                    set_user_preference('scorm_report_detailed', $detailedrep);
                    set_user_preference('scorm_report_pagesize', $pagesize);
                } else {
                    $detailedrep = get_user_preferences('scorm_report_detailed', false);
                    $pagesize = get_user_preferences('scorm_report_pagesize', 0);
                    $attemptsmode = optional_param('attemptsmode', SCORM_REPORT_ATTEMPTS_STUDENTS_WITH, PARAM_INT);
                }
                if ($pagesize < 1) {
                    $pagesize = SCORM_REPORT_DEFAULT_PAGE_SIZE;
                }

                // select group menu
                $displayoptions = array();
                $displayoptions['id'] = $cm->id;
                $displayoptions['attemptsmode'] = $attemptsmode;
                $reporturlwithdisplayoptions = new moodle_url($CFG->wwwroot.'/mod/scorm/report.php', $displayoptions);

                if ($groupmode = groups_get_activity_groupmode($cm)) {   // Groups are being used
                    if (!$download) {
                        groups_print_activity_menu($cm, $reporturlwithdisplayoptions->out());
                    }
                }

                // We only want to show the checkbox to delete attempts
                // if the user has permissions and if the report mode is showing attempts.
                $candelete = has_capability('mod/scorm:deleteresponses',$contextmodule)
                        && ($attemptsmode!= SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO);
                // select the students
                $nostudents = false;

                if (empty($currentgroup)) {
                    // all users who can attempt scoes
                    if (!$students = get_users_by_capability($contextmodule, 'mod/scorm:savetrack','','','','','','',false)){
                        echo $OUTPUT->notification(get_string('nostudentsyet'));
                        $nostudents = true;
                        $allowedlist = '';
                    } else {
                        $allowedlist = join(',',array_keys($students));
                    }
                } else {
                    // all users who can attempt scoes and who are in the currently selected group
                    if (!$groupstudents = get_users_by_capability($contextmodule, 'mod/scorm:savetrack','','','','',$currentgroup,'',false)){
                        echo $OUTPUT->notification(get_string('nostudentsingroup'));
                        $nostudents = true;
                        $groupstudents = array();
                    }
                    $allowedlist = join(',', array_keys($groupstudents));
                }

                if( !$nostudents ) {

                    // Now check if asked download of data
                    if ($download) {
                        $filename = clean_filename("$course->shortname ".format_string($scorm->name,true));
                    }

                    // Define table columns
                    $columns = array();
                    $headers = array();
                    if (!$download && $candelete) {
                        $columns[]= 'checkbox';
                        $headers[]= NULL;
                    }
                    if (!$download && $CFG->grade_report_showuserimage) {
                        $columns[]= 'picture';
                        $headers[]= '';
                    }
                    $columns[]= 'fullname';
                    $headers[]= get_string('name');
                    if ($CFG->grade_report_showuseridnumber) {
                        $columns[]= 'idnumber';
                        $headers[]= get_string('idnumber');
                    }
                    $columns[]= 'attempt';
                    $headers[]= get_string('attempt', 'scorm');
                    $columns[]= 'start';
                    $headers[]= get_string('started','scorm');
                    $columns[]= 'finish';
                    $headers[]= get_string('last','scorm');
                    $columns[]= 'score';
                    $headers[]= get_string('score','scorm');
                    if ($detailedrep && $scoes = $DB->get_records('scorm_scoes',array("scorm"=>$scorm->id),'id')) {
                        foreach ($scoes as $sco) {
                            if ($sco->launch!='') {
                                $columns[]= 'scograde'.$sco->id;
                                $headers[]= format_string($sco->title);
                                $table->head[]= format_string($sco->title);
                            }
                        }
                    } else {
                        $scoes = NULL;
                    }

                    if (!$download) {
                        $table = new flexible_table('mod-scorm-report');

                        $table->define_columns($columns);
                        $table->define_headers($headers);
                        $table->define_baseurl($reporturlwithdisplayoptions->out());

                        $table->sortable(true);
                        $table->collapsible(true);

                        $table->column_suppress('picture');
                        $table->column_suppress('fullname');
                        $table->column_suppress('idnumber');

                        $table->no_sorting('start');
                        $table->no_sorting('finish');
                        $table->no_sorting('score');
                        if( $scoes ) {
                            foreach ($scoes as $sco) {
                                if ($sco->launch!='') {
                                    $table->no_sorting('scograde'.$sco->id);
                                }
                            }
                        }

                        $table->column_class('picture', 'picture');
                        $table->column_class('fullname', 'bold');
                        $table->column_class('score', 'bold');

                        $table->set_attribute('cellspacing', '0');
                        $table->set_attribute('id', 'attempts');
                        $table->set_attribute('class', 'generaltable generalbox');

                        // Start working -- this is necessary as soon as the niceties are over
                        $table->setup();
                    } else if ($download =='ODS') {
                        require_once("$CFG->libdir/odslib.class.php");

                        $filename .= ".ods";
                        // Creating a workbook
                        $workbook = new MoodleODSWorkbook("-");
                        // Sending HTTP headers
                        $workbook->send($filename);
                        // Creating the first worksheet
                        $sheettitle = get_string('report','scorm');
                        $myxls =& $workbook->add_worksheet($sheettitle);
                        // format types
                        $format =& $workbook->add_format();
                        $format->set_bold(0);
                        $formatbc =& $workbook->add_format();
                        $formatbc->set_bold(1);
                        $formatbc->set_align('center');
                        $formatb =& $workbook->add_format();
                        $formatb->set_bold(1);
                        $formaty =& $workbook->add_format();
                        $formaty->set_bg_color('yellow');
                        $formatc =& $workbook->add_format();
                        $formatc->set_align('center');
                        $formatr =& $workbook->add_format();
                        $formatr->set_bold(1);
                        $formatr->set_color('red');
                        $formatr->set_align('center');
                        $formatg =& $workbook->add_format();
                        $formatg->set_bold(1);
                        $formatg->set_color('green');
                        $formatg->set_align('center');
                        // Here starts workshhet headers

                        $colnum = 0;
                        foreach ($headers as $item) {
                            $myxls->write(0,$colnum,$item,$formatbc);
                            $colnum++;
                        }
                        $rownum=1;
                    } else if ($download =='Excel') {
                        require_once("$CFG->libdir/excellib.class.php");

                        $filename .= ".xls";
                        // Creating a workbook
                        $workbook = new MoodleExcelWorkbook("-");
                        // Sending HTTP headers
                        $workbook->send($filename);
                        // Creating the first worksheet
                        $sheettitle = get_string('report','scorm');
                        $myxls =& $workbook->add_worksheet($sheettitle);
                        // format types
                        $format =& $workbook->add_format();
                        $format->set_bold(0);
                        $formatbc =& $workbook->add_format();
                        $formatbc->set_bold(1);
                        $formatbc->set_align('center');
                        $formatb =& $workbook->add_format();
                        $formatb->set_bold(1);
                        $formaty =& $workbook->add_format();
                        $formaty->set_bg_color('yellow');
                        $formatc =& $workbook->add_format();
                        $formatc->set_align('center');
                        $formatr =& $workbook->add_format();
                        $formatr->set_bold(1);
                        $formatr->set_color('red');
                        $formatr->set_align('center');
                        $formatg =& $workbook->add_format();
                        $formatg->set_bold(1);
                        $formatg->set_color('green');
                        $formatg->set_align('center');

                        $colnum = 0;
                        foreach ($headers as $item) {
                            $myxls->write(0,$colnum,$item,$formatbc);
                            $colnum++;
                        }
                        $rownum=1;
                    } else if ($download=='CSV') {
                        $filename .= ".txt";
                        header("Content-Type: application/download\n");
                        header("Content-Disposition: attachment; filename=\"$filename\"");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                        header("Pragma: public");
                        echo implode("\t", $headers)." \n";
                    }

                    // Construct the SQL
                    $select = 'SELECT DISTINCT '.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(st.attempt, 0)').' AS uniqueid, ';
                    $select .= 'st.scormid AS scormid, st.attempt AS attempt, ' .
                            'u.id AS userid, u.idnumber, u.firstname, u.lastname, u.picture, u.imagealt, u.email ';

                    // This part is the same for all cases - join users and scorm_scoes_track tables
                    $from = 'FROM {user} u ';
                    $from .= 'LEFT JOIN {scorm_scoes_track} st ON st.userid = u.id AND st.scormid = '.$scorm->id;
                    switch ($attemptsmode){
                        case SCORM_REPORT_ATTEMPTS_STUDENTS_WITH:
                            // Show only students with attempts
                            $where = ' WHERE u.id IN (' .$allowedlist. ') AND st.userid IS NOT NULL';
                            break;
                        case SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO:
                            // Show only students without attempts
                            $where = ' WHERE u.id IN (' .$allowedlist. ') AND st.userid IS NULL';
                            break;
                        case SCORM_REPORT_ATTEMPTS_ALL_STUDENTS:
                            // Show all students with or without attempts
                            $where = ' WHERE u.id IN (' .$allowedlist. ') AND (st.userid IS NOT NULL OR st.userid IS NULL)';
                            break;
                    }

                    $countsql = 'SELECT COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(st.attempt, 0)').')) AS nbresults, ';
                    $countsql .= 'COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'','st.attempt').')) AS nbattempts, ';
                    $countsql .= 'COUNT(DISTINCT(u.id)) AS nbusers ';
                    $countsql .= $from.$where;
                    $params = array();

                    if (!$download) {
                        $sort = $table->get_sql_sort();
                    }
                    else {
                        $sort = '';
                    }
                    // Fix some wired sorting
                    if (empty($sort)) {
                        $sort = ' ORDER BY uniqueid';
                    } else {
                        $sort = ' ORDER BY '.$sort;
                    }

                    if (!$download) {
                        // Add extra limits due to initials bar
                        list($twhere, $tparams) = $table->get_sql_where();
                        if ($twhere) {
                            $where .= ' AND '.$twhere; //initial bar
                            $params = array_merge($params, $tparams);
                        }

                        if (!empty($countsql)) {
                            $count = $DB->get_record_sql($countsql);
                            $totalinitials = $count->nbresults;
                            if ($twhere) {
                                $countsql .= ' AND '.$twhere;
                            }
                            $count = $DB->get_record_sql($countsql, $params);
                            $total  = $count->nbresults;
                        }

                        $table->pagesize($pagesize, $total);

                        echo '<div class="quizattemptcounts">';
                        if ( $count->nbresults == $count->nbattempts ) {
                            echo get_string('reportcountattempts','scorm', $count);
                        } else if( $count->nbattempts>0 ) {
                            echo get_string('reportcountallattempts','scorm', $count);
                        } else {
                            echo $count->nbusers.' '.get_string('users');
                        }
                        echo '</div>';
                    }

                    // Fetch the attempts
                    if (!$download) {
                        $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params,
                                                $table->get_page_start(), $table->get_page_size());
                        echo '<div id="scormtablecontainer">';
                        if ($candelete) {
                            // Start form
                            $strreallydel  = addslashes_js(get_string('deleteattemptcheck','scorm'));
                            echo '<form id="attemptsform" method="post" action="' . $reporturlwithdisplayoptions->out(true) .
                                    '" onsubmit="return confirm(\''.$strreallydel.'\');">';
                            echo '<input type="hidden" name="action" value="delete"/>';
                            echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                            echo '<div style="display: none;">';
                            echo html_writer::input_hidden_params($reporturlwithdisplayoptions);
                            echo '</div>';
                            echo '<div>';
                        }
                        $table->initialbars($totalinitials>20); // Build table rows
                    } else {
                        $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params);
                    }

                    if ($attempts) {
                        foreach($attempts as $scouser){
                            $row = array();
                            if (!empty($scouser->attempt)) {
                                $timetracks = scorm_get_sco_runtime($scorm->id, false, $scouser->userid, $scouser->attempt);
                            }
                            if (in_array('checkbox', $columns)){
                                if ($candelete && !empty($timetracks->start)) {
                                    $row[] = '<input type="checkbox" name="attemptid[]" value="'. $scouser->userid . ':' . $scouser->attempt . '" />';
                                } else if($candelete) {
                                    $row[] = '';
                                }
                            }
                            if (in_array('picture', $columns)){
                                $user = (object)array('id'=>$scouser->userid,
                                                      'picture'=>$scouser->picture,
                                                      'imagealt'=>$scouser->imagealt,
                                                      'email'=>$scouser->email,
                                                      'firstname'=>$scouser->firstname,
                                                      'lastname'=>$scouser->lastname);
                                $row[] = $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                            }
                            if (!$download){
                                $row[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$scouser->userid.'&amp;course='.$course->id.'">'.fullname($scouser).'</a>';
                            } else {
                                $row[] = fullname($scouser);
                            }
                            if (in_array('idnumber', $columns)){
                                $row[] = $scouser->idnumber;
                            }
                            if (empty($timetracks->start)) {
                                $row[] = '-';
                                $row[] = '-';
                                $row[] = '-';
                                $row[] = '-';
                            } else {
                                if (!$download) $row[] = '<a href="report.php?a='.$scorm->id.'&amp;user='.$scouser->userid.'&amp;attempt='.$scouser->attempt.'">'.$scouser->attempt.'</a>';
                                else $row[] = $scouser->attempt;
                                if ($download =='ODS' || $download =='Excel' ) $row[] = userdate($timetracks->start, get_string("strftimedatetime", "langconfig"));
                                else $row[] = userdate($timetracks->start);
                                if ($download =='ODS' || $download =='Excel' ) $row[] = userdate($timetracks->finish, get_string('strftimedatetime', 'langconfig'));
                                else $row[] = userdate($timetracks->finish);

                                $row[] = scorm_grade_user_attempt($scorm, $scouser->userid, $scouser->attempt);
                            }
                            // print out all scores of attempt
                            if ($scoes) {
                                foreach ($scoes as $sco) {
                                    if ($sco->launch!='') {
                                        if ($trackdata = scorm_get_tracks($sco->id,$scouser->userid,$scouser->attempt)) {
                                            if ($trackdata->status == '') {
                                                $trackdata->status = 'notattempted';
                                            }
                                            $strstatus = get_string($trackdata->status, 'scorm');
                                            // if raw score exists, print it
                                            if ($trackdata->score_raw != '') {
                                                $score = $trackdata->score_raw;
                                                // add max score if it exists
                                                if ($scorm->version == 'SCORM_1.3') {
                                                    $maxkey = 'cmi.score.max';
                                                } else {
                                                    $maxkey = 'cmi.core.score.max';
                                                }
                                                if (isset($trackdata->$maxkey)) {
                                                    $score .= '/'.$trackdata->$maxkey;
                                                }
                                            // else print out status
                                            } else {
                                                $score = $strstatus;
                                            }
                                            if (!$download) {
                                                $row[] = '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.$strstatus.'" /><br/>
                                                          <a href="report.php?b='.$sco->id.'&amp;user='.$scouser->userid.'&amp;attempt='.$scouser->attempt.
                                                         '" title="'.get_string('details','scorm').'">'.$score.'</a>';
                                            } else {
                                                $row[] = $score;
                                            }
                                        } else {
                                            // if we don't have track data, we haven't attempted yet
                                            $strstatus = get_string('notattempted', 'scorm');
                                            if (!$download) {
                                                $row[] = '<img src="'.$OUTPUT->pix_url('notattempted', 'scorm').'" alt="'.$strstatus.'" title="'.$strstatus.'" /><br/>'.$strstatus;
                                            } else {
                                                $row[] = $strstatus;
                                            }
                                        }
                                    }
                                }
                            }

                            if (!$download) {
                                $table->add_data($row);
                            } else if ($download == 'Excel' or $download == 'ODS') {
                                $colnum = 0;
                                foreach($row as $item){
                                    $myxls->write($rownum,$colnum,$item,$format);
                                    $colnum++;
                                }
                                $rownum++;
                            } else if ($download=='CSV') {
                                $text = implode("\t", $row);
                                echo $text." \n";
                            }
                        }
                        if (!$download) {
                            $table->finish_output();
                            if ($candelete) {
                                echo '<table id="commands">';
                                echo '<tr><td>';
                                echo '<a href="javascript:select_all_in(\'DIV\',null,\'scormtablecontainer\');">'.
                                        get_string('selectall', 'scorm').'</a> / ';
                                echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'scormtablecontainer\');">'.
                                        get_string('selectnone', 'scorm').'</a> ';
                                echo '&nbsp;&nbsp;';
                                echo '<input type="submit" value="'.get_string('deleteselected', 'quiz_overview').'"/>';
                                echo '</td></tr></table>';
                                // Close form
                                echo '</div>';
                                echo '</form>';
                            }
                            echo '</div>';
                            if (!empty($attempts)) {
                                echo '<table class="boxaligncenter"><tr>';
                                echo '<td>';
                                echo $OUTPUT->single_button(new moodle_url('/mod/scorm/report.php', $pageoptions + $displayoptions + array('download' => 'ODS')), get_string('downloadods'));
                                echo "</td>\n";
                                echo '<td>';
                                echo $OUTPUT->single_button(new moodle_url('/mod/scorm/report.php', $pageoptions + $displayoptions + array('download' => 'Excel')), get_string('downloadexcel'));
                                echo "</td>\n";
                                echo '<td>';
                                echo $OUTPUT->single_button(new moodle_url('/mod/scorm/report.php', $pageoptions + $displayoptions + array('download' => 'CSV')), get_string('downloadtext'));
                                echo "</td>\n";
                                echo "<td>";
                                echo "</td>\n";
                                echo '</tr></table>';
                        }
                    }
                    if (!$download) {
                        $mform->set_data($displayoptions + compact('detailedrep', 'pagesize'));
                        $mform->display();
                    }
                } else {
                    echo $OUTPUT->notification(get_string('noactivity', 'scorm'));
                }
                if ($download == 'Excel' or $download == 'ODS') {
                    $workbook->close();
                    exit;
                } else if ($download == 'CSV') {
                    exit;
                }
            } else {
                echo $OUTPUT->notification(get_string('noactivity', 'scorm'));
            }
        } else {
            if (!empty($user)) {
                // User SCORM report
                if ($scoes = $DB->get_records_select('scorm_scoes',"scorm=? ORDER BY id", array($scorm->id))) {
                    if (!empty($userdata)) {
                        echo $OUTPUT->box_start('generalbox boxaligncenter');
                        echo '<div class="mdl-align">'."\n";
                        $userrec = (object)array('id'=>$user);
                        echo $OUTPUT->user_picture($userrec, array('courseid'=>$course->id));
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user&amp;course=$course->id\">".
                             "$userdata->firstname $userdata->lastname</a><br />";
                        echo get_string('attempt','scorm').': '.$attempt;
                        echo '</div>'."\n";
                        echo $OUTPUT->box_end();

                        // Print general score data
                        $table = new html_table();
                        $table->head = array(get_string('title','scorm'),
                                             get_string('status','scorm'),
                                             get_string('time','scorm'),
                                             get_string('score','scorm'),
                                             '');
                        $table->align = array('left', 'center','center','right','left');
                        $table->wrap = array('nowrap', 'nowrap','nowrap','nowrap','nowrap');
                        $table->width = '80%';
                        $table->size = array('*', '*', '*', '*', '*');
                        foreach ($scoes as $sco) {
                            if ($sco->launch!='') {
                                $row = array();
                                $score = '&nbsp;';
                                if ($trackdata = scorm_get_tracks($sco->id,$user,$attempt)) {
                                    if ($trackdata->score_raw != '') {
                                        $score = $trackdata->score_raw;
                                    }
                                    if ($trackdata->status == '') {
                                        $trackdata->status = 'notattempted';
                                    }
                                    $detailslink = '<a href="report.php?b='.$sco->id.'&amp;user='.$user.'&amp;attempt='.$attempt.'" title="'.
                                                    get_string('details','scorm').'">'.get_string('details','scorm').'</a>';
                                } else {
                                    $trackdata->status = 'notattempted';
                                    $trackdata->total_time = '&nbsp;';
                                    $detailslink = '&nbsp;';
                                }
                                $strstatus = get_string($trackdata->status,'scorm');
                                $row[] = '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.
                                         $strstatus.'" />&nbsp;'.format_string($sco->title);
                                $row[] = get_string($trackdata->status,'scorm');
                                $row[] = scorm_format_duration($trackdata->total_time);
                                $row[] = $score;
                                $row[] = $detailslink;
                            } else {
                                $row = array(format_string($sco->title), '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;');
                            }
                            $table->data[] = $row;
                        }
                        echo html_writer::table($table);
                    }
                }
            } else {
                notice('No users to report');
            }
        }
    } else {
        // User SCO report
        if (!empty($userdata)) {
            echo $OUTPUT->box_start('generalbox boxaligncenter');
            //print_heading(format_string($sco->title));
            echo $OUTPUT->heading('<a href="'.$CFG->wwwroot.'/mod/scorm/player.php?a='.$scorm->id.'&amp;mode=browse&amp;scoid='.$sco->id.'" target="_new">'.format_string($sco->title).'</a>');
            echo '<div class="mdl-align">'."\n";
            $userrec = (object)array('id'=>$user);
            echo $OUTPUT->user_picture($userrec, array('courseid'=>$course->id));
            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user&amp;course=$course->id\">".
                 "$userdata->firstname $userdata->lastname</a><br />";
            $scoreview = '';
            if ($trackdata = scorm_get_tracks($sco->id,$user,$attempt)) {
                if ($trackdata->score_raw != '') {
                    $scoreview = get_string('score','scorm').':&nbsp;'.$trackdata->score_raw;
                }
                if ($trackdata->status == '') {
                    $trackdata->status = 'notattempted';
                }
            } else {
                $trackdata->status = 'notattempted';
                $trackdata->total_time = '';
            }
            $strstatus = get_string($trackdata->status,'scorm');
            echo '<img src="'.$OUTPUT->pix_url($trackdata->status, 'scorm').'" alt="'.$strstatus.'" title="'.
            $strstatus.'" />&nbsp;'.scorm_format_duration($trackdata->total_time).'<br />'.$scoreview.'<br />';
            echo '</div>'."\n";
            echo '<hr /><h2>'.get_string('details','scorm').'</h2>';

            // Print general score data
            $table = new html_table();
            $table->head = array(get_string('element','scorm'), get_string('value','scorm'));
            $table->align = array('left', 'left');
            $table->wrap = array('nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*');

            $existelements = false;
            if ($scorm->version == 'SCORM_1.3') {
                $elements = array('raw' => 'cmi.score.raw',
                                  'min' => 'cmi.score.min',
                                  'max' => 'cmi.score.max',
                                  'status' => 'cmi.completion_status',
                                  'time' => 'cmi.total_time');
            } else {
                $elements = array('raw' => 'cmi.core.score.raw',
                                  'min' => 'cmi.core.score.min',
                                  'max' => 'cmi.core.score.max',
                                  'status' => 'cmi.core.lesson_status',
                                  'time' => 'cmi.core.total_time');
            }
            $printedelements = array();
            foreach ($elements as $key => $element) {
                if (isset($trackdata->$element)) {
                    $existelements = true;
                    $printedelements[]=$element;
                    $row = array();
                    $row[] = get_string($key,'scorm');
                    switch($key) {
                        case 'status':
                            $row[] = $strstatus;
                        break;
                        case 'time':
                            $row[] = s(scorm_format_duration($trackdata->$element));
                        break;
                        default:
                            s($trackdata->$element);
                        break;
                    }
                    $table->data[] = $row;
                }
            }
            if ($existelements) {
                echo '<h3>'.get_string('general','scorm').'</h3>';
                echo html_writer::table($table);
            }

            // Print Interactions data
            $table = new html_table();
            $table->head = array(get_string('identifier','scorm'),
                                 get_string('type','scorm'),
                                 get_string('result','scorm'),
                                 get_string('student_response','scorm'));
            $table->align = array('center', 'center', 'center', 'center');
            $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*', '*', '*', '*');

            $existinteraction = false;

            $i = 0;
            $interactionid = 'cmi.interactions.'.$i.'.id';

            while (isset($trackdata->$interactionid)) {
                $existinteraction = true;
                $printedelements[]=$interactionid;
                $elements = array($interactionid,
                                  'cmi.interactions.'.$i.'.type',
                                  'cmi.interactions.'.$i.'.result',
                                  'cmi.interactions.'.$i.'.learner_response');
                $row = array();
                foreach ($elements as $element) {
                    if (isset($trackdata->$element)) {
                        $row[] = s($trackdata->$element);
                        $printedelements[]=$element;
                    } else {
                        $row[] = '&nbsp;';
                    }
                }
                $table->data[] = $row;

                $i++;
                $interactionid = 'cmi.interactions.'.$i.'.id';
            }
            if ($existinteraction) {
                echo '<h3>'.get_string('interactions','scorm').'</h3>';
                echo html_writer::table($table);
            }

            // Print Objectives data
            $table = new html_table();
            $table->head = array(get_string('identifier','scorm'),
                                 get_string('status','scorm'),
                                 get_string('raw','scorm'),
                                 get_string('min','scorm'),
                                 get_string('max','scorm'));
            $table->align = array('center', 'center', 'center', 'center', 'center');
            $table->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
            $table->width = '100%';
            $table->size = array('*', '*', '*', '*', '*');

            $existobjective = false;

            $i = 0;
            $objectiveid = 'cmi.objectives.'.$i.'.id';

            while (isset($trackdata->$objectiveid)) {
                $existobjective = true;
                $printedelements[]=$objectiveid;
                $elements = array($objectiveid,
                                  'cmi.objectives.'.$i.'.status',
                                  'cmi.objectives.'.$i.'.score.raw',
                                  'cmi.objectives.'.$i.'.score.min',
                                  'cmi.objectives.'.$i.'.score.max');
                $row = array();
                foreach ($elements as $element) {
                    if (isset($trackdata->$element)) {
                        $row[] = s($trackdata->$element);
                        $printedelements[]=$element;
                    } else {
                        $row[] = '&nbsp;';
                    }
                }
                $table->data[] = $row;

                $i++;
                $objectiveid = 'cmi.objectives.'.$i.'.id';
            }
            if ($existobjective) {
                echo '<h3>'.get_string('objectives','scorm').'</h3>';
                echo html_writer::table($table);
            }
            $table = new html_table();
            $table->head = array(get_string('element','scorm'), get_string('value','scorm'));
            $table->align = array('left', 'left');
            $table->wrap = array('nowrap', 'wrap');
            $table->width = '100%';
            $table->size = array('*', '*');

            $existelements = false;

            foreach($trackdata as $element => $value) {
                if (substr($element,0,3) == 'cmi') {
                    if (!(in_array ($element, $printedelements))) {
                        $existelements = true;
                        $row = array();
                        $row[] = get_string($element,'scorm') != '[['.$element.']]' ? get_string($element,'scorm') : $element;
                        if (strpos($element, '_time') === false) {
                            $row[] = s($value);
                        } else {
                            $row[] = s(scorm_format_duration($value));
                        }
                        $table->data[] = $row;
                    }
                }
            }
            if ($existelements) {
                echo '<h3>'.get_string('othertracks','scorm').'</h3>';
                echo html_writer::table($table);
            }
            echo $OUTPUT->box_end();
        } else {
            print_error('missingparameter');
        }
    }


    if (empty($noheader)) {
        echo $OUTPUT->footer();
    }

