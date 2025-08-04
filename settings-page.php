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

function truncate_table($tableName) {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = $wpdb->prefix . $tableName;

    // Step 1: Truncate the table
    $wpdb->query("TRUNCATE TABLE $table_name");

    // Check if the table was truncated successfully
    if ($wpdb->last_error) {
        echo 'Error truncating table: ' . $wpdb->last_error;
        return;
    }
}

function insert_entries($entries, $rowName, $tableName) {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = $wpdb->prefix . $tableName;

    foreach ($entries as $data) {
        $wpdb->insert($table_name, array($rowName => $data));
    
        // Check for errors
        if ($wpdb->last_error) {
            echo 'Error inserting row: ' . $wpdb->last_error;
            break;
        }
    }
    
}

function get_entries($rowName, $tableName) {
    global $wpdb;

    // Define your table name (with prefix)
    $table_name = $wpdb->prefix . $tableName;

    // Query to get all rows
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    $entries = [];

    // Check if results exist
    if ($results) {
        foreach ($results as $row) {
            array_push($entries, $row->$rowName);
        }
    }

    return $entries;
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

            truncate_table("wedstrijd_planner_teams");
            insert_entries($teams, "team", "wedstrijd_planner_teams");
		}
	}

    $teams = get_entries("team", "wedstrijd_planner_teams");

    if(isset($_POST['exclude_poules']) && wp_verify_nonce($_POST['save_exclude_poules_nonce'])) {

		if ($_POST['exclude_poules'] != null) {
            $exclude_poules = explode(',', preg_replace('/[^\S ]+/', '', $_POST['exclude_poules']));
 
            $exclude_poules = array_filter($exclude_poules);

            truncate_table("wedstrijd_planner_exclude_poules");
            insert_entries($exclude_poules, "poule", "wedstrijd_planner_exclude_poules");
		}
	}

    $exclude_poules = get_entries("poule", "wedstrijd_planner_exclude_poules");

    ?>
    <div class="wrap wedstrijd_planner_settings">
        <h1>Wedstrijd Planner settings</h1>
        <p>Teams</p>
        <form method="POST" class="settings_form">
            <?php wp_nonce_field(-1, 'save_teams_nonce') ?>
            <textarea name="teams"><?= join(",\n",$teams); ?></textarea>
            <input type="submit" name="save_teas" class="button button-primary" value="Opslaan">
        </form>

        <p>Exclude poules</p>
        <form method="POST" class="settings_form">
            <?php wp_nonce_field(-1, 'save_exclude_poules_nonce') ?>
            <textarea name="exclude_poules"><?= join(",\n",$exclude_poules); ?></textarea>
            <input type="submit" name="save_exclude_poules" class="button button-primary" value="Opslaan">
        </form>
    </div>
    <?php
}
