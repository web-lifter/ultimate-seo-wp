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
const TRANSIENT_KEY = 'my_plugin_update_cache';
const ULTIMATE_SEO_WP_UPDATE_URL = 'https://raw.githubusercontent.com/web-lifter/ultimate-seo-wp/main/updater/updates.json';

add_action('wp_ajax_fetch_plugin_update_json', 'fetch_plugin_update_json_callback');
add_action('wp_ajax_nopriv_fetch_plugin_update_json', 'fetch_plugin_update_json_callback'); // Allow non-logged-in users to access it

function fetch_plugin_update_json_callback() {
    $response = wp_remote_get(ULTIMATE_SEO_WP_UPDATE_URL, ['timeout' => 10]);

    if (is_wp_error($response)) {
        wp_send_json_error(['error' => 'Failed to fetch update JSON']);
    }

    $body = wp_remote_retrieve_body($response);

    if (!$body) {
        wp_send_json_error(['error' => 'Empty response from update JSON']);
    }

    $json = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['error' => 'Invalid JSON format']);
    }

    wp_send_json_success($json);
}

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
    if (!$remote) return $transient;

    $installed_version = get_plugin_data(WP_PLUGIN_DIR . '/' . MY_PLUGIN_FILE)['Version'];

    $installed_version = get_plugin_data(WP_PLUGIN_DIR . '/' . MY_PLUGIN_FILE)['Version'];
    if (version_compare($installed_version, $remote->version, '<')) {
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
    // Use transient caching to reduce API calls
    $cached = get_transient(TRANSIENT_KEY);
    if ($cached) return $cached;

    $response = wp_remote_get(GITHUB_API_URL, [
        'timeout' => 10,
        'headers' => [
            'User-Agent' => 'WordPress',
        ]
    ]);    

    if (is_wp_error($response)) return false;

    $data = json_decode(wp_remote_retrieve_body($response));
    if (!isset($data->tag_name)) return false;

    // Find the correct download URL from assets
    $download_url = '';
    if (!empty($data->assets)) {
        foreach ($data->assets as $asset) {
            if (strpos($asset->name, '.zip') !== false) {
                $download_url = $asset->browser_download_url;
                break;
            }
        }
    }

    // If no asset found, fallback to the auto-generated GitHub zip (not recommended)
    if (!$download_url) {
        $download_url = "https://github.com/" . MY_GITHUB_REPO . "/archive/refs/tags/{$data->tag_name}.zip";
    }

    $release = (object) [
        'version' => ltrim($data->tag_name, 'v'),
        'download_url' => $download_url
    ];

    // Cache the response for 12 hours
    set_transient(TRANSIENT_KEY, $release, 12 * HOUR_IN_SECONDS);

    return $release;
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
    $res->sections = [
        'description' => 'SEO plugin with sitemap and hreflang features.',
        'changelog' => 'Latest release available at: <a href="https://github.com/' . MY_GITHUB_REPO . '/releases" target="_blank">GitHub Releases</a>'
    ];

    return $res;
}

/**
 * Purge cache after plugin update.
 */
function my_plugin_purge_cache_after_update($upgrader, $options) {
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        delete_transient(TRANSIENT_KEY);
    }
}

/**
 * Add custom links to the plugin manager page.
 *
 * - View Details (opens plugin info modal)
 * - GitHub Sponsors link
 * - Documentation link (GitHub Wiki)
 */
function my_plugin_add_custom_links($plugin_meta, $plugin_file) {
    if ($plugin_file !== plugin_basename(__FILE__)) {
        return $plugin_meta;
    }

    // GitHub repository slug
    $github_repo = 'web-lifter/ultimate-seo-wp';

    // Custom links
    $view_details_link = sprintf(
        '<a href="%s" class="thickbox" title="Ultimate SEO WP Details">View Details</a>',
        esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=ultimate-seo-wp&TB_iframe=true&width=600&height=550'))
    );

    $sponsor_link = sprintf(
        '<a href="%s" target="_blank">Sponsor Us</a>',
        esc_url('https://github.com/sponsors/web-lifter')
    );

    $docs_link = sprintf(
        '<a href="%s" target="_blank">Docs</a>',
        esc_url('https://github.com/' . $github_repo . '/wiki')
    );

    // Add links to the plugin meta
    $plugin_meta[] = $view_details_link;
    $plugin_meta[] = $sponsor_link;
    $plugin_meta[] = $docs_link;

    return $plugin_meta;
}

// Hook into WordPress to modify plugin row meta links
add_filter('plugin_row_meta', 'my_plugin_add_custom_links', 10, 2);
