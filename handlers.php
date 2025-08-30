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