<?php
/**
 * Template Name: zaaltakenlijst page
 */

require_once(dirname(__FILE__) . '/zaaltaken.php');

if (!isset($_GET['team'])) {
    echo "Geef een team mee in de query parameter";
    return;
}

?>

<div class="zaaltaken-template">

    <?php
        $team = urldecode(sanitize_text_field($_GET['team']));
    ?>
        <h1>
            Zaaltaken <?= esc_html($team) ?>
        </h1>
    <?php
        RenderZaaltaken($team);
    ?>

</div>

<?php

