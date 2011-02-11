<?php

/**
 * Makes our changes to the CSS
 *
 * @param string $css
 * @param theme_config $theme
 * @return string
 */
function nz_schools_process_css($css, $theme) {
	global $CFG;

	// Set the link color
	if (!empty($theme->settings->colour1)) {
		$colour1 = $theme->settings->colour1;
	} else {
		$colour1 = null;
	}
	$css = nz_schools_set_colour1($css, $colour1);

	// Set the link color
	if (!empty($theme->settings->colour2)) {
		$colour2 = $theme->settings->colour2;
	} else {
		$colour2 = null;
	}
	$css = nz_schools_set_colour2($css, $colour2);

	// Set the link color
	if (!empty($theme->settings->colour3)) {
		$colour3 = $theme->settings->colour3;
	} else {
		$colour3 = null;
	}
	$css = nz_schools_set_colour3($css, $colour3);

	// Set whether the logo has a transparent background
	if (isset($theme->settings->plainbg) && $theme->settings->plainbg ){
		$replacement = '#page .page_effect {background:none;}';
	} else {
		$replacement = '#page .page-effect {background:url(';
		$imageurl = $theme->pix_url('pgbg-grad-dark', 'theme')->out(false);
		$imageurl = str_replace("$CFG->httpswwwroot/theme/", '', $imageurl);
		$replacement .= $imageurl;
		$replacement .= ') top left repeat-x;}';
	}
	$tag = '[[setting:plainbg]]';
	$css = str_replace( $tag, $replacement, $css);
	return $css;
}

/**
 * Sets the link color variable in CSS
 *
 */
function nz_schools_set_colour1($css, $colour1) {
	$tag = '[[setting:colour1]]';
	$replacement = $colour1;
	if (is_null($replacement)) {
		$replacement = '#555454';
	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function nz_schools_set_colour2($css, $colour2) {
	$tag = '[[setting:colour2]]';
	$replacement = $colour2;
	if (is_null($replacement)) {
		$replacement = '#a1a1a1';
	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}

function nz_schools_set_colour3($css, $colour3) {
	$tag = '[[setting:colour3]]';
	$replacement = $colour3;
	if (is_null($replacement)) {
		$replacement = '#a1a1a1';
	}
	$css = str_replace($tag, $replacement, $css);
	return $css;
}
