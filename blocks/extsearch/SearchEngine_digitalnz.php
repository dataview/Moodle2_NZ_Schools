<?php

require_once 'SearchEngine.php';

/**
 * Search Engine wrapper for the Digital NZ Search engine
 * (http://www.digitalnz.org)
 */
class SearchEngine_digitalnz extends SearchEngine{

    var $searchprovidername = 'digitalnz';
    var $supportsfacets     = true;
    var $filter             = array();

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
        parent::set_query($query, $page, $searchid, $blockconfig, $courselink);

        $apikey = get_config(NULL, 'block_extsearch_digitalnz_api_key');
        if (empty($apikey)) {
            print_error('error:missingdigitalnzapikey', 'block_extsearch', $this->courselink);
            return false;
        }
        else {
            foreach ($this->filter as $key => $value) {
                $val = stripslashes($value);
                $query .= " AND ${key}:${val}";
            }
            $querytext = urlencode($query);
            $this->searchurl = 'http://api.digitalnz.org/records/v1.json?search_text='.$querytext.'&api_key='.$apikey;

            if (!empty($this->sort)) {
                $this->searchurl .= '&sort='.urlencode(stripslashes($this->sort));
            }
            if (!empty($this->direction)) {
                $this->searchurl .= '&direction=desc';
            }

            $this->searchurl .= '&facets=category,content_partner,language,rights,decade,collection';
            $this->searchurl .= '&num_results='.$this->results->perpage;

            if ($page > 0) {
                // Note: first record = 0
                $startrecord = $page * $this->results->perpage;
                $this->searchurl .= "&start=$startrecord";
            }
            $this->results->filter = &$this->filter;
            $this->results->sort   = $this->sort;
            $this->results->direction = $this->direction;
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
        print '<a href="http://digitalnz.org">';
        print '<img src="'.$CFG->wwwroot.'/blocks/extsearch/DNZ_Logo.gif" alt="Powered by DigitalNZ"/>';
        print '</a></p>';
    }
}

/**
 * Search Results parser for the Digital NZ Search
 * engine (http://www.digitalnz.org)
 */
class SearchResults_digitalnz extends SearchResults
{
    /** Explicit definition of $facets */
    var $facets;

    /** Array containing URIs of images to ignore */
    var $brokenimages = array('http://www.digitalnz.org/images/search/thumbnails/books.png',
                              'http://www.digitalnz.org/images/search/thumbnails/research_papers.png',
                              'http://www.digitalnz.org/images/search/thumbnails/journals.png',
                              'http://www.digitalnz.org/images/search/thumbnails/reference_sources.png',
                              'http://www.digitalnz.org/images/search/thumbnails/newspapers.png',
                              'http://www.digitalnz.org/images/search/thumbnails/community_content.png',
                              'http://www.digitalnz.org/images/search/thumbnails/audio.png',
                              'http://www.digitalnz.org/images/search/thumbnails/manuscripts.png');

    /**
     * Load Search Results from JSON
     *
     * @param string $jsonresults Search results in an JSON string
     * @return true if the JSON was loaded succesfully, false otherwise
     */
    function load_results($jsonresults)
    {
        $results = json_decode($jsonresults);
        if (($results === $jsonresults) || ($results === false)) {
            debugging('Digital NZ returned an error: '. $jsonresults, DEBUG_NORMAL);
            return false;
        }
        $this->records = $results->results;
        $this->numresults = $results->result_count;
        $this->facets = $results->facets;

        $this->load_sources();
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
            print '<div class="facets">';
            if (!empty($this->filter)) {
                print '<div class="filters"><h3>Currently filtered by:</h3><ul>';
                foreach ($this->filter as $facet => $filter) {
                    print '<li><a href="'.$this->get_baseurl($facet).'"><h4>'.$facet.': </h4> '.trim(stripslashes($filter), '"').' (remove)</a></li>';
                }
                print '</ul></div><hr/>';
            }
            foreach ($this->facets as $facet) {
                print '<div class="facet"><h3>'.$facet->facet_field.'</h3><ul>';
                $i = 0;
                foreach ($facet->values as $value) {
                    if ($i >= 5) {
                        continue;
                    }
                    $urlname = $value->name;
                    if (strpos($urlname,' ') > -1) {
                        $urlname = '"'.$urlname.'"';
                    }
                    print '<li><a href="'.$this->get_baseurl().'filter['.$facet->facet_field.']='.urlencode($urlname).'&amp;">'.$value->name.' ('.$value->num_results.')</a></li>';
                    $i++;
                }
                print '</ul></div>';
            }
            print '</div><div class="results">';
            foreach ($this->records as $record) {
                print '<p>';
                if (!empty($choose)) {
                    $this->print_choose_button($record->source_url);
                }
                $this->print_title($record->title, $record->source_url);
                $this->print_description($record->description, $record->thumbnail_url);
                print '<br/>';
                $this->print_source($record->content_provider);
                $this->print_date($record->date);
                print '</p>';
            }
            print '</div>';
        }
        else {
            print get_string('noresultsfound', 'block_extsearch');
        }
    }

    /**
     * Internal Helper function for load_sources()
     */
    function add_source($url, $name)
    {
        $source = new stdclass;
        $source->title = $name;
        $source->description = '';
        $source->url = $url;
        $this->sources[$name] = $source;
    }

    /**
     * Internal function which loads in the latest source of DigitalNZ
     * content providers.
     *
     * Note: there is currently no API for this information so this
     * information was manually imported.
     */
    function load_sources()
    {
        // Scraped from http://www.digitalnz.org/about/list-of-contributors on 2009-03-11
        $this->add_source('http://www.archives.govt.nz', 'Archives New Zealand Te Rua Mahara o te Kāwanatanga');
        $this->add_source('http://warart.archives.govt.nz/', 'War Art Online');
        $this->add_source('http://www.natlib.govt.nz', 'Alexander Turnbull Library, National Library of New Zealand');
        $this->add_source('http://teaohou.natlib.govt.nz/journals/teaohou/index.html', 'Te Ao Hou');
        $this->add_source('http://timeframes.natlib.govt.nz/', 'Timeframes');
        $this->add_source('http://www.aucklandartgallery.govt.nz/', 'Auckland Art Gallery Toi o Tamaki');
        $this->add_source('http://www.aucklandcity.govt.nz/auckland/introduction/archives/Default.asp', 'Auckland City Council Archives');
        $this->add_source('http://www.aucklandcity.govt.nz', 'Auckland City Libraries');
        $this->add_source('http://www.aucklandcity.govt.nz/dbtw-wpd/maps/maps.html', 'Maps Online');
        $this->add_source('http://www.aucklandcity.govt.nz/dbtw-wpd/heritageimages/apphoto.htm', 'Heritage Images Online');
        $this->add_source('http://www.aut.ac.nz', 'Auckland University of Technology');
        $this->add_source('http://aut.researchgateway.ac.nz/', 'ScholarlyCommons@AUT');
        $this->add_source('http://www.aucklandmuseum.com', 'Auckland War Memorial Museum Tamaki Paenga Hira');
        $this->add_source('http://muse.aucklandmuseum.com/databases/cenotaph/locations.aspx', 'Cenotaph Database');
        $this->add_source('http://www.aucklandmuseum.com/108/pictorial-collection', 'Pictorial Collections');
        $this->add_source('http://muse.aucklandmuseum.com/databases/general/basicsearch.aspx?dataset=Muscat', 'Library Catalogue');
        $this->add_source('http://christchurchcitylibraries.com', 'Christchurch City Libraries');
        $this->add_source('http://christchurchcitylibraries.com/Heritage/Photos/', 'Heritage Images collection');
        $this->add_source('http://www.dunedinlibraries.com/home', 'Dunedin Public Libraries');
        $this->add_source('http://kete.digitalnz.org/', 'Kete Digital New Zealand');
        $this->add_source('http://www.hpl.govt.nz', 'Hamilton City Libraries');
        $this->add_source('http://ketehamilton.peoplesnetworknz.info/', 'Kete Hamilton');
        $this->add_source('http://www.library.otago.ac.nz/hocken/index.html', 'Hocken Collections, University of Otago Library');
        $this->add_source('http://digital.otago.ac.nz/index.php?PHPSESSID=de620ec6f9ca4534d892eddea6da91c8', 'Digital Collections');
        $this->add_source('http://horowhenua.kete.net.nz', 'Kete Horowhenua');
        $this->add_source('http://horowhenua.kete.net.nz', 'Kete Horowhenua');
        $this->add_source('http://www.nzmuseums.co.nz/index.php?option=com_nstp&amp;task=showAccountDetail&amp;accountIdSet=3133&amp;Itemid=28', 'Kowai Archives');
        $this->add_source('http://www.nzmuseums.co.nz/', 'NZMuseums');
        $this->add_source('http://www.lincoln.ac.nz', 'Lincoln University');
        $this->add_source('http://researcharchive.lincoln.ac.nz/dspace/', 'Lincoln University Research Archive');
        $this->add_source('http://www.livingheritage.org.nz', 'Living Heritage');
        $this->add_source('http://www.massey.ac.nz', 'Massey University Library');
        $this->add_source('http://muir.massey.ac.nz/', 'Massey Research Online');
        $this->add_source('http://www.mch.govt.nz', 'Ministry for Culture and Heritage');
        $this->add_source('http://www.teara.govt.nz/', 'Te Ara');
        $this->add_source('http://www.nzhistory.net.nz/', 'New Zealand History online');
        $this->add_source('http://www.minedu.govt.nz', 'Ministry of Education');
        $this->add_source('http://www.tki.org.nz/r/wick_ed/', 'wickED');
        $this->add_source('http://www.fish.govt.nz/en-nz/default.htm', 'Ministry of Fisheries');
        $this->add_source('https://www.nabis.govt.nz/nabis_prd/index.jsp', 'NABIS');
        $this->add_source('http://www.tepapa.govt.nz/Tepapa/English', 'Museum of New Zealand Te Papa Tongarewa');
        $this->add_source('http://collections.tepapa.govt.nz/', 'Collections Online');
        $this->add_source('http://www.nzetc.org', 'New Zealand Electronic Text Centre');
        $this->add_source('http://www.northotagomuseum.co.nz', 'North Otago Museum');
        $this->add_source('http://www.nzmuseums.co.nz/', 'NZMuseums');
        $this->add_source('http://www.nzonscreen.com', 'NZ On Screen');
        $this->add_source('http://www.otagomuseum.govt.nz', 'Otago Museum');
        $this->add_source('http://www.omvirtuallythere.co.nz/about.asp', 'Virtually There');
        $this->add_source('http://www.ournz.co.nz', 'Our NZ');
        $this->add_source('http://www.pukeariki.com', 'Puke Ariki');
        $this->add_source('http://vernon.npdc.govt.nz/', 'Collections online');
        $this->add_source('http://www.museumnp.org.nz', 'The Nelson Provincial Museum');
        $this->add_source('http://kete.digitalnz.org/', 'Kete Digital New Zealand');
        $this->add_source('http://www.filmarchive.org.nz', 'The New Zealand Film Archive Ngā Kaitiaki o Ngā Taonga Whitiāhua');
        $this->add_source('http://filmarchive.org.nz/catalogue/simplesearch.htm', 'Film Archive Catalogue');
        $this->add_source('http://www.rotoruamuseum.co.nz', 'Rotorua Museum of Art & History, Te Whare Taonga O Te Arawa');
        $this->add_source('http://www.nzmuseums.co.nz/', 'NZMuseums');
        $this->add_source('http://www.library.auckland.ac.nz', 'The University of Auckland Library');
        $this->add_source('http://www.matapihi.org.nz/bin/goto?http://www.library.auckland.ac.nz/databases/alt/anthpd/', 'Anthropology Photographic Archive');
        $this->add_source('http://www.matapihi.org.nz/bin/goto?http://www.architecture-archive.auckland.ac.nz/', 'Architecture Archive');
        $this->add_source('http://researchspace.auckland.ac.nz/', 'ResearchSpace@Auckland');
        $this->add_source('http://library.canterbury.ac.nz', 'University of Canterbury Library');
        $this->add_source('http://digital-library.canterbury.ac.nz', 'Digital Library');
        $this->add_source('http://ir.canterbury.ac.nz/', 'UC Research Repository');
        $this->add_source('http://www.otago.ac.nz', 'University of Otago');
        $this->add_source('http://eprints.otago.ac.nz/', 'Otago Eprints');
        $this->add_source('http://www.library.otago.ac.nz/index.php', 'University of Otago Library');
        $this->add_source('http://digital.otago.ac.nz/index.php?PHPSESSID=de620ec6f9ca4534d892eddea6da91c8', 'Digital Collections');
        $this->add_source('http://www.waikato.ac.nz', 'University of Waikato Library');
        $this->add_source('http://waikato.researchgateway.ac.nz/', 'ResearchCommons@Waikato');
        $this->add_source('http://www.upperhuttcity.com/page/4/UpperHuttCityLibrary.boss', 'Upper Hutt City Library');
        $this->add_source('http://kete.digitalnz.org/', 'Kete Digital New Zealand');
        $this->add_source('http://www.vuw.ac.nz', 'Victoria University of Wellington Library');
        $this->add_source('http://researcharchive.vuw.ac.nz/', 'ResearchArchive');

        // Manually added
        $this->add_source('http://www.radionz.co.nz', 'Radio New Zealand');
        $this->add_source('http://www.natlib.govt.nz', 'Alexander Turnbull Library');
        $this->add_source('http://www.natlib.govt.nz', 'National Library of New Zealand');
        $this->add_source('http://www.vuw.ac.nz/', 'Victoria University of Wellington');
        $this->add_source('http://www.library.auckland.ac.nz/', 'The University of Auckland');
        $this->add_source('http://library.canterbury.ac.nz/', 'University of Canterbury');
    }
}

?>
