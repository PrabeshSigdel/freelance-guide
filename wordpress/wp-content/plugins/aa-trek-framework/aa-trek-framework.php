<?php
/**
 * Plugin Name: AA Trek Framework
 * Description: Reusable trekking website framework architecture for WordPress agencies.
 * Version: 0.4.3
 * Author: suresh shrestha
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: aa-trek-framework
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('AATF_VERSION')) {
    define('AATF_VERSION', '0.4.3');
}

if (!defined('AATF_FILE')) {
    define('AATF_FILE', __FILE__);
}

if (!defined('AATF_PATH')) {
    define('AATF_PATH', plugin_dir_path(__FILE__));
}

if (!defined('AATF_URL')) {
    define('AATF_URL', plugin_dir_url(__FILE__));
}

require_once AATF_PATH . 'includes/class-aa-trek-framework.php';

AATF_Framework::boot();
