<?php
/**
 * Plugin Name:           DL Plugin Update Checker
 * Plugin URI:            https://github.com/designs-labz/dl-plugin-update/
 * Description:           DesignsLabz plugin update checker.
 * Version:               1.0.8
 * Requires PHP:          7.4
 * Requires at least:     6.1
 * Tested up to:          6.8.2
 * Author:                DesignsLabz
 * Author URI:            https://designslabz.com/
 * License:               GPL-3.0-or-later
 * License URI:           https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:           designslabz
 * GitHub Plugin URI:     https://github.com/designs-labz/dl-plugin-update/
 * GitHub Branch:         main
 */

defined('ABSPATH') || exit;

// Autoload or manually include the updater
require_once plugin_dir_path(__FILE__) . 'includes/GitHubUpdater.php';

// Check if the class exists before creating an instance
use DL\GitHubUpdater;

// Initialize the updater
new GitHubUpdater([
	'plugin_file'   => __FILE__,
	'github_user'   => 'designs-labz', // GitHub username
	'github_repo'   => 'dl-plugin-update', // Repo name
	'plugin_slug'   => plugin_basename(__FILE__),
	'version'       => '1.0.8',
]);
