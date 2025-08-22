<?php
/**
 * Template Name: Teamtakenlijst page
 */

$taken = fetch_database_wedstrijden_for_team("team1");

?>

<?php foreach($taken as $taak ): ?>
    <div><?= $taak->team_thuis; ?> - <?= $taak->team_uit; ?></div>
<?php endforeach; ?>