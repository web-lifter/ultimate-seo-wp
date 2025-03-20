<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

/**
 * Add a separate Sitemap Settings page under the Ultimate SEO WP menu
 */
function ultimate_seo_add_sitemap_settings_page() {
    add_submenu_page(
        'ultimate-seo-wp', // Parent slug (Main plugin menu)
        'Sitemap Settings', // Page title
        'Sitemap Settings', // Menu title
        'manage_options',   // Capability
        'ultimate-seo-wp-sitemap', // Menu slug
        'ultimate_seo_sitemap_settings_page' // Callback function
    );
}
add_action('admin_menu', 'ultimate_seo_add_sitemap_settings_page');

/**
 * Display the Sitemap Settings Page
 */
function ultimate_seo_sitemap_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ultimate SEO WP - Sitemap Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ultimate_seo_sitemap_settings_group');
            do_settings_sections('ultimate_seo_sitemap_settings');
            submit_button();
            ?>
        </form>

        <hr>
        <h2>Manage Sitemap</h2>
        <p>Click below to regenerate your sitemap.</p>
        <button id="regenerate-sitemap" class="button button-primary">Regenerate Sitemap</button>
        <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" class="button">View Sitemap</a>
        <p id="sitemap-status" style="margin-top: 10px;"></p>
    </div>
    <?php
}

/**
 * Register Sitemap-Specific Settings
 */
function ultimate_seo_register_sitemap_settings() {
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_enable_sitemap');
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_excluded_post_types');
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_excluded_posts');
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_excluded_pages');
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_excluded_categories');
    register_setting('ultimate_seo_sitemap_settings_group', 'ultimate_seo_excluded_tags');

    add_settings_section('ultimate_seo_sitemap_section', 'Sitemap Settings', null, 'ultimate_seo_sitemap_settings');

    add_settings_field('ultimate_seo_enable_sitemap', 'Enable Sitemap', 'ultimate_seo_enable_sitemap_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
    add_settings_field('ultimate_seo_excluded_post_types', 'Exclude Post Types & CPTs', 'ultimate_seo_excluded_post_types_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
    add_settings_field('ultimate_seo_excluded_posts', 'Exclude Specific Posts', 'ultimate_seo_excluded_posts_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
    add_settings_field('ultimate_seo_excluded_pages', 'Exclude Specific Pages', 'ultimate_seo_excluded_pages_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
    add_settings_field('ultimate_seo_excluded_categories', 'Exclude Specific Categories', 'ultimate_seo_excluded_categories_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
    add_settings_field('ultimate_seo_excluded_tags', 'Exclude Specific Tags', 'ultimate_seo_excluded_tags_callback', 'ultimate_seo_sitemap_settings', 'ultimate_seo_sitemap_section');
}
add_action('admin_init', 'ultimate_seo_register_sitemap_settings');

/**
 * Callback for excluding post types (includes CPTs)
 */
function ultimate_seo_excluded_post_types_callback() {
    // Fetch all post types (default & custom)
    $post_types = get_post_types(['public' => true], 'objects');
    $excluded_post_types = get_option('ultimate_seo_excluded_post_types', []);

    if (!is_array($excluded_post_types)) {
        $excluded_post_types = [];
    }

    echo "<select name='ultimate_seo_excluded_post_types[]' multiple style='width:100%; min-height:150px; resize:vertical;'>";
    foreach ($post_types as $post_type) {
        $selected = in_array($post_type->name, $excluded_post_types) ? 'selected' : '';
        echo "<option value='{$post_type->name}' $selected>{$post_type->label}</option>";
    }
    echo "</select>";
}

/**
 * Exclusion Callbacks (Resizable Fields)
 */
function ultimate_seo_generate_select_field($option_name, $items, $value_key = 'ID', $label_key = 'post_title') {
    $selected_values = get_option($option_name, []);
    if (!is_array($selected_values)) $selected_values = [];

    echo "<select name='{$option_name}[]' multiple style='width:100%; min-height:150px; resize:vertical;'>";
    foreach ($items as $item) {
        $selected = in_array($item->$value_key, $selected_values) ? 'selected' : '';
        echo "<option value='{$item->$value_key}' $selected>{$item->$label_key}</option>";
    }
    echo "</select>";
}

function ultimate_seo_excluded_posts_callback() { 
    ultimate_seo_generate_select_field('ultimate_seo_excluded_posts', get_posts(['numberposts' => -1])); 
}
function ultimate_seo_excluded_pages_callback() { 
    ultimate_seo_generate_select_field('ultimate_seo_excluded_pages', get_posts(['post_type' => 'page', 'numberposts' => -1])); 
}
function ultimate_seo_excluded_categories_callback() { 
    ultimate_seo_generate_select_field('ultimate_seo_excluded_categories', get_terms(['taxonomy' => 'category', 'hide_empty' => false]), 'term_id', 'name'); 
}
function ultimate_seo_excluded_tags_callback() { 
    ultimate_seo_generate_select_field('ultimate_seo_excluded_tags', get_terms(['taxonomy' => 'post_tag', 'hide_empty' => false]), 'term_id', 'name'); 
}

/**
 * AJAX Handler for Sitemap Regeneration
 */
function ultimate_seo_regenerate_sitemap() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);
    require_once plugin_dir_path(__FILE__) . '../tools/sitemap-generator.php';
    ultimate_seo_generate_sitemap();
    wp_send_json_success(['message' => 'Sitemap successfully regenerated!']);
}
add_action('wp_ajax_regenerate_sitemap', 'ultimate_seo_regenerate_sitemap');

/**
 * Enqueue Scripts for AJAX
 */
function ultimate_seo_admin_scripts($hook) {
    if ($hook !== 'ultimate-seo-wp_page_ultimate-seo-wp-sitemap') return;
    wp_enqueue_script('ultimate-seo-admin-js', plugin_dir_url(__FILE__) . '../assets/regenerate-sitemap.js', ['jquery'], false, true);
    wp_localize_script('ultimate-seo-admin-js', 'ultimateSEOAjax', ['ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ultimate_seo_nonce')]);
}
add_action('admin_enqueue_scripts', 'ultimate_seo_admin_scripts');
