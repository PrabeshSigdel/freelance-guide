<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Frontend
{
    public static function register()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_filter('body_class', array(__CLASS__, 'add_body_class'));
        add_action('pre_get_posts', array(__CLASS__, 'order_trek_queries'));
    }

    public static function enqueue_assets()
    {
        $css_version = file_exists(AATF_PATH . 'assets/css/design-system.css')
            ? (string) filemtime(AATF_PATH . 'assets/css/design-system.css')
            : AATF_VERSION;
        $js_version = file_exists(AATF_PATH . 'assets/js/main.js')
            ? (string) filemtime(AATF_PATH . 'assets/js/main.js')
            : AATF_VERSION;

        wp_enqueue_style('aatf-design-system', AATF_URL . 'assets/css/design-system.css', array(), $css_version);
        wp_add_inline_style('aatf-design-system', self::build_color_system_css());

        wp_enqueue_script('aatf-frontend', AATF_URL . 'assets/js/main.js', array(), $js_version, true);
    }

    public static function add_body_class($classes)
    {
        $classes[] = 'aatf-design-system';
        return $classes;
    }

    public static function order_trek_queries($query)
    {
        if (is_admin() || !$query instanceof WP_Query || !$query->is_main_query()) {
            return;
        }

        if ($query->get('post_type') !== 'trek' && !$query->is_post_type_archive('trek')) {
            return;
        }

        $query->set('meta_key', 'trek_display_order');
        $query->set('orderby', array(
            'meta_value_num' => 'ASC',
            'title' => 'ASC',
        ));
        $query->set('order', 'ASC');
    }

    public static function get_default_colors()
    {
        return array(
            'primary' => '#0b5fff',
            'primary_dark' => '#0847bc',
            'accent' => '#ff7a18',
            'surface' => '#f7f9fc',
            'ink' => '#13243c',
            'muted' => '#617087',
            'line' => '#d8e0ea',
            'success' => '#1f9d55',
        );
    }

    private static function build_color_system_css()
    {
        $defaults = self::get_default_colors();
        $saved = get_option('aatf_design_system_colors', array());
        $saved = is_array($saved) ? $saved : array();

        $colors = array();
        foreach ($defaults as $key => $default_value) {
            $candidate = isset($saved[$key]) ? $saved[$key] : $default_value;
            $colors[$key] = sanitize_hex_color($candidate) ? sanitize_hex_color($candidate) : $default_value;
        }

        $colors = apply_filters('aatf_color_system', $colors);

        $safe_colors = array();
        foreach ($defaults as $key => $default_value) {
            $candidate = isset($colors[$key]) ? $colors[$key] : $default_value;
            $safe_colors[$key] = sanitize_hex_color($candidate) ? sanitize_hex_color($candidate) : $default_value;
        }

        return sprintf(
            ':root{--aatf-color-primary:%1$s;--aatf-color-primary-dark:%2$s;--aatf-color-accent:%3$s;--aatf-color-surface:%4$s;--aatf-color-ink:%5$s;--aatf-color-muted:%6$s;--aatf-color-line:%7$s;--aatf-color-success:%8$s;}',
            $safe_colors['primary'],
            $safe_colors['primary_dark'],
            $safe_colors['accent'],
            $safe_colors['surface'],
            $safe_colors['ink'],
            $safe_colors['muted'],
            $safe_colors['line'],
            $safe_colors['success']
        );
    }
}
