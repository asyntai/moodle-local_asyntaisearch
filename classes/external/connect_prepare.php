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

namespace local_asyntaisearch\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_asyntaisearch\state;

/**
 * Step one of the connect handshake.
 *
 * Makes a preview secret and parks it at Asyntai under a one-time token. No
 * account exists yet, so nothing is linked to anybody. Returns the address
 * of the sign-in window.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connect_prepare extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Run it.
     *
     * @return array
     */
    public static function execute(): array {
        global $USER;

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/asyntaisearch:manage', $context);

        $handshake = 'mdl_' . bin2hex(random_bytes(12));

        // Made here and sent once, in this request body, which is the only
        // hop of the handshake that never passes through a browser. With it
        // this site can prove a preview belongs to it, and sign every later
        // call about its courses, without a second sign-in.
        $secret = bin2hex(random_bytes(24));

        $body = state::http_post_json(state::origin() . '/api/v1/wp-search/stage/', [
            'state' => $handshake,
            'site_url' => state::site_url(),
            'platform' => 'moodle',
            'product' => 'search',
            'consumer_key' => '',
            'consumer_secret' => '',
            'plugin_secret' => $secret,
        ]);
        if ($body === null) {
            throw new \moodle_exception('errunreachable', 'local_asyntaisearch');
        }

        // Kept only after Asyntai accepted it, so a failed handshake cannot
        // leave this site signing tokens with a secret nobody knows.
        state::set('secret', $secret);
        state::set('state', $handshake);
        state::set('state_at', (string) time());

        $url = state::origin() . '/wp-auth?' . http_build_query([
            'state' => $handshake,
            'site_url' => state::site_url(),
            'platform' => 'moodle',
            // Which product brought them, kept apart from which platform.
            'product' => 'search',
            'wp_email' => (string) $USER->email,
            'lang' => substr(current_language(), 0, 2),
        ], '', '&');

        return ['state' => $handshake, 'url' => $url];
    }

    /**
     * What comes back.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'state' => new external_value(PARAM_ALPHANUMEXT, 'The handshake token'),
            'url' => new external_value(PARAM_URL, 'The sign-in window address'),
        ]);
    }
}
