<?php

require('../../config.php');

?>

var $ = YAHOO.util.Dom.get;

function previewCourseIcon(courseId,type) {
    var iconimg = $('iconpreview');
    var iconselect = $('id_icon');
    iconimg.src = '<?php echo $CFG->wwwroot ?>/local/courseicon/icon.php?id=' + courseId + '&size=large&type='+type+'&icon='+iconselect.value;

	// Prevent caching from showing an old version of the custom icon
	if ( iconselect.value == 'custom' ){
		iconimg.src = iconimg.src + '&rev=' + (new Date()).getTime();
	}

    return true;
}
