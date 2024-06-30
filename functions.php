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

        <p>Add an ID for a Gravity Form to get back meta data on it for data comparison</p>

        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="test_gravity_forms">
            <?php wp_nonce_field( 'test_gravity_forms_action', 'test_gravity_forms_nonce' ); ?>
            
            <label for="gf_id">Gravity Form ID:</label>
            <input type="number" id="gf_id" name="gf_id" required><br><br>
            
            <?php submit_button( __( 'Test Gravity Form', 'text_domain' ) ); ?>
        </form>

    </div>
<?php
    if ( isset( $_GET['complete'] ) && $_GET['complete'] === 'true' ) {  

        global $wpdb;
        $table_name = $wpdb->prefix . 'gf_entries_data';
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1", ARRAY_A );

        if ( ! empty( $results ) ) {
            $gf_id = $results[0]['gf_id'];
            $gf_entries = get_gravity_forms_entries($gf_id);
            echo '<h2>Gravity Forms Entries</h2>';
            echo '<table>';
            echo '<tr><th>Entry ID</th><th>User ID</th><th>First Name</th><th>Last Name</th></tr>';
            foreach ( $gf_entries as $entry ) {
                $entry_id = $entry['id'];
                $user_id = $entry['created_by']; // Assuming 'created_by' is the user ID who created the entry
                $first_name = $entry['150.3'];
                $last_name = $entry['150.6'];
                
                // Replace '1' with the field ID for the name in your form
                $name = isset( $entry[1] ) ? $entry[1] : 'N/A'; 

                echo '<tr>';
                echo '<td>' . esc_html( $entry_id ) . '</td>';
                echo '<td>' . esc_html( $user_id ) . '</td>';
                echo '<td>' . esc_html( $first_name ) . '</td>';
                echo '<td>' . esc_html( $last_name ) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            ?>
            
            <?php
        } else {
            ?>
            <h1>No Data Found</h1>
            <?php
        }
    
    }
}

// Hook the 'add_main_menu_item' function to the 'admin_menu' action
add_action('admin_menu', 'add_main_menu_item');


// Button handlers

function handle_gf_button_click() {
    if ( isset( $_POST['test_gravity_forms_nonce'] ) && wp_verify_nonce( $_POST['test_gravity_forms_nonce'], 'test_gravity_forms_action' ) ) {

        $gf_id = sanitize_text_field( $_POST['gf_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'gf_entries_data';

        $row = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = 1" );

        if ( $row ) {
            error_log("Found a row with gf_id: " . $gf_id);
            // Update the existing row
            $wpdb->update(
                $table_name,
                array( 'gf_id' => $gf_id ),
                array( 'id' => 1 )
            );
        } else {
            error_log("Inserting a new row with gf_id: " . $gf_id);
            // Insert a new row
            $wpdb->insert(
                $table_name,
                array( 'gf_id' => $gf_id )
            );
        }

        wp_redirect( admin_url( 'admin.php?page=tax-to-users-admin&complete=true' ) );
        exit;
    } else {
        wp_die( 'Security check' );
    }
}
add_action( 'admin_post_test_gravity_forms', 'handle_gf_button_click' );



function get_gravity_forms_entries($id) {
    $entries = GFAPI::get_entries( $id );
    return $entries;
}