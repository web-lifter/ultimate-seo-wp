<?php
/**
 * Plugin Name: Ultimate SEO WP
 * Description: A lightweight and efficient SEO plugin for WordPress.
 * Version: 1.0.1
 * Author: Web Lifter
 * Author URI: https://weblifter.com.au
 * License: GPL2
 * Update URI: /updates.json
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define Plugin Paths
define('ULTIMATE_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ULTIMATE_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load Admin Settings Page
 */
require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/settings.php';

/**
 * Load Updater
 */
include_once plugin_dir_path(__FILE__) . 'updater/github-updater.php';

/**
 * Conditionally Load Sitemap Functionality
 */
if (get_option('ultimate_seo_enable_sitemap', 1)) {
    require_once ULTIMATE_SEO_PLUGIN_DIR . 'tools/sitemap-generator.php';
    require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/sitemap-settings.php';
}

/**
 * Conditionally Load Hreflang Functionality
 */
if (get_option('ultimate_seo_enable_hreflang', 1)) {
    require_once ULTIMATE_SEO_PLUGIN_DIR . 'tools/custom-hreflang.php';
    require_once ULTIMATE_SEO_PLUGIN_DIR . 'admin/hreflang-settings.php';
}

/**
 * Enqueue Global Admin Scripts
 */
function ultimate_seo_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'ultimate-seo-wp') === false) {
        return;
    }

    // Enqueue AJAX Regenerate Sitemap Script (Only if Sitemap is Enabled)
    if (get_option('ultimate_seo_enable_sitemap', 1)) {
        wp_enqueue_script(
            'ultimate-seo-regenerate-sitemap',
            ULTIMATE_SEO_PLUGIN_URL . 'assets/regenerate-sitemap.js',
            ['jquery'],
            false,
            true
        );

        wp_localize_script('ultimate-seo-regenerate-sitemap', 'ultimateSEOAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ultimate_seo_nonce')
        ]);
    }
}
add_action('admin_enqueue_scripts', 'ultimate_seo_enqueue_admin_scripts');