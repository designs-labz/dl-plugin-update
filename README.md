=== DL Plugin Update Checker ===
Contributors:      designs-labz
Tags:              plugin update, github update, updater, wordpress github updater
Requires at least: 6.1  
Tested up to:      6.8.2
Requires PHP:      7.4  
Stable tag:        1.1.1
License:           GPLv3 or later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

Automatically check for updates from GitHub repositories and update your plugin directly from GitHub releases.

== Description ==

**DL Plugin Update Checker** enables your custom WordPress plugin to update automatically via GitHub.

Features:
- Check for plugin updates hosted on GitHub.
- Uses the YahnisElsts/plugin-update-checker library.
- Supports private repositories (via token).
- GitHub release/tag-based update flow.
- Can be integrated into any WordPress plugin.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Make sure your plugin repository includes:
   - A `main.php` with version headers
   - Tagged GitHub releases matching the plugin version

== Frequently Asked Questions ==

= Can this update plugins from private GitHub repos? =  
Yes. You must provide a personal access token via `$updateChecker->setAuthentication('your-token');`.

= Does this plugin include its own GitHub update logic? =  
Yes. It includes the YahnisElsts/plugin-update-checker library internally.

= Can I use this with multiple plugins? =  
Yes. Each plugin must include and configure its own update checker instance.

== Screenshots ==

1. GitHub release triggering update.
2. Update notice shown in WordPress dashboard.

== Changelog ==

= 1.0.10 =
* Initial release
* GitHub updater integrated
* Public and private repo support

== License ==

This plugin is licensed under the GPLv3 or later.  
For details, visit [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html).
