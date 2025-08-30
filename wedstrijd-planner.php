<?php

/*
 * Plugin Name: VTC wedstrijd planner
 */


add_action('admin_menu', 'wedstrijd_planner_plugin_setup_menu');
 
function wedstrijd_planner_plugin_setup_menu(){
	add_menu_page( 'Wedstrijd planner Plugin Page', 'Wedstrijd Planner', 'manage_options', 'wedstrijd-planner-plugin', 'wedstrijd_planner_init', 'dashicons-list-view');
}
 
require_once(dirname(__FILE__) . '/settings-page.php');
require_once(dirname(__FILE__) . '/render.php');
require_once(dirname(__FILE__) . '/handlers.php');
require_once(dirname(__FILE__) . '/database.php');
require_once(dirname(__FILE__) . '/wedstrijddag/wedstrijddag-functions.php');
require_once(dirname(__FILE__) . '/zaaltaken/zaaltaken-functions.php');

register_activation_hook(__FILE__, 'activate_wedstrijd_planner');

// Register styles
function wedstrijd_planner_admin_files() {
    // Replace 'my-plugin' with your plugin's unique handle
    wp_enqueue_style(
        'wedstrijd-planner-table-style',
        plugins_url('css/style.css', __FILE__), // Path to your CSS file
        array(), // Dependencies (if any)
        '1.0.0' // Version number
    );

	wp_enqueue_script(
		'wedstrijd-planner-admin-script',
		plugins_url('scripts/sidebar.js', __FILE__),
		array('jquery'),
		'1.0.0',
		true
	);
}
add_action('admin_enqueue_scripts', 'wedstrijd_planner_admin_files');

// wedstrijddag template
register_activation_hook(__FILE__, 'create_wedstrijddag_page');
register_deactivation_hook(__FILE__, 'disable_wedstrijddag_page');
add_filter('page_template', 'load_wedstrijddag_plugin_template');

// zaaltaken template
register_activation_hook(__FILE__, 'create_zaaltaken_page');
register_deactivation_hook(__FILE__, 'disable_zaaltaken_page');
add_filter('page_template', 'load_zaaltaken_plugin_template');

function fetch_wedstrijden() {
	require_once(dirname(__FILE__) . '/SimpleXLSX.php');

	$excelBinaryData = file_get_contents("https://api.nevobo.nl/export/sporthal/WOEPO/programma.xlsx");

	$xlsx = Shuchkin\SimpleXLSX::parseData( $excelBinaryData, $debug = false );

	$rows = $xlsx->rows($worksheetIndex = 0, $limit = 0);

	$wedstrijden = [];

	foreach ($rows as  $key => $row) {
		if ($key == 0) {
			// First row is the heading
			continue;
		}
		array_push($wedstrijden, 
	array(
				'code' => $row[8],
				'team_thuis' => $row[2], 
				'team_uit' => $row[3],
				'datum' => $row[1], 
				'veld' => $row[5],
				'regio' => $row[6],
				'poule' => $row[7]
			));
	}

	return $wedstrijden;
}

function group_by_dynamic_half_year(array $items) {
    $grouped = [];
    
    foreach ($items as $item) {

		$item = (array)$item;

        $date = new DateTime($item["datum"]);
        $year = (int)$date->format('Y');
        $month = (int)$date->format('n');
        
        // Determine half-year period (1 = Jan-Jun, 2 = Jul-Dec)
        $halfYear = ($month <= 6) ? 1 : 2;
        
        // Create period identifier
        $periodId = $year ."-" . $halfYear; // Creates a unique numeric ID
        
        $grouped[$periodId][] = $item;
    }
    
    // Sort periods chronologically
    ksort($grouped);
    
    return $grouped;
}

function get_current_half_year(): string {
    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');
    
    $halfYear = ($currentMonth <= 6) ? 1 : 2;
    
    return $currentYear . "-" . $halfYear;
}


function wedstrijd_planner_init(){

	add_thickbox();

	handle_save_wedstrijden();
	handle_vernieuw_wedstrijden();
	handle_verwijder_rode_bolletjes();

	$exclude_poules = get_entries("poule", tableName: "wedstrijd_planner_exclude_poules");
	$teams_with_second_referees = get_entries("team", "wedstrijd_planner_second_referee");
	$teams_with_teller_only = get_entries("team", "wedstrijd_planner_teller_only");

	$alleWedstrijden = fetch_database_wedstrijden(null, $exclude_poules);

	if (count($alleWedstrijden) == 0) {
		?>
			<h3>Geen wedstrijden gevonden. Klik hieronder om wedstrijden op te halen.</h3>
			<form method="POST" id="vernieuw_wedstrijden_form">
				<?php wp_nonce_field(-1, 'vernieuw_wedstrijden_nonce') ?>
				<input type="submit" name="vernieuw_wedstrijden" class="button button-primary" value="Haal wedstrijden op"/>
			</form>
		<?php

		return;
	}

	$wedstrijdenSeizoenen = group_by_dynamic_half_year($alleWedstrijden);

	$activeSeason = $_GET['season'] ?? get_current_half_year($wedstrijdenSeizoenen);

	$wedstrijden = $wedstrijdenSeizoenen[$activeSeason];

	echo '<div class="wrap plugin_container">';

	render_tabel($wedstrijden, $wedstrijdenSeizoenen, $teams_with_second_referees, $teams_with_teller_only);

	render_sidebar($wedstrijden);

	echo '</div>';
}


