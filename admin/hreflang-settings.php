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
        'custom_hreflang_enabled',
        'Enable Hreflang Tags',
        'custom_hreflang_enabled_callback',
        'ultimate_seo_hreflang_settings', // Corrected
        'ultimate_seo_hreflang_section'   // Corrected
    );

    add_settings_field(
        'ultimate_seo_hreflang_language',
        'Default Hreflang Language',
        'ultimate_seo_hreflang_language_callback',
        'ultimate_seo_hreflang_settings',
        'ultimate_seo_hreflang_section'
    );

    add_settings_field(
        'custom_canonical_enabled',
        'Enable Canonical URLs',
        'custom_canonical_enabled_callback',
        'ultimate_seo_hreflang_settings', // Corrected
        'ultimate_seo_hreflang_section'   // Corrected
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

if (!function_exists('custom_hreflang_enabled_callback')) {
    function custom_hreflang_enabled_callback() {
        $option = get_option('custom_hreflang_enabled', 'yes');
        echo '<input type="checkbox" name="custom_hreflang_enabled" value="yes" ' . checked('yes', $option, false) . '> Enable';
    }
}

if (!function_exists('ultimate_seo_hreflang_language_callback')) {
    function ultimate_seo_hreflang_language_callback() {
        $language = get_option('ultimate_seo_hreflang_language', 'en');
        echo "<input type='text' name='ultimate_seo_hreflang_language' value='" . esc_attr($language) . "' />";
    }
}

if (!function_exists('custom_canonical_enabled_callback')) {
    function custom_canonical_enabled_callback() {
        $option = get_option('custom_canonical_enabled', 'yes');
        echo '<input type="checkbox" name="custom_canonical_enabled" value="yes" ' . checked('yes', $option, false) . '> Enable Canonical URLs';
    }
}

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

function ultimate_seo_add_hreflang_meta_box() {
    add_meta_box(
        'ultimate_seo_hreflang_meta',
        'Hreflang Translations',
        'ultimate_seo_hreflang_meta_callback',
        ['post', 'page'], // Supports posts and pages
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ultimate_seo_add_hreflang_meta_box');

/**
 * Display the Meta Box
 */
function ultimate_seo_hreflang_meta_callback($post) {
    $languages = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German']; // Add more languages as needed
    $translations = get_post_meta($post->ID, '_ultimate_seo_hreflang_translations', true);
    if (!is_array($translations)) $translations = [];

    wp_nonce_field('ultimate_seo_hreflang_save', 'ultimate_seo_hreflang_nonce');

    echo '<p>Assign translations for this post:</p>';
    foreach ($languages as $lang_code => $lang_name) {
        $post_id = isset($translations[$lang_code]) ? $translations[$lang_code] : '';
        echo "<p><label>$lang_name:</label><br>";
        echo "<input type='text' name='ultimate_seo_hreflang[$lang_code]' value='" . esc_attr($post_id) . "' placeholder='Enter post ID'></p>";
    }
}

function ultimate_seo_hreflang_save_meta($post_id) {
    if (!isset($_POST['ultimate_seo_hreflang_nonce']) || !wp_verify_nonce($_POST['ultimate_seo_hreflang_nonce'], 'ultimate_seo_hreflang_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['ultimate_seo_hreflang'])) {
        $translations = array_map('sanitize_text_field', $_POST['ultimate_seo_hreflang']);
        update_post_meta($post_id, '_ultimate_seo_hreflang_translations', $translations);
    }
}
add_action('save_post', 'ultimate_seo_hreflang_save_meta');
