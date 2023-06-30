<?php
// Create a new admin page
add_action('admin_menu', 'tmbb_create_admin_page');

function tmbb_create_admin_page() {
    add_menu_page(
        'Touch My Balaboombo',
        'Touch My Balaboombo',
        'manage_options',
        'tmbb-admin-page',
        'tmbb_admin_page_display',
        'dashicons-admin-media',
        20
    );
}

function tmbb_admin_page_display() {
    // Get filter option
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    // Get images without alt, caption, or description
    $images = tmbb_find_images($filter);

    // Display filter options
    echo '<div class="wrap tmbb-wrap">';
	echo '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url( __FILE__ ) . 'tmbb-styles.css">';
    echo '<h1 class="tmbb-title">Touch My Balaboombo</h1>';
    echo '<div class="tmbb-filter">';
    echo '<label for="tmbb-filter-select">Filter:</label>';
    echo '<select id="tmbb-filter-select" onchange="window.location.href=this.value">';
    echo '<option value="?page=tmbb-admin-page&filter=all"' . ($filter == 'all' ? ' selected' : '') . '>All</option>';
    echo '<option value="?page=tmbb-admin-page&filter=alt"' . ($filter == 'alt' ? ' selected' : '') . '>Empty ALT</option>';
    echo '<option value="?page=tmbb-admin-page&filter=caption"' . ($filter == 'caption' ? ' selected' : '') . '>Empty Caption</option>';
    echo '<option value="?page=tmbb-admin-page&filter=description"' . ($filter == 'description' ? ' selected' : '') . '>Empty Description</option>';
    echo '</select>';
    echo '</div>';

    // Pagination
    $page = isset($_GET['page_number']) ? intval($_GET['page_number']) : 1;
    $per_page = 20;
    $total_pages = ceil(count($images) / $per_page);
    $offset = ($page - 1) * $per_page;
    $paged_images = array_slice($images, $offset, $per_page);

    // Display images in a table
    echo '<form method="post">';
    echo '<table class="tmbb-table">';
    echo '<tr><th>Image</th><th>Alt</th><th>Caption</th><th>Description</th><th>Used In</th></tr>';
    foreach ($paged_images as $image) {
        echo '<tr>';
        echo '<td><a href="' . wp_get_attachment_url($image->ID) . '" target="_blank"><img src="' . wp_get_attachment_url($image->ID) . '" width="100"></a></td>';
        echo '<td><input type="text" name="alt[' . $image->ID . ']" value="' . get_post_meta($image->ID, '_wp_attachment_image_alt', true) . '"></td>';
        echo '<td><input type="text" name="caption[' . $image->ID . ']" value="' . $image->post_excerpt . '"></td>';
        echo '<td><input type="text" name="description[' . $image->ID . ']" value="' . $image->post_content . '"></td>';
        echo '<td>' . get_image_usage($image->ID) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<button type="submit" name="save_all" class="button button-primary tmbb-submit">Save All</button>';
    echo '</form>';

    // Pagination links
    echo '<div class="tmbb-pagination">';
    if ($page > 1) {
        echo '<a href="?page=tmbb-admin-page&filter=' . $filter . '&page_number=' . ($page - 1) . '">&laquo; Previous</a>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<a href="?page=tmbb-admin-page&filter=' . $filter . '&page_number=' . $i . '"' . ($page == $i ? ' class="active"' : '') . '>' . $i . '</a>';
    }
    if ($page < $total_pages) {
        echo '<a href="?page=tmbb-admin-page&filter=' . $filter . '&page_number=' . ($page + 1) . '">Next &raquo;</a>';
    }
    echo '</div>';

    echo '</div>';
}

// Process form submission
add_action('admin_init', 'tmbb_process_form_submission');

function tmbb_process_form_submission() {
    if (isset($_POST['save_all'])) {
        $alt_values = isset($_POST['alt']) ? $_POST['alt'] : array();
        $caption_values = isset($_POST['caption']) ? $_POST['caption'] : array();
        $description_values = isset($_POST['description']) ? $_POST['description'] : array();

        foreach ($alt_values as $image_id => $alt) {
            $image_id = intval($image_id);
            $alt = sanitize_text_field($alt);
            $caption = sanitize_text_field($caption_values[$image_id]);
            $description = sanitize_text_field($description_values[$image_id]);

            // Update image alt
            update_post_meta($image_id, '_wp_attachment_image_alt', $alt);

            // Update image caption and description
            wp_update_post(array(
                'ID' => $image_id,
                'post_excerpt' => $caption,
                'post_content' => $description,
            ));
        }

        // Redirect back to the admin page after saving
        wp_safe_redirect(menu_page_url('tmbb-admin-page', false));
        exit;
    }
}

// Get image usage
function get_image_usage($image_id) {
    global $wpdb;
    $posts_table = $wpdb->prefix . 'posts';
    $postmeta_table = $wpdb->prefix . 'postmeta';

    $query = $wpdb->prepare(
        "SELECT p.ID, p.post_title
        FROM $posts_table AS p
        LEFT JOIN $postmeta_table AS pm ON p.ID = pm.post_id
        WHERE (
            p.post_content LIKE %s OR
            p.post_excerpt LIKE %s OR
            pm.meta_value LIKE %s
        )
        AND p.post_status = 'publish'
        AND p.post_type IN ('post', 'page')
        GROUP BY p.ID",
        '%' . $wpdb->esc_like($image_id) . '%',
        '%' . $wpdb->esc_like($image_id) . '%',
        '%' . $wpdb->esc_like($image_id) . '%'
    );

    $results = $wpdb->get_results($query);

    if (!empty($results)) {
        $output = '';
        foreach ($results as $result) {
            $output .= '<a href="' . get_permalink($result->ID) . '" target="_blank">' . $result->post_title . '</a>, ';
        }
        $output = rtrim($output, ', ');

        return $output;
    }

    return 'N/A';
}
?>

