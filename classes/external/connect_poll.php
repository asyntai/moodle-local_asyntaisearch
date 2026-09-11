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
 * Step two of the connect handshake: has the owner finished signing in?
 *
 * Polled from PHP and NOT as a script tag, so no code from another server
 * ever executes inside the administration. Once the answer carries a site
 * id, it is stored here and the first status is fetched.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connect_poll extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'state' => new external_value(PARAM_ALPHANUMEXT, 'The handshake token this site generated'),
        ]);
    }

    /**
     * Run it.
     *
     * @param string $handshake
     * @return array
     */
    public static function execute(string $handshake): array {
        $params = self::validate_parameters(self::execute_parameters(), ['state' => $handshake]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/asyntaisearch:manage', $context);

        $handshake = trim($params['state']);
        $stored = state::get('state');
        $startedat = (int) state::get('state_at', '0');

        // Only the token this site just generated is ever polled, so a
        // guessed or replayed token cannot be used to read somebody else's
        // handshake.
        if ($handshake === '' || $handshake !== $stored) {
            throw new \moodle_exception('errhandshake', 'local_asyntaisearch');
        }
        if (time() - $startedat > state::STATE_TTL) {
            throw new \moodle_exception('errtimeout', 'local_asyntaisearch');
        }

        $body = state::http_get(state::origin() . '/api/v1/wp-search/connect-status/?state='
            . rawurlencode($handshake));
        if ($body === null) {
            return ['ready' => false, 'accountemail' => ''];
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || empty($decoded['ready']) || empty($decoded['data']['site_id'])) {
            return ['ready' => false, 'accountemail' => ''];
        }

        $siteid = trim((string) $decoded['data']['site_id']);
        // The id is ours, so its shape is known.
        if (!preg_match('/^[A-Za-z0-9_-]{6,64}$/', $siteid)) {
            throw new \moodle_exception('errhandshake', 'local_asyntaisearch');
        }
        $email = isset($decoded['data']['account_email'])
            ? clean_param((string) $decoded['data']['account_email'], PARAM_EMAIL) : '';

        state::set('site_id', $siteid);
        state::set('account_email', $email);
        state::forget('state');
        state::forget('state_at');
        state::forget('probe');
        state::forget('probe_at');
        state::refresh();

        return ['ready' => true, 'accountemail' => $email];
    }

    /**
     * What comes back.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'ready' => new external_value(PARAM_BOOL, 'Whether the sign-in has finished'),
            'accountemail' => new external_value(PARAM_EMAIL, 'The Asyntai account, once ready'),
        ]);
    }
}
