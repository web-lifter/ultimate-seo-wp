<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Ensure Hreflang functionality is enabled before loading this file
if (get_option('ultimate_seo_enable_hreflang', 1) != 1) {
    return;
}

/**
 * Check if a page has a noindex meta tag
 */
if (!function_exists('ultimate_seo_is_noindex')) {
    function ultimate_seo_is_noindex() {
        if (function_exists('wp_robots')) {
            $robots = wp_robots([]);
            return isset($robots['noindex']) && $robots['noindex'] === true;
        }
        return false;
    }
}

<?php
if (!function_exists('ultimate_seo_add_hreflang')) {
    function ultimate_seo_add_hreflang() {
        if (get_option('ultimate_seo_enable_hreflang', 1) != 1) return;
        if (is_admin() || wp_doing_ajax()) return;

        $excluded_pages = (array) get_option('ultimate_seo_excluded_hreflang_pages', []);
        if (is_singular() && in_array(get_the_ID(), $excluded_pages)) return;

        $current_url = ultimate_seo_get_current_url();
        $global_hreflang = get_option('ultimate_seo_hreflang_language', 'en');

        // Add self-referencing hreflang tag
        echo '<link rel="alternate" hreflang="' . esc_attr($global_hreflang) . '" href="' . esc_url($current_url) . '">' . "\n";

        // Add x-default
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_url) . '">' . "\n";

        // Add canonical tag if enabled
        if (get_option('custom_canonical_enabled', 'yes') === 'yes' && !ultimate_seo_is_noindex()) {
            echo '<link rel="canonical" href="' . esc_url($current_url) . '">' . "\n";
        }

        if (!ultimate_seo_is_noindex()) {
            // Retrieve translations using our custom Polylang-like functionality
            $translations = get_post_meta(get_the_ID(), '_ultimate_seo_hreflang_translations', true);
            if (!is_array($translations)) $translations = [];

            if (!empty($translations)) {
                foreach ($translations as $lang => $translated_post_id) {
                    $translated_url = get_permalink($translated_post_id);
                    $translated_url = user_trailingslashit($translated_url);
                    echo '<link rel="alternate" hreflang="' . esc_attr($lang) . '" href="' . esc_url($translated_url) . '">' . "\n";
                }
            } else {
                // Fallback to default language if no translations exist
                echo '<link rel="alternate" hreflang="' . esc_attr($global_hreflang) . '" href="' . esc_url($current_url) . '">' . "\n";
            }
        }
    }
}
add_action('wp_head', 'ultimate_seo_add_hreflang', 1);

/**
 * Get the current URL safely
 */
if (!function_exists('ultimate_seo_get_current_url')) {
    function ultimate_seo_get_current_url() {
        if (is_front_page() || is_home()) {
            return home_url('/');
        } elseif (is_category() || is_tag() || is_tax()) {
            return get_term_link(get_queried_object());
        } elseif (is_search()) {
            return home_url("/?s=" . get_search_query());
        } elseif (is_singular()) {
            return get_permalink();
        } else {
            return home_url(add_query_arg([], $_SERVER['REQUEST_URI']));
        }
    }
}