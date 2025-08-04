let isSubmitting = false;
let formChanged = false;

document.querySelector(".plugin_container .wedstrijden").addEventListener("change", () => {
    formChanged = true;
    let wedstrijden = []

    document
        .querySelectorAll(".plugin_container .wedstrijden_tabel tbody tr")
        .forEach((row) => {
            let code = row.querySelector(".naam").getAttribute("title");
            let teller = row.querySelector(".teller select").value;
            let scheidsrechter = row.querySelector(".scheidsrechter select").value;

            wedstrijden.push({code, teller, scheidsrechter})
        })

    document.querySelector("#save_wedstrijden_form #wedstrijd_data").value = JSON.stringify(wedstrijden);

    update_sidebar_counters();
})

function update_sidebar_counters() {
    let allTeams = [...document.querySelectorAll(".plugin_container .wedstrijden_tabel tbody tr:last-of-type .teller option")]
                    .map(o => o.value)
                    .filter(o => o !== '');

    let teamsMetTaak = {};

    allTeams.forEach((team) => {
        teamsMetTaak[team] = 0;
    })

    console.log(teamsMetTaak)

    function incrementTeams(team) {
        if (!team) return;
        teamsMetTaak[team]++;
    }

    let rows = document.querySelectorAll(".plugin_container .wedstrijden_tabel tbody tr");

    rows.forEach((row) => {
        let teller = row.querySelector(".teller select").value;
        let scheidsrechter = row.querySelector(".scheidsrechter select").value;

        incrementTeams(teller);
        incrementTeams(scheidsrechter);
    })

    let teamEntries = Object.entries(teamsMetTaak);

    let sortedTeams = teamEntries.sort((a, b) => b[1] - a[1]);

    document.querySelector("#wpbody-content > div.wrap.plugin_container > div.sidebar.section > div:nth-child(1) > table > tbody").innerHTML = ""

    for (let team of sortedTeams) {

        document.querySelector("#wpbody-content > div.wrap.plugin_container > div.sidebar.section > div:nth-child(1) > table > tbody").insertAdjacentHTML('beforeend', `
            <tr class="team">
                <td class="team_naam">${team[0]}</td>
                <td class="counter">${team[1]}</td>
            </tr>
        `);
    }
}

update_sidebar_counters();

document.querySelector("#save_wedstrijden_form").addEventListener('submit', () => {
    isSubmitting = true;
})

window.addEventListener('beforeunload', function (e) {
    // Check if there are unsaved changes
    if (hasUnsavedChanges()) {
        // Cancel the event and show the confirmation dialog
        e.preventDefault();
        e.returnValue = ''; // Required for Chrome and other modern browsers
    }
});

function hasUnsavedChanges() {
    // Implement your logic to check for unsaved changes
    // For example, you might check if a form has been modified
    return formChanged && !isSubmitting; // Return true if there are unsaved changes, otherwise false
}