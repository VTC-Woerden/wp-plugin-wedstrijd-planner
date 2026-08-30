<?php

function handle_save_wedstrijden() {
	if(isset($_POST['wedstrijd_data']) && wp_verify_nonce($_POST['save_wedstrijden_nonce'])) {
		if ($_POST['wedstrijd_data'] != null) {
			$json_wedstrijd_data = json_decode(str_replace('\\', '', $_POST['wedstrijd_data']), true);
			update_wedstrijden_database($json_wedstrijd_data);
			echo '<div class="notice notice-success is-dismissible"><p>Opgeslagen</p></div>';
		}
	}
}

function handle_verwijder_rode_bolletjes() {
	if(isset($_POST['verwijder_rode_bolletjes']) && wp_verify_nonce($_POST['verwijder_rode_bolletjes_nonce'])) {
		remove_all_veranderd_statuses();
	}
}

function handle_vernieuw_wedstrijden() {
	if(isset($_POST['vernieuw_wedstrijden_nonce']) && wp_verify_nonce($_POST['vernieuw_wedstrijden_nonce'])) {
		$nieuweWedstrijden = fetch_wedstrijden();

		$nieuweWedstrijdenInDeToekomst = array_filter($nieuweWedstrijden, function($wedstrijd) {
			return new DateTime($wedstrijd["datum"]) > new DateTime();
		});

		$updatedWedstrijden = update_wedstrijden_database($nieuweWedstrijdenInDeToekomst);

		// Set the veranderd value to true
		$wedstrijdenToUpdate = [];

		foreach($updatedWedstrijden as $value) {
			array_push($wedstrijdenToUpdate, 
				array(
					'code' => $value,
					'veranderd' => true,
				));
		}

		update_wedstrijden_database($wedstrijdenToUpdate);

		?>
			<a href="#TB_inline?width=600&height=550&inlineId=modal-window-id" class="thickbox" style="display: none;">Modal Me</a>

			<div id="modal-window-id" style="display: none;">
				<?php foreach($updatedWedstrijden as $wedstrijd):
					$index = findIndexByKeyValue($nieuweWedstrijdenInDeToekomst, 'code', $wedstrijd);
				?>
					<p>
						<?= $nieuweWedstrijdenInDeToekomst[$index]['team_thuis']." - ".$nieuweWedstrijdenInDeToekomst[$index]['team_uit']." ".$nieuweWedstrijdenInDeToekomst[$index]['datum'] ?> is veranderd
					</p>
				<?php endforeach; ?>
			</div>

			

		<?php if (count($updatedWedstrijden) > 0): ?>
			<script>
				window.onload = () => {
					document.querySelector('.thickbox').click();
				}
			</script>
		<?php endif;
	}
}

function findIndexByKeyValue($array, $key, $value) {
    foreach ($array as $index => $object) {
        if ($object[$key] === $value) {
            return $index;
        }
    }
    return -1; // Return -1 if the object is not found
}

function handle_export_excel($wedstrijden = null) {
	if(!isset($_POST['export_excel']) || !wp_verify_nonce($_POST['export_excel_nonce'])) {
		return;
	}

	require_once(dirname(__FILE__) . '/SimpleXLSXGen.php');

	// If wedstrijden not provided, fetch them
	if ($wedstrijden === null) {
		require_once(dirname(__FILE__) . '/database.php');
		$exclude_poules = get_entries("poule", tableName: "wedstrijd_planner_exclude_poules");
		$alleWedstrijden = fetch_database_wedstrijden(null, $exclude_poules);
		$wedstrijdenSeizoenen = group_by_dynamic_half_year($alleWedstrijden);
		$activeSeason = $_GET['season'] ?? get_current_half_year($wedstrijdenSeizoenen);
		$wedstrijden = $wedstrijdenSeizoenen[$activeSeason];
	}

	// Convert wedstrijden array to Excel format
	$data = [
		['Team Thuis', 'Team Uit', 'Datum', 'Veld', 'Teller', 'Scheidsrechter'],
	];

	foreach ($wedstrijden as $wedstrijd) {
		$data[] = [
			$wedstrijd['team_thuis'],
			$wedstrijd['team_uit'],
			$wedstrijd['datum'],
			$wedstrijd['veld'],
			$wedstrijd['teller'] ?? '',
			$wedstrijd['scheidsrechter'] ?? '',
		];
	}

	$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);

	// Set headers for file download
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="wedstrijden-export.xlsx"');

	// Make sure warnings/notices don't get mixed into the XLSX
	while (ob_get_level()) {
		ob_end_clean();
	}

	echo (string) $xlsx;
	exit;
}