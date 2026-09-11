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
 * Puts the search bar on the page.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class embed {
    /** @var string[] Page layouts that must never carry the bar. */
    const SKIP_LAYOUTS = ['embedded', 'popup', 'secure', 'print', 'frametop', 'redirect', 'maintenance'];

    /**
     * What replace mode takes the place of when no selector is given.
     *
     * Named rather than left to the widget's own guess. Boost keeps its
     * global search folded behind an icon, and a folded form is invisible
     * to the guess, which then settled for the first search-looking box on
     * the page: the Timeline block's activity filter on the Dashboard. That
     * filter, and the message drawer's search, are built from the same
     * template as the navigation-bar search, so the wrapper is named with
     * its place: the one inside the navigation bar, and the course search box.
     *
     * @var string
     */
    const REPLACE_DEFAULT = '#usernavigation .simplesearchform, #coursesearch';

    /**
     * The markup for the head of the page, or an empty string.
     *
     * Loaded in the head rather than after the load event, unlike our chat
     * widget: this bar sits in the header, and a late arrival means the
     * visitor watches it appear.
     *
     * @return string
     */
    public static function head_html(): string {
        global $PAGE;

        if ((defined('CLI_SCRIPT') && CLI_SCRIPT) || (defined('AJAX_SCRIPT') && AJAX_SCRIPT)) {
            return '';
        }
        if (state::site_id() === '') {
            return '';
        }
        // Our own look at the front page, which exists to measure the theme
        // and not ourselves.
        if (self::is_probe_request()) {
            return '';
        }

        // The scheduled task keeps the status current. Should cron have
        // stopped, this catches up after the page has been sent.
        if (state::needs_site_refresh()) {
            \core_shutdown_manager::register_function([state::class, 'refresh_from_site']);
        }

        if (!state::enabled()) {
            return '';
        }
        if (!state::show_guests() && (!isloggedin() || isguestuser())) {
            return '';
        }
        try {
            if (in_array($PAGE->pagelayout, self::SKIP_LAYOUTS, true)) {
                return '';
            }
        } catch (\Exception $e) {
            // A page with no layout yet is a page we can still render on.
            $e = null;
        }

        $attributes = [
            'src' => state::script_url(),
            'async' => 'async',
            'data-asyntai-id' => state::site_id(),
        ];

        $placement = state::placement();
        $selector = state::selector();
        $html = '';

        if ($placement === 'replace') {
            $attributes['data-replace'] = $selector !== '' ? $selector : self::REPLACE_DEFAULT;
            $html .= self::replace_css();
        } else if ($placement === 'custom' && $selector !== '') {
            $attributes['data-target'] = $selector;
        } else {
            // The default: a slot of our own in the top navigation bar. The
            // widget renders into any element marked data-asyntai-search, so
            // the slot only has to exist before the widget boots.
            $html .= self::navbar_slot();
        }

        $accent = state::accent();
        if ($accent !== '') {
            $attributes['data-accent'] = $accent;
        }
        $placeholder = state::placeholder();
        if ($placeholder !== '') {
            $attributes['data-placeholder'] = $placeholder;
        }

        $html .= '<script';
        foreach ($attributes as $name => $value) {
            $html .= ' ' . $name . '="' . s($value) . '"';
        }
        $html .= '></script>' . "\n";

        return $html;
    }

    /**
     * A container in the navigation bar, made as soon as the bar exists.
     *
     * Boost, Classic and the themes built on them all give the right-hand
     * group of the navigation bar the id usernavigation. The slot goes at
     * the start of that group, so the bar sits between the site links and
     * the user menu.
     *
     * @return string
     */
    private static function navbar_slot(): string {
        $css = '.asyntai-search-slot{display:flex;align-items:center;width:300px;max-width:38vw;'
            . 'margin:0 .5rem;flex:0 1 auto}'
            . '@media (max-width:767.98px){.asyntai-search-slot{width:170px;margin:0 .25rem}}';
        $js = '(function(){function p(){var u=document.getElementById("usernavigation");'
            . 'if(!u||document.querySelector(".asyntai-search-slot")){return;}'
            . 'var s=document.createElement("div");s.className="asyntai-search-slot";'
            . 's.setAttribute("data-asyntai-search","");u.insertBefore(s,u.firstChild);}'
            . 'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",p);}'
            . 'else{p();}})();';
        return '<style>' . $css . '</style>' . "\n" . '<script>' . $js . '</script>' . "\n";
    }

    /**
     * Room for the bar where the folded search icon stood.
     *
     * The widget copies the width of what it replaces, and an icon is forty
     * pixels wide. Sized here instead, and only inside the navigation bar,
     * so a course search box keeps the width the theme gave it.
     *
     * @return string
     */
    private static function replace_css(): string {
        return '<style>#usernavigation > div:has(> .asai-host){width:300px;max-width:38vw;'
            . 'margin:0 .5rem;display:flex;align-items:center}'
            . '@media (max-width:767.98px){#usernavigation > div:has(> .asai-host){width:170px}}'
            . '</style>' . "
";
    }

    /**
     * True while we are rendering the front page for our own probe.
     *
     * @return bool
     */
    public static function is_probe_request(): bool {
        return isset($_SERVER['HTTP_X_ASYNTAI_PROBE']) && $_SERVER['HTTP_X_ASYNTAI_PROBE'] === '1';
    }
}
