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

namespace local_asyntaisearch\task;

use local_asyntaisearch\state;

/**
 * Ask Asyntai whether the bar may be shown, when the stored answer is old.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresh_status extends \core\task\scheduled_task {
    /**
     * The name shown in Site administration.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskrefresh', 'local_asyntaisearch');
    }

    /**
     * Run it.
     *
     * @return void
     */
    public function execute(): void {
        if (state::site_id() === '') {
            return;
        }
        state::refresh_if_stale();
    }
}
