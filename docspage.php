<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle Component Library
 *
 * Servers the Hugo docs html pages.
 *
 * @package    tool_componentlibrary
 * @copyright  2020 Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// we need just the values from config.php and minlib.php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/lib/filelib.php');

if (empty($relativepath)) {
    $relativepath = get_file_argument();
}
$args = explode('/', ltrim($relativepath, '/'));

$docs = clean_param($args[0], PARAM_TEXT);
$version = clean_param($args[1], PARAM_TEXT);
$folder = clean_param($args[2], PARAM_TEXT);
$page = clean_param($args[3], PARAM_TEXT);
$theme = optional_param('theme', '', PARAM_TEXT);


$docsdir = '/admin/tool/componentlibrary/docs/';
$cssfile = '/admin/tool/componentlibrary/hugo/dist/css/docs.css';

if ($docs == 'bootstrap-4.3') {
	$docspage = $CFG->dirroot . $docsdir . 'bootstrap-4.3/' . $version . '/' . $folder . '/' . $page . '/index.html';
} else if ($docs == 'moodle-3.9') {
	$docspage = $CFG->dirroot . $docsdir . 'moodle-3.9/' . $version . '/' . $folder . '/' . $page . '/index.html';
}

$PAGE->set_pagelayout('embedded');
$thispageurl = new moodle_url('/admin/tool/componentlibrary/docspage.php');
$PAGE->set_url($thispageurl, $thispageurl->params());
$PAGE->set_context(context_system::instance());
$title = get_string('pluginname', 'tool_componentlibrary');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->set_docs_path('');
$PAGE->requires->css($cssfile);

echo $OUTPUT->header();
$config = new stdClass();
$config->posturl = $PAGE->url . $relativepath;
$config->jsonfile = $CFG->wwwroot . '/admin/tool/componentlibrary/docs/my-index.json';
echo $OUTPUT->render_from_template('tool_componentlibrary/navbar', $config);
if (!file_exists($CFG->dirroot . $docsdir)) {
	echo $OUTPUT->render_from_template('tool_componentlibrary/rundocs', (object) []);
	exit(0);
}
echo $OUTPUT->footer();


$themes = core_component::get_plugin_list('theme');
foreach ($themes as $themename => $themedir) {
	$config->themes[] = (object) ['name' => $themename, 'url' => $url];
}


// Load the content after the footer that contains the JS for this page.
if (file_exists($docspage)) {
    $page = file_get_contents($docspage);
    $page = str_replace('http://MOODLEROOT', $thispageurl, $page);
    $page = str_replace('MOODLEIMAGEDIR', new moodle_url('/admin/tool/componentlibrary/content/static'), $page);
    $filtered = str_replace('MOODLEROOT', $thispageurl, $page);
    $filtered = str_replace('MOODLESITE', $CFG->wwwroot, $page);
    echo $filtered;
} else {
	$firstpage = new moodle_url('/admin/tool/componentlibrary/docspage.php/moodle-3.9/getting-started/introduction/');
	redirect($firstpage);
}

