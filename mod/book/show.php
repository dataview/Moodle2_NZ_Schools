<?php
// This file is part of Book module for Moodle - http://moodle.org/
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
 * Show/hide book chapter
 *
 * @package    mod
 * @subpackage book
 * @copyright  2004-2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require dirname(__FILE__).'/../../config.php';
require_once($CFG->dirroot.'/mod/book/locallib.php');

$id        = required_param('id', PARAM_INT);        // Course Module ID
$chapterid = required_param('chapterid', PARAM_INT); // Chapter ID

$cm = get_coursemodule_from_id('book', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
require_sesskey();

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/book:edit', $context);

$PAGE->set_url('/mod/book/show.php', array('id'=>$id, 'chapterid'=>$chapterid));

$chapter = $DB->get_record('book_chapters', array('id'=>$chapterid, 'bookid'=>$book->id), '*', MUST_EXIST);


///switch hidden state
$chapter->hidden = $chapter->hidden ? 0 : 1;

// update record
$DB->update_record('book_chapters', $chapter);

///change visibility of subchapters too
if (!$chapter->subchapter) {
    $chapters = $DB->get_records('book_chapters', array('bookid'=>$book->id), 'pagenum', 'id, subchapter, hidden');
    $found = 0;
    foreach($chapters as $ch) {
        if ($ch->id == $chapter->id) {
            $found = 1;
        } else if ($found and $ch->subchapter) {
            $ch->hidden = $chapter->hidden;
            $DB->update_record('book_chapters', $ch);
        } else if ($found) {
            break;
        }
    }
}

add_to_log($course->id, 'course', 'update mod', '../mod/book/view.php?id='.$cm->id, 'book '.$book->id);
add_to_log($course->id, 'book', 'update', 'view.php?id='.$cm->id, $book->id, $cm->id);

book_check_structure($book->id);
redirect('view.php?id='.$cm->id.'&chapterid='.$chapter->id);

