<?php

function my_custom_submenu_page() {
    add_submenu_page(
        'wedstrijd-planner-plugin', // Parent menu slug (Settings)
        'Wedstrijd planner settings', // Page title
        'Settings', // Menu title
        'manage_options', // Capability
        'wedstrijd-planner-submenu', // Menu slug
        'wedstrijd_planner_submenu' // Callback function
    );
}
add_action('admin_menu', 'my_custom_submenu_page');

function truncate_teams_table() {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = get_teams_table_name();

    // Step 1: Truncate the table
    $wpdb->query("TRUNCATE TABLE $table_name");

    // Check if the table was truncated successfully
    if ($wpdb->last_error) {
        echo 'Error truncating table: ' . $wpdb->last_error;
        return;
    }
}

function insert_teams($teams) {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = get_teams_table_name();

    foreach ($teams as $data) {
        $wpdb->insert($table_name, array("team" => $data));
    
        // Check for errors
        if ($wpdb->last_error) {
            echo 'Error inserting row: ' . $wpdb->last_error;
            break;
        }
    }
    
}

function get_teams() {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = get_teams_table_name();

    // Query to get all rows
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    $teams = [];

    // Check if results exist
    if ($results) {
        foreach ($results as $row) {
            array_push($teams, $row->team);
        }
    }

    return $teams;
}

// Callback function to render the submenu page content
function wedstrijd_planner_submenu() {
    // =======================
	// Save data
	// =======================
	if(isset($_POST['teams']) && wp_verify_nonce($_POST['save_teams_nonce'])) {

		if ($_POST['teams'] != null) {
            $teams = explode(',', preg_replace('/[^\S ]+/', '', $_POST['teams']));

            $teams = array_filter($teams);

            truncate_teams_table();
            insert_teams($teams);
		}
	}

    $teams = get_teams();


    ?>
    <div class="wrap wedstrijd_planner_settings">
        <h1>Wedstrijd Planner settings</h1>
        <p>Teams</p>
        <form method="POST" class="settings_form">
            <?php wp_nonce_field(-1, 'save_teams_nonce') ?>
            <textarea name="teams"><?= join(",\n",$teams); ?></textarea>
            <input type="submit" name="save_wedstrijden" class="button button-primary" value="Opslaan">
        </form>
    </div>
    <?php
}
