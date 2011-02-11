<?php

require_once "$CFG->dirroot/blocks/moodleblock.class.php";

  /**
   * This block is designed to bring results from external search
   * engines into Moodle.
   *
   * @package extsearch
   * @subpackage extsearch block
   * @author: Francois Marier <francois@catalyst.net.nz>
   * @date: 2009-02-25
   */

  class block_extsearch extends block_base {

    function init() {
      $this->title = get_string('pluginname', 'block_extsearch');
      $this->cron = 0;
    }

    function instance_allow_multiple() {
      return true;
    }

    function has_config() {
      return true;
    }

    function get_content() {
        global $CFG, $COURSE;

      //cache block contents
      if ($this->content !== NULL) {
        return $this->content;
      }

      $this->content = new stdClass;
      $this->content->text = '';

      if (!empty($this->config->search_provider)) {
          $searchprovider = get_string($this->config->search_provider, 'block_extsearch');
          $this->content->text .= '<form action="'.$CFG->wwwroot.'/blocks/extsearch/search.php" method="get">';
          $this->content->text .= '<div><label for="query">'.get_string('searchlabel', 'block_extsearch', $searchprovider).'</label>';
          $this->content->text .= '<br/>';
          $this->content->text .= '<input type="hidden" name="id" value="'.$this->instance->id.'" />';
          $this->content->text .= '<input type="hidden" name="courseid" value="'.$COURSE->id.'" />';
          $this->content->text .= '<input type="hidden" name="pinned" value="'.(!empty($this->instance->pinned) ? 1 : 0).'" />';
          $this->content->text .= '<input type="text" id="query" name="query" size="16" value="" />';
          $this->content->text .= '<input type="submit" value="'.get_string('searchbutton', 'block_extsearch').'" /></div>';
          $this->content->text .= '</form>';
      }
      else {
          $this->content->text .= get_string('notconfigured', 'block_extsearch');
      }

      $this->content->footer = '';
      return $this->content;
    }
  }

?>
