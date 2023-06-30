<?php
/*
Plugin Name: Touch My Balaboombo
Description: A plugin to detect and manage images with empty or missing alt, caption, and description attributes.
Author: Yoav Prokofyev
Author URI: https://yoav.pro
*/

// Include other plugin files
include( plugin_dir_path( __FILE__ ) . 'includes/tmbb-admin-page.php');
include( plugin_dir_path( __FILE__ ) . 'includes/tmbb-image-finder.php');
include( plugin_dir_path( __FILE__ ) . 'includes/tmbb-image-updater.php');
?>

