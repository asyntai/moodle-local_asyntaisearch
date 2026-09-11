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
use local_asyntaisearch\sync;

/**
 * Forget this site's connection.
 *
 * The read key is taken back first and Asyntai is told to drop what it
 * read, then every stored value goes. The secret goes with it, because a
 * secret kept after a disconnect can only ever sign a link nobody should
 * follow.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disconnect extends external_api {
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
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/asyntaisearch:manage', $context);

        if (sync::enabled()) {
            // Best effort. A site that cannot reach Asyntai still disconnects.
            sync::handover('disconnect');
        }
        sync::revoke_tokens();
        set_config('syncenabled', 0, 'local_asyntaisearch');

        foreach (
            ['site_id', 'secret', 'status', 'status_at', 'state', 'state_at',
                'account_email', 'probe', 'probe_at'] as $key
        ) {
            state::forget($key);
        }

        return ['ok' => true];
    }

    /**
     * What comes back.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'ok' => new external_value(PARAM_BOOL, 'Always true'),
        ]);
    }
}
