<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Trek_Capabilities
{
    public static function register()
    {
        self::maybe_sync_roles();
    }

    public static function activate()
    {
        self::sync_roles();
    }

    private static function maybe_sync_roles()
    {
        $stored = get_option('aatf_caps_version', '');
        if ($stored === AATF_VERSION) {
            return;
        }

        self::sync_roles();
        update_option('aatf_caps_version', AATF_VERSION);
    }

    private static function sync_roles()
    {
        $all_caps = self::get_all_caps();

        $trek_manager = get_role('trek_manager');
        if (!$trek_manager) {
            $trek_manager = add_role('trek_manager', 'Trek Manager', array('read' => true, 'upload_files' => true));
        }

        if ($trek_manager instanceof WP_Role) {
            foreach ($all_caps as $cap) {
                $trek_manager->add_cap($cap);
            }
        }

        $admin = get_role('administrator');
        if ($admin instanceof WP_Role) {
            foreach ($all_caps as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    private static function get_all_caps()
    {
        $post_types = array(
            array('trek', 'treks'),
            array('destination', 'destinations'),
            array('departure', 'departures'),
            array('testimonial', 'testimonials'),
            array('trek_faq', 'trek_faqs'),
        );

        $caps = array();
        foreach ($post_types as $types) {
            $caps = array_merge($caps, self::build_caps($types[0], $types[1]));
        }

        $caps[] = 'read';
        $caps[] = 'upload_files';

        return array_values(array_unique($caps));
    }

    private static function build_caps($singular, $plural)
    {
        return array(
            "edit_{$singular}",
            "read_{$singular}",
            "delete_{$singular}",
            "edit_{$plural}",
            "edit_others_{$plural}",
            "publish_{$plural}",
            "read_private_{$plural}",
            "delete_{$plural}",
            "delete_private_{$plural}",
            "delete_published_{$plural}",
            "delete_others_{$plural}",
            "edit_private_{$plural}",
            "edit_published_{$plural}",
            "create_{$plural}",
        );
    }
}
