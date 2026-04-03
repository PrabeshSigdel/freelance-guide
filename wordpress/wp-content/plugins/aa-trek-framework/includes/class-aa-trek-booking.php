<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Booking_Handler
{
    public static function register()
    {
        add_action('wp_ajax_aatf_submit_booking', array(__CLASS__, 'handle_booking_submission'));
        add_action('wp_ajax_nopriv_aatf_submit_booking', array(__CLASS__, 'handle_booking_submission'));
    }

    public static function handle_booking_submission()
    {
        check_ajax_referer('aatf_booking_nonce', 'security');

        $trek_id = isset($_POST['trek_id']) ? absint(wp_unslash($_POST['trek_id'])) : 0;
        $departure_id = isset($_POST['departure_id']) ? absint(wp_unslash($_POST['departure_id'])) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field(wp_unslash($_POST['date'])) : '';
        $travelers = isset($_POST['travelers']) ? absint(wp_unslash($_POST['travelers'])) : 1;
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        $traveler_details = isset($_POST['travelers_details']) ? self::sanitize_traveler_details(wp_unslash($_POST['travelers_details'])) : array();

        if ($trek_id <= 0 || get_post_type($trek_id) !== 'trek') {
            wp_send_json_error(array('message' => 'Please select a valid trek.'));
        }

        if ($departure_id > 0) {
            if (get_post_type($departure_id) !== 'departure') {
                $departure_id = 0;
            } else {
                $linked_trek = (int) get_post_meta($departure_id, 'aatf_trek_id', true);
                if ($linked_trek > 0 && $linked_trek !== $trek_id) {
                    $departure_id = 0;
                }
            }
        }

        if ($travelers <= 0) {
            $travelers = 1;
        }

        if ($departure_id <= 0) {
            wp_send_json_error(array('message' => 'Please select a departure date.'));
        }

        if ($date === '') {
            wp_send_json_error(array('message' => 'Please choose your departure date.'));
        }

        if (count($traveler_details) !== $travelers) {
            wp_send_json_error(array('message' => 'Please complete traveler details for everyone in your group.'));
        }

        foreach ($traveler_details as $index => $traveler) {
            $traveler_name = isset($traveler['full_name']) ? (string) $traveler['full_name'] : '';
            if ($traveler_name === '') {
                wp_send_json_error(array('message' => sprintf('Please enter the full name for Traveler %d.', (int) $index + 1)));
            }
        }

        $lead_traveler = isset($traveler_details[0]) && is_array($traveler_details[0]) ? $traveler_details[0] : array();
        $name = isset($lead_traveler['full_name']) ? (string) $lead_traveler['full_name'] : '';
        $email = isset($lead_traveler['email']) ? (string) $lead_traveler['email'] : '';
        $phone = isset($lead_traveler['phone']) ? (string) $lead_traveler['phone'] : '';

        if ($name === '' || $email === '' || !is_email($email)) {
            wp_send_json_error(array('message' => 'Please add a valid name and email for Traveler 1.'));
        }

        $pricing = self::calculate_booking_pricing($trek_id, $departure_id, $travelers);
        $price_per_person = isset($pricing['price_per_person']) ? (float) $pricing['price_per_person'] : 0.0;
        $total_price = isset($pricing['total_price']) ? (float) $pricing['total_price'] : 0.0;
        $price_source = isset($pricing['source']) ? (string) $pricing['source'] : 'request';
        $price_source_label = isset($pricing['source_label']) ? (string) $pricing['source_label'] : 'Price on request';

        $trek_title = get_the_title($trek_id);
        $booking_title = sprintf('Booking: %s by %s', $trek_title, $name);

        $booking_id = wp_insert_post(array(
            'post_type' => 'trek_booking',
            'post_title' => $booking_title,
            'post_content' => $message,
            'post_status' => 'publish',
        ));

        if (is_wp_error($booking_id)) {
            wp_send_json_error(array('message' => 'Failed to save booking. Please try again.'));
        }

        update_post_meta($booking_id, 'booking_trek_id', $trek_id);
        update_post_meta($booking_id, 'booking_departure_id', $departure_id);
        update_post_meta($booking_id, 'booking_name', $name);
        update_post_meta($booking_id, 'booking_email', $email);
        update_post_meta($booking_id, 'booking_phone', $phone);
        update_post_meta($booking_id, 'booking_date', $date);
        update_post_meta($booking_id, 'booking_travelers', $travelers);
        update_post_meta($booking_id, 'booking_travelers_details', $traveler_details);
        update_post_meta($booking_id, 'booking_price_per_person', $price_per_person);
        update_post_meta($booking_id, 'booking_total_price', $total_price);
        update_post_meta($booking_id, 'booking_price_source', $price_source);
        update_post_meta($booking_id, 'booking_status', 'pending');

        self::send_notifications($booking_id, $trek_id, $departure_id, $name, $email, $date, $travelers, $message, $pricing, $traveler_details);

        $price_per_person_label = $price_per_person > 0
            ? 'USD ' . number_format_i18n($price_per_person, floor($price_per_person) === $price_per_person ? 0 : 2)
            : 'Price on request';
        $total_price_label = $total_price > 0
            ? 'USD ' . number_format_i18n($total_price, floor($total_price) === $total_price ? 0 : 2)
            : 'Price on request';

        wp_send_json_success(array(
            'message' => 'Thank you! Your booking request has been sent.',
            'price_per_person' => $price_per_person,
            'total_price' => $total_price,
            'price_source' => $price_source,
            'price_source_label' => $price_source_label,
            'price_per_person_label' => $price_per_person_label,
            'total_price_label' => $total_price_label,
        ));
    }

    private static function send_notifications($booking_id, $trek_id, $departure_id, $name, $email, $date, $travelers, $message, $pricing, $traveler_details)
    {
        $admin_email = get_option('admin_email');
        $trek_title = get_the_title($trek_id);
        $departure_label = '';

        if ((int) $departure_id > 0 && get_post_type((int) $departure_id) === 'departure') {
            $start_raw = (string) get_post_meta((int) $departure_id, 'departure_start_date', true);
            $end_raw = (string) get_post_meta((int) $departure_id, 'departure_end_date', true);
            $start_ts = $start_raw !== '' ? strtotime($start_raw) : false;
            $end_ts = $end_raw !== '' ? strtotime($end_raw) : false;

            if ($start_ts && $end_ts && date_i18n(get_option('date_format'), $start_ts) !== date_i18n(get_option('date_format'), $end_ts)) {
                $departure_label = date_i18n(get_option('date_format'), $start_ts) . ' - ' . date_i18n(get_option('date_format'), $end_ts);
            } elseif ($start_ts) {
                $departure_label = date_i18n(get_option('date_format'), $start_ts);
            } elseif ($end_ts) {
                $departure_label = date_i18n(get_option('date_format'), $end_ts);
            } else {
                $departure_label = (string) get_the_title((int) $departure_id);
            }
        }

        $subject = sprintf('New Booking Request: %s', $trek_title);
        $body = "New booking request received.\n\n";
        $body .= "Trek: $trek_title\n";
        if ($departure_label !== '') {
            $body .= "Departure: $departure_label\n";
        }
        $body .= "Name: $name\n";
        $body .= "Email: $email\n";
        $body .= "Date: $date\n";
        $body .= "Travelers: $travelers\n\n";

        if (is_array($traveler_details) && !empty($traveler_details)) {
            $body .= "Traveler Details:\n";
            foreach ($traveler_details as $index => $traveler) {
                if (!is_array($traveler)) {
                    continue;
                }

                $traveler_name = isset($traveler['full_name']) ? (string) $traveler['full_name'] : '';
                $traveler_email = isset($traveler['email']) ? (string) $traveler['email'] : '';
                $traveler_phone = isset($traveler['phone']) ? (string) $traveler['phone'] : '';
                $traveler_passport = isset($traveler['passport_number']) ? (string) $traveler['passport_number'] : '';
                $traveler_nationality = isset($traveler['nationality']) ? (string) $traveler['nationality'] : '';
                $traveler_age = isset($traveler['age']) ? (string) $traveler['age'] : '';
                $traveler_gender = isset($traveler['gender']) ? (string) $traveler['gender'] : '';

                $body .= sprintf("%d. %s\n", (int) $index + 1, $traveler_name !== '' ? $traveler_name : 'Traveler');
                if ($traveler_email !== '') {
                    $body .= "   Email: $traveler_email\n";
                }
                if ($traveler_phone !== '') {
                    $body .= "   Phone: $traveler_phone\n";
                }
                if ($traveler_passport !== '') {
                    $body .= "   Passport Number: $traveler_passport\n";
                }
                if ($traveler_nationality !== '') {
                    $body .= "   Nationality: $traveler_nationality\n";
                }
                if ($traveler_age !== '') {
                    $body .= "   Age: $traveler_age\n";
                }
                if ($traveler_gender !== '') {
                    $body .= "   Gender: $traveler_gender\n";
                }
            }
            $body .= "\n";
        }

        if (is_array($pricing)) {
            $price_per_person = isset($pricing['price_per_person']) ? (float) $pricing['price_per_person'] : 0.0;
            $total_price = isset($pricing['total_price']) ? (float) $pricing['total_price'] : 0.0;
            $source_label = isset($pricing['source_label']) ? (string) $pricing['source_label'] : '';

            if ($price_per_person > 0) {
                $body .= "Price Per Person: USD " . number_format_i18n($price_per_person, floor($price_per_person) === $price_per_person ? 0 : 2) . "\n";
                $body .= "Total Estimate: USD " . number_format_i18n($total_price, floor($total_price) === $total_price ? 0 : 2) . "\n";
            } else {
                $body .= "Price: Price on request\n";
            }

            if ($source_label !== '') {
                $body .= "Price Source: $source_label\n";
            }

            $body .= "\n";
        }
        $body .= "Message:\n$message\n\n";
        $body .= "Manage this booking: " . admin_url('post.php?post=' . $booking_id . '&action=edit');

        wp_mail($admin_email, $subject, $body);

        // Confirmation to user
        $user_subject = sprintf('Booking Request Received: %s', $trek_title);
        $user_body = "Hi $name,\n\nThank you for your booking request for $trek_title. We have received your details and will get back to you shortly.\n\nBest regards,\nThe Trekking Team";
        
        wp_mail($email, $user_subject, $user_body);
    }

    private static function calculate_booking_pricing($trek_id, $departure_id, $travelers)
    {
        $trek_id = (int) $trek_id;
        $departure_id = (int) $departure_id;
        $travelers = max(1, (int) $travelers);

        $price_per_person = 0.0;
        $source = 'request';
        $source_label = 'Price on request';

        if ($departure_id > 0 && get_post_type($departure_id) === 'departure') {
            $linked_trek = (int) get_post_meta($departure_id, 'aatf_trek_id', true);
            $departure_price = (float) get_post_meta($departure_id, 'departure_price', true);

            if ($linked_trek === $trek_id && $departure_price > 0) {
                $price_per_person = $departure_price;
                $source = 'departure';
                $source_label = 'Departure price';
            }
        }

        if ($price_per_person <= 0) {
            $group_rows = self::get_group_pricing_rows($trek_id);
            $best_match = null;

            foreach ($group_rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $min_people = isset($row['min_people']) ? (int) $row['min_people'] : 0;
                $max_people = isset($row['max_people']) ? (int) $row['max_people'] : 0;
                $group_price = isset($row['price_per_person']) ? (float) $row['price_per_person'] : 0.0;

                if ($group_price <= 0 || $min_people <= 0) {
                    continue;
                }

                if ($travelers < $min_people) {
                    continue;
                }

                if ($max_people > 0 && $travelers > $max_people) {
                    continue;
                }

                if (!is_array($best_match) || $min_people > (int) $best_match['min_people']) {
                    $best_match = array(
                        'min_people' => $min_people,
                        'max_people' => $max_people,
                        'price_per_person' => $group_price,
                    );
                }
            }

            if (is_array($best_match)) {
                $price_per_person = (float) $best_match['price_per_person'];
                $source = 'group';
                $source_label = 'Group discount';
            }
        }

        if ($price_per_person <= 0) {
            $base_price = (float) get_post_meta($trek_id, 'trek_price', true);
            if ($base_price > 0) {
                $price_per_person = $base_price;
                $source = 'base';
                $source_label = 'Base trek price';
            }
        }

        $total_price = $price_per_person > 0 ? ($price_per_person * $travelers) : 0.0;

        return array(
            'price_per_person' => $price_per_person,
            'total_price' => $total_price,
            'source' => $source,
            'source_label' => $source_label,
        );
    }

    private static function sanitize_traveler_details($raw_details)
    {
        if (!is_array($raw_details)) {
            return array();
        }

        $clean_details = array();

        foreach ($raw_details as $traveler) {
            if (!is_array($traveler)) {
                continue;
            }

            $full_name = isset($traveler['full_name']) ? sanitize_text_field((string) $traveler['full_name']) : '';
            $email = isset($traveler['email']) ? sanitize_email((string) $traveler['email']) : '';
            $phone = isset($traveler['phone']) ? sanitize_text_field((string) $traveler['phone']) : '';
            $passport_number = isset($traveler['passport_number']) ? sanitize_text_field((string) $traveler['passport_number']) : '';
            $nationality = isset($traveler['nationality']) ? sanitize_text_field((string) $traveler['nationality']) : '';
            $age = isset($traveler['age']) ? absint($traveler['age']) : 0;
            $gender = isset($traveler['gender']) ? sanitize_text_field((string) $traveler['gender']) : '';

            if ($full_name === '' && $email === '' && $phone === '' && $passport_number === '' && $nationality === '' && $age <= 0 && $gender === '') {
                continue;
            }

            $clean_details[] = array(
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'passport_number' => $passport_number,
                'nationality' => $nationality,
                'age' => $age > 0 ? (string) $age : '',
                'gender' => $gender,
            );
        }

        return $clean_details;
    }

    private static function get_group_pricing_rows($trek_id)
    {
        $rows = get_post_meta((int) $trek_id, 'trek_group_pricing', true);
        if (!is_array($rows)) {
            return array();
        }

        $clean_rows = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $min_people = isset($row['min_people']) ? absint($row['min_people']) : 0;
            $max_people = isset($row['max_people']) ? absint($row['max_people']) : 0;
            $price_raw = isset($row['price_per_person']) ? (string) $row['price_per_person'] : '';
            $price_raw = str_replace(',', '', $price_raw);
            $price_per_person = is_numeric($price_raw) ? max(0.0, (float) $price_raw) : 0.0;

            if ($min_people <= 0 && $max_people <= 0 && $price_per_person <= 0) {
                continue;
            }

            if ($min_people > 0 && $max_people > 0 && $max_people < $min_people) {
                $max_people = $min_people;
            }

            $clean_rows[] = array(
                'min_people' => $min_people,
                'max_people' => $max_people,
                'price_per_person' => $price_per_person,
            );
        }

        return $clean_rows;
    }
}
