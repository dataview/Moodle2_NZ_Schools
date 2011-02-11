<?php
$string['error:couldnotloadresults'] = 'Could not load results returned by the search engine';
$string['error:emptyqueryparam'] = 'Empty search query. Please enter some search terms and try again';
$string['error:incorrectblockid'] = 'Block instance ID is incorrect';
$string['error:incorrectblockidpicker'] = 'Could not find an instance of the External Search block in the course. Ask the course creator to make sure that one is available and properly configured for the correct search engine.';
$string['error:incorrectcourseid'] = 'Course ID is incorrect';
$string['error:missingdigitalnzapikey'] = 'Missing DigitalNZ API key. Your site administrator must reconfigure this block before it can be used.';
$string['error:servererror'] = 'Problem connecting to the {$a} server';
$string['error:unsupportedsearchprovider'] = 'The search provider configured for this block is not supported. Contact the course creator to get this fixed.';

$string['pluginname'] = 'External Search';
$string['cached'] = 'Cached';
$string['digitalnz'] = 'Digital NZ';
$string['digitalnzapikey'] = 'To use the DigitalNZ and/or EDNA (Education Network Australia) service, you must provide your own <a href="http://www.digitalnz.org/dashboard/api_key">API key</a>.';
$string['digitalnzapikey2'] = 'DigitalNZ / EDNA API key';
$string['entersearchterms'] = 'To search {$a}, enter some search terms in the box below.';
$string['edna'] = 'EDNA';
$string['forquery'] = 'for <b>{$a}</b>.';
$string['google'] = 'Google Web Search';
$string['googleapikey'] = 'If you have your own <a href="http://code.google.com/apis/ajaxsearch/signup.html">Google AJAX Search API key</a>, you may provide it here. This is optional and leaving the field blank is fine.';
$string['googleapikey2'] = 'Google AJAX Search API key';
$string['googlesafesearch_label'] = 'Google Safe Search:';
$string['googlesafesearch_active'] = 'Highest level of safe search filtering';
$string['googlesafesearch_moderate'] = 'Moderate safe search filtering';
$string['googlesafesearch_off'] = 'No search filtering';
$string['mustprovideidortype'] = 'Incorrect parameters in URL (must provide block id or search provider)';
$string['noneavailable'] = '(none available)';
$string['noresultsfound'] = '<p><b>No results found.</b></p><p>Try different or more general keywords.</p>';
$string['notconfigured'] = 'A search provider has not been selected for this block. You must configure this block before others will be able to use it.';
$string['noteaboutsitewideconfig'] = 'Note that you can only use and configure the search providers which have been enabled by your site administrator in the <a href="{$a}">block settings</a>.';
$string['popuplinks'] = 'Open external links in a popup window:';
$string['poweredbygoogle'] = 'powered by Google';
$string['querysyntax'] = 'Query Syntax';
$string['querytiming'] = '(<b>{$a}</b> seconds)';
$string['resultsdetails'] = 'Results <b>{$a->startrecord} - {$a->lastrecord}</b> of <b>{$a->total}</b>';
$string['searchbutton'] = 'Search';
$string['searchlabel'] = '{$a}:';
$string['searchquery_label'] = 'You have searched for';
$string['searchprovider_label'] = 'Select the search provider to use:';
$string['similarpages'] = 'Similar pages';
$string['querysyntax_digitalnz'] = 'Digital NZ Query Syntax';
$string['querysyntax_edna'] = 'EDNA Query Syntax';
$string['querysyntax_google'] = 'Google Search Query Syntax';

$string['querysyntax_digitalnz_help'] = <<<EOL
<p>The DigitalNZ search system uses Apache Lucene syntax, which is fully described in the <a href="http://lucene.apache.org/java/2_4_0/queryparsersyntax.html">Apache Lucene Query Parser Syntax</a> documentation.</p>
<p>Lucene syntax is very powerful, and gives you access to numerous features, including fielded search, boolean operators, Google-style operators, nesting and grouping, proximity operators, wildcards, even fuzzy search.</p>
<h2>Examples</h2>
<p>Complex queries can be built up using boolean operators (<em>AND</em>, <em>NOT</em>) and with fielded searches using <em>field:value</em> syntax.</p>

<ul>
    <li>Results from 1908: <b>year:1908</b></li>
    <li>Results from the 20th century: <b>century:1900-1999</b></li>
    <li>Results where the location is Wellington: <b>placename:Wellington</b></li>
    <li>Results where the location starts with Wellington: <b>placename:Wellington*</b></li>

    <li>Results from Auckland in 1908: <b>placename:Auckland* AND year:1908</b></li>
    <li>Results from North Otago Museum: <b>content_partner:&quot;North Otago Museum&quot;</b></li>
    <li>Results covered by Crown Copyright: <b>rights:&quot;Crown copyright&quot;</b></li>
    <li>Date range searching: <b>date:[1900-01-01T00:00:00Z TO 1903-01-01T00:00:00Z]</b></li>

    <li>And finally, you can search for everything in DigitalNZ by specifying that you want every value from every field: <b>*:*</b></li>
</ul>
<h2>Fielded, full text, and date searching</h2>
<p>DigitalNZ offers exact match, full text, and date range searching.</p>
<h3>Exact match indexing</h3>
<p>Exact match searches require that the search text matches the indexed data exactly. Fields that can be searched using exact matches include:</p>
<ul>
    <li>id</li>

    <li>category</li>
    <li>content_partner</li>
    <li>century</li>
    <li>creator</li>
    <li>decade</li>
    <li>language</li>

    <li>placename (metadata to be cleaned up)</li>
    <li>rights</li>
    <li>subject</li>
    <li>year</li>
</ul>
<p>Note that DigitalNZ generates the <em>year</em>, <em>decade </em>and <em>century </em>values based on any <em>date</em> metadata associated with a record.</p>

<h3>Text indexing</h3>
<p>Text index searches do not require an exact match, instead the search text will match against words that appear anywhere in the indexed data. Fields that can be searched using text indexing include:</p>
<ul>
    <li>title</li>
    <li>description</li>
    <li>fulltext (not used yet, currently full text is stored in the description field)</li>
    <li>text (the default search field - see below)</li>

</ul>
<h3>Date range indexing</h3>
<p>Date indexed fields must be searched using date range syntax. Fields that can be searched using date range indexing include</p>
<ul>
    <li>date (any date associated with the item)</li>
    <li>syndication_date (the date it was added to DigitalNZ)</li>
</ul>
<h2>The default search field</h2>
<p>The &quot;text&quot; field is the default search field: if you do not specify a field explicitly, the text field will be searched.</p>

<p>The text field is not a real field, it is a copy of all these fields: title, description, category, content_partner, creator, subject, year, placename, and fulltext.</p>
<p>&nbsp;</p>

<hr/>

<p>Source: <a href="http://www.digitalnz.org/developer/api-docs/query-syntax/">Digital NZ Query Syntax</a></p>

<p>Copyright &copy; 2009 National Library of New Zealand</p>

<p>This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.</p>

<p>This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   <a href="http://www.fsf.org/licensing/licenses/gpl.html">GNU General Public License</a> for more details.</p>
EOL;

$string['querysyntax_edna_help'] = <<<EOL
<p>Here are some example queries:</p>

<ul>
<li>To search for a single word: <b>childhood</b></li>
<li>To search for two words: <b>frogs toads</b></li>
<li>To search for a specific phrase: <b>"spring break"</b></li>
<li>To search for both a specific phrase and a word: <b>"spring break" summer</b></li>
</ul>

<p>For more information consult <a href="http://www.edna.edu.au">www.edna.edu.au</a>.</p>
EOL;

$string['querysyntax_google_help'] = <<<EOL
<p>To get started, read
  the <a href="http://www.google.com/support/websearch/bin/answer.py?answer=134479">Basic
  search help</a>.</p>

<p>More advanced users will want to consult
  the <a href="http://www.google.com/support/websearch/bin/answer.py?answer=136861">advanced topics</a>.</p>
EOL;
?>
