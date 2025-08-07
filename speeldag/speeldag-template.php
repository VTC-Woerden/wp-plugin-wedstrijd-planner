<?php
/**
 * Template Name: Speeldag page
 */

$date = '2025-09-20';

$wedstrijden = fetch_database_wedstrijden($date);

function get_wedstrijd_op_veld($wedstrijden, $veld) {
    return array_filter($wedstrijden, function($item) use ($veld) {
        return $item['veld'] == $veld;
    });
}

function render_wedstrijd($wedstrijden, $veld) {
    $wedstrijden_op_veld = get_wedstrijd_op_veld($wedstrijden, $veld);

    foreach ($wedstrijden_op_veld as $wedstrijd) {
        $datetime = new DateTime($wedstrijd["datum"]);

        ?>
            <div class="wedstrijd">
                <div class="team">
                    <span class='tijd'><?= $datetime->format("H:i") ?></span>
                    <span class='teams'><?= $wedstrijd["team_thuis"] ?> -  <?= $wedstrijd["team_uit"] ?></span>
                </div>
                <div class="taken">
                    <span>Fluiten <b><?= $wedstrijd['scheidsrechter'] ?></b></span> -
                    <span>Tellen <b><?= $wedstrijd['teller'] ?></b></span>
                </div>
            </div>
        <?php
    }
}

$hasH1Field = false;

foreach ($wedstrijden as $wedstrijd) {
    if (isset($wedstrijd['veld']) && strtolower($wedstrijd['veld']) === 'h1') {
        $hasH1Field = true;
        break;
    }
}

?>

<link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>" type="text/css" media="all" />

<div class="custom-plugin-content">
    <h1>
        <?php
            $formatter = new IntlDateFormatter('nl-NL', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
            $formatter->setPattern('EEEE d LLLL');
            echo $formatter->format(new DateTime($date));
        ?>
     </h1>
    <div class="velden">
        <div class="kantine">
            Kantine
        </div>
        <div class="veld">
            <h1>Veld 4</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "4");
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
            <h1>Veld 1</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "1");
                ?>
            </div>

            <?php if ($hasH1Field): ?>

                <h1>Veld H1</h1>
                <div>
                    <?php
                        render_wedstrijd($wedstrijden, "H1");
                    ?>
                </div>

            <?php endif; ?>
        </div>
        <div class="veld">
            <h1>Veld 2</h1>
            <div>
                <?php
                    render_wedstrijd($wedstrijden, "2");
                ?>
            </div>
        </div>
        <div class="tribune">
            Tribune
        </div>
    </div>
</div>