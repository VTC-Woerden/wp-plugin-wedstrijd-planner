<?php

require_once(dirname(__FILE__) . '/zaaltaken.php');

add_shortcode('zaaltaken', 'zaaltaken_shortcode');

function zaaltaken_shortcode() {
    if (!isset($_GET['team'])) {
        echo "Kan team niet vinden.";
        return;
    }

    $team = sanitize_text_field($_GET["team"]);

    ob_start(); // start a buffer
    RenderZaaltaken($team);
    return ob_get_clean(); // get the buffer contents and clean it
}