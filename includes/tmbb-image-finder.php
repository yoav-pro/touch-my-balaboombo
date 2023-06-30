<?php
// Function to find images without alt, caption, or description
function tmbb_find_images($filter = 'all') {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' =>'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    );
    $query_images = new WP_Query($args);
    $images = array();
    foreach ($query_images->posts as $image) {
        $alt = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
        $description = $image->post_content;
        $caption = $image->post_excerpt;
        if ($filter == 'all' && (empty($alt) || empty($description) || empty($caption)) ||
            $filter == 'alt' && empty($alt) ||
            $filter == 'caption' && empty($caption) ||
            $filter == 'description' && empty($description)) {
            $images[] = $image;
        }
    }
    return $images;
}
?>
