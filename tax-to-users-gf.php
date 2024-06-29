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
    // activation code
    // doesn't really need to do anything here
}
register_activation_hook(__FILE__, 'tax_to_users_gf_plugin_activate');


function tax_to_users_gf_plugin_deactivate() {
    // deactivation code
    // doesn't really need to do anything here either 
}
register_deactivation_hook(__FILE__, 'tax_to_users_gf_plugin_deactivate');

function tax_to_users_gf_plugin_function() {
    // main 
}
add_action('wp_footer', 'tax_to_users_gf_plugin_function');

require_once plugin_dir_path(__FILE__) . 'functions.php';

