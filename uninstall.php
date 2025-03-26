<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Prevent direct access
}

// List of plugin options to delete
$ultimate_seo_options = [
    'ultimate_seo_enable_sitemap',
    'ultimate_seo_excluded_post_types',
    'ultimate_seo_excluded_posts',
    'ultimate_seo_excluded_pages',
    'ultimate_seo_excluded_categories',
    'ultimate_seo_excluded_tags',
    'ultimate_seo_excluded_taxonomies',
    'ultimate_seo_enable_hreflang',
    'ultimate_seo_hreflang_language',
    'ultimate_seo_excluded_hreflang_pages',
    'custom_hreflang_enabled',
    'custom_canonical_enabled'
];

// Delete all plugin options
foreach ($ultimate_seo_options as $option) {
    delete_option($option);
    delete_site_option($option); // Multisite support
}

// Delete all stored translation meta data
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_ultimate_seo_hreflang_translations'");

// Delete sitemap file if exists
$sitemap_path = ABSPATH . 'sitemap.xml';
if (file_exists($sitemap_path)) {
    unlink($sitemap_path);
}

// Clear any scheduled cron jobs (if applicable)
wp_clear_scheduled_hook('ultimate_seo_sitemap_cron');
