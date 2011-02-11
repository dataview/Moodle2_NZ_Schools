<?php

// This file is part of Moodle - http://moodle.org/
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
 *  Media plugin filtering
 *
 *  This filter will replace any links to a media file with
 *  a media plugin that plays that media inline
 *
 * @package    filter
 * @subpackage mediaplugin
 * @copyright  2004 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

class filter_mediaplugin extends moodle_text_filter {
    private $eolas_fix_applied = false;
    function filter($text, array $options = array()) {
        global $CFG, $PAGE;
        // You should never modify parameters passed to a method or function, it's BAD practice. Create a copy instead.
        // The reason is that you must always be able to refer to the original parameter that was passed.
        // For this reason, I changed $text = preg_replace(..,..,$text) into $newtext = preg.... (NICOLAS CONNAULT)
        // Thanks to Pablo Etcheverry for pointing this out! MDL-10177

        // We're using the UFO technique for flash to attain XHTML Strict 1.0
        // See: http://www.bobbyvandersluis.com/ufo/
        if (!is_string($text)) {
            // non string data can not be filtered anyway
            return $text;
        }
        $newtext = $text; // fullclone is slow and not needed here

        if (!empty($CFG->filter_mediaplugin_enable_mp3)) {
            $search =   '/<a[^>]*?href="([^<]+\.mp3)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_mp3_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_ogg)) {
            $search =   '/<a[^>]*?href="([^<]+\.ogg)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_ogg_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_ogv)) {
            $search =   '/<a[^>]*?href="([^<]+\.ogv)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_ogv_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_swf)) {
            $search = '/<a[^>]*?href="([^<]+\.swf)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_swf_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_flv)) {
            $search = '/<a[^>]*?href="([^<]+\.flv)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_flv_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_mov)) {
            $search = '/<a[^>]*?href="([^<]+\.mov)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_qt_callback', $newtext);

            $search = '/<a[^>]*?href="([^<]+\.mp4)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_qt_callback', $newtext);

            $search = '/<a[^>]*?href="([^<]+\.m4v)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_qt_callback', $newtext);

            $search = '/<a[^>]*?href="([^<]+\.m4a)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_qt_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_wmv)) {
            $search = '/<a[^>]*?href="([^<]+\.wmv)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_wmp_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_mpg)) {
            $search = '/<a[^>]*?href="([^<]+\.mpe?g)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_qt_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_avi)) {
            $search = '/<a[^>]*?href="([^<]+\.avi)(\?d=([\d]{1,3}%?)x([\d]{1,3}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_wmp_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_ram)) {
            $search = '/<a[^>]*?href="([^<]+\.ram)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_real_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_rpm)) {
            $search = '/<a[^>]*?href="([^<]+\.rpm)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_real_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_rm)) {
            $search = '/<a[^>]*?href="([^<]+\.rm)"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_real_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_youtube)) {
            //see MDL-23903 for description of recent changes to this regex
            //$search = '/<a.*?href="([^<]*)youtube.com\/watch\?v=([^"]*)"[^>]*>(.*?)<\/a>/is';
            $search = '/<a[^>]*href="([^<]*?)youtube.com\/watch\?v=([^"]*)"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_youtube_callback', $newtext);

            $search = '/<a[^>]*href="([^<]*)youtube.com\/v\/([^"]*)"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_youtube_callback', $newtext);

            $search = '/<a(\s+[^>]+?)?\s+href="((([^"]+)youtube\.com)\/view_play_list\?p=([^"]*))"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_youtube_playlist_callback', $newtext);
        }

        if (!empty($CFG->filter_mediaplugin_enable_img)) {
            $search = '/<a[^>]*?href="([^<]+\.jpg)"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_img_callback', $newtext);
            $search = '/<a[^>]*?href="([^<]+\.png)"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_img_callback', $newtext);
            $search = '/<a[^>]*?href="([^<]+\.gif)"[^>]*>(.*?)<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_mediaplugin_img_callback', $newtext);
        }

        if (empty($newtext) or $newtext === $text) {
            // error or not filtered
            unset($newtext);
            return $text;
        }

        if (!$this->eolas_fix_applied) {
            $PAGE->requires->js('/filter/mediaplugin/eolas_fix.js');
            $this->eolas_fix_applied = true;
        }

        return $newtext;
    }
}

///===========================
/// callback filter functions

function filter_mediaplugin_mp3_callback($link) {
    global $CFG, $OUTPUT, $PAGE;

    $c = $OUTPUT->filter_mediaplugin_colors();   // You can set this up in your theme/xxx/config.php

    static $count = 0;
    $count++;
    $id = 'filter_mp3_'.time().$count; //we need something unique because it might be stored in text cache

    $url = addslashes_js($link[1]);

    $playerpath = $CFG->wwwroot.'/filter/mediaplugin/mp3player.swf';
    $audioplayerpath = $CFG->wwwroot .'/filter/mediaplugin/flowplayer.audio.swf';
    $colors = explode('&', $c);
    $playercolors = array();
    foreach ($colors as $color) {
        $color = explode('=', $color);
        $playercolors[$color[0]] = $color[1];
    }

    $output = <<<OET
    <span class="mediaplugin mediaplugin_mp3" id="$id"></span>
    <noscript><div>
    <object width="100" height="15" id="nonjsmp3plugin" name="undefined" data="$playerpath" type="application/x-shockwave-flash">
    <param name="movie" value="$playerpath" />
    <param name="allowfullscreen" value="false" />
    <param name="allowscriptaccess" value="always" />
    <param name="flashvars" value='config={"plugins": {"controls": {
                                                            "fullscreen": false,
                                                            "height": 15,
                                                            "autoHide": false,
                                                            "all": false,
                                                            "play": true,
                                                            "pause": true,
                                                            "scrubber": true
                                                            },
                                                       "audio": {"url": "$audioplayerpath"}
                                                      },
                                           "clip":{"url":"$url",
                                                   "autoPlay": false},
                                           "content":{"url":"$playerpath"}}}' />
    </object>
    </div></noscript>
OET;

    $jsoutput = create_flowplayer($id, $url, 'mp3', $playercolors);
    $output .= $jsoutput;

    return $output;
}

function filter_mediaplugin_ogg_callback($link) {
    global $CFG, $OUTPUT, $PAGE;

    static $count = 0;
    $count++;
    $id = 'filter_ogg_'.time().$count; //we need something unique because it might be stored in text cache

    $url = addslashes_js($link[1]);
    $printlink = html_writer::link($url, get_string('oggaudio', 'filter_mediaplugin'));
    $unsupportedplugins = get_string('unsupportedplugins', 'filter_mediaplugin', $printlink);
    $output = <<<OET
    <audio id="$id" src="$url" controls="true" width="100">
        $unsupportedplugins
    </audio>
OET;

    return $output;
}

function filter_mediaplugin_ogv_callback($link) {
    global $CFG, $OUTPUT, $PAGE;

    static $count = 0;
    $count++;
    $id = 'filter_ogv_'.time().$count; //we need something unique because it might be stored in text cache

    $url = addslashes_js($link[1]);
    $printlink = html_writer::link($url, get_string('ogvvideo', 'filter_mediaplugin'));
    $unsupportedplugins = get_string('unsupportedplugins', 'filter_mediaplugin', $printlink);
    $output = <<<OET
    <video id="$id" src="$url" controls="true" width="600" >
        $unsupportedplugins
    </video>
OET;

    return $output;
}

function filter_mediaplugin_swf_callback($link) {
    global $PAGE;
    static $count = 0;
    $count++;
    $id = 'filter_swf_'.time().$count; //we need something unique because it might be stored in text cache

    $width  = empty($link[3]) ? '400' : $link[3];
    $height = empty($link[4]) ? '300' : $link[4];
    $url = addslashes_js($link[1]);

    $args = Array();
    $args['movie'] = $url;
    $args['width'] = $width;
    $args['height'] = $height;
    $args['majorversion'] = 6;
    $args['build'] = 40;
    $args['allowscriptaccess'] = 'never';
    $args['quality'] = 'high';

    $jsoutput = create_ufo_inline($id, $args);

    $output = $link[0].'<span class="mediaplugin mediaplugin_swf" id="'.$id.'">('.get_string('flashanimation', 'filter_mediaplugin').')</span>'.$jsoutput;

    return $output;
}

function filter_mediaplugin_flv_callback($link) {
    global $CFG, $PAGE;

    static $count = 0;
    $count++;
    $id = 'filter_flv_'.time().$count; //we need something unique because it might be stored in text cache

    $width  = empty($link[3]) ? '480' : $link[3];
    $height = empty($link[4]) ? '360' : $link[4];
    $url = addslashes_js($link[1]);

    $playerpath = $CFG->wwwroot.'/filter/mediaplugin/flvplayer.swf';

    $output = <<<EOT
    <span class="mediaplugin mediaplugin_flv" id="$id"></span>
    <noscript><div>
    <object width="800" height="600" id="undefined" name="undefined" data="$playerpath" type="application/x-shockwave-flash">
    <param name="movie" value="$playerpath" />
    <param name="allowfullscreen" value="true" />
    <param name="allowscriptaccess" value="always" />
    <param name="flashvars" value='config={"clip":{"url":"$url",
                                                   "autoPlay": false},
                                           "content":{"url":"$playerpath"}}}' />
    </object>
    </div></noscript>
EOT;

    $jsoutput = create_flowplayer($id, $url, 'flv');
    $output .= $jsoutput;
    return $output;
}

function filter_mediaplugin_real_callback($link, $autostart=false) {
    $url = addslashes_js($link[1]);
    $mimetype = mimeinfo('type', $url);
    $autostart = $autostart ? 'true' : 'false';

// embed kept for now see MDL-8674
    return $link[0].
'<span class="mediaplugin mediaplugin_real">
<script type="text/javascript">
//<![CDATA[
document.write(\'<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="180">\\
  <param name="src" value="'.$url.'" />\\
  <param name="autostart" value="'.$autostart.'" />\\
  <param name="controls" value="imagewindow" />\\
  <param name="console" value="video" />\\
  <param name="loop" value="true" />\\
  <embed src="'.$url.'" width="240" height="180" loop="true" type="'.$mimetype.'" controls="imagewindow" console="video" autostart="'.$autostart.'" />\\
  </object><br />\\
  <object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="30">\\
  <param name="src" value="'.$url.'" />\\
  <param name="autostart" value="'.$autostart.'" />\\
  <param name="controls" value="ControlPanel" />\\
  <param name="console" value="video" />\\
  <embed src="'.$url.'" width="240" height="30" controls="ControlPanel" type="'.$mimetype.'" console="video" autostart="'.$autostart.'" />\\
  </object>\');
//]]>
</script></span>';
}

/**
 * Change links to Youtube into embedded Youtube videos
 */
function filter_mediaplugin_youtube_callback($link, $autostart=false) {

    $site = addslashes_js($link[1]);
    $url = addslashes_js($link[2]);
    $info = addslashes_js(strip_tags($link[3]));//strip out html tags as they won't work in the title attribute

    return '<object title="'.$info.'"
                    class="mediaplugin mediaplugin_youtube" type="application/x-shockwave-flash"
                    data="'.$site.'youtube.com/v/'.$url.'&amp;fs=1&amp;rel=0" width="425" height="344">'.
           '<param name="movie" value="'.$site.'youtube.com/v/'.$url.'&amp;fs=1&amp;rel=0" />'.
           '<param name="FlashVars" value="playerMode=embedded" />'.
           '<param name="wmode" value="transparent" />'.
           '<param name="allowFullScreen" value="true" />'.
           '</object>';
}

/**
 * Change Youtube playlist into embedded Youtube playlist videos
 */
function filter_mediaplugin_youtube_playlist_callback($link, $autostart=false) {

    $site = s($link[4]);
    $param = s($link[5]);
    $info = s($link[6]);

    return '<object title="'.$info.'"
                    class="mediaplugin mediaplugin_youtube" type="application/x-shockwave-flash"
                    data="'.$site.'youtube.com/p/'.$param.'&amp;fs=1&amp;rel=0" width="400" height="320">'.
           '<param name="movie" value="'.$site.'youtube.com/p/'.$param.'&amp;fs=1&amp;rel=0" />'.
           '<param name="FlashVars" value="playerMode=embedded" />'.
           '<param name="wmode" value="transparent" />'.
           '<param name="allowFullScreen" value="true" />'.
           '</object>';
}

/**
 * Change links to images into embedded images
 */
function filter_mediaplugin_img_callback($link, $autostart=false) {
    $url = addslashes_js($link[1]);
    $info = addslashes_js($link[2]);

    return '<img class="mediaplugin mediaplugin_img" alt="" title="'.$info.'" src="'.$url.'" />';
}

/**
 * Embed video using window media player if available
 */
function filter_mediaplugin_wmp_callback($link, $autostart=false) {
    $url = $link[1];
    if (empty($link[3]) or empty($link[4])) {
        $mpsize = '';
        $size = 'width="300" height="260"';
        $autosize = 'true';
    } else {
        $size = 'width="'.$link[3].'" height="'.$link[4].'"';
        $mpsize = $size;
        $autosize = 'false';
    }
    $mimetype = mimeinfo('type', $url);
    $autostart = $autostart ? 'true' : 'false';

    return $link[0].
'<span class="mediaplugin mediaplugin_wmp">
<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" '.$mpsize.'
  standby="Loading Microsoft(R) Windows(R) Media Player components..."
  type="application/x-oleobject">
 <param name="Filename" value="'.$url.'" />
 <param name="src" value="'.$url.'" />
 <param name="url" value="'.$url.'" />
 <param name="ShowControls" value="true" />
 <param name="AutoRewind" value="true" />
 <param name="AutoStart" value="'.$autostart.'" />
 <param name="Autosize" value="'.$autosize.'" />
 <param name="EnableContextMenu" value="true" />
 <param name="TransparentAtStart" value="false" />
 <param name="AnimationAtStart" value="false" />
 <param name="ShowGotoBar" value="false" />
 <param name="EnableFullScreenControls" value="true" />
<!--[if !IE]>-->
  <object data="'.$url.'" type="'.$mimetype.'" '.$size.'>
   <param name="src" value="'.$url.'" />
   <param name="controller" value="true" />
   <param name="autoplay" value="'.$autostart.'" />
   <param name="autostart" value="'.$autostart.'" />
   <param name="resize" value="scale" />
  </object>
<!--<![endif]-->
</object></span>';
}

function filter_mediaplugin_qt_callback($link, $autostart=false) {
    $url = $link[1];
    if (empty($link[3]) or empty($link[4])) {
        $size = 'width="440" height="315"';
    } else {
        $size = 'width="'.$link[3].'" height="'.$link[4].'"';
    }
    $mimetype = mimeinfo('type', $url);
    $autostart = $autostart ? 'true' : 'false';

    return $link[0].
'<span class="mediaplugin mediaplugin_qt">
<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
  codebase="http://www.apple.com/qtactivex/qtplugin.cab" '.$size.'>
 <param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
 <param name="src" value="'.$url.'" />
 <param name="controller" value="true" />
 <param name="loop" value="true" />
 <param name="autoplay" value="'.$autostart.'" />
 <param name="autostart" value="'.$autostart.'" />
 <param name="scale" value="aspect" />
<!--[if !IE]>-->
  <object data="'.$url.'" type="'.$mimetype.'" '.$size.'>
   <param name="src" value="'.$url.'" />
   <param name="pluginurl" value="http://www.apple.com/quicktime/download/" />
   <param name="controller" value="true" />
   <param name="loop" value="true" />
   <param name="autoplay" value="'.$autostart.'" />
   <param name="autostart" value="'.$autostart.'" />
   <param name="scale" value="aspect" />
  </object>
<!--<![endif]-->
</object></span>';
}


