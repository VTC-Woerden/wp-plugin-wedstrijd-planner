<?php

require_once(dirname(__FILE__) . '/zaaltaken.php');

add_shortcode('zaaltaken', 'zaaltaken_shortcode');

function zaaltaken_shortcode() {
    ob_start(); // start a buffer
    RenderZaaltaken("team1");
    return ob_get_clean(); // get the buffer contents and clean it
}