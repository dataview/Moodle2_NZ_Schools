This file is part of Moodle - http://moodle.org/

Moodle is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

copyright 2009 Petr Skoda (http://skodak.org)
copyright 2011 Aaron Wells (http://www.catalyst.net.nz)
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


External Search Result activity module
=============

The External Search Result activity module is a successor to the Moodle 1.9
"Digital NZ" resource type. It allows you to access external search engines,
and store their search results as resources in your course.

This version of the module is heavily based on the Moodle "url" module, the
primary difference being that it uses the search page from the External Search
block to pick the URL, rather than using the Moodle Repository API.

This module requires the External Search block (blocks/extsearch) to be installed,
although it is not necessary to set up any instances of the block. To search
DigitalNZ or EDNA, you must also provide a DigitalNZ API key.

TODO:
 * Change this into a repository plugin (though the repository API will have
to change substantially before that is feasible)