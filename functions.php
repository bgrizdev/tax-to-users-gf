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
        <form id="create-users-form" method="post" action="#">
            <input type="hidden" name="action" value="create_users_from_assessors">
            <?php
            wp_nonce_field('create_users_from_assessors_action', 'create_users_from_assessors_nonce');
            submit_button(__('Create Users', 'text_domain'), 'primary', 'create-users-button');
            ?>
        </form>

        <div id="batch-status">Processing batch number: 0</div>

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
    if ( isset( $_GET['created'] ) && $_GET['created'] === 'true' ) {  

        echo('<div class="notice notice-success is-dismissible">
                <p>Successfully created Users from Taxonomy</p>
        </div>');

    }

}

// Hook the 'add_main_menu_item' function to the 'admin_menu' action
add_action('admin_menu', 'add_main_menu_item');


// Button handlers

function handle_create_users_button_click() {
    // Check if the form is submitted
    if ( isset( $_POST['create_users_from_assessors_nonce'] ) ) {
        // Verify nonce

        //error_log('button clicked');

        if ( ! wp_verify_nonce( $_POST['create_users_from_assessors_nonce'], 'create_users_from_assessors_action' ) ) {
            return;
        }


        // Call the function to create users
        create_users_from_assessor_terms();

        // Redirect to avoid form resubmission
        wp_redirect( admin_url( 'admin.php?page=tax-to-users-admin&created=true' ) );
        exit;
    }
}
add_action( 'admin_post_create_users_from_assessors', 'handle_create_users_button_click' );

function handle_gf_button_click() {
    if ( isset( $_POST['test_gravity_forms_nonce'] ) && wp_verify_nonce( $_POST['test_gravity_forms_nonce'], 'test_gravity_forms_action' ) ) {

        $gf_id = sanitize_text_field( $_POST['gf_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'gf_entries_data';

        $row = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = 1" );

        if ( $row ) {
            // Update the existing row
            $wpdb->update(
                $table_name,
                array( 'gf_id' => $gf_id ),
                array( 'id' => 1 )
            );
        } else {
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


function create_users_from_assessor_terms() {
    // Get all terms in the 'assessors' taxonomy
    $terms = get_terms( array(
        'taxonomy' => 'assessors',
        'hide_empty' => false,
    ) );

    // Check if there are any terms
    if ( !empty($terms) && !is_wp_error($terms) ) {
        foreach ( $terms as $term ) {
            // Get ACF fields for the term
            $first_name = get_field('first_name', 'assessors_' . $term->term_id);
            $last_name = get_field('last_name', 'assessors_' . $term->term_id);
            $company_name = get_field('company_name', 'assessors_' . $term->term_id);
            $phone_number = get_field('phone_number', 'assessors_' . $term->term_id);
            $email_address = get_field('email_address', 'assessors_' . $term->term_id);

            // Check if required fields are present
            if ( $first_name && $last_name && $email_address ) {
                // Use the term name as the username
                $username = sanitize_user( $term->name );

                // Check if the user already exists
                if ( !username_exists( $username ) && !email_exists( $email_address ) ) {
                    // Create a new user
                    $user_id = wp_create_user( $username, wp_generate_password(), $email_address );

                    // Check if the user was created successfully
                    if ( !is_wp_error($user_id) ) {
                        // Update user meta with additional information
                        wp_update_user( array(
                            'ID' => $user_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'nickname' => $company_name,
                        ) );

                        // Update user meta with phone number
                        update_user_meta( $user_id, 'phone_number', $phone_number );

                        $user = new WP_User( $user_id );
                        $user->set_role( 'assessor' );

                        // Update gravity form entries with new user data
                        update_gravity_forms_entries($first_name, $last_name, $user_id);
                    }
                }
            }
        }
    }
}

function update_gravity_forms_entries($first_name, $last_name, $user_id) {

    $form_id = 3; 
    $criteria = array();
    $sorting = null;
    $paging = array( 'offset' => 0, 'page_size' => 1000 ); 
    
    // Fetch entries
    $entries = GFAPI::get_entries($form_id, $criteria, $sorting, $paging);
    
    foreach ( $entries as $entry ) {
        $entry_id = $entry['id'];
        $created_by = $entry['created_by']; 
        $form_first_name = $entry['150.3'];
        $form_last_name = $entry['150.6'];

        if($first_name == $form_first_name && $last_name == $form_last_name ) {

            $entry['created_by'] = $user_id;
            
            $result = GFAPI::update_entry($entry);

        }

    }

}

function create_users_from_assessor_terms_batch($batch_number = 0) {
    $batch_size = 10; 
    $offset = $batch_number * $batch_size;

    // Get terms in the 'assessors' taxonomy with offset and limit for batching
    $terms = get_terms( array(
        'taxonomy' => 'assessors',
        'hide_empty' => false,
        'number' => $batch_size,
        'offset' => $offset,
    ) );

    // Process terms
    if ( !empty($terms) && !is_wp_error($terms) ) {
        foreach ( $terms as $term ) {
            // Get ACF fields for the term
            $first_name = get_field('first_name', 'assessors_' . $term->term_id);
            $last_name = get_field('last_name', 'assessors_' . $term->term_id);
            $company_name = get_field('company_name', 'assessors_' . $term->term_id);
            $phone_number = get_field('phone_number', 'assessors_' . $term->term_id);
            $email_address = get_field('email_address', 'assessors_' . $term->term_id);

            // Check if required fields are present
            if ( $first_name && $last_name && $email_address ) {
                // Use the term name as the username
                $username = sanitize_user( $term->name );

                // Check if the user already exists
                if ( !username_exists( $username ) && !email_exists( $email_address ) ) {
                    // Create a new user
                    $user_id = wp_create_user( $username, wp_generate_password(), $email_address );

                    // Check if the user was created successfully
                    if ( !is_wp_error($user_id) ) {
                        // Update user meta with additional information
                        wp_update_user( array(
                            'ID' => $user_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'nickname' => $company_name,
                        ) );

                        // Update user meta with phone number
                        update_user_meta( $user_id, 'phone_number', $phone_number );

                        $user = new WP_User( $user_id );
                        $user->set_role( 'assessor' );

                        // Update gravity form entries with new user data
                        update_gravity_forms_entries($first_name, $last_name, $user_id);
                    }
                }
            }
        }

        // Check if there are more terms to process
        if ( count($terms) == $batch_size ) {
            return true; 
        }
    }

    return false; 
}


function handle_ajax_create_users() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_users_from_assessors_action')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    $batch_number = isset($_POST['batch_number']) ? intval($_POST['batch_number']) : 0;

    $has_more_batches = create_users_from_assessor_terms_batch($batch_number);

    wp_send_json_success(array('has_more_batches' => $has_more_batches, 'batch_number' => $batch_number + 1));
}

add_action('wp_ajax_create_users_batch', 'handle_ajax_create_users');


function enqueue_batch_processing_script() {
    wp_enqueue_script('batch-processing-script', plugin_dir_url(__FILE__) . 'batch-processing.js', array('jquery'), null, true);
    wp_localize_script('batch-processing-script', 'batchProcessingData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('create_users_from_assessors_action'),
        'redirect_url' => admin_url('admin.php?page=tax-to-users-admin&created=true'),
    ));
}

add_action('admin_enqueue_scripts', 'enqueue_batch_processing_script');

