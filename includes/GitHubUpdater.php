<?php

namespace DL;

class GitHubUpdater {
	protected $config;

	public function __construct($args) {
		$this->config = (object) $args;
		add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
		add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
		add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
	}

	public function get_repo_release_data() {
		$url = "https://api.github.com/repos/{$this->config->github_user}/{$this->config->github_repo}/releases/latest";
		$response = wp_remote_get($url, [
			'headers' => ['User-Agent' => 'WordPress/' . get_bloginfo('version')],
		]);
		if (is_wp_error($response)) return false;
		$data = json_decode(wp_remote_retrieve_body($response));
		return $data;
	}

	public function check_for_update($transient) {
		if (empty($transient->checked)) return $transient;
		$release = $this->get_repo_release_data();
		if (!$release || version_compare($release->tag_name, $this->config->version, '<=')) return $transient;

		$plugin_data = [
			'slug' => dirname($this->config->plugin_slug),
			'new_version' => $release->tag_name,
			'url' => $release->html_url,
			'package' => $release->zipball_url,
		];
		$transient->response[$this->config->plugin_slug] = (object)$plugin_data;
		return $transient;
	}

	public function plugin_info($false, $action, $args) {
		if ($action !== 'plugin_information' || $args->slug !== dirname($this->config->plugin_slug)) {
			return false;
		}

		$release = $this->get_repo_release_data();
		if (!$release) return false;

		return (object)[
			'name' => $this->config->github_repo,
			'slug' => dirname($this->config->plugin_slug),
			'version' => $release->tag_name,
			'author' => '<a href="https://github.com/' . $this->config->github_user . '">' . $this->config->github_user . '</a>',
			'homepage' => $release->html_url,
			'download_link' => $release->zipball_url,
			'sections' => [
				'description' => $release->body,
			],
		];
	}

	public function after_install($response, $hook_extra, $result) {
		global $wp_filesystem;
		$plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->config->plugin_slug);
		$wp_filesystem->move($result['destination'], $plugin_folder);
		$result['destination'] = $plugin_folder;
		return $result;
	}
}
