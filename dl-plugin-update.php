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

// Include updater class
require_once plugin_dir_path(__FILE__) . 'includes/class-dl-updater.php';

// Hook updater
add_filter('pre_set_site_transient_update_plugins', ['DL_Updater', 'check_for_update']);
add_filter('plugins_api', ['DL_Updater', 'plugin_info'], 10, 3);

// Show version check result in admin
add_action('admin_notices', function () {
	if (!current_user_can('update_plugins')) return;
	$message = DL_Updater::manual_check_version();
	echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($message) . '</p></div>';
});
