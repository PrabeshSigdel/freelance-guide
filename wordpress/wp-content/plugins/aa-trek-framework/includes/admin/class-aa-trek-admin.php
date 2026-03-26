<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Admin
{
    private static function color_fields()
    {
        return array(
            'primary' => 'Primary',
            'primary_dark' => 'Primary Dark',
            'accent' => 'Accent',
            'surface' => 'Surface',
            'ink' => 'Ink',
            'muted' => 'Muted',
            'line' => 'Line',
            'success' => 'Success',
        );
    }

    public static function register()
    {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function register_menu()
    {
        add_menu_page(
            'Trek Framework',
            'Trek Framework',
            'manage_options',
            'aatf-framework',
            array(__CLASS__, 'render_dashboard'),
            'dashicons-admin-site-alt3',
            25
        );

        add_submenu_page(
            'aatf-framework',
            'Design System',
            'Design System',
            'manage_options',
            'aatf-design-system',
            array(__CLASS__, 'render_design_system_page')
        );
    }

    public static function register_settings()
    {
        register_setting(
            'aatf_design_system',
            'aatf_design_system_colors',
            array(
                'type' => 'array',
                'sanitize_callback' => array(__CLASS__, 'sanitize_color_settings'),
                'default' => array(),
            )
        );

        add_settings_section(
            'aatf_design_colors_section',
            'Global Color System',
            array(__CLASS__, 'render_colors_section_intro'),
            'aatf-design-system'
        );

        foreach (self::color_fields() as $key => $label) {
            add_settings_field(
                'aatf_color_' . $key,
                $label,
                array(__CLASS__, 'render_color_field'),
                'aatf-design-system',
                'aatf_design_colors_section',
                array('key' => $key)
            );
        }
    }

    public static function sanitize_color_settings($input)
    {
        $input = is_array($input) ? $input : array();
        $clean = array();

        foreach (self::color_fields() as $key => $label) {
            $value = isset($input[$key]) ? sanitize_text_field(wp_unslash($input[$key])) : '';
            $clean[$key] = sanitize_hex_color($value) ? sanitize_hex_color($value) : '';
        }

        return $clean;
    }

    public static function render_colors_section_intro()
    {
        echo '<p>Update brand colors once and all trek components inherit them.</p>';
    }

    public static function render_color_field($args)
    {
        $key = isset($args['key']) ? (string) $args['key'] : '';
        if ($key === '') {
            return;
        }

        $defaults = AATF_Frontend::get_default_colors();
        $saved = get_option('aatf_design_system_colors', array());
        $value = isset($saved[$key]) && sanitize_hex_color($saved[$key]) ? sanitize_hex_color($saved[$key]) : $defaults[$key];

        echo '<input type="text" class="aatf-color-field" name="aatf_design_system_colors[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($defaults[$key]) . '" />';
    }

    public static function render_dashboard()
    {
        echo '<div class="wrap">';
        echo '<h1>Trek Framework</h1>';
        echo '<p>Manage Treks, Destinations, Departures, FAQs, and Testimonials from this menu.</p>';
        echo '</div>';
    }

    public static function render_design_system_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>Design System</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('aatf_design_system');
        do_settings_sections('aatf-design-system');
        submit_button('Save Design System');
        echo '</form>';
        echo '</div>';
    }

    public static function enqueue_assets($hook)
    {
        $admin_css_version = file_exists(AATF_PATH . 'assets/css/admin.css')
            ? (string) filemtime(AATF_PATH . 'assets/css/admin.css')
            : AATF_VERSION;
        $admin_js_version = file_exists(AATF_PATH . 'assets/js/admin.js')
            ? (string) filemtime(AATF_PATH . 'assets/js/admin.js')
            : AATF_VERSION;

        if ($hook === 'trek-framework_page_aatf-design-system') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_add_inline_script(
                'wp-color-picker',
                'jQuery(function($){$(".aatf-color-field").wpColorPicker();});'
            );
            return;
        }

        if ($hook === 'edit.php') {
            $screen = get_current_screen();
            if ($screen && $screen->post_type === 'trek') {
                wp_enqueue_style('aatf-admin-style', AATF_URL . 'assets/css/admin.css', array(), $admin_css_version);
            }
            return;
        }

        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $supported_post_types = array('trek', 'departure', 'trek_faq', 'destination', 'testimonial');
        if (!in_array($screen->post_type, $supported_post_types, true)) {
            return;
        }

        if ($screen->post_type === 'trek') {
            wp_enqueue_media();
            wp_enqueue_style('aatf-admin-style', AATF_URL . 'assets/css/admin.css', array(), $admin_css_version);
        }

        wp_enqueue_style('aatf-admin-style', AATF_URL . 'assets/css/admin.css', array(), $admin_css_version);
        wp_enqueue_script('aatf-admin', AATF_URL . 'assets/js/admin.js', array('jquery', 'wp-util'), $admin_js_version, true);
    }
}
