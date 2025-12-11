<?php
/**
 * Template Name: weekschema page
 */

require_once(dirname(__FILE__) . '/../wedstrijddag/wedstrijddag.php');

?>
    <link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>" type="text/css" media="all" />
<?php

if (isset($_GET['weeknummer'])) {
    $weeknummer = sanitize_text_field($_GET['weeknummer']);
} else {
    $weeknummer = date('W');
}

if (isset($_GET['columns'])) {
    $columns = sanitize_text_field($_GET['columns']);
} else {
    $columns = "2";
}

$exclude_poules = get_entries("poule", tableName: "wedstrijd_planner_exclude_poules");
$alleWedstrijden = fetch_database_wedstrijden(null, $exclude_poules);

$groupedByWeek = groupByWeek($alleWedstrijden);

if (!key_exists($weeknummer, $groupedByWeek)) {
    echo "Geen wedstrijden deze week.";
    return;
}

$dates = getDatesByWeek($groupedByWeek[$weeknummer]);

?>
    <img class="logo" src="https://vtcwoerden.nl/wp-content/uploads/2022/09/VTC-logo-nieuw-blauw.jpg" alt="VTC Woerden">
    <div class="weekschema" <?= "style='grid-template-columns: repeat($columns, 1fr)'" ?>>
        <?php foreach($dates as $date): ?>
            <div class="weekschema-container">
                <?= RenderWedstrijddag($date); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php

function getDatesByWeek($groupedByWeek){
    $uniqueDates = [];
    foreach ($groupedByWeek as $weekKey => &$weekData) {
        $dateOnly = (new DateTime($weekData["datum"]))->format("Y-m-d");
        $uniqueDates[$dateOnly] = true; // Use date as key for uniqueness
    }
    return array_keys($uniqueDates);
}

function groupByWeek($objects) {
    $grouped = [];
    
    foreach ($objects as $object) {
        if (!isset($object["datum"])) {
            continue;
        }
        
        $date = new DateTime($object["datum"]);
        $weekNumber = $date->format('W');
        
        if (!isset($grouped[$weekNumber])) {
            $grouped[$weekNumber] = [];
        }
        
        array_push($grouped[$weekNumber], $object);
    }

    return $grouped;
}

