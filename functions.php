<?php

// Add admin menu item 
function add_main_menu_item() {
    add_menu_page(
        'Tax To Users',      
        'Tax To Users',      
        'manage_options',            
        'tax-to-users-admin',      
        'admin_main_page_content', 
        'dashicons-admin-generic',   
        6                            
    );
}

// Admin main page content
function admin_main_page_content() {
    ?>
    <div class="wrap">
        <h1>CPT Taxonomy To Users Overview</h1>

        <p>This is for generating the users based on the Taxonomy in the Assessment CPT. This is very specific to this one use case.</p>
        <p>Running this will generate users based on the settings of the Taxonomy which include a first and last name and email address.</p>
        <p>The new users will be created as Accessors which a Custom User Role Defined in the theme.</p>
        <p>Each Piece of Taxonomy is associated with Gravity Forms entries, during the creation process the entries are checked based on the name field and compared to the name of the newly created user.</p>
        <p>If a match is identified then the entry's created_by field will be updated to reflect the value of the newly created user_id.</p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="create_users_from_assessors">
            <?php
            wp_nonce_field( 'create_users_from_assessors_action', 'create_users_from_assessors_nonce' );
            submit_button( __( 'Create Users', 'text_domain' ) );
            ?>
        </form>

        <p>The form fields below should be deleted, all this button does is return some GF data for comparison/confirmation purposes.</p>

        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="test_gravity_forms">
            <?php wp_nonce_field( 'test_gravity_forms_action', 'test_gravity_forms_nonce' ); ?>
            
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required><br><br>
            
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required><br><br>
            
            <label for="userid">User ID:</label>
            <input type="text" id="userid" name="userid" required><br><br>
            
            <?php submit_button( __( 'Test Gravity Form', 'text_domain' ) ); ?>
        </form>

    </div>
    <?php
}

// Hook the 'add_main_menu_item' function to the 'admin_menu' action
add_action('admin_menu', 'add_main_menu_item');


// 