<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!have_posts()) {
    return;
}

while (have_posts()) {
    the_post();

    $content = trim((string) get_the_content());
    if ($content === '') {
        continue;
    }

    echo '<section class="aatf-home-section aatf-home-section--content">';
    the_content();
    echo '</section>';
}

rewind_posts();
