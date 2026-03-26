<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Framework
{
    private static $loaded = false;

    public static function boot()
    {
        self::load_dependencies();
        self::register_hooks();
    }

    private static function load_dependencies()
    {
        if (self::$loaded) {
            return;
        }

        $dependencies = array(
            'includes/class-aa-trek-post-types.php',
            'includes/class-aa-trek-meta.php',
            'includes/class-aa-trek-booking.php',
            'includes/class-aa-trek-pages.php',
            'includes/admin/class-aa-trek-admin.php',
            'includes/admin/class-aa-trek-capabilities.php',
            'includes/admin/class-aa-trek-relationships.php',
            'includes/frontend/class-aa-trek-frontend.php',
            'includes/frontend/class-aa-trek-components.php',
        );

        foreach ($dependencies as $dependency) {
            require_once AATF_PATH . $dependency;
        }

        self::$loaded = true;
    }

    private static function register_hooks()
    {
        add_action('after_setup_theme', array('AATF_Trek_Post_Types', 'register_thumbnail_support'), 5);
        add_filter('use_block_editor_for_post_type', array(__CLASS__, 'maybe_disable_block_editor_for_post_type'), 10, 2);
        add_action('init', array('AATF_Trek_Post_Types', 'register_post_types'), 5);
        add_action('init', array('AATF_Trek_Post_Types', 'register_taxonomies'), 6);
        add_action('init', array('AATF_Trek_Meta', 'register'), 7);
        add_action('init', array('AATF_Booking_Handler', 'register'), 7);
        add_action('init', array('AATF_Trek_Capabilities', 'register'), 8);
        add_action('init', array('AATF_Trek_Relationships', 'register'), 9);
        add_action('init', array('AATF_Admin', 'register'), 10);
        add_action('init', array('AATF_Frontend', 'register'), 11);
        add_action('init', array('AATF_Frontend_Components', 'register'), 12);
        add_action('init', array('AATF_Trek_Meta', 'maybe_backfill_trek_display_order_meta'), 13);

        register_activation_hook(AATF_FILE, array(__CLASS__, 'activate'));
        register_deactivation_hook(AATF_FILE, array(__CLASS__, 'deactivate'));
    }

    public static function activate()
    {
        self::load_dependencies();
        AATF_Trek_Post_Types::register_post_types();
        AATF_Trek_Post_Types::register_taxonomies();
        AATF_Trek_Meta::maybe_backfill_trek_display_order_meta();
        AATF_Trek_Capabilities::activate();
        AATF_Trek_Pages::create_core_pages();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function maybe_disable_block_editor_for_post_type($use_block_editor, $post_type)
    {
        $classic_post_types = array('post', 'trek', 'destination', 'departure', 'trek_faq', 'testimonial');

        if (in_array($post_type, $classic_post_types, true)) {
            return false;
        }

        return $use_block_editor;
    }
}
