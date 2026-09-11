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
 * The plugin's settings page: connect, status, placement and course content.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_asyntaisearch\state;
use local_asyntaisearch\sync;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_asyntaisearch_manage');

$context = context_system::instance();
require_capability('local/asyntaisearch:manage', $context);

$PAGE->set_title(get_string('pluginname', 'local_asyntaisearch'));
$PAGE->set_heading(get_string('pluginname', 'local_asyntaisearch'));

$returnurl = new moodle_url('/local/asyntaisearch/manage.php');

// Where the bar goes.
if (optional_param('saveplacement', 0, PARAM_INT) && confirm_sesskey()) {
    state::save_placement(
        optional_param('placement', 'navbar', PARAM_ALPHA),
        optional_param('selector', '', PARAM_TEXT),
        optional_param('placeholder', '', PARAM_TEXT),
        optional_param('accent', '', PARAM_TEXT),
        (bool) optional_param('showguests', 0, PARAM_INT)
    );
    redirect($returnurl, get_string('placementsaved', 'local_asyntaisearch'));
}

// Which courses may be read.
if (optional_param('savesync', 0, PARAM_INT) && confirm_sesskey()) {
    $enabled = (bool) optional_param('syncenabled', 0, PARAM_INT);
    $courseids = optional_param_array('courses', [], PARAM_INT);
    $types = optional_param_array('types', [], PARAM_PLUGIN);
    $wasenabled = sync::enabled();
    sync::save_selection($enabled, $courseids, $types);

    if (!$enabled) {
        // Take the key back and tell Asyntai to drop what it read.
        if ($wasenabled) {
            sync::handover('disconnect');
        }
        sync::revoke_tokens();
        redirect($returnurl, get_string('syncsavedoff', 'local_asyntaisearch'));
    }

    // Web services stay off until the administrator actually asks for
    // content sync. Nothing is switched on behind their back, and nothing
    // here runs on install or upgrade.
    sync::enable_webservices();

    $token = sync::issue_token();
    if ($token === '') {
        redirect(
            $returnurl,
            get_string('synctokenfailed', 'local_asyntaisearch'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    // Hand the key over, signed with the secret from connect, so nobody has
    // to sign in a second time. Asyntai checks the key against this site
    // before it keeps it, and starts reading the ticked courses at once.
    $result = sync::handover('sync', $token);
    if (!$result['ok']) {
        // The key is real and this site keeps it, but Asyntai never got it.
        // Take it back rather than leave a live key nobody is using.
        sync::revoke_tokens();
        redirect(
            $returnurl,
            get_string('synchandoverfailed', 'local_asyntaisearch', $result['error']),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    // The status now carries the read, so ask for it straight away.
    state::refresh();
    redirect($returnurl, get_string('syncsaved', 'local_asyntaisearch'));
}

// Render.
$siteid = state::site_id();
$isconnected = $siteid !== '';
$status = $isconnected ? state::refresh_if_stale() : null;

$data = [
    'isconnected' => $isconnected,
    'formurl' => $returnurl->out(false),
    'sesskey' => sesskey(),
];

if ($isconnected) {
    $reason = is_array($status) && isset($status['reason']) ? $status['reason'] : '';
    if (is_array($status) && !empty($status['enabled'])) {
        $statekey = 'live';
    } else if ($reason === 'no_products') {
        $statekey = 'settingup';
    } else if ($reason === 'plan' || $reason === 'limit') {
        $statekey = 'blocked';
    } else {
        $statekey = 'unknown';
    }
    $dots = ['live' => 'on', 'settingup' => 'busy', 'blocked' => 'busy', 'unknown' => 'off'];

    $allowance = '';
    if ($statekey === 'live' && !empty($status['monthly_limit'])) {
        $allowance = get_string('allowance', 'local_asyntaisearch', (object) [
            'left' => number_format((int) ($status['searches_left'] ?? 0)),
            'limit' => number_format((int) $status['monthly_limit']),
        ]);
    }

    // Shown only when we looked at the front page and found a problem.
    // Silent when the probe failed: telling somebody their theme lacks a
    // navigation bar because our own request timed out is worse than
    // saying nothing at all.
    $hint = '';
    if ($statekey === 'live') {
        $probe = state::probe();
        if (is_array($probe)) {
            if (state::placement() === 'navbar' && !$probe['navbar']) {
                $hint = get_string('hintnonavbar', 'local_asyntaisearch');
            } else if (state::placement() === 'replace' && !$probe['search']) {
                $hint = get_string('hintnosearch', 'local_asyntaisearch');
            }
        }
    }

    $accountemail = state::get('account_email');
    $data += [
        'statedot' => $dots[$statekey],
        'stateheading' => get_string('state' . $statekey, 'local_asyntaisearch'),
        'statedetail' => $statekey === 'unknown'
            ? get_string('stateunknowndetail', 'local_asyntaisearch')
            : state::message($status),
        'syncdetail' => state::sync_message($status),
        'allowance' => $allowance,
        'connectedas' => $accountemail !== '' ? get_string('connectedas', 'local_asyntaisearch', $accountemail) : '',
        'dashboardurl' => !empty($status['dashboard_url']) ? $status['dashboard_url'] : 'https://asyntai.com/dashboard',
        'analyticsurl' => $statekey === 'live'
            ? (!empty($status['analytics_url']) ? $status['analytics_url'] : 'https://asyntai.com/ai-search-analytics/')
            : '',
        'previewurl' => state::preview_url(),
        'hint' => $hint,
        'placements' => array_map(function ($key) {
            return [
                'value' => $key,
                'label' => get_string('placement' . $key, 'local_asyntaisearch'),
                'desc' => get_string('placement' . $key . 'desc', 'local_asyntaisearch'),
                'checked' => state::placement() === $key,
            ];
        }, state::PLACEMENTS),
        'selector' => state::selector(),
        'placeholder' => state::placeholder(),
        'accent' => state::accent(),
        'showguests' => state::show_guests(),
        'syncenabled' => sync::enabled(),
    ];

    $selectedcourses = sync::selected_course_ids();
    $courserows = [];
    foreach (sync::all_courses() as $course) {
        $courserows[] = [
            'id' => $course->id,
            'fullname' => $course->fullname,
            'shortname' => $course->shortname,
            'categoryname' => $course->categoryname,
            'hidden' => empty($course->visible),
            'checked' => in_array((int) $course->id, $selectedcourses, true),
        ];
    }
    $data['courses'] = $courserows;
    $data['hascourses'] = !empty($courserows);

    // Only offer activity types this site actually has. An administrator can
    // uninstall a module, and asking for its name would then be a fatal
    // error on the settings page rather than a missing tick box.
    $installedmods = array_keys(core_component::get_plugin_list('mod'));
    $selectedtypes = sync::selected_types();
    $typerows = [];
    foreach (sync::MODULE_TYPES as $type) {
        if (!in_array($type, $installedmods, true)) {
            continue;
        }
        $typerows[] = [
            'type' => $type,
            'label' => get_string('pluginname', 'mod_' . $type),
            'checked' => in_array($type, $selectedtypes, true),
        ];
    }
    $data['types'] = $typerows;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_asyntaisearch/manage', $data);
$PAGE->requires->js_call_amd('local_asyntaisearch/connect', 'init');
echo $OUTPUT->footer();
