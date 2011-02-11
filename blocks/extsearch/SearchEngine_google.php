<?php

require_once 'SearchEngine.php';

/**
 * Search Engine wrapper for the Google AJAX Search API
 * engine (http://code.google.com/apis/ajaxsearch/)
 */
class SearchEngine_google extends SearchEngine{

    var $searchprovidername = 'google';

    /**
     * Parse the query parameters to create the search URL
     *
     * @param string $query       Query text
     * @param int    $page        The page number where are on (first page = 0)
     * @param int    $searchid   ID for this search
     * @param class  $blockconfig All configuration settings for this instance of the block
     * @param string $courselink  Link to the course in case of errors
     */
    function set_query($query, $page, $searchid, $blockconfig, $courselink)
    {
        parent::set_query($query, $page, $searchid, $blockconfig, $courselink);

        $querytext = urlencode($query);
        $this->searchurl = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.$querytext;

        if ($this->results->perpage > 4) {
            $this->searchurl .= '&rsz=large';
        }
        else {
            $this->searchurl .= '&rsz=small';
        }

        $apikey = get_config(NULL, 'block_extsearch_google_api_key');
        if (!empty($apikey)) {
            $this->searchurl .= '&key='.$apikey;
        }

        $safesearch = $blockconfig->google_safesearch;
        if ($safesearch === 'moderate') {
            $this->searchurl .= '&safe=moderate';
        }
        elseif ($safesearch === 'active') {
            $this->searchurl .= '&safe=active';
        }
        elseif ($safesearch === 'off') {
            $this->searchurl .= '&safe=off';
        }

        if ($page > 0) {
            // Note: first record = 0
            $startrecord = $this->page * $this->results->perpage;
            $this->searchurl .= "&start=$startrecord";
        }

        return true;
    }

    /**
     * Print out the necessary branding information as per the Term
     * and Conditions.
     */
    function print_branding()
    {
        print '<div id="branding">'.get_string('poweredbygoogle', 'block_extsearch').'</div>';
        print '<script src="http://www.google.com/jsapi" type="text/javascript"></script>';
        print '<script type="text/javascript">//<![CDATA[
google.load(\'search\', \'1\');
function OnLoad() {
    google.search.Search.getBranding(document.getElementById("branding"));
}
google.setOnLoadCallback(OnLoad, true);
//]]></script>';
    }
}

/**
 * Search Results parser for the Google AJAX Search API
 * engine (http://code.google.com/apis/ajaxsearch/)
 */
class SearchResults_google extends SearchResults
{
    /** Number of results to display per page */
    var $perpage = 8; // the Google API supports only 4 or 8

    /**
     * Load Search Results from JSON
     *
     * @param string $jsonresults Search results in an JSON string
     * @return true if the JSON was loaded succesfully, false otherwise
     */
    function load_results($jsonresults)
    {
        $results = json_decode($jsonresults);

        // Check for errors
        if ($results->responseStatus != 200) {
            $details = format_string($results->responseDetails);
            debugging("Google service reported abnormal status: $details", DEBUG_NORMAL);
            return false;
        }

        if (count($results->responseData->results) > 0) {
            $this->records = $results->responseData->results;

            // The Google API doesn't give us access to all of the results
            $availablepages = count($results->responseData->cursor->pages);
            $this->numresults = min($results->responseData->cursor->estimatedResultCount,
                                    ($availablepages - 1) * $this->perpage);
        }

        return true;
    }

    /**
     * Print nicely formatted search results
     *
     * @param string  $choose   HTML ID of the parent element to set (picker mode)
     */
    function print_results($choose='')
    {
        if (!empty($this->records)) {
            foreach ($this->records as $record) {
                print '<p>';
                if (!empty($choose)) {
                    $this->print_choose_button($record->unescapedUrl);
                }
                $this->print_title($record->titleNoFormatting, $record->unescapedUrl);
                $preview = '';
                if (!empty($record->tbUrl)) {
                    $preview = $record->tbUrl;
                }
                $this->print_description($record->content, $preview);
                print '<br/>';
                $this->print_url($record->visibleUrl);
                $this->print_cache_link($record->cacheUrl);
                print '</p>';
            }
        }
        else {
            print get_string('noresultsfound', 'block_extsearch');
        }
    }

    /**
     * Internal function to print the user-visible URL as text
     */
    function print_url($taintedurl)
    {
        print '<span class="detailsline">';
        print format_string($taintedurl);
        print '</span>';
    }

    /**
     * Internal function to display a link to the cached version of
     * the page if it is available.
     */
    function print_cache_link($taintedurl)
    {
        if (!empty($taintedurl)) {
            print '<span class="extraactions">';
            print ' - <a href="' . format_string($taintedurl) . '">';
            print get_string('cached', 'block_extsearch').'</a>';
            print '</span>';
        }
    }
}

?>