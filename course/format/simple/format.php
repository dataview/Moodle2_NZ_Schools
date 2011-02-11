<?php // $Id$
/**
 * A visually simple course format, desiged for younger children
 *
 * @copyright Catalyst IT Ltd.
 * @author Matt Clarkson <mattc@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


    $streditsummary  = get_string('editsummary');
    $stradd          = get_string('add');
    $stractivities   = get_string('activities');
    $strgroups       = get_string('groups');
    $strgroupmy      = get_string('groupmy');
    $strhideblocks   = get_string('hideblocks','format_simple');
    $strshowblocks   = get_string('showblocks','format_simple');
    $strnext         = get_string('next');
    $strprevious     = get_string('previous');

    $editing         = $PAGE->user_is_editing();

    if ($editing) {
        $strweekhide = get_string('weekhide','format_simple');
        $strweekshow = get_string('weekshow','format_simple');
        $strmoveback   = get_string('moveback','format_simple');
        $strmoveforward = get_string('moveforward','format_simple');
    }

    $context = get_context_instance(CONTEXT_COURSE, $course->id);
/* Internet Explorer min-width fix. (See theme/standard/styles_layout.css: min-width for Firefox.)
   Window width: 800px, Firefox 763px, IE 752px. (Window width: 640px, Firefox 602px, IE 588px.)
*/
?>

<!--[if IE]>
  <style type="text/css">
  .simplecss-format { width: expression(document.body.clientWidth < 800 ? "752px" : "auto"); }
  #wrapper {_height: 1px;} /* Hack for peek-a-boo bug
}
  </style>
<![endif]-->

<script type="text/javascript">
//<![CDATA[

    var courseid      = <?php echo  $course->id ?>;
    var isediting     = <?php echo  empty($editing) ? 0:1;?>;
    var strhideblocks = '<?php echo  $strhideblocks ?>';
    var strshowblocks = '<?php echo  $strshowblocks ?>';
    var strnext       = '<?php echo  $strnext ?>';
    var strprevious   = '<?php echo  $strprevious ?>';
//]]>
</script>
<?php
    $PAGE->requires->yui2_lib('yahoo');
    $PAGE->requires->yui2_lib('event');
    $PAGE->requires->yui2_lib('dom');
    $PAGE->requires->yui2_lib('cookie');
    $PAGE->requires->js('/course/format/simple/simple.js');

/// Layout the whole page as three big columns (was, id="layout-table")
    echo '<div class="simplecss-format">';

///// The left column ...
//
//    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
//        echo '<div id="left-column">';
//        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
//        echo '</div>';
//    }
//
///// The right column, BEFORE the middle-column.
//    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
//        echo '<div id="right-column">';
//        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
//        echo '</div>';
//    }

/// Start main column
    echo '<div id="middle-column">'. skip_main_destination();

    // Note, an ordered list would confuse - "1" could be the clipboard or summary.
    echo "<ul class='simplecss'>\n";

/// If currently moving a file then show the current clipboard
    if (ismoving($course->id)) {
        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', addslashes($USER->activitycopyname)));
        $strcancel= get_string('cancel');
        echo '<li class="clipboard">';
        echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
        echo "</li>\n";
    }

/// Create any missing sections
    for ($i=count($sections);$i<$course->numsections;$i++) {
        $newsection = new stdClass();

        $newsection->course = $course->id;   // Create a new section structure
        $newsection->section = $i;
        $newsection->summary = '';
        $newsection->name = '';
        $newsection->visible = 1;
        if (!$newsection->id = $DB->insert_record('course_sections', $newsection)) {
            notify('Error inserting new topic!');
        }
        $sections[$i] = $newsection;
    }

    $currentsection = 0;

    for ($i=0;$i<$course->numsections;$i++) {
        $thissection = $sections[$i];

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

        if (!empty($displaysection) and $displaysection != $section) {  // Check this week is visible
            $section++;
            continue;
        }

        if ($showsection) {

            if ($currentsection == $section) {
                $sectionstyle = ' current';
            } else {
                $sectionstyle = '';
            }
            if (!$thissection->visible) {
                $sectionstyle .= ' hidden';
            }

            echo '<li id="section-'.$section.'" class="section main'.$sectionstyle.'" >'; //'<div class="left side">&nbsp;</div>';

            // Note, 'right side' is BEFORE content.
            echo '<div class="right side">';


            if ($PAGE->user_is_editing($course->id) && $thissection->section != 0 && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                if ($thissection->visible) {        // Show the hide/show eye
                    echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strweekhide.'">'.
                         '<img src="'.$OUTPUT->pix_url('i/hide').'" class="icon hide" alt="'.$strweekhide.'" /></a><br />';
                } else {
                    echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$strweekshow.'">'.
                         '<img src="'.$OUTPUT->pix_url('i/show').'" class="icon hide" alt="'.$strweekshow.'" /></a><br />';
                }
                if ($section > 1) {                       // Add a arrow to move section up
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.sesskey().'#section-'.($section-1).'" title="'.$strmoveback.'">'.
                         '<img src="'.$OUTPUT->pix_url('t/left').'" class="icon up" alt="'.$strmoveback.'" /></a><br />';
                }

                if ($section+1 < $course->numsections) {    // Add a arrow to move section down
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.sesskey().'#section-'.($section+1).'" title="'.$strmoveforward.'">'.
                         '<img src="'.$OUTPUT->pix_url('t/right').'$" class="icon down" alt="'.$strmoveforward.'" /></a><br />';
                }
            }
            echo '</div>';


            echo '<div class="content">';
            if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                print_heading($currenttext.$weekperiod.' ('.get_string('notavailable').')', null, 3, 'weekdates');

            } else {
                echo $OUTPUT->heading($thissection->name, 3, 'sectionname');

                echo '<div class="summary">';
                $summaryformatoptions->noclean = true;
                echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                if ($PAGE->user_is_editing($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                         '<img src="'.$OUTPUT->pix_url('t/edit').'" class="icon edit" alt="'.$streditsummary.'" /></a><br /><br />';
                }
                echo '</div>';

                print_section($course, $thissection, $mods, $modnamesused);


                if ($PAGE->user_is_editing($course->id)) {
                    print_section_add_menus($course, $section, $modnames);
                }
            }
            echo '</div>';
            echo "</li>\n";
        }

        $section++;
    }
    echo "</ul>\n";
    echo '</div>';

    echo '</div>';
    echo '<div class="clearer"></div>';

?>
