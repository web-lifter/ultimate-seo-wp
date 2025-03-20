<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Ensure Hreflang functionality is enabled before loading this page
if (get_option('ultimate_seo_enable_hreflang', 1) != 1) {
    return;
}

/**
 * Add Hreflang Settings Page under Ultimate SEO WP menu
 */
function ultimate_seo_add_hreflang_settings_page() {
    add_submenu_page(
        'ultimate-seo-wp',                // Parent menu slug
        'Hreflang Settings',               // Page title
        'Hreflang Settings',               // Menu title
        'manage_options',                  // Capability
        'ultimate-seo-hreflang-settings',  // Menu slug
        'ultimate_seo_hreflang_settings_page' // Callback function
    );
}
add_action('admin_menu', 'ultimate_seo_add_hreflang_settings_page');

/**
 * Display the Hreflang Settings Page
 */
function ultimate_seo_hreflang_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ultimate SEO WP - Hreflang Settings</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('ultimate_seo_hreflang_settings_group');
            do_settings_sections('ultimate_seo_hreflang_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register Hreflang Settings
 */
function ultimate_seo_register_hreflang_settings() {
    register_setting('ultimate_seo_hreflang_settings_group', 'ultimate_seo_hreflang_language');
    register_setting('ultimate_seo_hreflang_settings_group', 'ultimate_seo_excluded_hreflang_pages');

    add_settings_section('ultimate_seo_hreflang_section', 'Hreflang Configuration', null, 'ultimate_seo_hreflang_settings');

    add_settings_field(
        'ultimate_seo_hreflang_language',
        'Default Hreflang Language',
        'ultimate_seo_hreflang_language_callback',
        'ultimate_seo_hreflang_settings',
        'ultimate_seo_hreflang_section'
    );

    add_settings_field(
        'ultimate_seo_excluded_hreflang_pages',
        'Exclude Specific Pages',
        'ultimate_seo_excluded_hreflang_pages_callback',
        'ultimate_seo_hreflang_settings',
        'ultimate_seo_hreflang_section'
    );
}
add_action('admin_init', 'ultimate_seo_register_hreflang_settings');

/**
 * Callback for setting default hreflang language
 */
if (!function_exists('ultimate_seo_hreflang_language_callback')) {
    function ultimate_seo_hreflang_language_callback() {
        $language = get_option('ultimate_seo_hreflang_language', 'en');
        echo "<input type='text' name='ultimate_seo_hreflang_language' value='" . esc_attr($language) . "' />";
    }
}

/**
 * Callback for excluding specific pages
 */
if (!function_exists('ultimate_seo_excluded_hreflang_pages_callback')) {
    function ultimate_seo_excluded_hreflang_pages_callback() {
        $excluded_pages = (array) get_option('ultimate_seo_excluded_hreflang_pages', []);
        $pages = get_posts(['post_type' => 'page', 'numberposts' => -1]);

        echo "<select name='ultimate_seo_excluded_hreflang_pages[]' multiple style='width:100%; min-height:150px; resize:vertical;'>";
        foreach ($pages as $page) {
            $selected = in_array($page->ID, $excluded_pages) ? 'selected' : '';
            echo "<option value='{$page->ID}' $selected>{$page->post_title}</option>";
        }
        echo "</select>";
    }
}
