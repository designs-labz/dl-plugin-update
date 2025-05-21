<?php
/**
 * Plugin Name:           DL Plugin Update Checker
 * Plugin URI:            https://github.com/designs-labz/dl-plugin-update/
 * Description:           DesignsLabz plugin update checker.
 * Version:               1.0.0
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

 // Add a simple admin notice to the dashboard
function tp_admin_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Test Plugin is activated and running!', 'test-plugin' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'tp_admin_notice' );

// Simple shortcode to display a message on the front end
function tp_display_message() {
    return '<p>This is a simple message from your test plugin!</p>';
}
add_shortcode( 'test_message', 'tp_display_message' );

// Add a settings link to the plugin page
function tp_plugin_action_links( $links ) {
    $settings_link = '<a href="options-general.php?page=test-plugin">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tp_plugin_action_links' );

// Create a settings page (for demonstration)
function tp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Test Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'tp_options_group' );
            do_settings_sections( 'test-plugin' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Test Option</th>
                    <td><input type="text" name="tp_test_option" value="<?php echo esc_attr( get_option('tp_test_option') ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings and add settings page to the menu
function tp_plugin_menu() {
    add_options_page( 'Test Plugin Settings', 'Test Plugin', 'manage_options', 'test-plugin', 'tp_settings_page' );
}
add_action( 'admin_menu', 'tp_plugin_menu' );

function tp_register_settings() {
    register_setting( 'tp_options_group', 'tp_test_option' );
}
add_action( 'admin_init', 'tp_register_settings' );

// Load the plugin update checker library
// to check for updates from a GitHub repository.
require_once plugin_dir_path(__FILE__) . 'vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/designs-labz/dl-plugin-update/',
    __FILE__,
    'dl-plugin-update'
);
