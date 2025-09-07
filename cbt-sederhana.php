<?php
/*
Plugin Name: CBT Sederhana
Description: Plugin Ujian CBT Sederhana berbasis WordPress.
Version: 1.0
Author: Dimas
Author URI: https://dimas-p.vercel.app/
*/

register_activation_hook(__FILE__, 'cbt_install');
register_activation_hook(__FILE__, 'cbt_create_pages');
include_once plugin_dir_path(__FILE__) . 'includes/install.php';
include_once plugin_dir_path(__FILE__) . 'includes/functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-panel.php';

add_shortcode('cbt_test', 'cbt_display_test');
add_shortcode('cbt_register', 'cbt_register_form');
add_shortcode('cbt_login', 'cbt_login_form');
add_shortcode('cbt_logout', 'cbt_logout_page');
// Enqueue Bootstrap dan timer
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], null, true);
    wp_enqueue_script('cbt-timer', plugin_dir_url(__FILE__) . 'assets/timer.js', [], false, true);
});
