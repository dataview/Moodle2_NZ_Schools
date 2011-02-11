<?php //$Id: externalpicker.php,v 1.1 2009/04/21 07:08:54 fmarier Exp $
global $CFG;
require_once "$CFG->libdir/form/group.php";

/**
 * Class for an element used to grab a text value from an external page.
 *
 * The idea here is that the external page will be called with the ID
 * of an HTML element to change. For example:
 *
 *   window.open('/files/index.php?id=$courseid&choose=PARENT_ELEMENT_ID');
 *
 * Then the external page needs to set that value itself using
 * Javascript, something like:
 *
 *   function set_value(newvalue) {
 *       opener.document.getElementById(PARENT_ELEMENT_ID).value = newvalue;
 *       window.close();
 *   }
 *
 * @author Francois Marier <francois@catalyst.net.nz>
 * @author Jamie Pratt <me@jamiep.org>
 * @author Aaron Wells {@link http://www.catalyst.net.nz}
 * @access public
 */
class MoodleQuickForm_externalpicker extends MoodleQuickForm_group
{
    /**
     * Options for element :
     *
     *  url           must be relative to wwwroot eg /mod/survey/stuff.php?id=1234&choose=
     *  buttoncaption string to be shown on the button
     *  width         int Height to assign to popup window
     *  title         string Text to be displayed as popup page title
     *  options       string List of additional options for popup window
     */
    var $_options = array('url' => '', 'buttoncaption' => '',
                          'height' => 600, 'width' => 900, 'options' => 'none');

    /**
     * These complement separators, they are appended to the resultant HTML
     * @access   private
     * @var      array
     */
    var $_wrap = array('', '');

    function MoodleQuickForm_externalpicker($elementName = null, $elementLabel = null,
                                            $options = array(), $attributes = null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_appendName = true;
        $this->_type = 'externalpicker';
        // set the options, do not bother setting bogus ones
        if (is_array($options)) {
            foreach ($options as $name => $value) {
                if (isset($this->_options[$name])) {
                    if (is_array($value) && is_array($this->_options[$name])) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }
    }

    function _createElements() {
        global $CFG;
        $this->_elements = array();

        $this->_elements[0] =& MoodleQuickForm::createElement('text', 'value', '', array('size'=>'48'));
        $this->_elements[1] =& MoodleQuickForm::createElement('button', 'popup', $this->_options['buttoncaption'].' ...');

        // Since the Moodle 1.9 "openpopup" Javascript function no longer exists, we'll define it here
        $rawhtml = <<<HTML
<script type="text/javascript">
//<![CDATA[
function extsearchopenpopup(url, name, options, fullscreen) {
    var fullurl = "{$CFG->httpswwwroot}" + url;
HTML;

        //code to add session id to url params if necessary for cookieless sessions
        if (!empty($CFG->usesid) && !isset($_COOKIE[session_name()])){
            $sessionparams = session_name() .'='. session_id();
            $rawhtml .= <<<HTML
    if (-1 == fullurl.indexOf('?')){
        fullurl = fullurl+'?{$sessionparams}';
    } else {
        fullurl = fullurl+'&{$sessionparams}';
    }
HTML;
        }
        $rawhtml .= <<<HTML
    var windowobj = window.open(fullurl, name, options);
    if (!windowobj) {
        return true;
    }
    if (fullscreen) {
        windowobj.moveTo(0, 0);
        windowobj.resizeTo(screen.availWidth, screen.availHeight);
    }
    windowobj.focus();
    return false;
}
//]]>
</script>
HTML;

        $this->_elements[2] =& MoodleQuickForm::createElement('html', $rawhtml);

        $button =& $this->_elements[1];

        // first find out the text field id - this is a bit hacky, is there a better way?
        $choose = 'id_'.str_replace(array('[', ']'), array('_', ''), $this->getElementName(0));
        $url = $this->_options['url'].$choose;

        if ($this->_options['options'] == 'none') {
            $options = 'menubar=0,location=0,scrollbars,resizable,width='. $this->_options['width'] .',height='. $this->_options['height'];
        }else{
            $options = $this->_options['options'];
        }
        $fullscreen = 0;

        $buttonattributes = array('title' => $this->_options['buttoncaption'],
                                  'onclick'=>"return extsearchopenpopup('$url', '".$button->getName()."', '$options', $fullscreen);");

        $button->updateAttributes($buttonattributes);
    }

    function exportValue(&$submitValues, $assoc = false)
    {
        $value = null;
        $valuearray = $this->_elements[0]->exportValue($submitValues[$this->getName()], true);
        $value[$this->getName()]=$valuearray['value'];
        return $value;
    }

    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (!is_array($value)) {
                   $value = array('value' => $value);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                return true;
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }
}
?>