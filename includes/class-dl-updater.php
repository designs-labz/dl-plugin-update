<?php

class DL_Updater {
	const GITHUB_REPO = 'designs-labz/dl-plugin-update';

	// Automatically detect the plugin file path
	public static function get_plugin_file() {
		return plugin_basename(dirname(__DIR__) . '/dl-plugin-update.php');
	}

	// Fetch latest GitHub release
	public static function get_repo_release() {
		$url = 'https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest';
		$response = wp_remote_get($url, [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo('version'),
			],
			'timeout' => 15,
		]);

		if (is_wp_error($response)) return false;

		$body = wp_remote_retrieve_body($response);
		return json_decode($body);
	}

	// Inject update into WP plugin update check
	public static function check_for_update($transient) {
		$plugin_file = self::get_plugin_file();

		if (empty($transient->checked) || !isset($transient->checked[$plugin_file])) {
			return $transient;
		}

		$current_version = $transient->checked[$plugin_file];
		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) return $transient;

		$latest_version = ltrim($release->tag_name, 'v');

		if (version_compare($current_version, $latest_version, '<')) {
			$transient->response[$plugin_file] = (object)[
				'slug'        => dirname($plugin_file),
				'plugin'      => $plugin_file,
				'new_version' => $latest_version,
				'url'         => $release->html_url,
				'package'     => $release->zipball_url,
			];
		}

		return $transient;
	}

	// Inject plugin info popup (changelog etc.)
	public static function plugin_info($false, $action, $args) {
		if ($action !== 'plugin_information') return $false;

		$plugin_file = self::get_plugin_file();
		if ($args->slug !== dirname($plugin_file)) return $false;

		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) return $false;

		return (object)[
			'name'           => 'DL Plugin Update',
			'slug'           => dirname($plugin_file),
			'version'        => ltrim($release->tag_name, 'v'),
			'author'         => '<a href="https://designslabz.com">DesignsLabz</a>',
			'homepage'       => $release->html_url,
			'download_link'  => $release->zipball_url,
			'sections'       => [
				'description' => $release->body ?? 'GitHub plugin updater',
			],
		];
	}

	// Admin notice version check
	public static function manual_check_version() {
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file_path = WP_PLUGIN_DIR . '/' . self::get_plugin_file();
		if (!file_exists($plugin_file_path)) {
			return '❌ Plugin main file not found.';
		}

		$data = get_plugin_data($plugin_file_path);
		$current = $data['Version'] ?? null;
		if (!$current) return '❌ Current version not found.';

		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) {
			return '❌ Failed to retrieve GitHub release info.';
		}

		$latest = ltrim($release->tag_name, 'v');
		if (version_compare($current, $latest, '<')) {
			return "🔔 Update available: {$current} → {$latest}";
		}
		return "✅ Plugin is up to date: {$current}";
	}
}
