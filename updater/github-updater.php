<?php
/**
 * Plugin Updater for GitHub
 * Automatically fetches and installs updates from GitHub releases.
 */
defined('ABSPATH') or exit;

// Define constants
const MY_PLUGIN_SLUG = 'ultimate-seo-wp';
const MY_PLUGIN_FILE = 'ultimate-seo-wp/ultimate-seo-wp.php';
const MY_GITHUB_REPO = 'web-lifter/ultimate-seo-wp'; // Change to your GitHub repo
const GITHUB_API_URL = 'https://api.github.com/repos/' . MY_GITHUB_REPO . '/releases/latest';

/**
 * Initialize the updater functionality.
 */
function my_plugin_updater_init() {
    if (!is_admin()) return;

    global $pagenow;
    $allowed_pages = ['plugins.php', 'update-core.php', 'plugin-install.php', 'admin-ajax.php'];
    if (!in_array($pagenow, $allowed_pages, true) && !(defined('DOING_CRON') && DOING_CRON)) return;

    add_filter('site_transient_update_plugins', 'my_plugin_check_for_updates');
    add_filter('plugins_api', 'my_plugin_get_plugin_info', 20, 3);
    add_action('upgrader_process_complete', 'my_plugin_purge_cache_after_update', 10, 2);
}
add_action('init', 'my_plugin_updater_init');

/**
 * Check for plugin updates from GitHub.
 */
function my_plugin_check_for_updates($transient) {
    if (empty($transient->checked)) return $transient;

    $remote = my_plugin_fetch_github_release();
    if (
        $remote &&
        version_compare(get_plugin_data(WP_PLUGIN_DIR . '/' . MY_PLUGIN_FILE)['Version'], $remote->version, '<')
    ) {
        $res = new stdClass();
        $res->slug = MY_PLUGIN_SLUG;
        $res->plugin = MY_PLUGIN_FILE;
        $res->new_version = $remote->version;
        $res->package = $remote->download_url;
        
        $transient->response[$res->plugin] = $res;
    }
    return $transient;
}

/**
 * Fetch release data from GitHub API.
 */
function my_plugin_fetch_github_release() {
    $response = wp_remote_get(GITHUB_API_URL, ['timeout' => 10, 'headers' => ['User-Agent' => 'WordPress']]);
    if (is_wp_error($response)) return false;

    $data = json_decode(wp_remote_retrieve_body($response));
    if (!isset($data->tag_name)) return false;

    return (object) [
        'version' => ltrim($data->tag_name, 'v'),
        'download_url' => $data->assets[0]->browser_download_url ?? ''
    ];
}

/**
 * Fetch plugin info for WordPress Plugin API.
 */
function my_plugin_get_plugin_info($res, $action, $args) {
    if ('plugin_information' !== $action || MY_PLUGIN_SLUG !== $args->slug) return $res;

    $remote = my_plugin_fetch_github_release();
    if (!$remote) return $res;

    $res = new stdClass();
    $res->name = 'Ultimate SEO WP';
    $res->slug = MY_PLUGIN_SLUG;
    $res->version = $remote->version;
    $res->download_link = $remote->download_url;
    $res->sections = ['description' => 'SEO plugin with sitemap and hreflang features.'];

    return $res;
}

/**
 * Purge cache after plugin update.
 */
function my_plugin_purge_cache_after_update($upgrader, $options) {
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        delete_transient('my_plugin_update_cache');
    }
}
