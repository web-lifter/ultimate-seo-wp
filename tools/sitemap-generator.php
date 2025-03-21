<?php
if (!defined('ABSPATH')) {
    exit;
}

// Hook to generate sitemap on content changes
add_action('save_post', 'ultimate_seo_generate_sitemap');

/**
 * Generate the sitemap and apply exclusions
 */
function ultimate_seo_generate_sitemap() {
    if (!get_option('ultimate_seo_enable_sitemap', 1)) return;

    // Fetch exclusions from the settings
    $excluded_post_types = get_option('ultimate_seo_excluded_post_types', []);
    $excluded_cpts = get_option('ultimate_seo_excluded_cpts', []);
    $excluded_posts = get_option('ultimate_seo_excluded_posts', []);
    $excluded_pages = get_option('ultimate_seo_excluded_pages', []);
    $excluded_categories = get_option('ultimate_seo_excluded_categories', []);
    $excluded_tags = get_option('ultimate_seo_excluded_tags', []);

    // Ensure arrays
    $excluded_post_types = is_array($excluded_post_types) ? $excluded_post_types : [];
    $excluded_cpts = is_array($excluded_cpts) ? $excluded_cpts : [];
    $excluded_posts = is_array($excluded_posts) ? $excluded_posts : [];
    $excluded_pages = is_array($excluded_pages) ? $excluded_pages : [];
    $excluded_categories = is_array($excluded_categories) ? $excluded_categories : [];
    $excluded_tags = is_array($excluded_tags) ? $excluded_tags : [];

    // Merge standard excluded post types with custom post types
    $excluded_post_types = array_merge($excluded_post_types, $excluded_cpts);

    // Ensure no whitespace or HTML is output
    if (!headers_sent()) {
        header('Content-Type: application/xml; charset=UTF-8');
    }

    $sitemap_path = ABSPATH . 'sitemap.xml';
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    // Get all public posts and pages excluding the selected ones
    $args = [
        'post_type'      => array_diff(get_post_types(['public' => true]), $excluded_post_types),
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'category__not_in' => $excluded_categories,
        'tag__not_in'     => $excluded_tags
    ];

    $posts = get_posts($args);
    foreach ($posts as $post) {
        // Skip excluded posts and pages
        if (in_array($post->ID, $excluded_posts) || in_array($post->ID, $excluded_pages)) {
            continue;
        }

        $xml .= ultimate_seo_generate_url_element(get_permalink($post->ID), get_the_modified_time('c', $post->ID));
    }

    $xml .= '</urlset>';

    // Save and serve the file
    file_put_contents($sitemap_path, $xml);
}

/**
 * Generate a <url> element for the sitemap
 */
function ultimate_seo_generate_url_element($url, $lastmod) {
    return "<url>\n" .
           "    <loc>" . esc_url($url) . "</loc>\n" .
           "    <lastmod>" . esc_html($lastmod) . "</lastmod>\n" .
           "</url>\n";
}

/**
 * Serve sitemap dynamically
 */
function ultimate_seo_serve_sitemap() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'sitemap.xml') !== false) {
        header('Content-Type: application/xml; charset=UTF-8');
        readfile(ABSPATH . 'sitemap.xml');
        exit;
    }
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