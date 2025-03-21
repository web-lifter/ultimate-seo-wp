<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

/**
 * Add the Main Settings Page under the Ultimate SEO WP menu
 */
function ultimate_seo_add_main_settings_page() {
    add_menu_page(
        'Ultimate SEO WP',        // Page Title
        'Ultimate SEO WP',        // Menu Title
        'manage_options',         // Capability
        'ultimate-seo-wp',        // Menu Slug
        'ultimate_seo_main_settings_page', // Callback Function
        'dashicons-chart-line',   // Icon
        80                        // Position
    );
}
add_action('admin_menu', 'ultimate_seo_add_main_settings_page');

/**
 * Display the Main Settings Page with Tabs
 */
function ultimate_seo_main_settings_page() {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';
    ?>
    <div class="wrap">
        <h1>Ultimate SEO WP</h1>

        <nav class="nav-tab-wrapper">
            <a href="?page=ultimate-seo-wp&tab=settings" class="nav-tab <?php echo ($tab === 'settings') ? 'nav-tab-active' : ''; ?>">Settings</a>
            <a href="?page=ultimate-seo-wp&tab=about" class="nav-tab <?php echo ($tab === 'about') ? 'nav-tab-active' : ''; ?>">About</a>
        </nav>

        <div class="tab-content">
            <?php
            if ($tab === 'settings') {
                ultimate_seo_plugin_settings();
            } elseif ($tab === 'about') {
                ultimate_seo_about_section();
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Display the Settings Tab
 */
function ultimate_seo_plugin_settings() {
    ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('ultimate_seo_main_settings_group');
        do_settings_sections('ultimate_seo_main_settings');
        submit_button();
        ?>
    </form>
    <?php
}

/**
 * Display the About Tab
 */
function ultimate_seo_about_section() {
    ?>
    <div class="about-section">
        <h2>About Ultimate SEO WP</h2>
        <p><strong>Version:</strong> 1.0</p>
        <p><strong>Author:</strong> Web Lifter</p>
        <p><strong>Company:</strong> Web Lifter</p>
        <p><strong>Website:</strong> <a href="https://weblifter.com.au" target="_blank">Web Lifter</a></p>
        <p>Ultimate SEO WP is a lightweight and efficient WordPress SEO plugin designed to improve website indexing and international targeting.</p>

        <h2>About Weblifter</h2>
        <p><strong>Weblifter</strong> is a leading web development and SEO agency focused on building innovative digital solutions.</p>
        <p>Visit us at <a href="https://weblifter.com.au" target="_blank">weblifter.com.au</a></p>

        <h3>Support & Sponsorship</h3>
        <p>If you like this plugin, consider supporting us on GitHub:</p>
        <iframe src="https://github.com/sponsors/web-lifter/button" title="Sponsor web-lifter" height="32" width="114" style="border: 0; border-radius: 6px;"></iframe>

        <h3>Other Plugins by Weblifter</h3>
        <ul>
            <li><a href="https://weblifter.com.au/product/hubspot-woocommerce-sync" target="_blank">Hubspot x WooCommerce Integration</a></li>
        </ul>
    </div>
    <style>
            .nav-tab-wrapper {
                margin-bottom: 20px;
            }
            .tab-content {
                background: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            .about-section {
                background: #f8f8f8;
                padding: 20px;
                border-radius: 5px;
            }
            .about-section h2, .about-section h3 {
                color: #0073aa;
            }
            .about-section ul {
                list-style-type: square;
                padding-left: 20px;
            }
            .about-section ul li {
                margin-bottom: 5px;
            }
        </style>
    <?php
}

/**
 * Register the Plugin Settings
 */
function ultimate_seo_register_main_settings() {
    register_setting('ultimate_seo_main_settings_group', 'ultimate_seo_enable_sitemap');
    register_setting('ultimate_seo_main_settings_group', 'ultimate_seo_enable_hreflang');

    add_settings_section('ultimate_seo_main_section', 'Plugin Features', null, 'ultimate_seo_main_settings');

    add_settings_field(
        'ultimate_seo_enable_sitemap',
        'Enable Sitemap',
        'ultimate_seo_enable_sitemap_callback',
        'ultimate_seo_main_settings',
        'ultimate_seo_main_section'
    );

    add_settings_field(
        'ultimate_seo_enable_hreflang',
        'Enable Hreflang Tags',
        'ultimate_seo_enable_hreflang_callback',
        'ultimate_seo_main_settings',
        'ultimate_seo_main_section'
    );
}
add_action('admin_init', 'ultimate_seo_register_main_settings');

/**
 * Callback for enabling/disabling sitemap
 */
if (!function_exists('ultimate_seo_enable_sitemap_callback')) {
    function ultimate_seo_enable_sitemap_callback() {
        $option = get_option('ultimate_seo_enable_sitemap', 1);
        echo "<input type='checkbox' name='ultimate_seo_enable_sitemap' value='1' " . checked(1, $option, false) . " />";
    }
}

/**
 * Callback for enabling/disabling hreflang
 */
if (!function_exists('ultimate_seo_enable_hreflang_callback')) {
    function ultimate_seo_enable_hreflang_callback() {
        $option = get_option('ultimate_seo_enable_hreflang', 1);
        echo "<input type='checkbox' name='ultimate_seo_enable_hreflang' value='1' " . checked(1, $option, false) . " />";
    }
}