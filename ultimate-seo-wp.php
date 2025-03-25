<?php
/**
 * Plugin Name: Ultimate SEO WP
 * Description: A lightweight and efficient SEO plugin for WordPress.
 * Version: 1.0.5
 * Author: Web Lifter
 * Author URI: https://weblifter.com.au
 * License: GPL2 
 * Text Domain: ultimate-seo-wp
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define Plugin Paths
define('ULTIMATE_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ULTIMATE_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load Plugin Translations
 */
add_action('plugins_loaded', function() {
    load_plugin_textdomain('ultimate-seo-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

/**
 * Load Admin Settings Page
 */
require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/settings.php';

/**
 * Load Updater
 */
require_once plugin_dir_path(__FILE__) . 'updater/github-updater.php';

/**
 * Conditionally Load Features After Plugins Are Fully Loaded
 */
add_action('plugins_loaded', function() {
    if (get_option('ultimate_seo_enable_sitemap', 1)) {
        require_once ULTIMATE_SEO_PLUGIN_DIR . 'tools/sitemap-generator.php';
        require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/sitemap-settings.php';
    }

    if (get_option('ultimate_seo_enable_hreflang', 1)) {
        require_once ULTIMATE_SEO_PLUGIN_DIR . 'tools/custom-hreflang.php';
        require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/hreflang-settings.php';
    }
});
