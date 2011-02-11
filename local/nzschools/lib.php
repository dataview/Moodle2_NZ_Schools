<?php

/**
 * Resize and save logo used in theme
 * @param $tempfilepath Path to temp file of image
 * @return string name of final file
 */
function local_nzschools_process_logo($tempfilepath) {
    global $CFG;

    $filename = local_nzschools_resize_image($tempfilepath, 'logo', get_context_instance(CONTEXT_SYSTEM), 'local_nzschools', 'logo', 0, '/', 400, 75);
    $filename = basename($filename);
    set_config('logofile', $filename);

    return($filename);
}

/**
 * Resize an image to fit within the given rectangle, maintaining aspect ratio
 * @param $originalfile Path to the original file (as a temp file)
 * @param $destname Filename to use for final file
 * @param $context Context to save file in
 * @param $component Component to save file in
 * @param $filearea Filearea to save file in
 * @param $itemid ID to save file with
 * @param $filepath Path to save file at
 * @param int $newwidth Width to resize to
 * @param int $newheight Height to resize to
 * @param string $forcetype If provided, force conversion to this format (should be png or jpeg)
 *
 * @global $CFG
 * @return string Final filename
 */
function local_nzschools_resize_image($originalfile, $destname, $context, $component, $filearea, $itemid, $filepath, $newwidth, $newheight, $forcetype = false) {
    global $CFG;

    require_once($CFG->libdir.'/gdlib.php');

    if(!(is_file($originalfile))) {
        return false;
    }

    if (empty($CFG->gdversion)) {
        return false;
    }

    $imageinfo = GetImageSize($originalfile);
    if (empty($imageinfo)) {
        return false;
    }

    $image = new stdClass;

    $image->width  = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->type   = $imageinfo[2];

    $ratiosrc = $image->width / $image->height;

    if ($newwidth/$newheight > $ratiosrc) {
        $newwidth = $newheight * $ratiosrc;
    } else {
        $newheight = $newwidth / $ratiosrc;
    }

    switch ($image->type) {
        case IMAGETYPE_GIF:
            if (function_exists('ImageCreateFromGIF')) {
                $im = ImageCreateFromGIF($originalfile);
                $outputformat = 'png';
            } else {
                notice('GIF not supported on this server');
                return false;
            }
            break;
        case IMAGETYPE_JPEG:
            if (function_exists('ImageCreateFromJPEG')) {
                $im = ImageCreateFromJPEG($originalfile);
                $outputformat = 'jpeg';
            } else {
                notice('JPEG not supported on this server');
                return false;
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('ImageCreateFromPNG')) {
                $im = ImageCreateFromPNG($originalfile);
                $outputformat = 'png';
            } else {
                notice('PNG not supported on this server');
                return false;
            }
            break;
        default:
            return false;
    }

    if ($forcetype) {
        $outputformat = $forcetype;
    }

    if (function_exists('ImageCreateTrueColor') and $CFG->gdversion >= 2) {
        $im1 = ImageCreateTrueColor($newwidth,$newheight);
    } else {
        $im1 = ImageCreate($newwidth, $newheight);
    }
    if ($outputformat == 'png') {

        // Turn off transparency blending (temporarily)
        imagealphablending($im1, false);

        // Create a new transparent color for image
        $color = imagecolorallocatealpha($im1, 0, 0, 0, 127);

        // Completely fill the background of the new image with allocated color.
        imagefill($im1, 0, 0, $color);

        // Restore transparency blending
        imagesavealpha($im1, true);
    }
    ImageCopyBicubic($im1, $im, 0, 0, 0, 0, $newwidth, $newheight, $image->width, $image->height);

    $fs = get_file_storage();

    $logo = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>'/');

    switch($outputformat) {
        case 'png':
            if ( function_exists('ImagePng') ){
                $imagefnc = 'ImagePng';
                $imageext = '.png';
                $filters = PNG_NO_FILTER;
                $quality = 1;
            } else {
                debugging('PNG not supported on this server, please fix server configuration.');
            }
            break;
        case 'jpeg':
            if ( function_exists('ImageJpeg') ){
                $imagefnc = 'ImageJpeg';
                $imageext = '.jpg';
                $filters = null;
                $quality = 90;
            } else {
                debugging( 'JPEG not supported on this server, please fix server configuration.');
            }
            break;
        default:
            return false;
    }

    ob_start();
    if (!$imagefnc($im1, null, $quality, $filters)){
        ob_end_clean();
        return false;
    }
    $data = ob_get_clean();
    ImageDestroy($im1);
    $destname .= $imageext;
    $logo['filename'] = $destname;
    $fs->delete_area_files($context->id, $component, $filearea);
    $fs->create_file_from_string($logo, $data);

    return $destname;
}


/**
 * Create course categories
 *
 * @param int $fromyear starting year of school
 * @param int $toyear   ending year of school
 * @return bool
 */
function local_nzschools_createcats($fromyear, $toyear) {
    global $DB;

    $cats = array(array('name'      => 'courses',
                        'visible'   => 1),
                  array('name'      => 'sandpits',
                        'visible'   => 0),
                  array('name'      => 'templates',
                        'visible'   => 0));


    $newcategory = new stdClass();
    $newcategory->description = '';
    $newcategory->theme = '';
    $newcategory->parent = 0;

    // Delete the default cat moodle creates if it's empty
    if ($misccat = $DB->get_record('course_categories', array('name'=>'Miscellaneous'))) {
        if(!$DB->record_exists('course', array('category'=>$misccat->id))) {
            $DB->delete_records('course_categories', array('id'=>$misccat->id));
        }
    }

    $i = 1;
    foreach($cats as $cat) {
        $newcategory->name = get_string($cat['name'], 'local_nzschools');
        $newcategory->visible = $cat['visible'];
        $sortorder = $i++;

        if (!$DB->record_exists('course_categories', array('name'=>$newcategory->name))) {
            if (!$newcategory->id = $DB->insert_record('course_categories', $newcategory)) {
                error("Could not insert the new category '$newcategory->name' ");
            }
            $newcategory->context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
            mark_context_dirty($newcategory->context->path);

            if ($cat['name'] == 'templates') {
                set_config('templatecat', $newcategory->id);
            }
        }

    }

    $coursecatid = $DB->get_field('course_categories', 'id', array('name'=>get_string('courses', 'local_nzschools')));


    for ( $year=$fromyear; $year<=$toyear; $year++ ){
        $name = get_string('catyear', 'local_nzschools', $year);
        $newcategory->name = $name;
        $newcategory->sortorder = $i++;
        $newcategory->parent = $coursecatid;
        $newcategory->visible = 1;
        $newcategory->icon = 'year'.$year.'.png';

        if (!$DB->record_exists('course_categories', array('name'=>$name))) {
            if (!$newcategory->id = $DB->insert_record('course_categories', $newcategory)) {
                error("Could not insert the new category '$newcategory->name' ");
            }
            $newcategory->context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
            mark_context_dirty($newcategory->context->path);

        }
    }

    fix_course_sortorder(); // Required to build course_categories.depth and .path.
}


/**
 * Restore course templates silently
 *
 * @param string $dir Directory containing moodle course backups
 * @global $CFG
 * @global $SESSION
 */
function local_nzschools_restoretemplates($dir) {
    global $CFG, $SESSION, $DB;

    if (!is_dir($dir)) {
        print_error('templatedirnotfound', 'local_nzschools', $dir);
    }

    if (!$DB->record_exists('course_categories', array('id'=>$CFG->templatecat))) {
        print_error('templatecatnotfound', 'local_nzschools', $CFG->templatecat);
    }

    $d = dir($dir);
    while (($entry = $d->read()) !== false) {
        if (!is_file($dir.'/'.$entry)){
            continue;
        }

        $course = new stdClass();
        $course->category = $CFG->templatecat;
        $course->fullname = $entry;
        $course->shortname = $entry;
        $course->idnumber = $entry;
        $course->format = 'weeks';
        $course->numsections = 20;

        require_once($CFG->dirroot.'/course/lib.php');
        if ($destcourse = create_course($course)) {
            $origdebug = $CFG->debug;
            $CFG->debug = DEBUG_MINIMAL;
            error_reporting($CFG->debug);
            local_nzschools_import_backup_file_silently($dir .'/'.$entry, $destcourse->id, true);
            error_reporting($origdebug);
            $CFG->debug = $origdebug;
        }
    }
}


/** this function will restore an entire backup.zip into the specified course
 * using standard moodle backup/restore functions, but silently.
 * @param string $pathtofile the absolute path to the backup file.
 * @param int $destinationcourse the course id to restore to.
 * @param boolean $emptyfirst whether to delete all coursedata first.
 */
function local_nzschools_import_backup_file_silently($pathtofile,$destinationcourse,$emptyfirst=false) {
    global $CFG, $USER;
    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    $tmpdir = time();
    $fulltmpdir = $CFG->dataroot . '/temp/backup/' . $tmpdir;

    $zp = new zip_packer();
    $zp->extract_to_pathname($pathtofile, $fulltmpdir);
    $rc = new restore_controller(
        $tmpdir,
        $destinationcourse,
        backup::INTERACTIVE_NO,
        backup::MODE_AUTOMATED,
        $USER->id,
        backup::TARGET_EXISTING_DELETING
    );

    $s =& $rc->get_plan()->get_setting('overwrite_conf');
    if ($emptyfirst){
        $s->set_value(1);
    } else {
        $s->set_value(0);
    }

    // todo: If you were going to turn this into a generalized import_backup_file_silently function,
    // you'd want to add a $settings array variable. To see the available settings, use something
    // like this:
    //$settings = $rc->get_plan()->get_settings();
    //foreach( $settings as &$s ){
    //    echo $s->get_name() . "\n";
    //}

    $rc->execute_precheck(false);
    $rc->execute_plan();
}

/**
 * Select a forground colour based on the background colour
 *
 * @param string $bg background colour
 * @param string $light_option colour to return if background is dark
 * @param string $dark_option colour to return if background is light
 * @return string forground colour
 */
function local_nzschools_fg_colour($bg, $light_option = 'FFFFFF', $dark_option = '333333') {
    $bg = hexdec($bg);

    //rgb conversion
    $r = 0xFF & $bg >> 0x10;
    $g = 0xFF & $bg >> 0x08;
    $b = 0xFF & $bg;

    // Calculate brightness using a weighted distance between colours
    $brightness = sqrt( pow($r,2) * .241 + pow($g,2) * .691 + pow($b,2) *.068);
    if ($brightness < 165) { // an arbitrary cutoff point for choosing a fg colour
        return ($light_option);
    } else {
        return ($dark_option);
    }

}