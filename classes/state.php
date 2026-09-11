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

namespace local_asyntaisearch;

/**
 * Everything this plugin remembers, and the calls it makes to Asyntai.
 *
 * State lives in the plugin's own config rows. None of it is ever declared
 * as an admin setting, so none of it is printed into a form: the preview
 * secret in particular must never reach a browser.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state {
    /** @var string Where the config rows live. */
    const COMPONENT = 'local_asyntaisearch';

    /** @var string[] Where the bar may go. The first one is the default. */
    const PLACEMENTS = ['navbar', 'replace', 'custom'];

    /** @var int How long a handshake token stays valid, in seconds. */
    const STATE_TTL = 900;

    /** @var int How long a preview link stays valid, in seconds. */
    const PREVIEW_TTL = 1500;

    /**
     * Where this site's PHP talks to Asyntai.
     *
     * Overridable from config.php so a staging copy can point at a test
     * server. There is deliberately no setting for it: a settings screen that
     * lets somebody retype the server address is a settings screen that lets
     * somebody break their own site in a way support cannot see.
     *
     * @return string
     */
    public static function origin(): string {
        if (defined('ASYNTAI_SEARCH_ORIGIN')) {
            return rtrim(ASYNTAI_SEARCH_ORIGIN, '/');
        }
        return 'https://asyntai.com';
    }

    /**
     * What the VISITOR'S browser downloads, which is not the same host as the
     * one above. Every other install route we document points at the widget
     * subdomain, so this one does too.
     *
     * @return string
     */
    public static function script_url(): string {
        if (defined('ASYNTAI_SEARCH_SCRIPT')) {
            return ASYNTAI_SEARCH_SCRIPT;
        }
        return 'https://widget.asyntai.com/static/js/search-widget.js';
    }

    // Storage.

    /**
     * One stored value.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function get(string $name, string $default = ''): string {
        $value = get_config(self::COMPONENT, $name);
        if ($value === false || $value === null) {
            return $default;
        }
        return (string) $value;
    }

    /**
     * Store one value.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public static function set(string $name, string $value): void {
        set_config($name, $value, self::COMPONENT);
    }

    /**
     * Drop one value.
     *
     * @param string $name
     * @return void
     */
    public static function forget(string $name): void {
        unset_config($name, self::COMPONENT);
    }

    // The connection.

    /**
     * The widget id Asyntai handed back, or an empty string.
     *
     * @return string
     */
    public static function site_id(): string {
        return trim(self::get('site_id'));
    }

    /**
     * The secret shared with Asyntai at connect. Never printed anywhere.
     *
     * @return string
     */
    public static function secret(): string {
        return trim(self::get('secret'));
    }

    /**
     * The public address of this site, as Asyntai should know it.
     *
     * @return string
     */
    public static function site_url(): string {
        global $CFG;
        return rtrim((string) $CFG->wwwroot, '/');
    }

    // Settings the site owner chose.

    /**
     * Where the bar goes: navbar, replace or custom.
     *
     * @return string
     */
    public static function placement(): string {
        $value = self::get('placement', 'navbar');
        return in_array($value, self::PLACEMENTS, true) ? $value : 'navbar';
    }

    /**
     * The CSS selector for replace and custom placement, or an empty string.
     *
     * @return string
     */
    public static function selector(): string {
        return trim(self::get('selector'));
    }

    /**
     * The placeholder text, or an empty string for the visitor's language.
     *
     * @return string
     */
    public static function placeholder(): string {
        return trim(self::get('placeholder'));
    }

    /**
     * The accent colour, or an empty string for the dashboard's choice.
     *
     * @return string
     */
    public static function accent(): string {
        return trim(self::get('accent'));
    }

    /**
     * May visitors who are not signed in see the bar?
     *
     * Off by default. The bar searches the course material the administrator
     * ticked, and on a learning site that is for its users.
     *
     * @return bool
     */
    public static function show_guests(): bool {
        return self::get('showguests', '0') === '1';
    }

    /**
     * Store the placement choices, after cleaning.
     *
     * @param string $placement
     * @param string $selector
     * @param string $placeholder
     * @param string $accent
     * @param bool $showguests
     * @return void
     */
    public static function save_placement(
        string $placement,
        string $selector,
        string $placeholder,
        string $accent,
        bool $showguests
    ): void {
        if (!in_array($placement, self::PLACEMENTS, true)) {
            $placement = 'navbar';
        }
        // A colour is a hex value or nothing. Anything else would be printed
        // into an attribute on every page.
        $accent = trim($accent);
        if ($accent !== '' && !preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $accent)) {
            $accent = '';
        }
        self::set('placement', $placement);
        self::set('selector', \core_text::substr(trim($selector), 0, 200));
        self::set('placeholder', \core_text::substr(trim($placeholder), 0, 120));
        self::set('accent', $accent);
        self::set('showguests', $showguests ? '1' : '0');
    }

    // The one question we ask Asyntai.

    /**
     * The last answer, or null when there has never been one.
     *
     * @return array|null
     */
    public static function status(): ?array {
        $raw = self::get('status');
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * May the bar render on the site right now?
     *
     * FALSE while we have never had an answer, on purpose. A bar that cannot
     * answer is worse than no bar.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $status = self::status();
        return $status !== null && !empty($status['enabled']);
    }

    /**
     * Re-ask Asyntai and store the answer.
     *
     * Returns the answer, or null when the call failed, in which case the
     * previous answer is kept: a network blip must not switch a working site's
     * search off.
     *
     * @param int $timeout Seconds to wait for Asyntai.
     * @return array|null
     */
    public static function refresh(int $timeout = 10): ?array {
        $siteid = self::site_id();
        if ($siteid === '') {
            self::forget('status');
            self::forget('status_at');
            return null;
        }

        $body = self::http_get(
            self::origin() . '/api/v1/search-widget/status/?widget_id=' . rawurlencode($siteid),
            $timeout
        );
        if ($body === null) {
            return null;
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || !array_key_exists('enabled', $decoded)) {
            return null;
        }

        self::set('status', json_encode($decoded));
        self::set('status_at', (string) time());
        return $decoded;
    }

    /**
     * Refresh only when the stored answer is older than Asyntai asked us to
     * keep it. For the settings screen and the scheduled task.
     *
     * @return array|null
     */
    public static function refresh_if_stale(): ?array {
        $status = self::status();
        if ($status === null) {
            return self::refresh();
        }
        $age = time() - (int) self::get('status_at', '0');
        return $age >= self::max_age($status) ? self::refresh() : $status;
    }

    /**
     * Whether a visitor's page should trigger a refresh.
     *
     * The scheduled task is the normal way. This only steps in when cron is
     * late by a wide margin, so a site whose cron has stopped still follows a
     * plan change within the hour.
     *
     * @return bool
     */
    public static function needs_site_refresh(): bool {
        if (self::site_id() === '') {
            return false;
        }
        $status = self::status();
        $age = time() - (int) self::get('status_at', '0');
        $limit = $status === null ? 60 : max(2 * self::max_age($status), 1800);
        return $age >= $limit;
    }

    /**
     * The same refresh, from an ordinary page on the site.
     *
     * The stamp is written BEFORE the call, not after. Otherwise a server
     * that accepts the connection and then says nothing makes EVERY visitor
     * wait the full timeout, because none of them ever gets to record an
     * attempt. With the stamp claimed first, one visitor waits and the rest
     * sail past. And the timeout is short: on a visitor's page ten seconds is
     * a page nobody waits for.
     *
     * @return void
     */
    public static function refresh_from_site(): void {
        if (!self::needs_site_refresh()) {
            return;
        }
        self::set('status_at', (string) time());
        self::refresh(4);
    }

    /**
     * How long Asyntai asked us to keep an answer, with a floor.
     *
     * @param array $status
     * @return int
     */
    private static function max_age(array $status): int {
        $maxage = isset($status['cache_seconds']) ? (int) $status['cache_seconds'] : 3600;
        return $maxage < 30 ? 30 : $maxage;
    }

    /**
     * A sentence for the settings screen.
     *
     * Known reasons are worded here so they follow the site's own language.
     * An unknown reason falls back to whatever Asyntai sent, which is what
     * lets a new reason appear without a new release of this plugin.
     *
     * @param array|null $status
     * @return string
     */
    public static function message(?array $status): string {
        if ($status === null) {
            return get_string('msgnotconnected', self::COMPONENT);
        }
        if (!empty($status['enabled'])) {
            $products = (int) ($status['products'] ?? 0);
            if ($products < 1) {
                return get_string('msglivepages', self::COMPONENT);
            }
            return get_string('msgliveproducts', self::COMPONENT, number_format($products));
        }
        $reason = (string) ($status['reason'] ?? '');
        switch ($reason) {
            case 'plan':
                return get_string('msgplan', self::COMPONENT);
            case 'limit':
                return get_string('msglimit', self::COMPONENT);
            case 'no_products':
                return sync::enabled()
                    ? get_string('msgindexing', self::COMPONENT)
                    : get_string('msgnocourses', self::COMPONENT);
            case 'unknown_widget':
                return get_string('msgunknownsite', self::COMPONENT);
        }
        return !empty($status['message'])
            ? strip_tags((string) $status['message'])
            : get_string('msgoff', self::COMPONENT);
    }

    /**
     * A sentence about the course read, or an empty string.
     *
     * @param array|null $status
     * @return string
     */
    public static function sync_message(?array $status): string {
        if ($status === null || empty($status['sync']) || !is_array($status['sync'])) {
            return '';
        }
        $sync = $status['sync'];
        $state = (string) ($sync['status'] ?? '');
        switch ($state) {
            case 'syncing':
                return get_string('syncreading', self::COMPONENT);
            case 'completed':
                return get_string('syncdone', self::COMPONENT, number_format((int) ($sync['count'] ?? 0)));
            case 'failed':
                return get_string('syncfailed', self::COMPONENT, strip_tags((string) ($sync['error'] ?? '')));
        }
        return '';
    }

    // The owner's own bar, on a link.

    /**
     * The address of this site's own search bar, carrying proof that this
     * site asked for it.
     *
     * Signed rather than relying on a session: the owner is signed in to
     * Moodle, not necessarily to Asyntai. Empty without a secret, which hides
     * the panel rather than offering a door that does not open.
     *
     * @return string
     */
    public static function preview_url(): string {
        $siteid = self::site_id();
        $secret = self::secret();
        if ($siteid === '' || $secret === '') {
            return '';
        }
        $expiry = time() + self::PREVIEW_TTL;
        return self::origin() . '/ai-search-bar/preview/?' . http_build_query([
            'widget_id' => $siteid,
            'token' => $expiry . '.' . hash_hmac('sha256', $siteid . ':' . $expiry, $secret),
        ], '', '&');
    }

    // What does the front page look like?

    /**
     * Whether the front page shows a top navigation bar, and a search box.
     *
     * The settings screen uses this to say something useful: a theme with no
     * navigation bar gives the default placement nowhere to go, and replace
     * mode does nothing on a page with no search box. Returns null when we
     * could not look, which is treated as "say nothing": a failed request
     * must never turn into advice about somebody's theme.
     *
     * @param bool $fresh Ignore the stored answer.
     * @return array|null ['navbar' => bool, 'search' => bool]
     */
    public static function probe(bool $fresh = false): ?array {
        if (!$fresh) {
            $at = (int) self::get('probe_at', '0');
            if ($at && (time() - $at) < 43200) {
                $raw = json_decode(self::get('probe'), true);
                if (is_array($raw) && isset($raw['navbar'], $raw['search'])) {
                    return ['navbar' => (bool) $raw['navbar'], 'search' => (bool) $raw['search']];
                }
            }
        }

        // The header lets our own script stay out of the page, so we measure
        // the theme and not ourselves.
        $html = self::http_get(self::site_url() . '/', 10, ['X-Asyntai-Probe: 1']);
        if ($html === null || $html === '') {
            return null;
        }
        $found = [
            'navbar' => (bool) preg_match('#id=["\']usernavigation["\']#i', $html),
            'search' => self::html_has_search($html),
        ];
        self::set('probe', json_encode($found));
        self::set('probe_at', (string) time());
        return $found;
    }

    /**
     * The same test the widget makes in the browser, done on the HTML.
     *
     * @param string $html
     * @return bool
     */
    public static function html_has_search(string $html): bool {
        if (preg_match('#<form[^>]*role=["\']search["\']#i', $html)) {
            return true;
        }
        if (preg_match('#<input[^>]*type=["\']search["\']#i', $html)) {
            return true;
        }
        // What Moodle's global search and course search actually name their input.
        return (bool) preg_match('#<input[^>]*name=["\'](q|search)["\']#i', $html);
    }

    // HTTP.

    /**
     * Body of a GET, or null on any failure.
     *
     * @param string $url
     * @param int $timeout Seconds.
     * @param string[] $headers
     * @return string|null
     */
    public static function http_get(string $url, int $timeout = 10, array $headers = []): ?string {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        try {
            $curl = new \curl();
            $curl->setHeader(array_merge(['Accept: application/json, text/html'], $headers));
            $body = $curl->get($url, [], self::curl_options($timeout));
            $info = $curl->get_info();
        } catch (\Exception $e) {
            return null;
        }
        return self::read_body($curl, $body, $info);
    }

    /**
     * Body of a JSON POST, or null on any failure.
     *
     * @param string $url
     * @param array $payload
     * @param int $timeout Seconds.
     * @return string|null
     */
    public static function http_post_json(string $url, array $payload, int $timeout = 15): ?string {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        try {
            $curl = new \curl();
            $curl->setHeader(['Content-Type: application/json', 'Accept: application/json']);
            $body = $curl->post($url, json_encode($payload), self::curl_options($timeout));
            $info = $curl->get_info();
        } catch (\Exception $e) {
            return null;
        }
        return self::read_body($curl, $body, $info);
    }

    /**
     * Timeouts for one call.
     *
     * @param int $timeout
     * @return array
     */
    private static function curl_options(int $timeout): array {
        return [
            'CURLOPT_TIMEOUT' => $timeout,
            'CURLOPT_CONNECTTIMEOUT' => min($timeout, 5),
        ];
    }

    /**
     * The body when the call succeeded, null otherwise.
     *
     * Moodle's curl wrapper reports a blocked or failed request through
     * get_errno() and returns an error string as the body, so the body alone
     * says nothing.
     *
     * @param \curl $curl
     * @param mixed $body
     * @param array $info
     * @return string|null
     */
    private static function read_body(\curl $curl, $body, array $info): ?string {
        if ($curl->get_errno()) {
            return null;
        }
        $code = isset($info['http_code']) ? (int) $info['http_code'] : 0;
        if ($code < 200 || $code > 299) {
            return null;
        }
        return is_string($body) ? $body : null;
    }
}
