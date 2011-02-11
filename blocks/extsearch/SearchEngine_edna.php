<?php

require_once 'SearchEngine.php';

define('EDNA_MAX_RESULTS',1000);

class SearchEngine_edna extends SearchEngine {

    var $searchprovidername = 'edna';

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
        $query = str_replace('%','',trim($query));
        if (empty($query)) {
            return;
        }
        parent::set_query($query, $page, $searchid, $blockconfig, $courselink);

        $apikey = get_config(NULL, 'block_extsearch_digitalnz_api_key');
        if (empty($apikey)) {
            print_error('error:missingdigitalnzapikey', 'block_extsearch', $this->courselink);
            return false;
        }
        else {
            $querytext = urlencode(urlencode($query));
            $this->searchurl = 'http://api.digitalnz.org/edna/v2.xml?search_text='.$querytext.'&api_key='.$apikey;
            $this->searchurl .= '&num_results='.$this->results->perpage;

            if ($page > 0) {
                // Note: first record = 0
                $startrecord = ($page * $this->results->perpage)+1;
                $this->searchurl .= "&start=$startrecord";
            }
        }

        return true;
    }


    /**
     * Print out the necessary branding information as per the Term
     * and Conditions.
     */
    function print_branding()
    {
        global $CFG;

        print '<p style="text-align: right">';
        print '<a href="http://www.edna.edu.au">';
        print '<img src="'.$CFG->wwwroot.'/blocks/extsearch/EDNA_Logo.gif" alt="Powered by EDNA"/>';
        print '</a></p>';
    }
}

/**
 * Extract the first child node matching the given name and
 * namespace. Optionally filering on a specific attribute.
 *
 * @return mixed The DOMNode object or FALSE in case of errors
 */
function get_single_node($xmlnode, $nodename, $attributename='', $attributevalue='')
{
    $allsubnodes = $xmlnode->getElementsByTagName($nodename);
    if (!$allsubnodes or $allsubnodes->length < 1) {
        //debugging("No nodes named '$nodename' under $xmlnode->nodeName", DEBUG_DEVELOPER);
        return false;
    }

    if (empty($attributename)) {
        // No attribute filter
        if ($allsubnodes->length > 1) {
            debugging("More than one node named '$nodename' under $xmlnode->nodeName", DEBUG_NORMAL);
            return false;
        }
    }
    else {
        // Look for attributename=attributevalue (first match wins)
        foreach ($allsubnodes as $node) {
            if ($node->getAttribute($attributename) == $attributevalue) {
                return $node;
            }
        }
        //debugging("No nodes named '$nodename' under $xmlnode->nodeName with attribute $attributename = $attributevalue", DEBUG_DEVELOPER);
        return false;
    }
    return $allsubnodes->item(0);
}

/**
 * Extract the value of the first child node matching the given name
 * and namespace.
 *
 * @return mixed The node value or FALSE in case of errors
 */
function get_node_value($xmlnode, $nodename, $attributename='', $attributevalue='')
{
    if ($node = get_single_node($xmlnode, $nodename, $attributename, $attributevalue)) {
        return $node->nodeValue;
    }
    else {
        return false;
    }
}

/**
 * Search Results parser for the Education Network Australia
 * engine (http://www.edna.edu.au).
 */
class SearchResults_edna extends SearchResults {

    /** Images not worth displaying to the user */
    var $brokenimages = array('http://www.scienceimage.csiro.au/index.cfm?event=site.image.thumbnail');

    /**
     * Load Search Results from XML
     *
     * @param string $xmlresults Search results in an XML string
     * @return true if the XML was loaded succesfully, false otherwise
     */
    function load_results($xmlresults)
    {
        if (!$domdocument = DOMDocument::loadXML($xmlresults)) {
            debugging('Could not load XML document', DEBUG_NORMAL);
            return false;
        }

        if ($responsenode = get_single_node($domdocument, 'hash')) {
            $this->numresults = get_node_value($responsenode, 'result-count');

            if ($records = get_single_node($responsenode, 'results')) {
                $recordnodes = $records->getElementsByTagName('result');

                if ($recordnodes->length > $this->numresults) {
                    debugging('More records in this batch of results than in the entire query', DEBUG_ALL);
                }

                $this->parse_records($recordnodes);
            }
        }
        else {
            debugging('XML document is not a searchRetrieveResponse', DEBUG_NORMAL);
            return false;
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
                    $this->print_choose_button($record->source_url);
                }
                $this->print_title($record->title, $record->source_url);
                $this->print_description($record->description, NULL);
                print '<br/>';
                $this->print_source($record->content_provider);
                $this->print_date($record->date);
                print '</p>';
            }
        }
        else {
            print get_string('noresultsfound', 'block_extsearch');
        }
    }

    /**
     * Internal function to print the document format if it's
     * recognised.
     */
    function print_format($taintedformat)
    {
        $format = '';

        // Look for a few common MIME types
        $lcformat = strtolower(trim($taintedformat));
        if (strpos($lcformat, 'pdf') !== false) {
            $format = 'PDF';
        }
        else if (strpos($lcformat, 'msword') !== false) {
            $format = 'DOC';
        }
        else if (strpos($lcformat, 'powerpoint') !== false) {
            $format = 'PPT';
        }

        if (!empty($format)) {
            print '['.format_string($format).'] ';
        }
    }

    /**
     * Internal function for parsing the records from the results set.
     *
     * @param $recordnodes DOMNodeList Nodes inside a response's srw:records
     */
    function parse_records($recordnodes)
    {
        $this->records = array();

        $i=0;
        foreach ($recordnodes as $node) {
            // Record data
            $record = new stdclass;
            $record->id               = get_node_value($node, 'id');
            $record->title            = get_node_value($node, 'title');
            $record->description      = get_node_value($node, 'description');
            $record->metadata_url     = get_node_value($node, 'metadata-url');
            $record->source_url       = get_node_value($node, 'source-url');
            $record->display_url      = get_node_value($node, 'display-url');
            $record->date             = get_node_value($node, 'date');
            $record->content_provider = get_node_value($node, 'content-provider');

            if (!empty($record->source_url)) {
               $this->records[] = $record;
            }
            else {
                // Skip results which don't lead anywhere
                debugging("Record $i doesn't have an identifier", DEBUG_ALL);
            }

            $i++;
        }
    }
}


?>
