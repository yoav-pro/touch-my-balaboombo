<?php
// Function to update image alt, caption, and description
function tmbb_update_image() {
    // Check if image ID and new values are set
    if (isset($_POST['id']) && isset($_POST['alt']) && isset($_POST['caption']) && isset($_POST['description'])) {
        $id = intval($_POST['id']);
        $alt = sanitize_text_field($_POST['alt']);
        $caption = sanitize_text_field($_POST['caption']);
        $description = sanitize_text_field($_POST['description']);

        // Update image alt
        update_post_meta($id, '_wp_attachment_image_alt', $alt);

        // Update image caption and description
        wp_update_post(array(
            'ID' => $id,
            'post_excerpt' => $caption,
            'post_content' => $description,
        ));
    }

    // Send a response back to the script
    echo 'Image updated successfully';
    wp_die();
}
add_action('wp_ajax_tmbb_update_image', 'tmbb_update_image');
?>

