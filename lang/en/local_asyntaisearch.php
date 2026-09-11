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
 * Language strings for the Asyntai AI Search plugin.
 *
 * @package     local_asyntaisearch
 * @copyright   2026 Asyntai <hello@asyntai.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Asyntai AI Search';
$string['asyntaisearch:manage'] = 'Manage the Asyntai AI Search plugin';
$string['asyntaisearch:sync'] = 'Read the ticked course material for Asyntai';
$string['taskrefresh'] = 'Check with Asyntai whether the search bar may be shown';

// Connect panel.
$string['connectheading'] = 'Connect your Asyntai account';
$string['connectintro'] = 'Sign in, or create a free account. Then tick the courses the bar may search, and it switches on by itself when they are ready.';
$string['connectbutton'] = 'Connect Asyntai';
$string['connectfineprint'] = 'Only the courses you tick are read. Nothing in Moodle is changed.';
$string['point1'] = 'Students describe what they need, in their own words, and find the right page, book chapter or file.';
$string['point2'] = 'Nothing to paste. Nothing to set up.';
$string['point3'] = 'Grades, submissions, forum posts and quiz answers are never read.';

// Status.
$string['statelive'] = 'Live on your site';
$string['statesettingup'] = 'Setting up your site';
$string['stateblocked'] = 'Connected, but not on your site yet';
$string['stateunknown'] = 'We could not reach Asyntai just now';
$string['stateunknowndetail'] = 'Your site is still connected. We will check again shortly, and nothing on your site has changed in the meantime.';
$string['connectedas'] = 'Connected as {$a}';
$string['allowance'] = '{$a->left} of your {$a->limit} monthly replies left. Searches and chat replies share this allowance.';
$string['linkdashboard'] = 'Open your Asyntai dashboard';
$string['linkanalytics'] = 'See what people searched for';
$string['linkdisconnect'] = 'Disconnect this site';

// Messages about the answer Asyntai gave.
$string['msgnotconnected'] = 'Not connected yet.';
$string['msglivepages'] = 'Searching your courses and pages.';
$string['msgliveproducts'] = 'Searching {$a} products, plus your courses and pages.';
$string['msgplan'] = 'Your plan does not include the AI Search Bar yet. Nothing on your site has changed.';
$string['msglimit'] = 'You have used all your replies for this month. The bar comes back when your allowance resets.';
$string['msgindexing'] = 'We are reading your courses. This takes a few minutes, then the bar switches on by itself.';
$string['msgnocourses'] = 'Nothing to search yet. Tick the courses the bar may read, below, and save.';
$string['msgunknownsite'] = 'This site is not connected to Asyntai any more. Connect it again above.';
$string['msgoff'] = 'The search bar is not running.';
$string['syncreading'] = 'Reading your ticked courses now.';
$string['syncdone'] = '{$a} items read from your ticked courses.';
$string['syncfailed'] = 'Reading your courses failed: {$a}';

// Preview.
$string['previewheading'] = 'Try it on your own content';
$string['previewintro'] = 'Open your real search bar and type something a student would look for. Searches you run there are not counted against your monthly allowance.';
$string['previewbutton'] = 'Try your search bar';

// Hints.
$string['hintnonavbar'] = 'We could not find the top navigation bar on your front page, so the bar has nowhere to go. Choose a different place below, or give a CSS selector.';
$string['hintnosearch'] = 'Your theme shows no search box on the front page, so there is nothing for the bar to replace yet. Choose In the top navigation bar below.';

// Placement.
$string['placementtitle'] = 'Where the bar goes';
$string['placement'] = 'Place';
$string['placementnavbar'] = 'In the top navigation bar';
$string['placementnavbardesc'] = 'Shown on every page, next to the user menu. Works with Boost, Classic and the themes built on them.';
$string['placementreplace'] = 'In place of the theme\'s own search box';
$string['placementreplacedesc'] = 'Needs a search box on the page: global search in the navigation bar, or the course search box. Pressing Enter without picking a result still opens the usual search page.';
$string['placementcustom'] = 'Inside an element I choose';
$string['placementcustomdesc'] = 'Give a CSS selector below. The bar is rendered inside every element that matches.';
$string['selector'] = 'CSS selector';
$string['selectordesc'] = 'For the theme\'s own search box: leave empty and the navigation-bar search and the course search box are replaced. For an element you choose: the element to render into.';
$string['showguests'] = 'Show the bar to visitors who are not signed in';
$string['showguestsdesc'] = 'Off by default. The bar searches the course material you ticked, so only signed-in users see it unless you say otherwise.';
$string['placeholder'] = 'Placeholder text';
$string['placeholderdesc'] = 'Leave empty and each visitor sees it in their own language.';
$string['accent'] = 'Accent colour';
$string['accentdesc'] = 'A hex colour such as #1f4e79. Leave empty to use the colour set in your Asyntai dashboard.';
$string['saveplacement'] = 'Save placement';
$string['placementsaved'] = 'Placement saved.';

// Course content.
$string['synctitle'] = 'Course content';
$string['syncdesc'] = 'Choose which courses the search bar may read. Nothing is read until you tick a course and save.';
$string['syncenable'] = 'Let Asyntai read the ticked courses';
$string['synccourses'] = 'Courses';
$string['synctypes'] = 'Activity types';
$string['syncsave'] = 'Save course content';
$string['syncsaved'] = 'Course content saved. We are reading the ticked courses now.';
$string['syncsavedoff'] = 'Course content saved. Your courses are no longer read.';
$string['nocourses'] = 'This site has no courses yet.';
$string['coursehidden'] = '(hidden)';
$string['syncnote'] = 'Only the course material is read: descriptions, pages, book chapters and the names of attached files. Student data is never read. That includes grades, submissions, forum posts and quiz answers. Saving switches on Moodle web services and the REST protocol, and creates one token that can call a single read-only function.';
$string['synctokenfailed'] = 'Could not create the read key. Check that the Asyntai AI Search plugin finished installing, then try again.';
$string['synchandoverfailed'] = 'Saved, but Asyntai could not take the read key: {$a} The key was removed again. Press Save once more when the problem is fixed.';

// Errors.
$string['errunreachable'] = 'Could not reach Asyntai. Check that this site can make outgoing HTTPS requests.';
$string['errtimeout'] = 'This connection attempt has expired. Please press Connect again.';
$string['errhandshake'] = 'This connection attempt is not the one this site started. Please press Connect again.';
$string['errfailed'] = 'Could not connect. Please try again.';

// Browser messages.
$string['jspreparing'] = 'Preparing...';
$string['jswaiting'] = 'Waiting for you to finish in the Asyntai window...';
$string['jssaving'] = 'Connected. Saving...';
$string['jsblocked'] = 'Your browser blocked the pop-up. Allow pop-ups and try again.';
$string['jsfailed'] = 'Could not connect. Please try again.';
$string['jstimeout'] = 'Timed out waiting for the Asyntai window. Please try again.';
$string['jsconfirmdisconnect'] = 'Disconnect this site from Asyntai? The search bar stops, and the read key is deleted.';

// Privacy.
$string['privacy:metadata'] = 'The Asyntai AI Search plugin stores only its own settings, and no personal data.';
$string['privacy:metadata:asyntai'] = 'The search bar runs on the Asyntai service, so some data leaves this site.';
$string['privacy:metadata:asyntai:query'] = 'The words a visitor types into the search bar, so results can be returned.';
$string['privacy:metadata:asyntai:language'] = 'The language of the page, so results are worded in the right one.';
$string['privacy:metadata:asyntai:coursecontent'] = 'The material in the courses the administrator ticked, so it can be searched. Student data is never sent.';
