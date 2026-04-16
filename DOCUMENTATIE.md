# VTC Wedstrijd Planner — functionele documentatie

WordPress-plugin (**Plugin Name:** `VTC wedstrijd planner`, GitHub: [VTC-Woerden/wp-plugin-wedstrijd-planner](https://github.com/VTC-Woerden/wp-plugin-wedstrijd-planner)) voor het **inplannen van tellers en scheidsrechters** bij thuiswedstrijden. Wedstrijden worden uit een **Nevobo Excel-export** gehaald; toewijzingen worden in de **database** bewaard. Dit is een **andere** plugin dan de VTC Training Planner.

---

## Wat doet de plugin?

1. **Beheer (wp-admin)**  
   - Wedstrijden per **halfjaar** (jan–jun / jul–dec) tonen, gegroepeerd per **dag** en **veld**.  
   - Per wedstrijd **teller** en **scheidsrechter** kiezen uit dropdowns (teamnamen).  
   - Wijzigingen **opslaan** naar de database.  
   - **Vernieuwen**: toekomstige wedstrijden opnieuw uit Nevobo halen; bij gewijzigde records verschijnt een **indicator** (“rode bol”) tot je “Wijzigingen gezien” gebruikt.  
   - **Takenoverzicht** in de zijbalk: telt hoe vaak een team als teller/scheids is ingepland (via `scripts/sidebar.js`).

2. **Publieke pagina’s** (worden bij **activeren** aangemaakt, bij **deactiveren** weer verwijderd)  
   - **`/wedstrijddag/?datum=YYYY-MM-DD`** — overzicht per dag per veld (vaste layout: velden 4, 3, 1, 2, optioneel H1).  
   - **`/weekschema/?weeknummer=&jaar=&columns=`** — weekoverzicht (standaard huidige week/jaar, grid met meerdere dagen).  
   - **`/zaaltaken/?team=...`** — takenlijst voor één team (tellen/fluiten).  
   - Shortcode **`[zaaltaken]`** —zelfde als zaaltaken-pagina, mits `?team=` in de URL staat.

---

## Installatie en rechten

- Plugin-map in `wp-content/plugins/` plaatsen en activeren.  
- Menu **Wedstrijd Planner** en submenu **Settings** zijn alleen zichtbaar voor gebruikers met **`manage_options`**.  
- Vereisten: **PHP** met o.a. `intl` (`IntlDateFormatter`), **SimpleXLSX** (meegeleverd), WordPress-omgeving met `wp_remote_get` / `file_get_contents` naar Nevobo (afhankelijk van serverconfig).

---

## Nevobo-data (hardcoded)

In `wedstrijd-planner.php` wordt het programma opgehaald via:

`https://api.nevobo.nl/export/sporthal/WOEPO/programma.xlsx`

- Alleen regels met **toekomstdatum** (t.o.v. moment van vernieuwen) worden bij een ververs teruggeschreven.  
- Kolommen uit Excel (indices in code): o.a. datum `row[1]`, teams `row[2]`/`row[3]`, veld `row[5]`, regio `row[6]`, poule `row[7]`, wedstrijdcode `row[8]`.  
- **Andere sporthal/club:** pas de URL in de broncode aan (er is geen instellingenscherm voor de download-URL).

---

## Admin: hoofdpagina “Wedstrijd Planner”

| Onderdeel | Werking |
|-----------|---------|
| **Seizoenen** | Links per halfjaar (`YYYY-1` of `YYYY-2`), via query `?season=`. |
| **Legenda** | Icoon = wedstrijd gewijzigd t.o.v. vorige Nevobo-sync (`veranderd`). |
| **Wijzigingen gezien** | Zet `veranderd` voor alle rijen terug naar 0. |
| **Vernieuw wedstrijden** | Haalt XLSX, filtert toekomst, upsert database, zet `veranderd` op gewijzigde codes; toont Thickbox met meldingen indien er wijzigingen zijn. |
| **Opslaan** | POST met JSON van alle rijen: `code`, `teller`, `scheidsrechter` (nonce `save_wedstrijden_nonce`). |

Als er **nog geen** wedstrijden in de database staan, zie je alleen een knop om de eerste import te starten.

---

## Admin: Settings (submenu)

Vier losse formulieren (comma- of regeleindes gescheiden waarden worden genormaliseerd):

| Instelling | Tabel | Effect |
|------------|-------|--------|
| **Teams** | `{prefix}wedstrijd_planner_teams` | Teamnamen in dropdowns + zijbalk (samen met teams die al in wedstrijden voorkomen). |
| **Gefilterde wedstrijdcodes** | `..._exclude_poules` | **Poulenamen** (niet “codes” ondanks label): wedstrijden met deze poule worden **niet** getoond in planner/weekschema-queries. |
| **Teams met tweedescheidsrechter** | `..._second_referee` | Thuisteams in deze lijst krijgen label “Tweede scheidsrechter” / “Fluiten (2ᵉ)”. |
| **Teams met alleen tellers** | `..._teller_only` | Voor deze thuisteams wordt géén scheidsrechter-kolom getoond in de admin-tabel. |

Bij opslaan wordt de betreffende tabel **geleegd** en opnieuw gevuld.

---

## Database-tabellen (`$wpdb->prefix`)

| Tabel | Inhoud |
|-------|--------|
| `wedstrijd_planner` | Wedstrijden: `code` (PK), teams, `datum`, veld, regio, poule, `teller`, `scheidsrechter`, `actief`, `veranderd`. |
| `wedstrijd_planner_teams` | `id`, `team` |
| `wedstrijd_planner_exclude_poules` | `id`, `poule` |
| `wedstrijd_planner_second_referee` | `id`, `team` (thuisteam) |
| `wedstrijd_planner_teller_only` | `id`, `team` (thuisteam) |

> **Technisch:** in `database.php` staat bij de aanmaak van `wedstrijd_planner_second_referee` en `wedstrijd_planner_teller_only` per ongeluk `$se` i.p.v. de tabelnaam in `SHOW TABLES LIKE ...`. Controleer na activatie of beide tabellen bestaan; zo niet, handmatig aanmaken of bugfix toepassen.

---

## Publieke URL’s en parameters

### Wedstrijddag (`wedstrijddag-template.php`)

- **Verplicht:** `?datum=YYYY-MM-DD` (anders melding: geef datum mee).  
- Toont wedstrijden voor die dag, gegroepeerd op **veld** `"4"`, `"3"`, `"1"`, `"2"` en optioneel **`H1`** (alleen als er die dag een wedstrijd op veld H1 staat).

### Weekschema (`weekschema-template.php`)

- **`weeknummer`** — ISO-weeknummer (default: huidige week).  
- **`jaar`** — kalenderjaar voor filter op `fetch_database_wedstrijden(..., $year)` (default: huidig jaar).  
- **`columns`** — aantal kolommen in CSS-grid (default `2`).  
- Logo-URL in template is **hardcoded** naar `vtcwoerden.nl`; voor andere sites aanpassen.

### Zaaltaken (`zaaltaken-template.php` + shortcode)

- **Verplicht:** `?team=Teamnaam` (spaties via URL encoding). In de template wordt `team` met `urldecode` + `sanitize_text_field` gelezen.  
- Shortcode **`[zaaltaken]`**: verwacht eveneens `?team=` in de request.  
- **`?print`** op de zaaltaken-URL triggert `window.print()` (via `zaaltaken.php`).  
- Toont alleen taken in het **huidige halfjaar** (`get_current_half_year()`). Teamnaam in DB wordt vergeleken met **streepjes → spaties** vervangen in de query-string.

---

## Assets

- **Admin:** `css/style.css`, `scripts/sidebar.js` (tellers/scheids verzamelen, teller in zijbalk, waarschuwing bij niet-opgeslagen wijzigingen).  
- **Frontend:** per module `wedstrijddag/style.css`, `zaaltaken/style.css`, `weekschema/style.css`.

---

## CI / deployment

In de repository staat onder `.github/workflows/` een **deploy-workflow** (pad in repo: `deploy.yaml`); inhoud niet in dit document gedetailleerd — raadpleeg het YAML-bestand voor triggers en doelomgeving.

---

## Relatie tot andere plugins

De **VTC Training Planner** (`vtc-training-planner`) is een apart product: trainingsrooster, blauwdruk, Nevobo RSS-programma in weekoverzicht, REST API, enz. Deze **Wedstrijd planner** richt zich op **Excel-programma sporthal + teller/scheids-toewijzing** en vaste **frontend-templates** voor zaal/veld.
