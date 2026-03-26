<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Trek_Pages
{
    public static function create_core_pages()
    {
        $pages = array(
            'home' => 'Homepage',
            'treks' => 'Trek Listing',
            'destination' => 'Destination Page',
            'about' => 'About',
            'blog' => 'Blog',
            'contact' => 'Contact',
            'booking' => 'Booking System',
        );

        $created = array();

        foreach ($pages as $slug => $title) {
            $page = get_page_by_path($slug);

            if (!$page) {
                $default_content = '';
                if ($slug === 'booking') {
                    $default_content = '[aatf_booking_form]';
                }

                $page_id = wp_insert_post(array(
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => $default_content,
                ));

                if (!is_wp_error($page_id)) {
                    $created[$slug] = $page_id;
                }
            } else {
                $created[$slug] = $page->ID;
            }
        }

        if (!empty($created['home'])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', (int) $created['home']);
        }

        if (!empty($created['blog'])) {
            update_option('page_for_posts', (int) $created['blog']);
        }
    }
}
