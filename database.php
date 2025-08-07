<?php
function activate_wedstrijd_planner() {
	// Create database
	global $wpdb;

	$wedstrijd_planner_table_name = get_wedstrijd_planner_table_name();
	$teams_table_name = get_teams_table_name();
    $exclude_poules_table_name = get_exclude_poules_table_name();

    // Define the charset
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$wedstrijd_planner_table_name'") != $wedstrijd_planner_table_name) {
        // SQL query to create the table
        $sql = "CREATE TABLE $wedstrijd_planner_table_name (
            code varchar(100) NOT NULL,
            team_thuis varchar(255) NOT NULL,
            team_uit varchar(255) NOT NULL,
            datum datetime NOT NULL,
			veld varchar(255),
			regio varchar(255),
            poule varchar(255),
			teller varchar(255),
			scheidsrechter varchar(255),
			actief boolean DEFAULT 1,
			veranderd boolean DEFAULT 0,
            PRIMARY KEY (code)
        ) $charset_collate;";

        // Include the upgrade file for dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute the query
        dbDelta($sql);
    }

	// Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$teams_table_name'") != $teams_table_name) {
        // SQL query to create the table
        $sql = "CREATE TABLE $teams_table_name (
            id INT NOT NULL AUTO_INCREMENT,
            team varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Include the upgrade file for dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute the query
        dbDelta($sql);
    }

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$exclude_poules_table_name'") != $exclude_poules_table_name) {
        // SQL query to create the table
        $sql = "CREATE TABLE $exclude_poules_table_name (
            id INT NOT NULL AUTO_INCREMENT,
            poule varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Include the upgrade file for dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute the query
        dbDelta($sql);
    }
}


function upsert_row_into_wedstrijd_planner($data) {
	global $wpdb;

    // Define the table name (with WordPress prefix)
    $table_name = get_wedstrijd_planner_table_name();

    $primary_key = 'code';
    $primary_key_value = isset($data[$primary_key]) ? $data[$primary_key] : null;

    if ($primary_key_value) {
        // Check if the row already exists
        $existing_row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE $primary_key = %s",
            $primary_key_value
        ));

        if ($existing_row) {
            // Row exists, update only the specified fields
            $updatedCount = $wpdb->update($table_name, $data, array($primary_key => $primary_key_value));

            // Check for errors
            if ($wpdb->last_error) {
                error_log('Error updating row: ' . $wpdb->last_error);
            } else {
                error_log('Row updated successfully!');
            }

			return $updatedCount > 0;
        } else {
            // Row does not exist, insert a new row
            $wpdb->insert($table_name, $data);

            // Check for errors
            if ($wpdb->last_error) {
                error_log('Error inserting row: ' . $wpdb->last_error);
            } else {
                error_log('Row inserted successfully!');
            }

			return true;
        }
    } else {
        error_log('Primary key value is missing!');
    }

	return false;
}

function update_wedstrijden_database($wedstrijden) {
	$updatedWedstijden = [];
	foreach ($wedstrijden as $wedstrijd) {

		// Only set values that are not an empty string
		$wedstrijdcopy = $wedstrijd;
		foreach ($wedstrijd as $key => $val) {
			if ($val == '') {
				unset($wedstrijdcopy[$key]);
			}
		}

		$wedstrijdUpdated = upsert_row_into_wedstrijd_planner($wedstrijdcopy);

		if ($wedstrijdUpdated) {
			array_push($updatedWedstijden, $wedstrijd['code']);
		}
	}

	return $updatedWedstijden;
}

function remove_all_veranderd_statuses() {
    global $wpdb;

    // Define the table name (with WordPress prefix)
    $table_name = get_wedstrijd_planner_table_name();

    // Basic update query
    $wpdb->query("UPDATE $table_name SET veranderd = 0");
}

function fetch_database_wedstrijden($filter_date = null, $exclude_poules = null) {
	global $wpdb;

    // Define the table name (with WordPress prefix)
    $table_name = get_wedstrijd_planner_table_name();

    // Base query
    $query = "SELECT * FROM $table_name";
    
    // Initialize where clauses array
    $where_clauses = [];
    $query_params = [];

    // Add date filter if provided
    if ($filter_date !== null) {
        $where_clauses[] = "DATE(datum) = %s";
        $query_params[] = $filter_date;
    }
    
    // Add other filter if provided
    if ($exclude_poules !== null) {

        $exclude_poules = array_map(function($p) {
            return "'" . esc_sql($p) . "'";
        }, $exclude_poules);
        $exclude_poules = implode(',', $exclude_poules);

        $where_clauses[] = "poule NOT IN ($exclude_poules)";
    }
    
    // Combine where clauses if any exist
    if (!empty($where_clauses)) {
        $query .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    // Add ordering
    $query .= " ORDER BY datum";

    // Prepare and execute the query if we have parameters
    if (!empty($query_params)) {
        $query = $wpdb->prepare($query, $query_params);
    }

	// Query to select all rows
    $results = $wpdb->get_results($query);

	$wedstrijden = [];

    // Check if results exist
    if ($results) {
        foreach ($results as $row) {

			array_push($wedstrijden, 
		array(
					'code' => $row->code,
					'team_thuis' => $row->team_thuis, 
					'team_uit' => $row->team_uit, 
					'datum' => $row->datum, 
					'veld' => $row->veld,
					'regio' => $row->regio,
                    'poule' => $row->poule,
					'teller' => $row->teller,
					'scheidsrechter' => $row->scheidsrechter,
					'actief' => $row->actief,
					'veranderd' => $row->veranderd,
				));
        }
    } else {
        echo "No rows found.";
    }

	return $wedstrijden;
}

function get_wedstrijd_planner_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wedstrijd_planner';
}

function get_teams_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wedstrijd_planner_teams';
}

function get_exclude_poules_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wedstrijd_planner_exclude_poules';
}
