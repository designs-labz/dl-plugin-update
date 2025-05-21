<?php
class DL_Updater {
	const GITHUB_REPO = 'designs-labz/dl-plugin-update';
	const PLUGIN_FILE = 'dl-plugin-update/index.php';

	public static function get_repo_release() {
		$url = 'https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest';
		$response = wp_remote_get($url, [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
			],
			'timeout' => 15,
		]);

		if (is_wp_error($response)) return false;

		$body = wp_remote_retrieve_body($response);
		return json_decode($body);
	}

	public static function check_for_update($transient) {
		if (empty($transient->checked)) return $transient;

		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) return $transient;

		if (!isset($transient->checked[self::PLUGIN_FILE])) {
			return $transient;
		}

		$current_version = $transient->checked[self::PLUGIN_FILE];

		$latest_version = ltrim($release->tag_name, 'v');

		if (version_compare($current_version, $latest_version, '<')) {
			$transient->response[self::PLUGIN_FILE] = (object)[
				'slug'        => dirname(self::PLUGIN_FILE),
				'plugin'      => self::PLUGIN_FILE,
				'new_version' => $latest_version,
				'url'         => $release->html_url,
				'package'     => $release->zipball_url,
			];
		}

		return $transient;
	}

	public static function plugin_info($false, $action, $args) {
		if ($action !== 'plugin_information' || $args->slug !== dirname(self::PLUGIN_FILE)) {
			return $false;
		}

		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) return $false;

		return (object)[
			'name'          => 'DL Plugin Update',
			'slug'          => dirname(self::PLUGIN_FILE),
			'version'       => ltrim($release->tag_name, 'v'),
			'author'        => '<a href="https://designslabz.com">DesignsLabz</a>',
			'homepage'      => $release->html_url,
			'download_link' => $release->zipball_url,
			'sections'      => [
				'description' => $release->body ?: 'GitHub-based plugin update',
			],
		];
	}

	// ✅ Manual version check (can be used for admin notices, etc.)
	public static function manual_check_version() {
		if ( ! function_exists('get_plugin_data') ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file_path = WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE;

		if (!file_exists($plugin_file_path)) {
			return '❌ Plugin main file not found.';
		}

		$plugin_data = get_plugin_data($plugin_file_path);
		$current_version = $plugin_data['Version'] ?? null;

		if (!$current_version) {
			return '❌ Failed to get current plugin version.';
		}

		$release = self::get_repo_release();
		if (!$release || !isset($release->tag_name)) {
			return '❌ Failed to retrieve GitHub release info.';
		}

		$latest_version = ltrim($release->tag_name, 'v');

		if (version_compare($current_version, $latest_version, '<')) {
			return "🔔 Update available: {$current_version} → {$latest_version}";
		} else {
			return "✅ Plugin is up to date: {$current_version}";
		}
	}

}
