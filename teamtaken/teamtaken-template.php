<?php
/**
 * Template Name: Teamtakenlijst page
 */

$team = $_GET['team'];

$taken = (array)fetch_database_wedstrijden_for_team($team);

$taakSeizoenen = group_by_dynamic_half_year(((array)$taken));

$teams_with_second_referees = get_entries("team", "wedstrijd_planner_second_referee");

?>

<link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>" type="text/css" media="all" />

<div class="zaaltaken">
    <h1>
        Zaaltaken <?= $team ?>
    </h1>

    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Taak</th>
            </tr>
        </thead>
        <tbody>

            <?php 
                foreach($taakSeizoenen[get_current_half_year()] as $taak):

                    $second_referee = false;
                    if (in_array($taak["team_thuis"], $teams_with_second_referees)) {
                        $second_referee = true;
                    }

                    $moetTellen = $taak["teller"] == $team ? true : false;
                    $moetScheidsen = $taak["scheidsrechter"] == $team ? true : false;

                    $formatter = new IntlDateFormatter('nl-NL', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
                    $formatter->setPattern('EEEE d LLLL');
                    $datum = $formatter->format(new DateTime($taak["datum"]));

                    $tijd = (new DateTime($taak["datum"]))->format("H:i");

                    if ($moetTellen) {
                        ?>
                            <tr>
                                <td><div><?= $datum ?></div></td>
                                <td><div><?= $tijd ?></div></td>
                                <td><div>Tellen bij <?= $taak["team_thuis"] ?></div></td>
                            </tr>
                        <?php
                    }

                    if ($moetScheidsen) {
                        ?>
                            <tr>
                                <td><div><?= $datum ?></div></td>
                                <td><div><?= $tijd ?></div></td>
                                <td><div><?= $second_referee ? "Tweede scheids" : "Fluiten" ?> bij <?= $taak["team_thuis"] ?></div></td>
                            </tr>
                        <?php
                    }
            ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>