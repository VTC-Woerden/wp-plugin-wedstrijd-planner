<?php

function render_wedstrijden($wedstrijden, $wedstrijd_op_veld, $teams_with_second_referees, $teams_with_teller_only) {
	$row = "";

	$teams = get_all_teams($wedstrijden);

	foreach ($wedstrijd_op_veld as $key => $value) {
		$teller = render_teller($teams, $value['teller']);
		$scheidsrechter = render_scheidsrechter($teams, $value['scheidsrechter']);

		$second_referee = false;
		if (in_array($value["team_thuis"], $teams_with_second_referees)) {
			$second_referee = true;
		}

		$teller_only = false;
		if (in_array($value["team_thuis"], $teams_with_teller_only)) {
			$teller_only = true;
		}

		?>
			<tr>
				<td width="10px" class="veranderd">
					<?php
						if ($value['veranderd'] == "1"):
							?>
								<span class="icon-veranderd"></span>
							<?php
						endif;
					?>
				</td>
				<td width="30%" class="naam" title="<?= $value['code'] ?>"><?=$value['team_thuis'] ?> - <?= $value['team_uit']?></td>
				<td width="30px" class="datum"><?=(new DateTime($value['datum']))->format('G:i')?></td>
				<td width="130px" class="teller"><?= $teller ?></td>
				<?php if (!$teller_only) : ?>
					<td width="130px" class="scheidsrechter"><?= $second_referee ? "Tweede scheidsrechter<br>" : null ?><?= $scheidsrechter ?></td>
				<?php else: ?>
					<td width="130px"></td>
				<?php endif; ?>
			</tr>

		<?php
	}

	return $row;
}

function get_unique_teams_from_wedstrijden($wedstrijden) {
	$teams = [];
	foreach ($wedstrijden as $key => $wedstrijd) {
		if ($wedstrijd['teller']) {
			array_push($teams, $wedstrijd['teller']);
		}

		if ($wedstrijd['scheidsrechter']) {
			array_push($teams, $wedstrijd['scheidsrechter']);
		}
	}
	return array_unique($teams);
}

function get_all_teams($wedstrijden){
	$wedstrijdenTeams = get_unique_teams_from_wedstrijden($wedstrijden);

	$databaseTeams = get_entries("team", "wedstrijd_planner_teams");

	return array_unique(array_merge($databaseTeams, $wedstrijdenTeams));
}

function render_tabel($wedstrijden, $wedstrijdenSeizoenen, $teams_with_second_referees, $teams_with_teller_only) {

	$groupedData = array_reduce($wedstrijden, function ($result, $item) {
		$date = new DateTime($item['datum']);
		$formattedDate = $date->format('Y-m-d');
		
		if (!isset($result[$formattedDate])) {
			$result[$formattedDate] = [];
		}
		
		$result[$formattedDate][] = $item;
		
		return $result;
	}, []);
	?>

	<div class="wedstrijden">

		<?= render_header($wedstrijdenSeizoenen); ?>

		<div class="section">
			<?php foreach ($groupedData as $datum => $wedstrijd_dagen): 
				
				$groupedDataField = array_reduce($wedstrijd_dagen, function ($result, $item) {
					$veld = $item['veld'];
					
					if (!isset($result[$veld])) {
						$result[$veld] = [];
					}
					
					$result[$veld][] = $item;
					
					return $result;
				}, []);

				ksort($groupedDataField);

				$firstWestrijd = true;

			?>
				<div class="wedstrijd_dag">
					<h1>
						<?php
							$formatter = new IntlDateFormatter('nl-NL', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
							$formatter->setPattern('EEEE d LLLL yyyy');
							echo $formatter->format(new DateTime($datum));
						?>
					</h1>

					<?php foreach ($groupedDataField as $veld => $wedstrijd_op_veld): ?>
						<div class="wedstrijd_veld">
							<div>Veld <?= $veld ?></div>
							<table class="wedstrijden_tabel widefat striped fixed">
								<?php if ($firstWestrijd): $firstWestrijd = false; ?>
									<thead>
										<tr>
											<th width="10px" class="veranderd"></th>
											<th width="30%" class="wedstrijd">Wedstrijd</th>
											<th width="30px" class="datum">Tijd</th>
											<th width="130px" class="teller">Teller</th>
											<th width="130px" class="scheidsrechter">Scheidsrechter</th>
										</tr>
									</thead>
								<?php endif ?>
								<tbody>
						
									<?= render_wedstrijden($wedstrijden, $wedstrijd_op_veld, $teams_with_second_referees, $teams_with_teller_only) ?>
								
								</tbody>
							</table>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function render_sidebar($wedstrijden) {
	if (!$wedstrijden) {
		$wedstrijden = [];
	}
	?>
		<div class="sidebar-filler">

		</div>
		<div class="sidebar section">
			<div>
				<h3>Takenoverzicht</h3>

				<table class="taken widefat striped fixed">
					<?php foreach(get_all_teams($wedstrijden) as $key=>$value): ?>
						<tr class="team">
							<td class="team_naam"><?= $value ?></td>
							<td class="counter">0</td>
						</tr>
					<?php endforeach; ?>
				</table>

			</div>

			<form method="POST" id="save_wedstrijden_form">
				<?php wp_nonce_field(-1, 'save_wedstrijden_nonce') ?>
				<input type="hidden" id="wedstrijd_data" name="wedstrijd_data">
				<input type="submit" name="save_wedstrijden" class="button button-primary" value="Opslaan"/>
			</form>
		</div>
	<?php
}

function render_teller($tellers, $teller) {
	$tellersOptions = "";

	if ($teller == NULL) {
		$tellersOptions .= "<option selected></option>";
	} else {
		$tellersOptions .= "<option></option>";
	}

	foreach ($tellers as $key => $value) {
		if ($value == $teller) {
			$tellersOptions .= "<option value='$value' selected>$value</option>";
		} else {
			$tellersOptions .= "<option value='$value' >$value</option>";
		}
	}

	$tellersSelect = <<<HTML
	<select>
		$tellersOptions
	</select>
HTML;

	return $tellersSelect;
}

function render_scheidsrechter($scheidsrechters, $scheidsrechter) {
	$scheidsrechtersOptions = "";

	if ($scheidsrechter == NULL) {
		$scheidsrechtersOptions .= "<option selected></option>";
	} else {
		$scheidsrechtersOptions .= "<option></option>";
	}

	foreach ($scheidsrechters as $key => $value) {
		if ($value == $scheidsrechter) {
			$scheidsrechtersOptions .= "<option value='$value' selected>$value</option>";
		} else {
			$scheidsrechtersOptions .= "<option value='$value' >$value</option>";
		}
	}

	$scheidsrectersSelect = <<<HTML
	<select>
		$scheidsrechtersOptions
	</select>
HTML;

	return $scheidsrectersSelect;
}

function render_header($wedstrijdenSeizoenen) {
	?>
		<div class="section">
			<h1>Wedstrijd planner</h1>

			<h3>Seizoenen</h3>

			<?php foreach(array_keys($wedstrijdenSeizoenen) as $season): ?>
				<a href="<?= $_SERVER['REQUEST_URI'].'&season='. $season ?>"><?= $season ?></a>
			<?php endforeach; ?>

			

			<div class="legenda">
				<h3>Legenda</h3>
				<div>
					<span class="icon-veranderd"></span> = De wedstrijd heeft een wijziging gehad
				</div>

				<div class="flex-spread">
					<form method="POST" id="verwijder_rode_bolletjes">
						<?php wp_nonce_field(-1, 'verwijder_rode_bolletjes_nonce') ?>
						<input type="submit" name="verwijder_rode_bolletjes" class="button button-primary" value="Wijzigingen gezien"/>
					</form>
					<form method="POST" id="vernieuw_wedstrijden_form">
						<?php wp_nonce_field(-1, 'vernieuw_wedstrijden_nonce') ?>
						<input type="submit" name="vernieuw_wedstrijden" class="button button-primary" value="Venieuw wedstrijden"/>
					</form>
				</div>
			</div>
		</div>
	<?php
}
	