<?php
/**
 * Template Name: zaaltakenlijst page
 */

require_once(dirname(__FILE__) . '/zaaltaken.php');

if (!isset($_GET['team'])) {
    echo "Geef een team mee in de query parameter";
    return;
}

$team = urldecode(sanitize_text_field($_GET['team']));

RenderZaaltaken($team);