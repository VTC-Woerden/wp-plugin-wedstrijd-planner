<?php
/**
 * Template Name: weekschema page
 */

require_once(dirname(__FILE__) . '/../wedstrijddag/wedstrijddag.php');

if (!isset($_GET['datum'])) {
    echo "Geef een datum mee in de query parameter";
    return;
}

$date = sanitize_text_field($_GET['datum']);

RenderWedstrijddag($date);
RenderWedstrijddag($date);

