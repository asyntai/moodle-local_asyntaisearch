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
 * The connect handshake, in the browser.
 *
 * Everything it calls goes to this site's own web service functions. Nothing
 * is ever fetched from Asyntai as a script, so no code from another server
 * runs inside the administration.
 *
 * @module     local_asyntaisearch/connect
 * @copyright  2026 Asyntai <hello@asyntai.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/str'], function(Ajax, Str) {
    'use strict';

    var strings = {};
    var popup = null;

    /**
     * Show a line above the panels.
     *
     * @param {String} message
     * @param {Boolean} ok
     */
    function say(message, ok) {
        var box = document.getElementById('asyntai-alert');
        if (!box) {
            return;
        }
        box.style.display = 'block';
        box.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
        box.textContent = message;
    }

    /**
     * Call one of this plugin's web service functions.
     *
     * @param {String} name
     * @param {Object} args
     * @return {Promise}
     */
    function call(name, args) {
        return Ajax.call([{methodname: 'local_asyntaisearch_' + name, args: args || {}}])[0];
    }

    /**
     * Ask again every two seconds until the owner has signed in.
     *
     * @param {String} state
     * @param {Number} attempt
     */
    function poll(state, attempt) {
        // Fifteen minutes, the same as the handshake token itself. Creating an
        // account and finding a password takes longer than a sign-in.
        if (attempt > 450) {
            say(strings.jstimeout, false);
            return;
        }
        call('connect_poll', {state: state}).then(function(data) {
            if (data && data.ready) {
                say(strings.jssaving, true);
                window.location.reload();
                return;
            }
            setTimeout(function() {
                poll(state, attempt + 1);
            }, 2000);
            return;
        }).catch(function(error) {
            // A handshake Moodle refused never recovers by waiting. Anything
            // else is a blip worth another try.
            if (error && error.errorcode && error.errorcode !== 'errunreachable') {
                if (popup) {
                    try {
                        popup.close();
                    } catch (e) {
                        // The window may already be gone.
                    }
                }
                say(error.message || strings.jsfailed, false);
                return;
            }
            setTimeout(function() {
                poll(state, attempt + 1);
            }, 3000);
        });
    }

    /**
     * The window must be opened inside the click, before any network call,
     * or the browser treats it as unrequested and blocks it.
     */
    function connect() {
        popup = window.open('about:blank', 'asyntai_connect', 'width=820,height=740,scrollbars=yes,resizable=yes');
        if (!popup) {
            say(strings.jsblocked, false);
            return;
        }
        say(strings.jspreparing, true);
        call('connect_prepare', {}).then(function(data) {
            popup.location = data.url;
            say(strings.jswaiting, true);
            poll(data.state, 0);
            return;
        }).catch(function(error) {
            if (popup) {
                try {
                    popup.close();
                } catch (e) {
                    // The window may already be gone.
                }
            }
            say((error && error.message) || strings.jsfailed, false);
        });
    }

    /**
     * Forget the connection.
     */
    function disconnect() {
        if (!window.confirm(strings.jsconfirmdisconnect)) {
            return;
        }
        call('disconnect', {}).then(function() {
            window.location.reload();
            return;
        }).catch(function(error) {
            say((error && error.message) || strings.jsfailed, false);
        });
    }

    /**
     * Wire the buttons.
     */
    function init() {
        var keys = ['jspreparing', 'jswaiting', 'jssaving', 'jsblocked', 'jsfailed', 'jstimeout', 'jsconfirmdisconnect'];
        Str.get_strings(keys.map(function(key) {
            return {key: key, component: 'local_asyntaisearch'};
        })).then(function(values) {
            keys.forEach(function(key, i) {
                strings[key] = values[i];
            });
            return;
        }).catch(function() {
            // Buttons still work; the messages fall back to English below.
            keys.forEach(function(key) {
                strings[key] = strings[key] || key;
            });
        });

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) {
                return;
            }
            if (target.id === 'asyntai-connect') {
                event.preventDefault();
                connect();
            } else if (target.id === 'asyntai-disconnect') {
                event.preventDefault();
                disconnect();
            }
        });
    }

    return {init: init};
});
