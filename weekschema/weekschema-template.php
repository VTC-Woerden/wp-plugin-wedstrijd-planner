<?php
/**
 * Template Name: weekschema page
 */

require_once(dirname(__FILE__) . '/../wedstrijddag/wedstrijddag.php');

if (isset($_GET['weeknummer'])) {
    $weeknummer = sanitize_text_field($_GET['weeknummer']);
} else {
    $weeknummer = date('W');
}

$exclude_poules = get_entries("poule", tableName: "wedstrijd_planner_exclude_poules");
$alleWedstrijden = fetch_database_wedstrijden(null, $exclude_poules);

$groupedByWeek = groupByWeek($alleWedstrijden);

if (!key_exists($weeknummer, $groupedByWeek)) {
    echo "Geen wedstrijden deze week.";
    return;
}

$dates = getDatesByWeek($groupedByWeek[$weeknummer]);

foreach($dates as $date) {
    RenderWedstrijddag($date);
}

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

