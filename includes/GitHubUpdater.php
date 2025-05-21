<?php
/**
 * GitHub Updater Class
 *
 * This class checks for updates from a GitHub repository and provides plugin information.
 *
 * @package DL
 */

namespace DL;

class GitHubUpdater {
	/**
	 * @var object Configuration arguments.
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param array $args Configuration arguments.
	 */
	public function __construct($args) {
		$this->config = (object) $args;
		add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
		add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
		add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
	}

	/**
	 * Get the latest release data from the GitHub repository.
	 *
	 * @return object|false Release data or false on error.
	 */
	public function get_repo_release_data() {
		$url = "https://api.github.com/repos/{$this->config->github_user}/{$this->config->github_repo}/releases/latest";

		$response = wp_remote_get($url, [
			'headers' => ['User-Agent' => 'WordPress/' . get_bloginfo('version')],
		]);

		if (is_wp_error($response)) {
			error_log('GitHub API error: ' . $response->get_error_message());
			return false;
		}

		$data = json_decode(wp_remote_retrieve_body($response));
		error_log('GitHub API response: ' . print_r($data, true)); // ADD THIS LINE
		return $data ?: false;
	}

	/**
	 * Check for updates from GitHub.
	 *
	 * @param object $transient The transient data.
	 * @return object Modified transient data with update info.
	 */
	public function check_for_update($transient) {
		if (empty($transient->checked)) return $transient;

		$release = $this->get_repo_release_data();
		if (!$release || empty($release->tag_name)) return $transient;

		$latest_version  = ltrim($release->tag_name, 'v');
		$current_version = ltrim($this->config->version, 'v');

		if (version_compare($latest_version, $current_version, '<=')) {
			return $transient;
		}

		$plugin_slug = $this->config->plugin_slug;

		$transient->response[$plugin_slug] = (object) [
			'slug'        => dirname($plugin_slug),
			'plugin'      => $plugin_slug,
			'new_version' => $latest_version,
			'url'         => $release->html_url,
			'package'     => $release->zipball_url,
		];

		return $transient;
	}

	/**
	 * Provide plugin info for WordPress "View details" modal.
	 *
	 * @param bool   $false Default false.
	 * @param string $action The current action.
	 * @param object $args The plugin API args.
	 * @return object|false Plugin info or false.
	 */
	public function plugin_info($false, $action, $args) {
		if ($action !== 'plugin_information' || $args->slug !== dirname($this->config->plugin_slug)) {
			return false;
		}

		$release = $this->get_repo_release_data();
		if (!$release) return false;

		return (object)[
			'name'           => $this->config->github_repo,
			'slug'           => dirname($this->config->plugin_slug),
			'version'        => ltrim($release->tag_name, 'v'),
			'author'         => '<a href="https://github.com/' . esc_attr($this->config->github_user) . '">' . esc_html($this->config->github_user) . '</a>',
			'homepage'       => $release->html_url,
			'download_link'  => $release->zipball_url,
			'requires'       => '5.0',
			'tested'         => '6.5',
			'sections'       => [
				'description' => $release->body ?: 'No description provided.',
			],
		];
	}

	/**
	 * After install hook to rename the plugin folder.
	 *
	 * @param array $response The response from the installer.
	 * @param array $hook_extra Extra data from the installer.
	 * @param array $result The result of the installation.
	 * @return array Modified result.
	 */
	public function after_install($response, $hook_extra, $result) {
		global $wp_filesystem;

		$plugin_folder_name = dirname($this->config->plugin_slug);
		$correct_destination = WP_PLUGIN_DIR . '/' . $plugin_folder_name;

		// Remove old plugin folder if exists
		if ($wp_filesystem->exists($correct_destination)) {
			$wp_filesystem->delete($correct_destination, true);
		}

		// Move the downloaded folder to the correct folder name
		$wp_filesystem->move($result['destination'], $correct_destination);

		// Update result destination
		$result['destination'] = $correct_destination;

		return $result;
	}
}
