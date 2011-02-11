<?php
require('config.php');
ob_start();
$colour1 = optional_param('colour1', !empty($CFG->theme_colour1) ? $CFG->theme_colour1 : '555454', PARAM_TEXT);
$colour2 = optional_param('colour2', !empty($CFG->theme_colour2) ? $CFG->theme_colour2 : 'A1A1A1', PARAM_TEXT);
$colour3 = optional_param('colour3', !empty($CFG->theme_colour3) ? $CFG->theme_colour3 : 'A1A1A1', PARAM_TEXT);
$plainbg = optional_param('plainbg', empty($CFG->theme_plainbg) ? 'false' : 'true', PARAM_TEXT);
?>
/* Primary Colour */
#page, body {
  background: #<?php echo  $colour1 ?>;
}

<?php
  if ($plainbg == 'true') {
    echo '.page-effect {background: none;}';
  } else {
    echo '.page-effect {background: url(pix/pgbg-grad-dark.png) top left repeat-x;}';
  }
?>

#header h1, #header-home h1 {
  padding-top: 25px;
  color: #<?php echo local_nzschools_fg_colour($colour1) ?>;
}

#footer {
  color: #<?php echo  local_nzschools_fg_colour($colour1) ?>;
}

.logininfo a, #footer p.helplink a {
  color:#<?php echo  local_nzschools_fg_colour($colour1, 'CCCCCC', '0077FF') ?>;
}

/* Secondary Colour */
.navbar, .navbar-home {
  background: #<?php echo  $colour2 ?>;
  color: #<?php echo  local_nzschools_fg_colour($colour2) ?>;
}


.navbar .breadcrumb a, .navbar .breadcrumb {
  color: #<?php echo  local_nzschools_fg_colour($colour2) ?>;
}


/* Tertiary Colour */
.sideblock .header {
  background: #<?php echo  $colour3 ?>;
}

.sideblock h2 {
  color: #<?php echo  local_nzschools_fg_colour($colour3) ?>;
}

.simplebutton {
    background-color: #<?php echo  $colour2 ?>;
}
<?php
$css = ob_get_clean();
$length = strlen($css);
$hash = md5($css);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $hash) !== false) {
    header('HTTP/1.1 304 Not Modified');
} else {
    header('Cache-Control: private');
    header('Pragma: ');
    header('Expires: ');
    header("ETag: \"$hash\"");
    header("Accept-Ranges: bytes");
    header("Content-Length: $length");
    header("Content-Type: text/css");
    header('Content-Disposition: inline; filename="dynamic.css";');
    echo $css;
}
