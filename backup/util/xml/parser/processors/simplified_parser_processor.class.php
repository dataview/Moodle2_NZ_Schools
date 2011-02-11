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
 * @package moodlecore
 * @subpackage xml
 * @copyright 2003 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/backup/util/xml/parser/processors/progressive_parser_processor.class.php');

/**
 * Abstract xml parser processor to to simplify and dispatch parsed chunks
 *
 * This @progressive_parser_processor handles the requested paths,
 * performing some conversions from the original "propietary array format"
 * used by the @progressive_parser to a simplified structure to be used
 * easily. Found attributes are converted automatically to tags and cdata
 * to simpler values.
 *
 * Note: final tag attributes are discarded completely!
 *
 * TODO: Complete phpdocs
 */
abstract class simplified_parser_processor extends progressive_parser_processor {
    protected $paths;       // array of paths we are interested on
    protected $parentpaths; // array of parent paths of the $paths
    protected $parentsinfo; // array of parent attributes to be added as child tags

    public function __construct(array $paths) {
        parent::__construct();
        $this->paths = array();
        $this->parentpaths = array();
        $this->parentsinfo = array();
        // Add paths and parentpaths. We are looking for attributes there
        foreach ($paths as $key => $path) {
            $this->add_path($path);
        }
    }

    public function add_path($path) {
        $this->paths[] = $path;
        $this->parentpaths[] = progressive_parser::dirname($path);
    }

    /**
     * Get the already simplified chunk and dispatch it
     */
    abstract protected function dispatch_chunk($data);

    /**
     * Get one chunk of parsed data and make it simpler
     * adding attributes as tags and delegating to
     * dispatch_chunk() the procesing of the resulting chunk
     */
    public function process_chunk($data) {
        // Precalculate some vars for readability
        $path = $data['path'];
        $parentpath = progressive_parser::dirname($path);
        $tag = basename($path);

        // If the path is a registered parent one, store all its tags
        // so, we'll be able to find attributes later when processing
        // (child) registered paths (to get attributes if present)
        if ($this->path_is_selected_parent($path)) { // if path is parent
            if (isset($data['tags'])) {              // and has tags, save them
                $this->parentsinfo[$path] = $data['tags'];
            }
        }

        // If the path is a registered one, let's process it
        if ($this->path_is_selected($path)) {
            // First of all, look for attributes available at parentsinfo
            // in order to get them available as normal tags
            if (isset($this->parentsinfo[$parentpath][$tag]['attrs'])) {
                $data['tags'] = array_merge($this->parentsinfo[$parentpath][$tag]['attrs'], $data['tags']);
                unset($this->parentsinfo[$parentpath][$tag]['attrs']);
            }
            // Now, let's simplify the tags array, ignoring tag attributtes and
            // reconverting to simpler name => value array. At the same time,
            // check for all the tag values being whitespace-string values, if all them
            // are whitespace strings, we aren't going to postprocess/dispatch the chunk
            $alltagswhitespace = true;
            foreach ($data['tags'] as $key => $value) {
                // If the value is already a single value, do nothing
                // surely was added above from parentsinfo attributes,
                // so we'll process the chunk always
                if (!is_array($value)) {
                    $alltagswhitespace = false;
                    continue;
                }
                // If the path including the tag name matches another selected path
                // (registered or parent) delete it, another chunk will contain that info
                if ($this->path_is_selected($path . '/' . $key) ||
                    $this->path_is_selected_parent($path . '/' . $key)) {
                    unset($data['tags'][$key]);
                    continue;
                }
                // Convert to simple name => value array
                $data['tags'][$key] = isset($value['cdata']) ? $value['cdata'] : null;

                // Check $alltagswhitespace continues being true
                if ($alltagswhitespace && strlen($data['tags'][$key]) !== 0 && trim($data['tags'][$key]) !== '') {
                    $alltagswhitespace = false; // Found non-whitespace value
                }
            }

            // Arrived here, if the chunk has tags and not all tags are whitespace,
            // send it to postprocess filter that will decide about dispatching. Else
            // skip the chunk completely
            if (!empty($data['tags']) && !$alltagswhitespace) {
                return $this->postprocess_chunk($data);
            } else {
                $this->chunks--; // Chunk skipped
            }
        } else {
            $this->chunks--; // Chunk skipped
        }
        return true;
    }

// Protected API starts here

    protected function postprocess_chunk($data) {
        $this->dispatch_chunk($data);
    }

    protected function path_is_selected($path) {
        return in_array($path, $this->paths);
    }

    protected function path_is_selected_parent($path) {
        return in_array($path, $this->parentpaths);
    }
}
