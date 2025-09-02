<?php

function get_wedstrijd_op_veld($wedstrijden, $veld) {
    return array_filter($wedstrijden, function($item) use ($veld) {
        return $item['veld'] == $veld;
    });
}

function render_wedstrijd($wedstrijden, $veld) {
    $wedstrijden_op_veld = get_wedstrijd_op_veld($wedstrijden, $veld);

    $teams_with_second_referees = get_entries("team", "wedstrijd_planner_second_referee");
    $teams_with_teller_only = get_entries("team", "wedstrijd_planner_teller_only");



    foreach ($wedstrijden_op_veld as $wedstrijd) {
        $datetime = new DateTime($wedstrijd["datum"]);

        $second_referee = false;
        if (in_array($wedstrijd["team_thuis"], $teams_with_second_referees)) {
            $second_referee = true;
        }

        $teller_only = false;
        if (in_array($wedstrijd["team_thuis"], $teams_with_teller_only)) {
            $teller_only = true;
        }

        ?>
            <div class="wedstrijd">
                <div class="team">
                    <span class='tijd'><?= $datetime->format("H:i") ?></span>
                    <span class='teams'><?= $wedstrijd["team_thuis"] ?> -  <?= $wedstrijd["team_uit"] ?></span>
                </div>
                <div class="taken">
                    <?php if (!$teller_only) : ?>
                        <span><?= $second_referee ? "Fluiten (2ᵉ)" : "Fluiten" ?> <b><?= $wedstrijd['scheidsrechter'] ?></b></span> -
                    <?php endif; ?>
                    <span>Tellen <b><?= $wedstrijd['teller'] ?></b></span>
                </div>
            </div>
        <?php
    }
}


function RenderWedstrijddag($date) {


    $wedstrijden = fetch_database_wedstrijden($date);



    $hasH1Field = false;

    foreach ($wedstrijden as $wedstrijd) {
        if (isset($wedstrijd['veld']) && strtolower($wedstrijd['veld']) === 'h1') {
            $hasH1Field = true;
            break;
        }
    }

    ?>

    <link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>" type="text/css" media="all" />
        <div class="wedstrijddag">
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
<?php
}