<?php

/// This page lists all the instances of book in a particular course

require dirname(__FILE__).'/../../config.php';
require_once($CFG->dirroot.'/mod/book/locallib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

// =========================================================================
// security checks START - teachers and students view
// =========================================================================

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);

//check all variables
unset($id);

// =========================================================================
// security checks END
// =========================================================================

/// Get all required strings
$strbooks = get_string('modulenameplural', 'book');
$strbook  = get_string('modulename', 'book');


$navlinks = array();
$navlinks[] = array('name' => $strbooks, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header_simple($strbooks, '', $navigation, '', '', true, '', navmenu($course));

add_to_log($course->id, 'book', 'view all', 'index.php?id='.$course->id, '');

/// Get all the appropriate data
if (!$books = get_all_instances_in_course('book', $course)) {
    notice('There are no books', '../../course/view.php?id='.$course->id);
    die;
}

/// Print the list of instances
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic  = get_string('topic');
$strsummary = get_string('summary');
$strchapters  = get_string('chapterscount', 'book');

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname, $strsummary, $strchapters);
    $table->align = array ('center', 'left', 'left', 'center');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname, $strsummary, $strchapters);
    $table->align = array ('center', 'left', 'left', 'center');
} else {
    $table->head  = array ($strname, $strsummary, $strchapters);
    $table->align = array ('left', 'left', 'left');
}

$currentsection = '';
foreach ($books as $book) {
    $nocleanoption = new object();
    $nocleanoption->noclean = true;
    $book->summary = format_text($book->intro, $book->introformat, $nocleanoption, $course->id);
    $book->summary = '<span style="font-size:x-small;">'.$book->summary.'</span>';

    if (!$book->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$book->coursemodule.'">'.$book->name.'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='.$book->coursemodule.'">'.$book->name.'</a>';
    }

    $count = $DB->count_records('book_chapters', array('bookid'=>$book->id, 'hidden'=>'0'));

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $printsection = '';
        if ($book->section !== $currentsection) {
            if ($book->section) {
                $printsection = $book->section;
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $book->section;
        }
        $table->data[] = array ($printsection, $link, $book->summary, $count);
    } else {
        $table->data[] = array ($link, $book->summary, $count);
    }
}

echo '<br />';
print_table($table);

print_footer($course);

