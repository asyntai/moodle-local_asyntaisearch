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

/**
 * Plugin library functions.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Put the search bar's script into the head of every page.
 *
 * The legacy callback, for Moodle 4.2 and 4.3. From 4.4 on Moodle calls the
 * hook registered in db/hooks.php instead and leaves this one alone, so the
 * script is never added twice.
 *
 * @return string
 */
function local_asyntaisearch_before_standard_html_head(): string {
    return \local_asyntaisearch\embed::head_html();
}
