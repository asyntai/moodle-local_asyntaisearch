<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_asyntaisearch\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider.
 *
 * The plugin stores no personal data in Moodle itself. It does send data to
 * Asyntai, so it declares that external location: the words a visitor types
 * into the search bar, and the course material the administrator ticked.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\data_provider {
    /**
     * Describe what leaves the site.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'asyntai',
            [
                'query' => 'privacy:metadata:asyntai:query',
                'language' => 'privacy:metadata:asyntai:language',
                'coursecontent' => 'privacy:metadata:asyntai:coursecontent',
            ],
            'privacy:metadata:asyntai'
        );
        return $collection;
    }
}
