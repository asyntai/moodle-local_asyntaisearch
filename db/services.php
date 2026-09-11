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
 * External functions and services are defined here.
 *
 * Three of the functions run the connect handshake from the plugin's own
 * settings page, over Moodle's AJAX layer. Doing the handshake in PHP means
 * no script from another server is ever executed inside the administration,
 * and the preview secret never reaches a browser at all.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_asyntaisearch_connect_prepare' => [
        'classname' => 'local_asyntaisearch\external\connect_prepare',
        'methodname' => 'execute',
        'description' => 'Start the Asyntai connect handshake and return the sign-in address',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/asyntaisearch:manage',
    ],
    'local_asyntaisearch_connect_poll' => [
        'classname' => 'local_asyntaisearch\external\connect_poll',
        'methodname' => 'execute',
        'description' => 'Ask whether the administrator has finished signing in to Asyntai',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/asyntaisearch:manage',
    ],
    'local_asyntaisearch_disconnect' => [
        'classname' => 'local_asyntaisearch\external\disconnect',
        'methodname' => 'execute',
        'description' => 'Disconnect this site from Asyntai',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/asyntaisearch:manage',
    ],
    'local_asyntaisearch_get_course_content' => [
        'classname' => 'local_asyntaisearch\external\get_course_content',
        'methodname' => 'execute',
        'description' => 'Return the text of the courses the administrator ticked for Asyntai',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => 'local/asyntaisearch:sync',
    ],
];

// A service of its own, holding exactly one function. The token Asyntai is
// given belongs to this service, so it cannot call anything else in Moodle.
$services = [
    'Asyntai AI Search content sync' => [
        'functions' => ['local_asyntaisearch_get_course_content'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'local_asyntaisearch_sync',
        'downloadfiles' => 1,
        'uploadfiles' => 0,
    ],
];
