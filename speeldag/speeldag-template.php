<?php
/**
 * Template Name: Speeldag page
 */

$wedstrijden = fetch_database_wedstrijden('2025-03-08');

function get_wedstrijd_op_veld($wedstrijden, $veld) {
    return array_filter($wedstrijden, function($item) use ($veld) {
        return $item['veld'] == $veld;
    });
}

function get_wedstrijden_op_ander_veld($wedstrijden) {
    $velden = ['1', '2', '3', '4'];
    return array_filter($wedstrijden, function($item) use ($velden)  {
        return !in_array($item['veld'], $velden);
    });
}

function render_wedstrijd($wedstrijden, $veld) {
    $wedstrijden_op_veld = get_wedstrijd_op_veld($wedstrijden, $veld);
    $wedstrijden_op_ander_veld = get_wedstrijden_op_ander_veld($wedstrijden);

    foreach ($wedstrijden_op_veld as $wedstrijd) {
        $datetime = new DateTime($wedstrijd["datum"]);
        echo "<span class='tijd'>" . $datetime->format("H:i") . "</span><span class='teams'>" . $wedstrijd["team_thuis"] . " - " . $wedstrijd["team_uit"] . "</span>";
        echo "<br>";
    }

    if ($veld == '1') {
        foreach ($wedstrijden_op_ander_veld as $wedstrijd) {
            $datetime = new DateTime($wedstrijd["datum"]);
            echo "<span class='tijd'>" . $datetime->format("H:i") . " ".$wedstrijd["veld"]."</span><span class='teams'>" . $wedstrijd["team_thuis"] . " - " . $wedstrijd["team_uit"] . "</span>";
            echo "<br>";
        }
    }
}

?>

<link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>" type="text/css" media="all" />

<div class="custom-plugin-content">
    
    <div class="velden">
        <div class="kantine">
            Kantine
        </div>
        <div class="veld">
            <h1>Veld 1</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "1");
                ?>
            </div>
        </div>
        <div class="veld">
            <h1>Veld 2</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "2");
                ?>
            </div>
        </div>
        <div class="veld">
            <h1>Veld 3</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "3");
                ?>
            </div>
        </div>
        <div class="veld">
            <h1>Veld 4</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "4");
                ?>
            </div>
        </div>
        <div class="tribune">
            Tribune
        </div>
    </div>
</div>