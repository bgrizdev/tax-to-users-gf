<?php
/*
Plugin Name: CPT Taxonomy to User Creation
Plugin URI: https://github.com/bgrizdev/tax-to-users-gf
Description: WP Plugin for converting Taxonomy from a CPT to Users and assigns old gravity form entries based on newly generated users
Version: 1.0
Author: Ben G
Author URI: https://bgriz.dev
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function tax_to_users_gf_plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gf_entries_data';
    $charset_collate = $wpdb->get_charset_collate(); 
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        gf_id mediumint(9) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook(__FILE__, 'tax_to_users_gf_plugin_activate');


function tax_to_users_gf_plugin_deactivate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gf_entries_data';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'tax_to_users_gf_plugin_deactivate');

function tax_to_users_gf_plugin_function() {
    // main functionality 
}
add_action('wp_footer', 'tax_to_users_gf_plugin_function');

require_once plugin_dir_path(__FILE__) . 'functions.php';
