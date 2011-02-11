<?php

require_once "$CFG->dirroot/blocks/extsearch/block_extsearch.php";
require_once "$CFG->libdir/filelib.php";

// Maximum number of characters to display in the description
define('MAX_DESCRIPTION', 1000);

// Maximum number of characters to display in the title
define('MAX_TITLE', 200);

class SearchEngine
{
    /** URL to hit to perform the search */
    var $searchurl;

    /** SearchResults object */
    var $results;

    /** String to be overriden in derived classes */
    var $searchprovidername = '';

    /** Page number as entered by the user */
    var $page;

    /** Query as typed in by the user */
    var $query;

    /** Used as a return page for error pages */
    var $courselink;

    /** Used to determine whether or not provider supports facets */
    var $supportsfacets = false;

    /** Used to track in-use facets */
    var $filter = array();

    /** Used to track available facet information */
    var $facets = array();

    /** Keeping track of 'choose' state */
    var $choose = '';

    /** Keeping track of 'pinned' state */
    var $pinned = 0;

    /** Keeping track of 'sort' value */
    var $sort = '';

    /** Keeping track of sort direction */
    var $direction = 0;

    /**
     * Parse the query parameters to create the search URL
     *
     * @param string $query      Query text
     * @param int    $page       The page number where are on (first page = 0)
     * @param int    $searchid   ID for this search
     * @param class  $blockconfig All configuration settings for this instance of the block
     * @param string $courselink Link to the course in case of errors
     */
    function set_query($query, $page, $searchid, $blockconfig, $courselink)
    {
        $this->query = $query;
        $this->page = $page;
        $this->courselink = $courselink;

        $classname = "SearchResults_$this->searchprovidername";
        $this->results = new $classname;

        if (!empty($blockconfig->popup_links)) {
            $this->results->popuplinks = $blockconfig->popup_links;
        }

        return false; // should not call the base class directly
    }

    /**
     * Perform the search
     */
    function search()
    {
        global $CFG;

        if (empty($this->searchurl)) {
            return false;
        }

        if (empty($this->results)) {
            debugging('The set_query() must run succesfully before search() can be used', DEBUG_DEVELOPER);
            return false;
        }

        $body = download_file_content($this->searchurl);
        if (!$body) {
            print_error('error:servererror', 'block_extsearch', $this->courselink, $this->searchprovidername);
            return false;
        }

        if (!$this->results->load_results($body)) {
            print_error('error:couldnotloadresults', 'block_extsearch', $this->courselink, $this->searchprovidername);
            return false;
        }

        return true;
    }

    /**
     * Print paged search results
     *
     * @param integer $blockid  ID of the block (or 0 if not tied to a specific block)
     * @param integer $courseid ID of the course where the block is
     * @param string  $choose   HTML ID of the parent element to set (picker mode)
     */
    function print_results($blockid=0, $courseid, $choose='')
    {
        global $CFG;

        $this->results->baseurl = $CFG->wwwroot.'/blocks/extsearch/search.php?';
        if ( $blockid ){
            $this->results->baseurl .= 'id='.$blockid;
        } else {
            $this->results->baseurl .= 'type='.$this->searchprovidername;
        }
        $this->results->baseurl .= "&amp;courseid=$courseid";
        $this->results->baseurl .= '&amp;query='.urlencode(stripslashes($this->query)).'&amp;';
        if (!empty($choose)) {
            $this->results->baseurl .= 'choose='.urlencode(stripslashes($choose)).'&amp;';
        }
        if (!empty($this->pinned)) {
            $this->results->baseurl .= 'pinned='.urlencode(stripslashes($this->pinned)).'&amp;';
        }
        if (!empty($this->direction)) {
            $this->results->baseurl .= 'direction='.$this->direction.'&amp;';
        }
        if (!empty($this->sort)) {
            $this->results->baseurl .= 'sort='.urlencode(stripslashes($this->sort)).'&amp;';
        }
        $this->results->print_query_details($this->page, $this->query);
        $this->results->print_results($choose);

        $this->results->print_pager($this->page);

        $this->print_branding();

        // Picker mode: code to give the ID back to the parent window
        if (!empty($choose)) {
            print '
<script type="text/javascript">;
//<![CDATA[
function set_value(txt) {
    opener.document.getElementById(\''.$choose.'\').value = txt;
    window.close();
}
//]]>
</script>
';
        }
    }

    /**
     * Print the branding code appropriate for the search engine
     */
    function print_branding()
    {
        // Nothing to do by default
    }
}

/**
 * Base class for parsing search results in the External Search block.
 */
class SearchResults
{
    /** Whether external links should open in popup windows */
    var $popuplinks = false;

    /** Number of results to display per page */
    var $perpage = 10;

    /** Number of search results returned */
    var $numresults = 0;

    /** Resultset ID as returned by the search server */
    var $resultsetid = 0;

    /** Records to display on the results page */
    var $records = array();

    /** Search response time in seconds */
    var $querytiming = 0;

    /** Images not worth displaying to the user */
    var $brokenimages = array();

    /** Information about each repository collection */
    var $sources = array();

    /** Base URL of search.php, primarily for use with facets */
    var $baseurl = '';

    /** Explicit definition of $filter as to prevent errors */
    var $filter = array();

    /** Keep state of 'choose' value */
    var $choose = '';

    /** Keep track of 'sort' value */
    var $sort = '';

    /** Keep track of sort direction */
    var $direction = 0;

    function get_baseurl($unfilter='') {
        $baseurl = $this->baseurl;
        foreach ($this->filter as $key => $value) {
            if ($key != $unfilter) {
                $baseurl .= 'filter['.$key.']='.urlencode(stripslashes($value)).'&amp;';
            }
        }
        return $baseurl;
    }

    /**
     * Print query and result details
     */
    function print_query_details($page, $taintedquery)
    {
        if ($this->numresults > 0) {
            $query = format_string(stripslashes($taintedquery));

            $stats = new stdclass;
            $stats->startrecord = $page * $this->perpage + 1;
            $stats->lastrecord = min($stats->startrecord + $this->perpage - 1, $this->numresults);
            $stats->total = $this->numresults;

            print '<p style="text-align: right">';
            print get_string('resultsdetails', 'block_extsearch', $stats).' ';
            print get_string('forquery', 'block_extsearch', $query);
            if (!empty($this->querytiming)) {
                print ' '.get_string('querytiming', 'block_extsearch', $this->querytiming);
            }
            print '</p>';
        }
    }

    /**
     * Print the paging bar for navigating the result set.
     *
     * @param $page    integer  The page number to display
     * @param $baseurl string   The URL to which we will append the page number
     */
    function print_pager($page)
    {
        global $CFG, $OUTPUT;
        require_once($CFG->dirroot.'/lib/outputcomponents.php');

        // Sanity checks
        if ($page < 0) {
            debugging('The page number must be at least 0', DEBUG_DEVELOPER);
            return;
        }
        if ($this->perpage <= 0) {
            debugging('The number of records per page must be greater than 0', DEBUG_DEVELOPER);
            return;
        }
        $totalnumpages = ceil($this->numresults / $this->perpage);
        if ($page >= $totalnumpages) {
            return;
        }

        if (!empty($this->resultsetid)) {
            $this->baseurl .= "searchid=$this->resultsetid&amp;";
        }
        print $OUTPUT->render(new paging_bar($this->numresults, $page, $this->perpage, $this->get_baseurl(), 'page'));
    }

    /**
     * Internal function to print the link and title of each search
     * result.
     */
    function print_title($taintedtitle, $taintedurl)
    {
        $title = format_string(trim($taintedtitle));
        if (strlen($title) > MAX_TITLE) {
            $title = substr($title, 0, MAX_TITLE) . '...';
        }

        $url = format_string($taintedurl);

        $onclick = '';
        if ($this->popuplinks) {
            // Relies on the Javascript function openpopup in javascript.php
            $onclick = "onclick=\"window.open('$url'); return false;\"";
        }

        if (!empty($title)) {
            print "<a $onclick href=\"$url\">$title</a>";
        }
        else {
            print "<a $onclick href=\"$url\">$url</a>";
        }
    }

    /**
     * Internal function to print the description up to a certain
     * number of characters along with a thumbnail if available.
     */
    function print_description($tainteddescription, $taintedpreview)
    {
        $description = format_string(trim($tainteddescription));
        if (!empty($description)) {
            if (strlen($description) > MAX_DESCRIPTION) {
                $description = substr($description, 0, MAX_DESCRIPTION) . '...';
            }
            print "<br/>$description";
        }

        $taintedpreview = trim($taintedpreview);
        if (!empty($taintedpreview) && !in_array($taintedpreview, $this->brokenimages)) {
            print '<br/><img src="'.format_string($taintedpreview).'" alt="'.
                format_string($description).'"/>';
        }
    }

    /**
     * Internal function to print the date into a locale-based human
     * readable format.
     */
    function print_date($tainteddate)
    {
        if (!empty($tainteddate)) {
            print '<span class="detailsline">';
            if (strlen($tainteddate) > 4) {
                $timestamp = strtotime($tainteddate);
                $formatteddate = userdate($timestamp, get_string('strftimedate'));
            }
            else {
                $formatteddate = format_string($tainteddate);
            }
            print " - $formatteddate";
            print '</span>';
        }
    }

    /**
     * Internal function to print a link to the source of the
     * information
     */
    function print_source($taintedsource)
    {
        print '<span class="detailsline">';
        if (!empty($this->sources[$taintedsource])) {
            $source = $this->sources[$taintedsource];

            $onclick = '';
            if ($this->popuplinks) {
                // Relies on the Javascript function openpopup in javascript.php
                $onclick = "onclick=\"window.open('$source->url'); return false;\"";
            }

            print "<a $onclick".' href="'.format_string($source->url).'"'.
                ' title="'.format_string($source->description).'">'.
                $source->title.'</a>';
        }
        else {
            print format_string($taintedsource);
        }
        print '</span>';
    }

    /**
     * Show a choose button used by the picker mode.
     */
    function print_choose_button($identifier)
    {
        print '<input type="button" value="'.get_string('choose').'" onclick="return set_value(\''.$identifier.'\')" />&nbsp;';
    }
}

?>
